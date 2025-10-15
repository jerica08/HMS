<?php
namespace App\Controllers;
use App\Models\PatientModel;
use CodeIgniter\HTTP\ResponseInterface;

class Patients extends BaseController{
     public function patients()
    {
        // Fetch patient statistics for this doctor
        $totalPatients = 0;
        $inPatients = 0;
        $outPatients = 0;
        try {
            $totalPatients = $this->db->table('patient')->where('primary_doctor_id', $this->doctorId)->countAllResults();
            $inPatients = $this->db->table('patient')->where('primary_doctor_id', $this->doctorId)->where('patient_type', 'inpatient')->countAllResults();
            $outPatients = $this->db->table('patient')->where('primary_doctor_id', $this->doctorId)->where('patient_type', 'outpatient')->countAllResults();
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            log_message('error', 'Patients table does not exist: ' . $e->getMessage());
        }

        // Fetch patients list assigned to this doctor
        $patients = [];
        try {
            $patients = $this->db->table('patient')
                ->select('patient_id as id, first_name, middle_name, last_name, date_of_birth, gender, contact_no as phone, email, patient_type, status')
                ->where('primary_doctor_id', $this->doctorId)
                ->get()
                ->getResultArray();

            // Calculate age for each patient
            foreach ($patients as &$patient) {
                if ($patient['date_of_birth']) {
                    $dob = new \DateTime($patient['date_of_birth']);
                    $now = new \DateTime();
                    $age = $now->diff($dob)->y;
                    $patient['age'] = $age;
                } else {
                    $patient['age'] = null;
                }
            }
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            log_message('error', 'Failed to fetch patients: ' . $e->getMessage());
        }

        $data = [
            'totalPatients' => $totalPatients,
            'inPatients' => $inPatients,
            'outPatients' => $outPatients,
            'patients' => $patients,
        ];

        return view('doctor/patient', $data);
    }

    public function createPatient()
    {
        // Expect JSON payload
        $input = $this->request->getJSON(true) ?? $this->request->getPost();

        // Basic validation for non-nullable fields in migration
        $validation = \Config\Services::validation();
        $validation->setRules([
            'first_name'              => 'required|min_length[2]|max_length[100]',
            'last_name'               => 'required|min_length[2]|max_length[100]',
            'gender'                  => 'required|in_list[male,female,other,MALE,FEMALE,OTHER,Male,Female,Other]',
            'date_of_birth'           => 'required|valid_date',
            'civil_status'            => 'required',
            'phone'                   => 'required|max_length[50]',
            'email'                   => 'permit_empty|valid_email',
            'address'                 => 'required',
            'province'                => 'required|max_length[100]',
            'city'                    => 'required|max_length[100]',
            'barangay'                => 'required|max_length[100]',
            'zip_code'                => 'required|max_length[20]',
            'emergency_contact_name'  => 'required|max_length[100]',
            'emergency_contact_phone' => 'required|max_length[50]',
            'patient_type'            => 'permit_empty|in_list[outpatient,inpatient,emergency,Outpatient,Inpatient,Emergency]'
        ]);

        if (!$validation->run($input)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $validation->getErrors(),
            ])->setStatusCode(422);
        }

        // Map incoming fields to DB schema
        $gender = $input['gender'] ?? null;
        $status = $input['status'] ?? 'Active';

        // Normalize enum values to match migration
        $gender = $gender ? ucfirst(strtolower($gender)) : null; // Male/Female/Other
        $status = $status ? ucfirst(strtolower($status)) : 'Active'; // Active/Inactive

        $data = [
            'first_name'         => $input['first_name'] ?? null,
            'middle_name'        => $input['middle_name'] ?? null,
            'last_name'          => $input['last_name'] ?? null,
            'gender'             => $gender,
            'civil_status'       => $input['civil_status'] ?? null,
            'date_of_birth'      => $input['date_of_birth'] ?? null,
            'contact_no'         => $input['phone'] ?? ($input['contact_no'] ?? null),
            'email'              => $input['email'] ?? null,
            'address'            => $input['address'] ?? null,
            'province'           => $input['province'] ?? null,
            'city'               => $input['city'] ?? null,
            'barangay'           => $input['barangay'] ?? null,
            'zip_code'           => $input['zip_code'] ?? null,
            'insurance_provider' => $input['insurance_provider'] ?? null,
            'insurance_number'   => $input['insurance_number'] ?? null,
            'emergency_contact'  => $input['emergency_contact_name'] ?? ($input['emergency_contact'] ?? null),
            'emergency_phone'    => $input['emergency_contact_phone'] ?? ($input['emergency_phone'] ?? null),
            'patient_type'       => $input['patient_type'] ?? null,
            // Optional/unavailable in form: blood_group
            'blood_group'        => $input['blood_group'] ?? null,
            'medical_notes'      => $input['medical_notes'] ?? null,
            'date_registered'    => date('Y-m-d'),
            'status'             => $status,
        ];

        try {
            $builder = $this->db->table('patient');
            $ok = $builder->insert($data);
            if ($ok) {
                return $this->response->setJSON([
                    'status'  => 'success',
                    'message' => 'Patient saved successfully',
                    'id'      => $this->db->insertID(),
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Failed to insert patient: ' . $e->getMessage());
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Database error: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }

        return $this->response->setJSON([
            'status'  => 'error',
            'message' => 'Failed to save patient',
        ])->setStatusCode(500);
    }
    public function getPatient($id)
    {
        try {
            $patient = $this->db->table('patient')
                ->where('patient_id', $id)
                ->get()
                ->getRowArray();

            if ($patient) {
                // Calculate age
                if ($patient['date_of_birth']) {
                    $dob = new \DateTime($patient['date_of_birth']);
                    $now = new \DateTime();
                    $age = $now->diff($dob)->y;
                    $patient['age'] = $age;
                } else {
                    $patient['age'] = null;
                }

                return $this->response->setJSON([
                    'status' => 'success',
                    'patient' => $patient,
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Patient not found',
                ])->setStatusCode(404);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Failed to fetch patient: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Database error',
            ])->setStatusCode(500);
        }
    }

     public function updatePatient($id)
    {
        // Expect JSON payload
        $input = $this->request->getJSON(true) ?? $this->request->getPost();

        // Basic validation for non-nullable fields in migration
        $validation = \Config\Services::validation();
        $validation->setRules([
            'first_name'              => 'required|min_length[2]|max_length[100]',
            'last_name'               => 'required|min_length[2]|max_length[100]',
            'gender'                  => 'required|in_list[male,female,other,MALE,FEMALE,OTHER,Male,Female,Other]',
            'date_of_birth'           => 'required|valid_date',
            'civil_status'            => 'required',
            'phone'                   => 'required|max_length[50]',
            'email'                   => 'permit_empty|valid_email',
            'address'                 => 'required',
            'province'                => 'required|max_length[100]',
            'city'                    => 'required|max_length[100]',
            'barangay'                => 'required|max_length[100]',
            'zip_code'                => 'required|max_length[20]',
            'emergency_contact_name'  => 'required|max_length[100]',
            'emergency_contact_phone' => 'required|max_length[50]',
            'patient_type'            => 'permit_empty|in_list[outpatient,inpatient,emergency,Outpatient,Inpatient,Emergency]'
        ]);

        if (!$validation->run($input)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $validation->getErrors(),
            ])->setStatusCode(422);
        }

        // Map incoming fields to DB schema
        $gender = $input['gender'] ?? null;
        $status = $input['status'] ?? 'Active';

        // Normalize enum values to match migration
        $gender = $gender ? ucfirst(strtolower($gender)) : null; // Male/Female/Other
        $status = $status ? ucfirst(strtolower($status)) : 'Active'; // Active/Inactive

        $data = [
            'first_name'         => $input['first_name'] ?? null,
            'middle_name'        => $input['middle_name'] ?? null,
            'last_name'          => $input['last_name'] ?? null,
            'gender'             => $gender,
            'civil_status'       => $input['civil_status'] ?? null,
            'date_of_birth'      => $input['date_of_birth'] ?? null,
            'contact_no'         => $input['phone'] ?? ($input['contact_no'] ?? null),
            'email'              => $input['email'] ?? null,
            'address'            => $input['address'] ?? null,
            'province'           => $input['province'] ?? null,
            'city'               => $input['city'] ?? null,
            'barangay'           => $input['barangay'] ?? null,
            'zip_code'           => $input['zip_code'] ?? null,
            'insurance_provider' => $input['insurance_provider'] ?? null,
            'insurance_number'   => $input['insurance_number'] ?? null,
            'emergency_contact'  => $input['emergency_contact_name'] ?? ($input['emergency_contact'] ?? null),
            'emergency_phone'    => $input['emergency_contact_phone'] ?? ($input['emergency_phone'] ?? null),
            'patient_type'       => $input['patient_type'] ?? null,
            'blood_group'        => $input['blood_group'] ?? null,
            'medical_notes'      => $input['medical_notes'] ?? null,
            'status'             => $status,
        ];

        try {
            $builder = $this->db->table('patient');
            $updated = $builder->where('patient_id', $id)->update($data);
            if ($updated !== false) {
                return $this->response->setJSON([
                    'status'  => 'success',
                    'message' => 'Patient updated successfully',
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Failed to update patient: ' . $e->getMessage());
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Database error: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }

        return $this->response->setJSON([
            'status'  => 'error',
            'message' => 'Failed to update patient',
        ])->setStatusCode(500);
    }

    /**
     * Get today's appointments for the doctor
     */
    private function getTodayAppointments($doctorId)
    {
        try {
            return $this->db->table('appointments a')
                ->select('a.*, p.first_name as patient_first_name, p.last_name as patient_last_name, p.patient_id, p.date_of_birth')
                ->join('patient p', 'p.patient_id = a.patient_id')
                ->where('a.doctor_id', $doctorId)
                ->where('a.appointment_date', date('Y-m-d'))
                ->orderBy('a.appointment_time', 'ASC')
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'Failed to fetch today appointments: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Calculate today's appointment statistics
     */
    private function calculateTodayStats($doctorId)
    {
        try {
            $today = date('Y-m-d');
            $total = $this->db->table('appointments')
                ->where('doctor_id', $doctorId)
                ->where('appointment_date', $today)
                ->countAllResults();
            
            $completed = $this->db->table('appointments')
                ->where('doctor_id', $doctorId)
                ->where('appointment_date', $today)
                ->where('status', 'completed')
                ->countAllResults();
            
            $pending = $total - $completed;
            
            return [
                'total' => $total,
                'completed' => $completed,
                'pending' => $pending
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Failed to calculate today stats: ' . $e->getMessage());
            return ['total' => 0, 'completed' => 0, 'pending' => 0];
        }
    }
      public function getPatientsAPI()
    {
        try {
            // Fetch patients assigned to this doctor
            $patients = $this->db->table('patient p')
                ->select('p.patient_id, p.first_name, p.middle_name, p.last_name, p.date_of_birth, p.gender, p.contact_no, p.email, p.patient_type, p.status, p.primary_doctor_id, CONCAT(s.first_name, " ", s.last_name) as assigned_doctor_name')
                ->join('staff s', 's.staff_id = p.primary_doctor_id', 'left')
                ->where('p.primary_doctor_id', $this->doctorId)
                ->orderBy('p.patient_id', 'DESC')
                ->get()
                ->getResultArray();

            // Calculate age for each patient
            foreach ($patients as &$patient) {
                if ($patient['date_of_birth']) {
                    $dob = new \DateTime($patient['date_of_birth']);
                    $now = new \DateTime();
                    $age = $now->diff($dob)->y;
                    $patient['age'] = $age;
                } else {
                    $patient['age'] = null;
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $patients
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to fetch patients API: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to load patients',
                'data' => []
            ])->setStatusCode(500);
        }
    }
}
