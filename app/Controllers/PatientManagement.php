<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\PatientService;

class PatientManagement extends BaseController
{
    protected $db;
    protected $patientService;
    protected $userRole;
    protected $staffId;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->patientService = new PatientService();
        
        $session = session();
        $this->userRole = $session->get('role');
        $this->staffId = $session->get('staff_id');
    }

    public function index()
    {
        $stats = [
            'total_patients' => 0,
            'in_patients'    => 0,
            'out_patients'   => 0,
        ];
        $patients = [];

        try {
            // Stats
            $stats['total_patients'] = $this->db->table('patient')->countAllResults();
            $stats['in_patients']    = $this->db->table('patient')->where('patient_type', 'inpatient')->countAllResults();
            $stats['out_patients']   = $this->db->table('patient')->where('patient_type', 'outpatient')->countAllResults();

            // Patients list with optional assigned doctor name
            $builder = $this->db->table('patient p')
                ->select('p.patient_id, p.first_name, p.last_name, p.email, p.date_of_birth, p.patient_type, p.status, p.primary_doctor_id, CONCAT(s.first_name, " ", s.last_name) AS primary_doctor_name')
                ->join('doctor d', 'd.doctor_id = p.primary_doctor_id', 'left')
                ->join('staff s', 's.staff_id = d.staff_id', 'left')
                ->orderBy('p.patient_id', 'DESC');
            $patients = $builder->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'PatientManagement index error: '.$e->getMessage());
        }

        $data = [
            'title'         => 'Patient Management',
            'patientStats'  => $stats,
            'patients'      => $patients,
        ];

        return view('admin/patient-management', $data);
    }

    /**
     * Create Patient - All authorized roles
     */
    public function createPatient()
    {
        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        
        $result = $this->patientService->createPatient(
            $input,
            $this->userRole,
            $this->staffId
        );
        
        return $this->response->setJSON($result);
    }

    /**
     * Update Patient - Role-based permissions
     */
    public function updatePatient($patientId = null)
    {
        $patientId = $patientId ?? $this->request->getPost('patient_id');
        
        if (!$this->canEditPatient($patientId)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Permission denied'
            ]);
        }

        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        
        $result = $this->patientService->updatePatient(
            $patientId,
            $input,
            $this->userRole,
            $this->staffId
        );
        
        return $this->response->setJSON($result);
    }

    /**
     * Get Single Patient
     */
    public function getPatient($patientId)
    {
        if (!$this->canViewPatient($patientId)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Permission denied'
            ]);
        }

        try {
            $patient = $this->db->table('patient p')
                ->select('p.*, CONCAT(s.first_name, " ", s.last_name) AS primary_doctor_name')
                ->join('staff s', 's.staff_id = p.primary_doctor_id', 'left')
                ->where('p.patient_id', $patientId)
                ->get()->getRowArray();
                
            if (!$patient) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Patient not found'
                ]);
            }
            
            return $this->response->setJSON([
                'status' => 'success',
                'data' => $patient
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Get patient error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to load patient'
            ]);
        }
    }

    /**
     * Delete Patient - Admin only
     */
    public function deletePatient($patientId)
    {
        if ($this->userRole !== 'admin') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Only administrators can delete patients'
            ]);
        }

        try {
            if ($this->db->table('patient')->where('patient_id', $patientId)->delete()) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Patient deleted successfully'
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Delete patient error: ' . $e->getMessage());
        }
        
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Failed to delete patient'
        ]);
    }

    /**
     * API Endpoint for Patient List
     */
    public function getPatientsAPI()
    {
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $this->getPatients()
        ]);
    }

    /**
     * Update Patient Status (Complete, Discharge, etc.)
     */
    public function updatePatientStatus($patientId)
    {
        if (!$this->canEditPatient($patientId)) {
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
     * Assign Doctor to Patient
     */
    public function assignDoctor($patientId)
    {
        if (!in_array($this->userRole, ['admin', 'receptionist'])) {
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
    // PRIVATE HELPER METHODS
    // ===================================================================

    /**
     * Get Patients based on user role
     */
    private function getPatients()
    {
        try {
            $builder = $this->db->table('patient p')
                ->select('p.*, CONCAT(s.first_name, " ", s.last_name) AS primary_doctor_name')
                ->join('staff s', 's.staff_id = p.primary_doctor_id', 'left');

            // Role-based filtering
            switch ($this->userRole) {
                case 'admin':
                case 'receptionist':
                    // Can see all patients
                    break;
                case 'doctor':
                    // Only see assigned patients
                    $builder->where('p.primary_doctor_id', $this->staffId);
                    break;
                case 'nurse':
                    // See patients in their department/ward
                    $builder->join('staff ns', 'ns.staff_id', $this->staffId)
                            ->join('staff ds', 'ds.staff_id = p.primary_doctor_id')
                            ->where('ns.department = ds.department');
                    break;
            }

            return $builder->orderBy('p.patient_id', 'DESC')->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'Get patients error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get Patient Statistics
     */
    private function getPatientStats()
    {
        $stats = [
            'total_patients' => 0,
            'active_patients' => 0,
            'inactive_patients' => 0,
            'inpatients' => 0,
            'outpatients' => 0,
            'emergency_patients' => 0
        ];
        
        try {
            $builder = $this->db->table('patient');
            
            // Apply role-based filtering
            if ($this->userRole === 'doctor') {
                $builder->where('primary_doctor_id', $this->staffId);
            }
            
            $stats['total_patients'] = $builder->countAllResults(false);
            $stats['active_patients'] = $builder->where('status', 'Active')->countAllResults(false);
            $stats['inactive_patients'] = $builder->where('status', 'Inactive')->countAllResults(false);
            $stats['inpatients'] = $builder->where('patient_type', 'inpatient')->countAllResults(false);
            $stats['outpatients'] = $builder->where('patient_type', 'outpatient')->countAllResults(false);
            $stats['emergency_patients'] = $builder->where('patient_type', 'emergency')->countAllResults();
        } catch (\Throwable $e) {
            log_message('error', 'Patient stats error: ' . $e->getMessage());
        }
        
        return $stats;
    }

    /**
     * Get User Permissions for UI
     */
    private function getUserPermissions()
    {
        return [
            'canCreate' => in_array($this->userRole, ['admin', 'receptionist']),
            'canEdit' => in_array($this->userRole, ['admin', 'receptionist', 'doctor']),
            'canDelete' => in_array($this->userRole, ['admin']),
            'canAssignDoctor' => in_array($this->userRole, ['admin', 'receptionist']),
            'canViewAll' => in_array($this->userRole, ['admin', 'receptionist']),
            'canViewMedicalHistory' => in_array($this->userRole, ['admin', 'doctor', 'nurse']),
            'canDischarge' => in_array($this->userRole, ['admin', 'doctor']),
            'canUpdateVitals' => in_array($this->userRole, ['nurse', 'doctor'])
        ];
    }

    
    private function canViewPatient($patientId)
    {
        switch ($this->userRole) {
            case 'admin':
            case 'receptionist':
                return true;
            case 'doctor':
                $patient = $this->db->table('patient')
                    ->where('patient_id', $patientId)
                    ->where('primary_doctor_id', $this->staffId)
                    ->get()->getRow();
                return !empty($patient);
            case 'nurse':
                return $this->isPatientInNurseDepartment($patientId);
            default:
                return false;
        }
    }

    
    private function canEditPatient($patientId)
    {
        switch ($this->userRole) {
            case 'admin':
            case 'receptionist':
                return true;
            case 'doctor':
                return $this->canViewPatient($patientId);
            case 'nurse':
                // Nurses can update vitals/notes but not core patient data
                return $this->isPatientInNurseDepartment($patientId);
            default:
                return false;
        }
    }

    /**
     * Check if patient is in nurse's department
     */
    private function isPatientInNurseDepartment($patientId)
    {
        try {
            $result = $this->db->table('patient p')
                ->join('staff ns', 'ns.staff_id', $this->staffId)
                ->join('staff ds', 'ds.staff_id = p.primary_doctor_id')
                ->where('p.patient_id', $patientId)
                ->where('ns.department = ds.department')
                ->get()->getRow();
                
            return !empty($result);
        } catch (\Throwable $e) {
            log_message('error', 'Check nurse department error: ' . $e->getMessage());
            return false;
        }
    }
}
