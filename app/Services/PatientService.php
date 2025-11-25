<?php

namespace App\Services;

use CodeIgniter\Database\ConnectionInterface;

class PatientService
{
    protected $db;
    protected string $patientTable;
    protected array $patientTableColumns = [];

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->patientTable = $this->resolvePatientTableName();
        $this->patientTableColumns = $this->loadPatientTableColumns();
    }

    public function createPatient($input, $userRole, $staffId = null)
    {
        // Determine primary doctor assignment when the schema supports it
        $primaryDoctorId = null;
        $hasPrimaryDoctorColumn = $this->patientTableHasColumn('primary_doctor_id');

        if ($hasPrimaryDoctorColumn) {
            $primaryDoctorId = $this->determinePrimaryDoctor($input, $userRole, $staffId);

            if (!$primaryDoctorId) {
                log_message('warning', 'PatientService::createPatient - No doctor available for assignment, defaulting to NULL');
            }
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
        $data = $this->sanitizePatientDataForTable($data);

        try {
            $this->db->table($this->patientTable)->insert($data);
            $newPatientId = (int) $this->db->insertID();
            $this->persistRoleSpecificRecords($newPatientId, $input);
            return [
                'status' => 'success',
                'message' => 'Patient added successfully',
                'id' => $newPatientId,
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
            $builder = $this->db->table($this->patientTable . ' p');

            $hasPrimaryDoctor = $this->patientTableHasColumn('primary_doctor_id');
            if ($hasPrimaryDoctor) {
                // Join doctor table first, then staff to get doctor name
                $builder->select('p.*, CONCAT(s.first_name, " ", s.last_name) as assigned_doctor_name')
                        ->join('doctor d', 'd.doctor_id = p.primary_doctor_id', 'left')
                        ->join('staff s', 's.staff_id = d.staff_id', 'left');
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
                        // Get doctor_id from doctor table using staff_id
                        $doctorInfo = $this->db->table('doctor')->where('staff_id', $staffId)->get()->getRowArray();
                        $doctorId = $doctorInfo['doctor_id'] ?? null;
                        
                        if ($doctorId) {
                            $builder->where('p.primary_doctor_id', $doctorId);
                        } else {
                            $builder->where('1=0'); // Show no patients if doctor record not found
                        }
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
            $builder = $this->db->table($this->patientTable . ' p')
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
            $patient = $this->db->table($this->patientTable)->where('patient_id', $id)->get()->getRowArray();

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

        $data = $this->sanitizePatientDataForTable($data);

        try {
            $this->db->table($this->patientTable)->where('patient_id', $id)->update($data);
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
            $existing = $this->db->table($this->patientTable)->where('patient_id', $id)->get()->getRowArray();
            if (!$existing) {
                return ['success' => false, 'message' => 'Patient not found'];
            }

            // Only admin and IT staff can delete patients
            if (!in_array($userRole, ['admin', 'it_staff'])) {
                return ['success' => false, 'message' => 'Insufficient permissions'];
            }

            // Delete patient record
            if ($this->db->table($this->patientTable)->where('patient_id', $id)->delete()) {
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
                        'total_patients' => $this->db->table($this->patientTable)->countAllResults(),
                        'active_patients' => $this->countPatientFieldValue('status', 'Active'),
                        'inactive_patients' => $this->countPatientFieldValue('status', 'Inactive'),
                        'outpatients' => $this->countPatientFieldValue('patient_type', 'Outpatient'),
                        'inpatients' => $this->countPatientFieldValue('patient_type', 'Inpatient'),
                        'emergency_patients' => $this->countPatientFieldValue('patient_type', 'Emergency'),
                    ];
                    break;

                case 'doctor':
                    $doctorInfo = $this->db->table('doctor')->where('staff_id', $staffId)->get()->getRowArray();
                    $doctorId = $doctorInfo['doctor_id'] ?? null;

                    if ($doctorId) {
                        $stats = [
                            'my_patients' => $this->db->table($this->patientTable)->where('primary_doctor_id', $doctorId)->countAllResults(),
                            'active_patients' => $this->countPatientFieldValue('status', 'Active', ['primary_doctor_id' => $doctorId]),
                            'new_patients_month' => $this->db->table($this->patientTable)
                                ->where('primary_doctor_id', $doctorId)
                                ->where('date_registered >=', date('Y-m-01'))
                                ->countAllResults(),
                            'emergency_patients' => $this->countPatientFieldValue('patient_type', 'Emergency', ['primary_doctor_id' => $doctorId]),
                        ];
                    } else {
                        $stats = [
                            'my_patients' => 0,
                            'active_patients' => 0,
                            'new_patients_month' => 0,
                            'emergency_patients' => 0,
                        ];
                    }
                    break;

                case 'nurse':
                    $nurseInfo = $this->db->table('staff')->where('staff_id', $staffId)->get()->getRowArray();
                    $department = $nurseInfo['department'] ?? null;

                    if ($department) {
                        $stats = [
                            'department_patients' => $this->db->table($this->patientTable . ' p')
                                ->join('staff s', 's.staff_id = p.primary_doctor_id', 'left')
                                ->where('s.department', $department)
                                ->countAllResults(),
                            'active_patients' => $this->countPatientFieldValue('status', 'Active', ['s.department' => $department])
                                ? $this->db->table($this->patientTable . ' p')
                                    ->join('staff s', 's.staff_id = p.primary_doctor_id', 'left')
                                    ->where('s.department', $department)
                                    ->where('p.status', 'Active')
                                    ->countAllResults()
                                : 0,
                        ];
                    }
                    break;

                case 'receptionist':
                    $stats = [
                        'total_patients' => $this->db->table($this->patientTable)->countAllResults(),
                        'active_patients' => $this->countPatientFieldValue('status', 'Active'),
                        'new_patients_today' => $this->db->table($this->patientTable)->where('date_registered', date('Y-m-d'))->countAllResults(),
                        'new_patients_week' => $this->db->table($this->patientTable)->where('date_registered >=', date('Y-m-d', strtotime('-7 days')))->countAllResults(),
                    ];
                    break;

                case 'pharmacist':
                    $stats = [
                        'patients_with_prescriptions' => $this->db->table($this->patientTable . ' p')
                            ->join('prescription pr', 'pr.patient_id = p.patient_id', 'inner')
                            ->countAllResults(),
                        'active_prescriptions' => $this->db->table('prescription')->where('status', 'Active')->countAllResults(),
                    ];
                    break;

                case 'laboratorist':
                    $stats = [
                        'patients_with_tests' => $this->db->table($this->patientTable . ' p')
                            ->join('lab_test lt', 'lt.patient_id = p.patient_id', 'inner')
                            ->countAllResults(),
                        'pending_tests' => $this->db->table('lab_test')->where('status', 'Pending')->countAllResults(),
                    ];
                    break;

                case 'accountant':
                    $stats = [
                        'total_patients' => $this->db->table($this->patientTable)->countAllResults(),
                        'active_patients' => $this->countPatientFieldValue('status', 'Active'),
                        'patients_with_insurance' => $this->db->table($this->patientTable)->where('insurance_provider IS NOT NULL')->countAllResults(),
                    ];
                    break;

                default:
                    $stats = [
                        'total_patients' => $this->db->table($this->patientTable)->countAllResults(),
                        'active_patients' => $this->countPatientFieldValue('status', 'Active'),
                    ];
            }

            return $stats;
        } catch (\Throwable $e) {
            log_message('error', 'Patient stats error: ' . $e->getMessage());
            return [];
        }
    }

    private function countPatientFieldValue(string $field, $value, array $additionalConditions = []): int
    {
        if (! $this->patientTableHasColumn($field)) {
            return 0;
        }

        $builder = $this->db->table($this->patientTable);
        $builder->where($field, $value);

        foreach ($additionalConditions as $key => $val) {
            $builder->where($key, $val);
        }

        return $builder->countAllResults();
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

            $patients = $this->db->table($this->patientTable . ' p')
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
        if (!$this->db->tableExists('staff')) {
            return [];
        }

        $staffDoctors = $this->db->table('staff s')
            ->select('s.staff_id, s.first_name, s.last_name')
            ->join('roles r', 'r.role_id = s.role_id', 'inner')
            ->where('r.slug', 'doctor')
            ->orderBy('s.first_name', 'ASC')
            ->get()
            ->getResultArray();

        return $this->formatDoctorData($staffDoctors);
    }

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
            // Inpatient-only details are optional at backend level; frontend enforces them when needed
            'province' => 'permit_empty|max_length[100]',
            'city' => 'permit_empty|max_length[100]',
            'barangay' => 'permit_empty|max_length[100]',
            'zip_code' => 'permit_empty|max_length[20]',
            'emergency_contact_name' => 'permit_empty|max_length[100]',
            'emergency_contact_phone' => 'permit_empty|max_length[50]',
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
        return in_array($column, $this->patientTableColumns, true);
    }

    private function loadPatientTableColumns(): array
    {
        try {
            if (! $this->db->tableExists($this->patientTable)) {
                return [];
            }

            $fields = $this->db->getFieldData($this->patientTable);
            $columns = array_map(fn ($field) => $field->name ?? null, $fields);
            return array_filter($columns);
        } catch (\Throwable $e) {
            log_message('warning', 'Unable to load patient table columns: ' . $e->getMessage());
            return [];
        }
    }

    private function sanitizePatientDataForTable(array $data): array
    {
        if (empty($this->patientTableColumns)) {
            return $data;
        }

        $allowed = array_flip($this->patientTableColumns);
        return array_intersect_key($data, $allowed);
    }

    private function persistRoleSpecificRecords(int $patientId, array $input): void
    {
        if (!$patientId) {
            return;
        }

        $patientType = strtolower($input['patient_type'] ?? 'outpatient');

        if ($patientType === 'inpatient') {
            $this->createInpatientAdmissionRecord($patientId, $input);
            return;
        }

        $this->createOutpatientVisitRecord($patientId, $input);
    }

    private function createOutpatientVisitRecord(int $patientId, array $input): void
    {
        if (! $this->db->tableExists('outpatient_visits')) {
            return;
        }

        $visitData = [
            'patient_id' => $patientId,
            'department' => $input['department'] ?? null,
            'assigned_doctor' => $this->resolveDoctorFullName($input['assigned_doctor'] ?? null),
            'appointment_datetime' => $input['appointment_datetime'] ?? null,
            'visit_type' => $input['visit_type'] ?? null,
            'chief_complaint' => $input['chief_complaint'] ?? null,
            'allergies' => $input['allergies'] ?? null,
            'existing_conditions' => $input['existing_conditions'] ?? null,
            'current_medications' => $input['current_medications'] ?? null,
            'blood_pressure' => $input['blood_pressure'] ?? null,
            'heart_rate' => $input['heart_rate'] ?? null,
            'respiratory_rate' => $input['respiratory_rate'] ?? null,
            'temperature' => $input['temperature'] ?? null,
            'weight' => $input['weight_kg'] ?? $input['weight'] ?? null,
            'height' => $input['height_cm'] ?? $input['height'] ?? null,
            'payment_type' => $input['payment_type'] ?? null,
        ];

        try {
            $this->db->table('outpatient_visits')->insert($visitData);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to insert outpatient visit for patient ' . $patientId . ': ' . $e->getMessage());
        }
    }

    private function createInpatientAdmissionRecord(int $patientId, array $input): void
    {
        if (! $this->db->tableExists('inpatient_admissions')) {
            return;
        }

        $consent = $input['consent_uploaded'] ?? $input['consent_signed'] ?? null;
        $admissionData = [
            'patient_id' => $patientId,
            'admission_datetime' => $input['admission_datetime'] ?? null,
            'admission_type' => $input['admission_type'] ?? null,
            'admitting_diagnosis' => $input['admitting_diagnosis'] ?? null,
            'admitting_doctor' => $input['admitting_doctor'] ?? null,
            'department' => $input['admitting_department'] ?? $input['department'] ?? null,
            'patient_classification' => $input['patient_classification'] ?? null,
            'consent_signed' => in_array($consent, ['1', 'true', 'yes', 'on'], true) ? 1 : 0,
        ];

        try {
            $this->db->table('inpatient_admissions')->insert($admissionData);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to insert inpatient admission for patient ' . $patientId . ': ' . $e->getMessage());
        }
    }

    private function resolveDoctorFullName(mixed $staffId): ?string
    {
        if (! $staffId || ! $this->db->tableExists('staff')) {
            return null;
        }

        $staff = $this->db->table('staff')
            ->select('first_name, last_name')
            ->where('staff_id', (int) $staffId)
            ->get()
            ->getRowArray();

        if (! $staff) {
            return null;
        }

        $fullName = trim(($staff['first_name'] ?? '') . ' ' . ($staff['last_name'] ?? ''));
        return $fullName !== '' ? $fullName : null;
    }

    /**
     * Resolve patient table name
     */
    private function resolvePatientTableName(): string
    {
        if ($this->db->tableExists('patient')) {
            return 'patient';
        }

        if ($this->db->tableExists('patients')) {
            return 'patients';
        }

        throw new \RuntimeException('Neither "patient" nor "patients" table exists. Please run the appropriate migrations.');
    }
}