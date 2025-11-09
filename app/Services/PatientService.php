<?php

namespace App\Services;

use CodeIgniter\Database\ConnectionInterface;

class PatientService
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function createPatient($input, $userRole, $staffId = null)
    {
        // Determine primary doctor assignment
        $primaryDoctorId = $this->determinePrimaryDoctor($input, $userRole, $staffId);
        
        if (!$primaryDoctorId) {
            return [
                'status' => 'error',
                'message' => 'No doctor available for assignment',
            ];
        }

        // Validation
        $validation = \Config\Services::validation();
        $validation->setRules($this->getValidationRules());

        if (!$validation->run($input)) {
            return [
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validation->getErrors(),
            ];
        }

        // Prepare data
        $data = $this->preparePatientData($input, $primaryDoctorId);

        try {
            $this->db->table('patient')->insert($data);
            return [
                'status' => 'success',
                'message' => 'Patient added successfully',
                'id' => $this->db->insertID(),
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Failed to insert patient: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get patients based on user role and permissions
     */
    public function getPatientsByRole($userRole, $staffId)
    {
        try {
            $builder = $this->db->table('patient p');

            $hasPrimaryDoctor = $this->patientTableHasColumn('primary_doctor_id');
            if ($hasPrimaryDoctor) {
                $builder->select('p.*, CONCAT(s.first_name, " ", s.last_name) as assigned_doctor_name')
                        ->join('staff s', 's.staff_id = p.primary_doctor_id', 'left');
            } else {
                $builder->select('p.*');
            }

            switch ($userRole) {
                case 'admin':
                case 'it_staff':
                    // Admin and IT staff can see all patients
                    break;
                    
                case 'doctor':
                    // Doctors can see only their assigned patients
                    if ($hasPrimaryDoctor) {
                        $builder->where('p.primary_doctor_id', $staffId);
                    } else {
                        // Without primary_doctor_id column, fallback to showing all (or none)
                    }
                    break;
                    
                case 'nurse':
                    // Nurses can see patients in their department
                    $nurseInfo = $this->db->table('staff')->where('staff_id', $staffId)->get()->getRowArray();
                    $department = $nurseInfo['department'] ?? null;
                    
                    if ($department) {
                        $builder->join('staff doc', 'doc.staff_id = p.primary_doctor_id', 'left')
                               ->where('doc.department', $department);
                    } else {
                        // If no department, show no patients
                        $builder->where('1=0');
                    }
                    break;
                    
                case 'receptionist':
                    // Receptionists can see all patients for scheduling purposes
                    break;
                    
                case 'pharmacist':
                    // Pharmacists can see patients with prescriptions
                    $builder->join('prescription pr', 'pr.patient_id = p.patient_id', 'inner')
                           ->groupBy('p.patient_id');
                    break;
                    
                case 'laboratorist':
                    // Laboratorists can see patients with lab tests
                    $builder->join('lab_test lt', 'lt.patient_id = p.patient_id', 'inner')
                           ->groupBy('p.patient_id');
                    break;
                    
                case 'accountant':
                    // Accountants can see all patients for billing
                    break;
                    
                default:
                    // Other roles see no patients
                    $builder->where('1=0');
            }

            $patients = $builder->orderBy('p.patient_id', 'DESC')
                ->get()
                ->getResultArray();

            // Compute ages and format data
            foreach ($patients as &$p) {
                $p['age'] = $p['date_of_birth']
                    ? (new \DateTime())->diff(new \DateTime($p['date_of_birth']))->y
                    : null;
                $p['id'] = $p['patient_id'];
                $p['full_name'] = trim(($p['first_name'] ?? '') . ' ' . ($p['middle_name'] ?? '') . ' ' . ($p['last_name'] ?? ''));
            }

            return $patients;
            
        } catch (\Throwable $e) {
            log_message('error', 'Error fetching patients: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get patients with optional filtering by doctor (legacy method)
     */
    public function getPatients($doctorId = null)
    {
        try {
            $builder = $this->db->table('patient p')
                ->select('p.*, CONCAT(s.first_name, " ", s.last_name) as assigned_doctor_name')
                ->join('staff s', 's.staff_id = p.primary_doctor_id', 'left');

            if ($doctorId) {
                $builder->where('p.primary_doctor_id', $doctorId);
            }

            $patients = $builder->orderBy('p.patient_id', 'DESC')
                ->get()
                ->getResultArray();

            // Compute ages
            foreach ($patients as &$p) {
                $p['age'] = $p['date_of_birth']
                    ? (new \DateTime())->diff(new \DateTime($p['date_of_birth']))->y
                    : null;
                $p['id'] = $p['patient_id'];
                $p['full_name'] = trim(($p['first_name'] ?? '') . ' ' . ($p['middle_name'] ?? '') . ' ' . ($p['last_name'] ?? ''));
            }

            return [
                'success' => true,
                'data' => $patients,
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Error fetching patients: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch patients',
                'data' => [],
            ];
        }
    }

    /**
     * Get single patient by ID
     */
    public function getPatient($id)
    {
        try {
            $patient = $this->db->table('patient')->where('patient_id', $id)->get()->getRowArray();

            if (!$patient) {
                return [
                    'status' => 'error',
                    'message' => 'Patient not found',
                ];
            }

            // Compute age
            $patient['age'] = $patient['date_of_birth']
                ? (new \DateTime())->diff(new \DateTime($patient['date_of_birth']))->y
                : null;

            return [
                'status' => 'success',
                'patient' => $patient,
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Error fetching patient: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Database error',
            ];
        }
    }

    public function updatePatient($id, $input)
    {
        // Validation
        $validation = \Config\Services::validation();
        $validation->setRules($this->getValidationRules());

        if (!$validation->run($input)) {
            return [
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validation->getErrors(),
            ];
        }

        $data = [
            'first_name' => $input['first_name'],
            'middle_name' => $input['middle_name'] ?? null,
            'last_name' => $input['last_name'],
            'gender' => ucfirst(strtolower($input['gender'] ?? '')),
            'civil_status' => $input['civil_status'],
            'date_of_birth' => $input['date_of_birth'],
            'contact_no' => $input['phone'] ?? null,
            'email' => $input['email'] ?? null,
            'address' => $input['address'],
            'province' => $input['province'],
            'city' => $input['city'],
            'barangay' => $input['barangay'],
            'zip_code' => $input['zip_code'],
            'insurance_provider' => $input['insurance_provider'] ?? null,
            'insurance_number' => $input['insurance_number'] ?? null,
            'emergency_contact' => $input['emergency_contact_name'],
            'emergency_phone' => $input['emergency_contact_phone'],
            'patient_type' => ucfirst(strtolower($input['patient_type'] ?? 'Outpatient')),
            'blood_group' => $input['blood_group'] ?? null,
            'medical_notes' => $input['medical_notes'] ?? null,
            'status' => ucfirst(strtolower($input['status'] ?? 'Active')),
        ];

        try {
            $this->db->table('patient')->where('patient_id', $id)->update($data);
            return [
                'status' => 'success',
                'message' => 'Patient updated successfully',
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Failed to update patient: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Delete patient
     */
    public function deletePatient($id, $userRole)
    {
        try {
            // Check if patient exists
            $existing = $this->db->table('patient')->where('patient_id', $id)->get()->getRowArray();
            if (!$existing) {
                return ['success' => false, 'message' => 'Patient not found'];
            }

            // Only admin and IT staff can delete patients
            if (!in_array($userRole, ['admin', 'it_staff'])) {
                return ['success' => false, 'message' => 'Insufficient permissions'];
            }

            // Delete patient record
            if ($this->db->table('patient')->where('patient_id', $id)->delete()) {
                return ['success' => true, 'message' => 'Patient deleted successfully'];
            }

            return ['success' => false, 'message' => 'Failed to delete patient'];

        } catch (\Throwable $e) {
            log_message('error', 'Patient deletion error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Get patient statistics based on user role
     */
    public function getPatientStats($userRole, $staffId)
    {
        try {
            $stats = [];
            
            switch ($userRole) {
                case 'admin':
                case 'it_staff':
                    $stats = [
                        'total_patients' => $this->db->table('patient')->countAllResults(),
                        'active_patients' => $this->db->table('patient')->where('status', 'Active')->countAllResults(),
                        'inactive_patients' => $this->db->table('patient')->where('status', 'Inactive')->countAllResults(),
                        'outpatients' => $this->db->table('patient')->where('patient_type', 'Outpatient')->countAllResults(),
                        'inpatients' => $this->db->table('patient')->where('patient_type', 'Inpatient')->countAllResults(),
                        'emergency_patients' => $this->db->table('patient')->where('patient_type', 'Emergency')->countAllResults(),
                        'new_patients_month' => $this->db->table('patient')->where('date_registered >=', date('Y-m-01'))->countAllResults(),
                        'new_patients_week' => $this->db->table('patient')->where('date_registered >=', date('Y-m-d', strtotime('-7 days')))->countAllResults(),
                    ];
                    break;
                    
                case 'doctor':
                    $stats = [
                        'my_patients' => $this->db->table('patient')->where('primary_doctor_id', $staffId)->countAllResults(),
                        'active_patients' => $this->db->table('patient')->where('primary_doctor_id', $staffId)->where('status', 'Active')->countAllResults(),
                        'new_patients_month' => $this->db->table('patient')->where('primary_doctor_id', $staffId)->where('date_registered >=', date('Y-m-01'))->countAllResults(),
                        'emergency_patients' => $this->db->table('patient')->where('primary_doctor_id', $staffId)->where('patient_type', 'Emergency')->countAllResults(),
                    ];
                    break;
                    
                case 'nurse':
                    // Get department-based stats
                    $nurseInfo = $this->db->table('staff')->where('staff_id', $staffId)->get()->getRowArray();
                    $department = $nurseInfo['department'] ?? null;
                    
                    if ($department) {
                        $stats = [
                            'department_patients' => $this->db->table('patient p')
                                ->join('staff s', 's.staff_id = p.primary_doctor_id', 'left')
                                ->where('s.department', $department)
                                ->countAllResults(),
                            'active_patients' => $this->db->table('patient p')
                                ->join('staff s', 's.staff_id = p.primary_doctor_id', 'left')
                                ->where('s.department', $department)
                                ->where('p.status', 'Active')
                                ->countAllResults(),
                        ];
                    }
                    break;
                    
                case 'receptionist':
                    $stats = [
                        'total_patients' => $this->db->table('patient')->countAllResults(),
                        'active_patients' => $this->db->table('patient')->where('status', 'Active')->countAllResults(),
                        'new_patients_today' => $this->db->table('patient')->where('date_registered', date('Y-m-d'))->countAllResults(),
                        'new_patients_week' => $this->db->table('patient')->where('date_registered >=', date('Y-m-d', strtotime('-7 days')))->countAllResults(),
                    ];
                    break;
                    
                case 'pharmacist':
                    $stats = [
                        'patients_with_prescriptions' => $this->db->table('patient p')
                            ->join('prescription pr', 'pr.patient_id = p.patient_id', 'inner')
                            ->countAllResults(),
                        'active_prescriptions' => $this->db->table('prescription')->where('status', 'Active')->countAllResults(),
                    ];
                    break;
                    
                case 'laboratorist':
                    $stats = [
                        'patients_with_tests' => $this->db->table('patient p')
                            ->join('lab_test lt', 'lt.patient_id = p.patient_id', 'inner')
                            ->countAllResults(),
                        'pending_tests' => $this->db->table('lab_test')->where('status', 'Pending')->countAllResults(),
                    ];
                    break;
                    
                case 'accountant':
                    $stats = [
                        'total_patients' => $this->db->table('patient')->countAllResults(),
                        'active_patients' => $this->db->table('patient')->where('status', 'Active')->countAllResults(),
                        'patients_with_insurance' => $this->db->table('patient')->where('insurance_provider IS NOT NULL')->countAllResults(),
                    ];
                    break;
                    
                default:
                    $stats = [
                        'total_patients' => $this->db->table('patient')->countAllResults(),
                        'active_patients' => $this->db->table('patient')->where('status', 'Active')->countAllResults(),
                    ];
            }
            
            return $stats;
            
        } catch (\Throwable $e) {
            log_message('error', 'Patient stats error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all patients for API
     */
    public function getAllPatients($userRole = null, $staffId = null)
    {
        try {
            if ($userRole && $staffId) {
                return $this->getPatientsByRole($userRole, $staffId);
            }

            $patients = $this->db->table('patient p')
                ->select('p.*, CONCAT(s.first_name, " ", s.last_name) as assigned_doctor_name')
                ->join('staff s', 's.staff_id = p.primary_doctor_id', 'left')
                ->orderBy('p.patient_id', 'DESC')
                ->get()
                ->getResultArray();
            
            foreach ($patients as &$p) {
                $p['age'] = $p['date_of_birth']
                    ? (new \DateTime())->diff(new \DateTime($p['date_of_birth']))->y
                    : null;
                $p['id'] = $p['patient_id'];
                $p['full_name'] = trim(($p['first_name'] ?? '') . ' ' . ($p['middle_name'] ?? '') . ' ' . ($p['last_name'] ?? ''));
            }

            return $patients;

        } catch (\Throwable $e) {
            log_message('error', 'Patient list error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get available doctors for patient assignment
     */
    public function getAvailableDoctors()
    {
        try {
            log_message('debug', 'PatientService::getAvailableDoctors called');
            
            // Get doctors ONLY from the doctor table joined with staff table
            $fromDoctorTable = $this->db->table('doctor d')
                ->select('s.staff_id, s.first_name, s.last_name, d.specialization')
                ->join('staff s', 's.staff_id = d.staff_id', 'inner') // Inner join to ensure we only get doctors with staff records
                ->orderBy('s.first_name', 'ASC')
                ->get()
                ->getResultArray();

            log_message('debug', 'PatientService::getAvailableDoctors found ' . count($fromDoctorTable) . ' doctors from doctor table');
            
            // Log the actual results for debugging
            if (!empty($fromDoctorTable)) {
                foreach ($fromDoctorTable as $doctor) {
                    log_message('debug', 'PatientService - Doctor found: ID=' . $doctor['staff_id'] . ', Name=' . $doctor['first_name'] . ' ' . $doctor['last_name'] . ', Spec=' . ($doctor['specialization'] ?? 'None'));
                }
            }
            
            return $this->formatDoctorData($fromDoctorTable);

        } catch (\Throwable $e) {
            log_message('error', 'Available doctors fetch error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Format doctor data for consistent output
     */
    private function formatDoctorData($doctors)
    {
        return array_map(function($d) {
            // Ensure we have valid names
            $first = trim($d['first_name'] ?? '');
            $last = trim($d['last_name'] ?? '');
            if ($first === '' && $last === '') {
                $d['first_name'] = 'Doctor';
                $d['last_name'] = (string)($d['staff_id'] ?? '');
            }
            $d['full_name'] = trim(($d['first_name'] ?? '') . ' ' . ($d['last_name'] ?? ''));
            $d['id'] = $d['staff_id'];
            return $d;
        }, $doctors);
    }

    private function determinePrimaryDoctor($input, $userRole, $staffId)
    {
        if ($userRole === 'doctor') {
            // For doctors, get their doctor_id from the doctor table using their staff_id
            $doctorRecord = $this->db->table('doctor')
                ->select('doctor_id')
                ->where('staff_id', $staffId)
                ->get()
                ->getRowArray();
            return $doctorRecord['doctor_id'] ?? null;
        }

        // Admin, Receptionist, and IT Staff can optionally choose a doctor; otherwise fallback
        if (in_array($userRole, ['admin', 'receptionist', 'it_staff'])) {
            if (!empty($input['assigned_doctor'])) {
                // Convert staff_id to doctor_id
                $doctorRecord = $this->db->table('doctor')
                    ->select('doctor_id')
                    ->where('staff_id', (int)$input['assigned_doctor'])
                    ->get()
                    ->getRowArray();
                return $doctorRecord['doctor_id'] ?? null;
            }

            // Fallback to first available doctor from doctor table
            $firstDoctor = $this->db->table('doctor d')
                ->select('d.doctor_id')
                ->join('staff s', 's.staff_id = d.staff_id', 'inner')
                ->orderBy('s.first_name', 'ASC')
                ->get()
                ->getRowArray();

            return $firstDoctor['doctor_id'] ?? null;
        }

        return null;
    }

    private function getValidationRules()
    {
        return [
            'first_name' => 'required|min_length[2]|max_length[100]',
            'last_name' => 'required|min_length[2]|max_length[100]',
            'gender' => 'required|in_list[male,female,other,MALE,FEMALE,OTHER,Male,Female,Other]',
            'date_of_birth' => 'required|valid_date',
            'civil_status' => 'required',
            'phone' => 'required|max_length[50]',
            'email' => 'permit_empty|valid_email',
            'address' => 'required',
            'province' => 'required|max_length[100]',
            'city' => 'required|max_length[100]',
            'barangay' => 'required|max_length[100]',
            'zip_code' => 'required|max_length[20]',
            'emergency_contact_name' => 'required|max_length[100]',
            'emergency_contact_phone' => 'required|max_length[50]',
            'patient_type' => 'permit_empty|in_list[outpatient,inpatient,emergency,Outpatient,Inpatient,Emergency]',
        ];
    }

    private function preparePatientData($input, $primaryDoctorId)
    {
        $data = [
            'first_name' => $input['first_name'] ?? null,
            'middle_name' => $input['middle_name'] ?? null,
            'last_name' => $input['last_name'] ?? null,
            'gender' => ucfirst(strtolower($input['gender'] ?? '')),
            'civil_status' => $input['civil_status'] ?? null,
            'date_of_birth' => $input['date_of_birth'] ?? null,
            'contact_no' => $input['phone'] ?? ($input['contact_no'] ?? null),
            'email' => $input['email'] ?? null,
            'address' => $input['address'] ?? null,
            'province' => $input['province'] ?? null,
            'city' => $input['city'] ?? null,
            'barangay' => $input['barangay'] ?? null,
            'zip_code' => $input['zip_code'] ?? null,
            'insurance_provider' => $input['insurance_provider'] ?? null,
            'insurance_number' => $input['insurance_number'] ?? null,
            'emergency_contact' => $input['emergency_contact_name'] ?? null,
            'emergency_phone' => $input['emergency_contact_phone'] ?? null,
            'patient_type' => ucfirst(strtolower($input['patient_type'] ?? 'Outpatient')),
            'blood_group' => $input['blood_group'] ?? null,
            'medical_notes' => $input['medical_notes'] ?? null,
            'date_registered' => date('Y-m-d'),
            'status' => ucfirst(strtolower($input['status'] ?? 'Active')),
        ];

        // Include primary_doctor_id only if the column exists in the schema
        if ($this->patientTableHasColumn('primary_doctor_id')) {
            $data['primary_doctor_id'] = $primaryDoctorId;
        }

        return $data;
    }

    /**
     * Check if a column exists on the patient table
     */
    private function patientTableHasColumn(string $column): bool
    {
        try {
            $fields = $this->db->getFieldData('patient');
            foreach ($fields as $field) {
                if (($field->name ?? '') === $column) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
            // If any error occurs, assume column does not exist
        }
        return false;
    }
}