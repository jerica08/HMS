<?php

namespace App\Controllers;

use App\Models\PatientModel;
use CodeIgniter\HTTP\ResponseInterface;

class Patients extends BaseController
{
    protected $db;
    protected $builder;
    protected $doctorId;

    /**
     * Constructor - initializes database connection and checks doctor authentication
     */
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('patient');

        // Ensure doctor is logged in
        $session = session();
        if (!$session->get('isLoggedIn') || $session->get('role') !== 'doctor') {
            redirect()->to(base_url('/login'))->send();
            exit();
        }

        // Get doctor_id from staff_id (ensuring valid doctor account)
        $staffId = $session->get('staff_id');
        if ($staffId) {
            $doctor = $this->db->table('doctor')
                ->where('staff_id', $staffId)
                ->get()
                ->getRowArray();

            $this->doctorId = $doctor ? $doctor['doctor_id'] : null;
        } else {
            $this->doctorId = null;
        }
    }

    /**
     * Display list of patients assigned to this doctor
     */
    public function patients()
    {
        $totalPatients = $inPatients = $outPatients = 0;

        try {
            $totalPatients = $this->builder
                ->where('primary_doctor_id', $this->doctorId)
                ->countAllResults();

            $inPatients = $this->builder
                ->where('primary_doctor_id', $this->doctorId)
                ->where('patient_type', 'inpatient')
                ->countAllResults();

            $outPatients = $this->builder
                ->where('primary_doctor_id', $this->doctorId)
                ->where('patient_type', 'outpatient')
                ->countAllResults();
        } catch (\Throwable $e) {
            log_message('error', 'Error fetching patient counts: ' . $e->getMessage());
        }

        // Fetch full patient list for this doctor
        $patients = [];
        try {
            $patients = $this->builder
                ->select('patient_id as id, first_name, middle_name, last_name, date_of_birth, gender, contact_no as phone, email, patient_type, status')
                ->where('primary_doctor_id', $this->doctorId)
                ->get()
                ->getResultArray();

            // Compute age for each patient
            foreach ($patients as &$patient) {
                $patient['age'] = $patient['date_of_birth']
                    ? (new \DateTime())->diff(new \DateTime($patient['date_of_birth']))->y
                    : null;
            }
        } catch (\Throwable $e) {
            log_message('error', 'Failed to fetch patient list: ' . $e->getMessage());
        }

        return view('doctor/patient', [
            'totalPatients' => $totalPatients,
            'inPatients' => $inPatients,
            'outPatients' => $outPatients,
            'patients' => $patients,
        ]);
    }

    /**
     * Create new patient
     */
    public function createPatient()
    {
        $input = $this->request->getJSON(true) ?? $this->request->getPost();

        // Validation rules (consistent with DB schema)
        $validation = \Config\Services::validation();
        $validation->setRules([
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
        ]);

        if (!$validation->run($input)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validation->getErrors(),
            ])->setStatusCode(422);
        }

        // Normalize data
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
            'primary_doctor_id' => $this->doctorId,
        ];

        try {
            $this->builder->insert($data);
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Patient added successfully',
                'id' => $this->db->insertID(),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to insert patient: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    /**
     * Get specific patient details
     */
    public function getPatient($id)
    {
        try {
            $patient = $this->builder->where('patient_id', $id)->get()->getRowArray();

            if (!$patient) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Patient not found',
                ])->setStatusCode(404);
            }

            // Compute age
            $patient['age'] = $patient['date_of_birth']
                ? (new \DateTime())->diff(new \DateTime($patient['date_of_birth']))->y
                : null;

            return $this->response->setJSON([
                'status' => 'success',
                'patient' => $patient,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Error fetching patient: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Database error',
            ])->setStatusCode(500);
        }
    }

    /**
     * Update patient details
     */
    public function updatePatient($id)
    {
        $input = $this->request->getJSON(true) ?? $this->request->getPost();

        $validation = \Config\Services::validation();
        $validation->setRules([
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
        ]);

        if (!$validation->run($input)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validation->getErrors(),
            ])->setStatusCode(422);
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
            $this->builder->where('patient_id', $id)->update($data);
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Patient updated successfully',
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to update patient: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    /**
     * REST API - Get all patients for logged-in doctor
     */
    public function getPatientsAPI()
    {
        try {
            $patients = $this->db->table('patient p')
                ->select('p.*, CONCAT(s.first_name, " ", s.last_name) as assigned_doctor_name')
                ->join('staff s', 's.staff_id = p.primary_doctor_id', 'left')
                ->where('p.primary_doctor_id', $this->doctorId)
                ->orderBy('p.patient_id', 'DESC')
                ->get()
                ->getResultArray();

            // Compute ages
            foreach ($patients as &$p) {
                $p['age'] = $p['date_of_birth']
                    ? (new \DateTime())->diff(new \DateTime($p['date_of_birth']))->y
                    : null;
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $patients,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Error fetching patient API: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to fetch patients',
                'data' => [],
            ])->setStatusCode(500);
        }
    }
}
