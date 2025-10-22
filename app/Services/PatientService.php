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
     * Get patients with optional filtering by doctor
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

    private function determinePrimaryDoctor($input, $userRole, $staffId)
    {
        if ($userRole === 'doctor') {
            // For doctors, use their staff_id directly
            return $staffId;
        } 
        
        if ($userRole === 'receptionist') {
            // For receptionist, use assigned doctor or fallback
            if (!empty($input['assigned_doctor'])) {
                return (int)$input['assigned_doctor'];
            }
            
            // Fallback to first available doctor
            $firstDoctor = $this->db->table('staff')
                ->select('staff_id')
                ->where('role', 'doctor')
                ->where('status', 'active')
                ->get()
                ->getRowArray();
            return $firstDoctor ? $firstDoctor['staff_id'] : null;
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
        return [
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
            'primary_doctor_id' => $primaryDoctorId,
        ];
    }
}