<?php

namespace App\Services;

use App\Libraries\PermissionManager;

class PrescriptionService
{
    protected $db;
    protected $permissionManager;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->permissionManager = new PermissionManager();
    }

    /**
     * Get prescriptions based on user role and permissions
     */
    public function getPrescriptionsByRole($userRole, $staffId = null, $filters = [])
    {
        try {
            $builder = $this->db->table('prescriptions p')
                ->select([
                    'p.id',
                    'p.prescription_id',
                    'p.patient_id',
                    'p.doctor_id',
                    'p.medication',
                    'p.dosage',
                    'p.frequency',
                    'p.duration',
                    'p.notes',
                    'p.date_issued',
                    'p.status',
                    'p.created_at',
                    'p.updated_at',
                    "CONCAT(COALESCE(pat.first_name,''),' ',COALESCE(pat.last_name,'')) as patient_name",
                    'pat.patient_id as pat_id',
                    'pat.date_of_birth',
                    'pat.phone as patient_phone',
                    'pat.email as patient_email',
                    "CONCAT(COALESCE(s.first_name,''),' ',COALESCE(s.last_name,'')) as doctor_name",
                    's.department as doctor_department'
                ])
                ->join('patient pat', 'pat.patient_id = p.patient_id', 'left')
                ->join('staff s', 's.staff_id = p.doctor_id', 'left');

            // Apply role-based filtering
            switch ($userRole) {
                case 'admin':
                case 'it_staff':
                    // Admin and IT staff see all prescriptions
                    break;
                    
                case 'doctor':
                    // Doctors see only their own prescriptions
                    if ($staffId) {
                        $builder->where('p.doctor_id', $staffId);
                    }
                    break;
                    
                case 'nurse':
                    // Nurses see prescriptions for patients in their department
                    if ($staffId) {
                        $userDept = $this->getUserDepartment($staffId);
                        if ($userDept) {
                            $builder->join('staff doc_staff', 'doc_staff.staff_id = p.doctor_id', 'left')
                                ->where('doc_staff.department', $userDept);
                        }
                    }
                    break;
                    
                case 'pharmacist':
                    // Pharmacists see all prescriptions for dispensing
                    break;
                    
                case 'receptionist':
                    // Receptionists see all prescriptions for coordination
                    break;
                    
                default:
                    // Other roles see no prescriptions
                    $builder->where('1', '0');
                    break;
            }

            // Apply additional filters
            if (!empty($filters['status'])) {
                $builder->where('p.status', $filters['status']);
            }
            
            if (!empty($filters['date'])) {
                $builder->where('DATE(p.date_issued)', $filters['date']);
            }
            
            if (!empty($filters['patient_id'])) {
                $builder->where('p.patient_id', $filters['patient_id']);
            }
            
            if (!empty($filters['doctor_id'])) {
                $builder->where('p.doctor_id', $filters['doctor_id']);
            }

            if (!empty($filters['date_range'])) {
                $builder->where('p.date_issued >=', $filters['date_range']['start']);
                $builder->where('p.date_issued <=', $filters['date_range']['end']);
            }

            // Search functionality
            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $builder->groupStart()
                    ->like('p.prescription_id', $search)
                    ->orLike('p.medication', $search)
                    ->orLike("CONCAT(pat.first_name, ' ', pat.last_name)", $search)
                    ->orLike("CONCAT(s.first_name, ' ', s.last_name)", $search)
                    ->groupEnd();
            }

            $builder->orderBy('p.created_at', 'DESC');

            return $builder->get()->getResultArray();

        } catch (\Throwable $e) {
            log_message('error', 'PrescriptionService::getPrescriptionsByRole error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get prescription statistics based on user role
     */
    public function getPrescriptionStats($userRole, $staffId = null)
    {
        try {
            $stats = [];
            $today = date('Y-m-d');
            $thisWeek = [
                'start' => date('Y-m-d', strtotime('monday this week')),
                'end' => date('Y-m-d', strtotime('sunday this week'))
            ];

            switch ($userRole) {
                case 'admin':
                case 'it_staff':
                    $stats = [
                        'total_prescriptions' => $this->getTotalPrescriptions(),
                        'today_prescriptions' => $this->getPrescriptionsCount(['date' => $today]),
                        'week_prescriptions' => $this->getPrescriptionsCount(['date_range' => $thisWeek]),
                        'active_prescriptions' => $this->getPrescriptionsCount(['status' => 'active']),
                        'completed_prescriptions' => $this->getPrescriptionsCount(['status' => 'completed']),
                        'pending_prescriptions' => $this->getPrescriptionsCount(['status' => 'pending']),
                        'cancelled_prescriptions' => $this->getPrescriptionsCount(['status' => 'cancelled']),
                        'active_doctors' => $this->getActiveDoctorsCount()
                    ];
                    break;
                    
                case 'doctor':
                    $stats = [
                        'my_prescriptions' => $this->getPrescriptionsCount([], 'doctor', $staffId),
                        'today_prescriptions' => $this->getPrescriptionsCount(['date' => $today], 'doctor', $staffId),
                        'week_prescriptions' => $this->getPrescriptionsCount(['date_range' => $thisWeek], 'doctor', $staffId),
                        'active_prescriptions' => $this->getPrescriptionsCount(['status' => 'active'], 'doctor', $staffId),
                        'completed_prescriptions' => $this->getPrescriptionsCount(['status' => 'completed'], 'doctor', $staffId),
                        'pending_prescriptions' => $this->getPrescriptionsCount(['status' => 'pending'], 'doctor', $staffId),
                        'my_patients' => $this->getMyPatientsCount($staffId)
                    ];
                    break;
                    
                case 'nurse':
                    $userDept = $this->getUserDepartment($staffId);
                    $stats = [
                        'department_prescriptions' => $this->getDepartmentPrescriptionsCount($userDept),
                        'today_prescriptions' => $this->getDepartmentPrescriptionsCount($userDept, ['date' => $today]),
                        'active_prescriptions' => $this->getDepartmentPrescriptionsCount($userDept, ['status' => 'active']),
                        'pending_prescriptions' => $this->getDepartmentPrescriptionsCount($userDept, ['status' => 'pending']),
                        'department' => $userDept
                    ];
                    break;
                    
                case 'pharmacist':
                    $stats = [
                        'total_prescriptions' => $this->getTotalPrescriptions(),
                        'pending_prescriptions' => $this->getPrescriptionsCount(['status' => 'active']),
                        'dispensed_today' => $this->getPrescriptionsCount(['status' => 'completed', 'date' => $today]),
                        'ready_to_dispense' => $this->getPrescriptionsCount(['status' => 'ready']),
                        'stat_prescriptions' => $this->getStatPrescriptionsCount()
                    ];
                    break;
                    
                case 'receptionist':
                    $stats = [
                        'total_prescriptions' => $this->getTotalPrescriptions(),
                        'today_prescriptions' => $this->getPrescriptionsCount(['date' => $today]),
                        'active_prescriptions' => $this->getPrescriptionsCount(['status' => 'active']),
                        'completed_prescriptions' => $this->getPrescriptionsCount(['status' => 'completed'])
                    ];
                    break;
                    
                default:
                    $stats = ['message' => 'No prescription access for this role'];
                    break;
            }

            return $stats;

        } catch (\Throwable $e) {
            log_message('error', 'PrescriptionService::getPrescriptionStats error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a new prescription
     */
    public function createPrescription($data, $userRole, $staffId = null)
    {
        try {
            // Validate permissions
            if (!$this->permissionManager->hasPermission($userRole, 'prescriptions', 'create')) {
                return ['success' => false, 'message' => 'Permission denied'];
            }

            // Validate required fields
            $validation = \Config\Services::validation();
            $validation->setRules([
                'patient_id' => 'required|integer',
                'medication' => 'required|max_length[255]',
                'dosage' => 'required|max_length[100]',
                'frequency' => 'required|max_length[100]',
                'duration' => 'required|max_length[50]',
                'date_issued' => 'required|valid_date',
                'notes' => 'permit_empty|max_length[1000]'
            ]);

            if (!$validation->run($data)) {
                return [
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validation->getErrors()
                ];
            }

            // Generate prescription ID
            $prescriptionId = $this->generatePrescriptionId();

            // For doctors, use their staff_id as doctor_id
            $doctorId = ($userRole === 'doctor') ? $staffId : ($data['doctor_id'] ?? $staffId);

            $prescriptionData = [
                'prescription_id' => $prescriptionId,
                'patient_id' => (int)$data['patient_id'],
                'doctor_id' => $doctorId,
                'medication' => $data['medication'],
                'dosage' => $data['dosage'],
                'frequency' => $data['frequency'],
                'duration' => $data['duration'],
                'notes' => $data['notes'] ?? null,
                'date_issued' => $data['date_issued'],
                'status' => $data['status'] ?? 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($this->db->table('prescriptions')->insert($prescriptionData)) {
                return [
                    'success' => true,
                    'message' => 'Prescription created successfully',
                    'prescription_id' => $prescriptionId,
                    'id' => $this->db->insertID()
                ];
            }

            return ['success' => false, 'message' => 'Failed to create prescription'];

        } catch (\Throwable $e) {
            log_message('error', 'PrescriptionService::createPrescription error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create prescription'];
        }
    }

    /**
     * Update a prescription
     */
    public function updatePrescription($id, $data, $userRole, $staffId = null)
    {
        try {
            // Get existing prescription
            $existingPrescription = $this->getPrescription($id);
            if (!$existingPrescription) {
                return ['success' => false, 'message' => 'Prescription not found'];
            }

            // Check permissions
            if (!$this->canEditPrescription($existingPrescription, $userRole, $staffId)) {
                return ['success' => false, 'message' => 'Permission denied'];
            }

            // Prepare update data
            $updateData = array_filter([
                'medication' => $data['medication'] ?? null,
                'dosage' => $data['dosage'] ?? null,
                'frequency' => $data['frequency'] ?? null,
                'duration' => $data['duration'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => $data['status'] ?? null,
                'updated_at' => date('Y-m-d H:i:s')
            ], function($v) { return $v !== null; });

            if (empty($updateData)) {
                return ['success' => false, 'message' => 'No fields to update'];
            }

            if ($this->db->table('prescriptions')->where('id', $id)->update($updateData)) {
                return ['success' => true, 'message' => 'Prescription updated successfully'];
            }

            return ['success' => false, 'message' => 'Failed to update prescription'];

        } catch (\Throwable $e) {
            log_message('error', 'PrescriptionService::updatePrescription error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update prescription'];
        }
    }

    /**
     * Delete a prescription
     */
    public function deletePrescription($id, $userRole, $staffId = null)
    {
        try {
            // Get existing prescription
            $existingPrescription = $this->getPrescription($id);
            if (!$existingPrescription) {
                return ['success' => false, 'message' => 'Prescription not found'];
            }

            // Check permissions
            if (!$this->canDeletePrescription($existingPrescription, $userRole, $staffId)) {
                return ['success' => false, 'message' => 'Permission denied'];
            }

            if ($this->db->table('prescriptions')->where('id', $id)->delete()) {
                return ['success' => true, 'message' => 'Prescription deleted successfully'];
            }

            return ['success' => false, 'message' => 'Failed to delete prescription'];

        } catch (\Throwable $e) {
            log_message('error', 'PrescriptionService::deletePrescription error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to delete prescription'];
        }
    }

    /**
     * Get a single prescription
     */
    public function getPrescription($id)
    {
        try {
            return $this->db->table('prescriptions p')
                ->select([
                    'p.*',
                    "CONCAT(COALESCE(pat.first_name,''),' ',COALESCE(pat.last_name,'')) as patient_name",
                    'pat.patient_id as pat_id',
                    'pat.date_of_birth',
                    'pat.phone as patient_phone',
                    'pat.email as patient_email',
                    "CONCAT(COALESCE(s.first_name,''),' ',COALESCE(s.last_name,'')) as doctor_name",
                    's.department as doctor_department'
                ])
                ->join('patient pat', 'pat.patient_id = p.patient_id', 'left')
                ->join('staff s', 's.staff_id = p.doctor_id', 'left')
                ->where('p.id', $id)
                ->get()
                ->getRowArray();

        } catch (\Throwable $e) {
            log_message('error', 'PrescriptionService::getPrescription error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update prescription status
     */
    public function updatePrescriptionStatus($id, $status, $userRole, $staffId = null)
    {
        try {
            // Get existing prescription
            $existingPrescription = $this->getPrescription($id);
            if (!$existingPrescription) {
                return ['success' => false, 'message' => 'Prescription not found'];
            }

            // Check permissions
            if (!$this->canEditPrescription($existingPrescription, $userRole, $staffId)) {
                return ['success' => false, 'message' => 'Permission denied'];
            }

            // Validate status
            $validStatuses = ['active', 'completed', 'cancelled', 'expired', 'pending', 'ready'];
            if (!in_array($status, $validStatuses)) {
                return ['success' => false, 'message' => 'Invalid status'];
            }

            $updateData = [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($this->db->table('prescriptions')->where('id', $id)->update($updateData)) {
                return ['success' => true, 'message' => 'Prescription status updated successfully'];
            }

            return ['success' => false, 'message' => 'Failed to update prescription status'];

        } catch (\Throwable $e) {
            log_message('error', 'PrescriptionService::updatePrescriptionStatus error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update prescription status'];
        }
    }

    /**
     * Get available patients for prescription creation
     */
    public function getAvailablePatients($userRole, $staffId = null)
    {
        try {
            $builder = $this->db->table('patient')
                ->select([
                    'patient_id',
                    'first_name',
                    'last_name',
                    'date_of_birth',
                    'phone',
                    'email',
                    'primary_doctor_id'
                ])
                ->where('status', 'Active');

            // Role-based patient filtering
            if ($userRole === 'doctor' && $staffId) {
                $builder->where('primary_doctor_id', $staffId);
            }

            return $builder->orderBy('first_name', 'ASC')->get()->getResultArray();

        } catch (\Throwable $e) {
            log_message('error', 'PrescriptionService::getAvailablePatients error: ' . $e->getMessage());
            return [];
        }
    }

    // Private helper methods

    private function getTotalPrescriptions()
    {
        return $this->db->table('prescriptions')->countAllResults();
    }

    private function getPrescriptionsCount($filters = [], $userRole = null, $staffId = null)
    {
        $builder = $this->db->table('prescriptions p');
        
        if ($userRole === 'doctor' && $staffId) {
            $builder->where('p.doctor_id', $staffId);
        }

        if (!empty($filters['date'])) {
            $builder->where('DATE(p.date_issued)', $filters['date']);
        }
        
        if (!empty($filters['status'])) {
            $builder->where('p.status', $filters['status']);
        }

        if (!empty($filters['date_range'])) {
            $builder->where('p.date_issued >=', $filters['date_range']['start']);
            $builder->where('p.date_issued <=', $filters['date_range']['end']);
        }

        return $builder->countAllResults();
    }

    private function getDepartmentPrescriptionsCount($department, $filters = [])
    {
        try {
            $builder = $this->db->table('prescriptions p')
                ->join('staff s', 's.staff_id = p.doctor_id', 'left')
                ->where('s.department', $department);

            if (!empty($filters['date'])) {
                $builder->where('DATE(p.date_issued)', $filters['date']);
            }
            
            if (!empty($filters['status'])) {
                $builder->where('p.status', $filters['status']);
            }

            return $builder->countAllResults();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function getActiveDoctorsCount()
    {
        try {
            return $this->db->table('staff')
                ->where('role', 'doctor')
                ->where('status', 'Active')
                ->countAllResults();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function getMyPatientsCount($staffId)
    {
        try {
            return $this->db->table('patient')
                ->where('primary_doctor_id', $staffId)
                ->where('status', 'Active')
                ->countAllResults();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function getStatPrescriptionsCount()
    {
        try {
            return $this->db->table('prescriptions')
                ->where('status', 'active')
                ->where('priority', 'stat')
                ->countAllResults();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function getUserDepartment($staffId)
    {
        try {
            $result = $this->db->table('staff')
                ->select('department')
                ->where('staff_id', $staffId)
                ->get()
                ->getRowArray();
            return $result['department'] ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function generatePrescriptionId()
    {
        $prefix = 'RX';
        $year = date('Y');
        $month = date('m');
        $unique = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $prescriptionId = $prefix . $year . $month . $unique;

        // Ensure uniqueness
        while ($this->db->table('prescriptions')->where('prescription_id', $prescriptionId)->countAllResults() > 0) {
            $unique = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $prescriptionId = $prefix . $year . $month . $unique;
        }

        return $prescriptionId;
    }

    private function canEditPrescription($prescription, $userRole, $staffId = null)
    {
        // Admin and IT staff can edit all prescriptions
        if (in_array($userRole, ['admin', 'it_staff'])) {
            return true;
        }

        // Doctors can edit their own prescriptions
        if ($userRole === 'doctor' && $staffId && $prescription['doctor_id'] == $staffId) {
            return true;
        }

        // Pharmacists can update status for dispensing
        if ($userRole === 'pharmacist') {
            return true;
        }

        return false;
    }

    private function canDeletePrescription($prescription, $userRole, $staffId = null)
    {
        // Only admin can delete prescriptions
        if ($userRole === 'admin') {
            return true;
        }

        // Doctors can delete their own prescriptions if not yet dispensed
        if ($userRole === 'doctor' && $staffId && $prescription['doctor_id'] == $staffId && 
            in_array($prescription['status'], ['active', 'pending'])) {
            return true;
        }

        return false;
    }
}
