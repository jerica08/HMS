<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\PrescriptionService;
use App\Services\ResourceService;
use App\Services\FinancialService;
use App\Libraries\PermissionManager;

class PrescriptionManagement extends BaseController
{
    protected $prescriptionService;
    protected $permissionManager;
    protected $financialService;
    protected $userRole;
    protected $staffId;

    public function __construct()
    {
        $this->prescriptionService = new PrescriptionService();
        $this->permissionManager = new PermissionManager();
        $this->financialService = new FinancialService();
        
        // Get user role and staff_id from session
        $session = session();
        $this->userRole = $session->get('role');
        $this->staffId = $session->get('staff_id');
    }

    /**
     * Add a prescription to a patient's billing account
     */
    public function addToBilling($id)
    {
        // Only allow specific roles to bill prescriptions
        if (!in_array($this->userRole, ['admin', 'accountant', 'pharmacist'])) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'You are not allowed to add prescriptions to billing.'
            ]);
        }

        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'message' => 'Invalid request method.'
            ]);
        }

        try {
            $payload = $this->request->getJSON(true) ?? $this->request->getPost();

            $unitPrice = isset($payload['unit_price']) ? (float)$payload['unit_price'] : 0.0;
            $quantity  = isset($payload['quantity']) ? (int)$payload['quantity'] : null;

            if ($unitPrice <= 0) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Unit price must be greater than zero.'
                ]);
            }

            // Load prescription to get patient_id and validate existence
            $prescription = $this->prescriptionService->getPrescription($id);
            if (!$prescription) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Prescription not found.'
                ]);
            }

            // Only allow completed or dispensed prescriptions to be billed
            $status = strtolower($prescription['status'] ?? '');
            if (!in_array($status, ['completed', 'dispensed'])) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Only completed or dispensed prescriptions can be added to billing.'
                ]);
            }

            $patientId = (int)($prescription['patient_id'] ?? $prescription['pat_id'] ?? 0);
            if ($patientId <= 0) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Prescription is not linked to a valid patient.'
                ]);
            }

            // Ensure the patient has a billing account
            $account = $this->financialService->getOrCreateBillingAccountForPatient($patientId, null, (int)$this->staffId);
            if (!$account || empty($account['billing_id'])) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Unable to create or load billing account for this patient.'
                ]);
            }

            $billingId = (int)$account['billing_id'];

            // Add item from prescription
            $result = $this->financialService->addItemFromPrescription($billingId, (int)$id, $unitPrice, $quantity, (int)$this->staffId);

            $statusCode = !empty($result['success']) ? 200 : 500;

            return $this->response->setStatusCode($statusCode)->setJSON([
                'success' => !empty($result['success']),
                'message' => $result['message'] ?? 'Unable to add prescription to billing.'
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'PrescriptionManagement::addToBilling error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'An unexpected error occurred while adding prescription to billing.'
            ]);
        }
    }

    /**
     * Main prescription management view - role-based
     */
    public function index()
    {
        // Get role-specific page configuration for redirects
        $pageConfig = $this->getPageConfig();

        try {
            // Check basic prescription access permission
            if (!$this->canViewPrescriptions()) {
                return redirect()->to($pageConfig['redirectUrl'])->with('error', 'Access denied');
            }

            // Get role-specific data
            $prescriptions = $this->prescriptionService->getPrescriptionsByRole($this->userRole, $this->staffId);
            $stats = $this->prescriptionService->getPrescriptionStats($this->userRole, $this->staffId);
            $availablePatients = $this->getAvailablePatientsForRole();

            // Get permissions for this role
            $permissions = $this->getUserPermissions();

            $data = [
                'title' => $pageConfig['title'],
                'prescriptions' => $prescriptions,
                'stats' => $stats,
                'availablePatients' => $availablePatients,
                'userRole' => $this->userRole,
                'permissions' => $permissions,
                'pageConfig' => $pageConfig,
                'statuses' => $this->getPrescriptionStatuses(),
                'priorities' => $this->getPrescriptionPriorities()
            ];

            return view('unified/prescription-management', $data);

        } catch (\Throwable $e) {
            log_message('error', 'PrescriptionManagement::index error: ' . $e->getMessage());
            return redirect()->to($pageConfig['redirectUrl'])->with('error', 'Failed to load prescription management');
        }
    }

    /**
     * Get prescriptions API - role-based filtering
     */
    public function getPrescriptionsAPI()
    {
        try {
            if (!$this->canViewPrescriptions()) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => 'Access denied'
                ]);
            }

            // Get filters from request
            $filters = $this->getFiltersFromRequest();
            
            // Get prescriptions based on role
            $prescriptions = $this->prescriptionService->getPrescriptionsByRole($this->userRole, $this->staffId, $filters);
            
            return $this->response->setJSON([
                'status' => 'success',
                'data' => $prescriptions
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'PrescriptionManagement::getPrescriptionsAPI error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to load prescriptions'
            ]);
        }
    }

    /**
     * Create a new prescription
     */
    public function create()
    {
        try {
            if (!$this->canCreatePrescription()) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => 'Permission denied',
                    'csrf' => ['name' => csrf_token(), 'value' => csrf_hash()]
                ]);
            }

            $input = $this->request->getJSON(true) ?? $this->request->getPost();
            
            $result = $this->prescriptionService->createPrescription($input, $this->userRole, $this->staffId);
            
            $statusCode = $result['success'] ? 200 : 422;
            
            return $this->response->setStatusCode($statusCode)->setJSON([
                'status' => $result['success'] ? 'success' : 'error',
                'message' => $result['message'],
                'prescription_id' => $result['prescription_id'] ?? null,
                'id' => $result['id'] ?? null,
                'errors' => $result['errors'] ?? null,
                'csrf' => ['name' => csrf_token(), 'value' => csrf_hash()]
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'PrescriptionManagement::create error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to create prescription',
                'csrf' => ['name' => csrf_token(), 'value' => csrf_hash()]
            ]);
        }
    }

    /**
     * Update a prescription
     */
    public function update()
    {
        try {
            $input = $this->request->getJSON(true) ?? $this->request->getPost();
            
            if (empty($input['id'])) {
                return $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'message' => 'Prescription ID is required',
                    'csrf' => ['name' => csrf_token(), 'value' => csrf_hash()]
                ]);
            }

            $result = $this->prescriptionService->updatePrescription($input['id'], $input, $this->userRole, $this->staffId);
            
            $statusCode = $result['success'] ? 200 : ($result['message'] === 'Permission denied' ? 403 : 422);
            
            return $this->response->setStatusCode($statusCode)->setJSON([
                'status' => $result['success'] ? 'success' : 'error',
                'message' => $result['message'],
                'csrf' => ['name' => csrf_token(), 'value' => csrf_hash()]
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'PrescriptionManagement::update error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to update prescription',
                'csrf' => ['name' => csrf_token(), 'value' => csrf_hash()]
            ]);
        }
    }

    /**
     * Delete a prescription
     */
    public function delete()
    {
        try {
            $input = $this->request->getJSON(true) ?? $this->request->getPost();
            $id = $input['id'] ?? null;

            if (!$id) {
                return $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'message' => 'Prescription ID is required',
                    'csrf' => ['name' => csrf_token(), 'value' => csrf_hash()]
                ]);
            }

            $result = $this->prescriptionService->deletePrescription($id, $this->userRole, $this->staffId);
            
            $statusCode = $result['success'] ? 200 : ($result['message'] === 'Permission denied' ? 403 : 422);
            
            return $this->response->setStatusCode($statusCode)->setJSON([
                'status' => $result['success'] ? 'success' : 'error',
                'message' => $result['message'],
                'csrf' => ['name' => csrf_token(), 'value' => csrf_hash()]
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'PrescriptionManagement::delete error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to delete prescription',
                'csrf' => ['name' => csrf_token(), 'value' => csrf_hash()]
            ]);
        }
    }

    /**
     * Get a single prescription
     */
    public function getPrescription($id)
    {
        try {
            log_message('info', 'PrescriptionManagement::getPrescription - Request for ID: ' . $id);
            log_message('info', 'PrescriptionManagement::getPrescription - User role: ' . $this->userRole);
            log_message('info', 'PrescriptionManagement::getPrescription - Staff ID: ' . $this->staffId);
            
            if (!$this->canViewPrescriptions()) {
                log_message('warning', 'PrescriptionManagement::getPrescription - Access denied for role: ' . $this->userRole);
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => 'Access denied'
                ]);
            }

            $prescription = $this->prescriptionService->getPrescription($id);
            
            if (!$prescription) {
                log_message('warning', 'PrescriptionManagement::getPrescription - Prescription not found: ' . $id);
                
                // Check if prescriptions table has any data
                $db = \Config\Database::connect();
                $count = $db->table('prescriptions')->countAllResults();
                log_message('info', 'Total prescriptions in database: ' . $count);
                
                // Check if this specific ID exists
                $exists = $db->table('prescriptions')->where('id', $id)->countAllResults();
                log_message('info', 'Prescription with ID ' . $id . ' exists: ' . ($exists ? 'YES' : 'NO'));
                
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'error',
                    'message' => 'Prescription not found',
                    'debug' => [
                        'id' => $id,
                        'total_prescriptions' => $count,
                        'exists' => $exists > 0
                    ]
                ]);
            }

            log_message('info', 'PrescriptionManagement::getPrescription - Successfully found prescription: ' . $id);
            
            return $this->response->setJSON([
                'status' => 'success',
                'data' => $prescription
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'PrescriptionManagement::getPrescription error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to load prescription: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update prescription status
     */
    public function updateStatus($id)
    {
        try {
            $input = $this->request->getJSON(true) ?? $this->request->getPost();
            $status = $input['status'] ?? null;

            if (!$status) {
                return $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'message' => 'Status is required',
                    'csrf' => ['name' => csrf_token(), 'value' => csrf_hash()]
                ]);
            }

            $result = $this->prescriptionService->updatePrescriptionStatus($id, $status, $this->userRole, $this->staffId);
            
            $statusCode = $result['success'] ? 200 : ($result['message'] === 'Permission denied' ? 403 : 422);
            
            return $this->response->setStatusCode($statusCode)->setJSON([
                'status' => $result['success'] ? 'success' : 'error',
                'message' => $result['message'],
                'csrf' => ['name' => csrf_token(), 'value' => csrf_hash()]
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'PrescriptionManagement::updateStatus error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to update prescription status',
                'csrf' => ['name' => csrf_token(), 'value' => csrf_hash()]
            ]);
        }
    }

    /**
     * Get available patients for prescription creation
     */
    public function getAvailablePatientsAPI()
    {
        try {
            if (!$this->canCreatePrescription()) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => 'Access denied'
                ]);
            }

            $patients = $this->prescriptionService->getAvailablePatients($this->userRole, $this->staffId);
            
            return $this->response->setJSON([
                'status' => 'success',
                'data' => $patients
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'PrescriptionManagement::getAvailablePatientsAPI error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to load available patients'
            ]);
        }
    }

    /**
     * Get available doctors for prescription assignment (admin only)
     */
    public function getAvailableDoctorsAPI()
    {
        try {
            // Only admin can assign doctors
            if ($this->userRole !== 'admin') {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => 'Access denied'
                ]);
            }

            $db = \Config\Database::connect();
            
            // Check if doctor table exists for specialization
            $doctorTable = $db->tableExists('doctor');
            
            if ($doctorTable) {
                // Join with doctor table to get specialization only
                $doctors = $db->table('staff s')
                    ->select('s.staff_id, s.first_name, s.last_name, d.specialization')
                    ->join('doctor d', 'd.staff_id = s.staff_id', 'left')
                    ->where('s.role', 'doctor')
                    ->orderBy('s.first_name', 'ASC')
                    ->get()
                    ->getResultArray();
            } else {
                // Just get basic staff info if doctor table doesn't exist
                $doctors = $db->table('staff')
                    ->select('staff_id, first_name, last_name')
                    ->where('role', 'doctor')
                    ->orderBy('first_name', 'ASC')
                    ->get()
                    ->getResultArray();
            }
            
            return $this->response->setJSON([
                'status' => 'success',
                'data' => $doctors
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'PrescriptionManagement::getAvailableDoctorsAPI error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to load available doctors'
            ]);
        }
    }

    /**
     * Get available medications from Resource Management (Medications category)
     */
    public function getAvailableMedicationsAPI()
    {
        try {
            if (!$this->canCreatePrescription()) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => 'Access denied',
                ]);
            }

            $search = $this->request->getGet('search');
            $resourceService = new ResourceService();
            $medications = $resourceService->getMedications($search);

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $medications,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'PrescriptionManagement::getAvailableMedicationsAPI error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to load medications',
            ]);
        }
    }

    // Permission methods

    private function canViewPrescriptions()
    {
        return $this->permissionManager->hasPermission($this->userRole, 'prescriptions', 'view') ||
               $this->permissionManager->hasPermission($this->userRole, 'prescriptions', 'view_own') ||
               $this->permissionManager->hasPermission($this->userRole, 'prescriptions', 'view_all');
    }

    private function canCreatePrescription()
    {
        return $this->permissionManager->hasPermission($this->userRole, 'prescriptions', 'create');
    }

    private function canEditPrescription()
    {
        return $this->permissionManager->hasPermission($this->userRole, 'prescriptions', 'edit') ||
               $this->permissionManager->hasPermission($this->userRole, 'prescriptions', 'edit_own');
    }

    private function canDeletePrescription()
    {
        return $this->permissionManager->hasPermission($this->userRole, 'prescriptions', 'delete');
    }

    // Helper methods

    private function getUserPermissions()
    {
        return [
            'canView' => $this->canViewPrescriptions(),
            'canCreate' => $this->canCreatePrescription(),
            'canEdit' => $this->canEditPrescription(),
            'canDelete' => $this->canDeletePrescription(),
            'canViewAll' => $this->permissionManager->hasPermission($this->userRole, 'prescriptions', 'view_all'),
            'canViewOwn' => $this->permissionManager->hasPermission($this->userRole, 'prescriptions', 'view_own'),
            'canFulfill' => $this->permissionManager->hasPermission($this->userRole, 'prescriptions', 'fulfill')
        ];
    }

    private function getPageConfig()
    {
        $configs = [
            'admin' => [
                'title' => 'Prescription Management',
                'subtitle' => 'Manage all prescriptions and medication orders',
                'redirectUrl' => 'admin/dashboard',
                'showSidebar' => true,
                'sidebarType' => 'admin'
            ],
            'doctor' => [
                'title' => 'My Prescriptions',
                'subtitle' => 'Create and manage patient prescriptions',
                'redirectUrl' => 'doctor/dashboard',
                'showSidebar' => true,
                'sidebarType' => 'doctor'
            ],
            'nurse' => [
                'title' => 'Department Prescriptions',
                'subtitle' => 'View department prescription orders',
                'redirectUrl' => 'nurse/dashboard',
                'showSidebar' => true,
                'sidebarType' => 'nurse'
            ],
            'pharmacist' => [
                'title' => 'Prescription Queue',
                'subtitle' => 'Process and dispense medications',
                'redirectUrl' => 'pharmacist/dashboard',
                'showSidebar' => true,
                'sidebarType' => 'pharmacist'
            ],
            'receptionist' => [
                'title' => 'Prescription Overview',
                'subtitle' => 'View prescription status for coordination',
                'redirectUrl' => 'receptionist/dashboard',
                'showSidebar' => true,
                'sidebarType' => 'receptionist'
            ],
            'it_staff' => [
                'title' => 'Prescription Management',
                'subtitle' => 'System administration of prescriptions',
                'redirectUrl' => 'it-staff/dashboard',
                'showSidebar' => true,
                'sidebarType' => 'admin'
            ]
        ];

        return $configs[$this->userRole] ?? $configs['admin'];
    }

    private function getAvailablePatientsForRole()
    {
        if ($this->canCreatePrescription()) {
            return $this->prescriptionService->getAvailablePatients($this->userRole, $this->staffId);
        }
        return [];
    }

    private function getFiltersFromRequest()
    {
        $filters = [];
        
        if ($date = $this->request->getGet('date')) {
            $filters['date'] = $date;
        }
        
        if ($status = $this->request->getGet('status')) {
            $filters['status'] = $status;
        }
        
        if ($patientId = $this->request->getGet('patient_id')) {
            $filters['patient_id'] = $patientId;
        }
        
        if ($doctorId = $this->request->getGet('doctor_id')) {
            $filters['doctor_id'] = $doctorId;
        }
        
        if ($search = $this->request->getGet('search')) {
            $filters['search'] = $search;
        }

        // Date range filters
        if ($startDate = $this->request->getGet('start_date')) {
            $endDate = $this->request->getGet('end_date') ?? $startDate;
            $filters['date_range'] = [
                'start' => $startDate,
                'end' => $endDate
            ];
        }

        return $filters;
    }

    private function getPrescriptionStatuses()
    {
        return [
            ['status' => 'active'],
            ['status' => 'pending'],
            ['status' => 'ready'],
            ['status' => 'completed'],
            ['status' => 'cancelled'],
            ['status' => 'expired']
        ];
    }

    private function getPrescriptionPriorities()
    {
        return [
            ['priority' => 'routine'],
            ['priority' => 'priority'],
            ['priority' => 'stat']
        ];
    }
}
