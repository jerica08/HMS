<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\PatientService;
use App\Services\RoomService;
use App\Libraries\PermissionManager;

class PatientManagement extends BaseController
{
    protected $patientService;
    protected RoomService $roomService;
    protected $session;
    protected $userRole;
    protected $staffId;
    protected $db;

    public function __construct()
    {
        $this->patientService = new PatientService();
        $this->roomService = new RoomService();
        $this->session = session();
        $this->userRole = $this->session->get('role');
        $this->staffId = $this->session->get('staff_id');
        $this->db = \Config\Database::connect();
        
        // Authentication is now handled by the roleauth filter in routes
    }

    /**
     * Main unified patient management - Role-based
     */
    public function index()
    {
        // Get patient data based on role permissions
        $patients = $this->patientService->getPatientsByRole($this->userRole, $this->staffId);
        $stats = $this->patientService->getPatientStats($this->userRole, $this->staffId);
        $availableDoctors = $this->patientService->getAvailableDoctors();
        $roomTypes = $this->db->table('room_type')
            ->select('room_type_id, type_name, base_daily_rate')
            ->orderBy('type_name', 'ASC')
            ->get()
            ->getResultArray();
        $rooms = [];
        $roomInventory = [];
        if ($this->db->tableExists('room')) {
            $rooms = $this->roomService->getRooms();
        }

        foreach ($rooms as $room) {
            $typeId = (int) ($room['room_type_id'] ?? 0);
            if (! $typeId) {
                continue;
            }

            $roomInventory[$typeId][] = [
                'room_id' => (int) ($room['room_id'] ?? 0),
                'room_number' => (string) ($room['room_number'] ?? ''),
                'room_name' => (string) ($room['room_name'] ?? ''),
                'floor_number' => (string) ($room['floor_number'] ?? ''),
                'status' => (string) ($room['status'] ?? ''),
                'bed_capacity' => (int) ($room['bed_capacity'] ?? 0),
            ];
        }
        $permissions = PermissionManager::getRolePermissions($this->userRole);

        $data = [
            'title' => $this->getPageTitle(),
            'userRole' => $this->userRole,
            'patients' => $patients,
            'patientStats' => $stats,
            'availableDoctors' => $availableDoctors,
            'permissions' => $permissions['patients'] ?? [],
            'total_patients' => count($patients),
            'roomTypes' => $roomTypes,
            'roomInventory' => $roomInventory,
        ];

        // Use unified view for all roles
        return view('unified/patient-management', $data);
    }

    /**
     * Create Patient - All authorized roles
     */
    public function createPatient()
    {
        if (!$this->canCreate()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Insufficient permissions']);
        }

        if ($this->request->getMethod() === 'POST') {
            $isAjax = $this->request->isAJAX() || 
                      $this->request->getHeaderLine('Accept') == 'application/json' ||
                      $this->request->getHeaderLine('X-Requested-With') == 'XMLHttpRequest';

            $input = $this->request->getJSON(true) ?? $this->request->getPost();
            
            $result = $this->patientService->createPatient(
                $input,
                $this->userRole,
                $this->staffId
            );
            
            if ($isAjax) {
                return $this->response->setJSON($result);
            }
            
            if ($result['status'] === 'success') {
                session()->setFlashdata('success', $result['message']);
            } else {
                session()->setFlashdata('error', $result['message']);
            }
            
            return redirect()->to($this->getRedirectUrl());
        }

        $data = ['title' => 'Add Patient'];
        return view($this->getCreateViewPath(), $data);
    }

    /**
     * Update Patient - Role-based permissions
     */
    public function updatePatient($patientId = null)
    {
        if (!$this->canEdit()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Insufficient permissions']);
        }

        $patientId = $patientId ?? $this->request->getPost('patient_id');
        
        if (!$patientId) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Invalid patient ID']);
        }

        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        
        $result = $this->patientService->updatePatient($patientId, $input);
        
        if ($result['status'] === 'success') {
            return $this->response->setJSON(['status' => 'success', 'message' => $result['message']]);
        }
        
        return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => $result['message']]);
    }

    /**
     * Get Single Patient
     */
    public function getPatient($patientId)
    {
        if (!$this->canView()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Insufficient permissions']);
        }

        if (!$patientId) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Invalid patient ID']);
        }

        $result = $this->patientService->getPatient($patientId);
        
        if ($result['status'] === 'success') {
            return $this->response->setJSON(['status' => 'success', 'data' => $result['patient']]);
        }
        
        return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => $result['message']]);
    }

    /**
     * Delete Patient - Admin only
     */
    public function deletePatient($patientId)
    {
        if (!$this->canDelete()) {
            // Check if this is an AJAX request
            if ($this->request->isAJAX() || $this->request->getHeaderLine('Accept') == 'application/json') {
                return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Insufficient permissions']);
            }
            session()->setFlashdata('error', 'Insufficient permissions');
            return redirect()->to($this->getRedirectUrl());
        }

        if (!$patientId) {
            // Check if this is an AJAX request
            if ($this->request->isAJAX() || $this->request->getHeaderLine('Accept') == 'application/json') {
                return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Invalid patient ID']);
            }
            session()->setFlashdata('error', 'Invalid patient ID');
            return redirect()->to($this->getRedirectUrl());
        }

        $result = $this->patientService->deletePatient($patientId, $this->userRole);
        
        // Check if this is an AJAX request
        if ($this->request->isAJAX() || $this->request->getHeaderLine('Accept') == 'application/json') {
            if ($result['success']) {
                return $this->response->setJSON(['status' => 'success', 'message' => $result['message']]);
            } else {
                return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => $result['message']]);
            }
        }
        
        // For non-AJAX requests, use redirect
        if ($result['success']) {
            session()->setFlashdata('success', $result['message']);
        } else {
            session()->setFlashdata('error', $result['message']);
        }
        
        return redirect()->to($this->getRedirectUrl());
    }

    /**
     * API Endpoint for Patient List
     */
    public function getPatientsAPI()
    {
        if (!$this->canView()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Insufficient permissions']);
        }

        try {
            $patients = $this->patientService->getAllPatients($this->userRole, $this->staffId);
            return $this->response->setJSON(['status' => 'success', 'data' => $patients]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Failed to load patients']);
        }
    }

    /**
     * Update Patient Status (Complete, Discharge, etc.)
     */
    public function updatePatientStatus($patientId)
    {
        if (!$this->canEdit()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Permission denied'
            ]);
        }

        $status = $this->request->getPost('status');
        $notes = $this->request->getPost('notes') ?? '';
        
        try {
            $updateData = ['status' => $status];
            if (!empty($notes)) {
                $updateData['medical_notes'] = $notes;
            }
            
            if ($this->db->table('patient')->where('patient_id', $patientId)->update($updateData)) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Patient status updated successfully'
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Update patient status error: ' . $e->getMessage());
        }
        
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Failed to update patient status'
        ]);
    }

    /**
     * Get Available Doctors API
     */
    public function getDoctorsAPI()
    {
        if (!$this->canView()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Insufficient permissions']);
        }

        try {
            $doctors = $this->patientService->getAvailableDoctors();
            return $this->response->setJSON(['status' => 'success', 'data' => $doctors]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Failed to load doctors']);
        }
    }

    /**
     * Assign Doctor to Patient
     */
    public function assignDoctor($patientId)
    {
        if (!in_array($this->userRole, ['admin', 'receptionist', 'it_staff'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Permission denied'
            ]);
        }

        $doctorId = $this->request->getPost('doctor_id');
        
        try {
            if ($this->db->table('patient')->where('patient_id', $patientId)->update(['primary_doctor_id' => $doctorId])) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Doctor assigned successfully'
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Assign doctor error: ' . $e->getMessage());
        }
        
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Failed to assign doctor'
        ]);
    }

    // ===================================================================
    // PERMISSION AND HELPER METHODS
    // ===================================================================

    /**
     * Check if user can create patients
     */
    private function canCreate()
    {
        return PermissionManager::hasPermission($this->userRole, 'patients', 'create');
    }

    /**
     * Check if user can edit patients
     */
    private function canEdit()
    {
        return PermissionManager::hasPermission($this->userRole, 'patients', 'edit');
    }

    /**
     * Check if user can delete patients
     */
    private function canDelete()
    {
        return PermissionManager::hasPermission($this->userRole, 'patients', 'delete');
    }

    /**
     * Check if user can view patients
     */
    private function canView()
    {
        return PermissionManager::hasPermission($this->userRole, 'patients', 'view');
    }

    /**
     * Get page title based on user role
     */
    private function getPageTitle()
    {
        switch ($this->userRole) {
            case 'admin':
                return 'Patient Management';
            case 'doctor':
                return 'My Patients';
            case 'nurse':
                return 'Department Patients';
            case 'receptionist':
                return 'Patient Registration';
            case 'pharmacist':
                return 'Patient Prescriptions';
            case 'laboratorist':
                return 'Patient Lab Tests';
            case 'accountant':
                return 'Patient Billing';
            case 'it_staff':
                return 'Patient Records';
            default:
                return 'Patient Directory';
        }
    }

    /**
     * Get redirect URL based on user role
     */
    private function getRedirectUrl()
    {
        switch ($this->userRole) {
            case 'admin':
                return base_url('admin/patient-management');
            case 'doctor':
                return base_url('doctor/patients');
            case 'nurse':
                return base_url('nurse/patients');
            case 'receptionist':
                return base_url('receptionist/patients');
            case 'pharmacist':
                return base_url('pharmacist/patients');
            case 'laboratorist':
                return base_url('laboratorist/patients');
            case 'accountant':
                return base_url('accountant/patients');
            case 'it_staff':
                return base_url('it-staff/patients');
            default:
                return base_url('dashboard');
        }
    }

    /**
     * Get view path based on user role
     */
    private function getViewPath()
    {
        switch ($this->userRole) {
            case 'admin':
                return 'admin/patient-management';
            case 'doctor':
                return 'doctor/patient';
            case 'nurse':
                return 'nurse/patient-management';
            case 'receptionist':
                return 'receptionist/patient-registration';
            case 'pharmacist':
            case 'laboratorist':
            case 'accountant':
            case 'it_staff':
                return 'admin/patient-management'; // Use admin view for other roles
            default:
                return 'admin/patient-management';
        }
    }

    /**
     * Get create view path based on user role
     */
    private function getCreateViewPath()
    {
        switch ($this->userRole) {
            case 'admin':
                return 'admin/add-patient';
            case 'doctor':
                return 'doctor/add-patient';
            case 'nurse':
                return 'nurse/add-patient';
            case 'receptionist':
                return 'receptionist/add-patient';
            default:
                return 'admin/add-patient';
        }
    }
}
