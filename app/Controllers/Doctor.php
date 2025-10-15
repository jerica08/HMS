<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Doctor extends BaseController
{
    protected $db;
    protected $builder;
    protected $doctorId;

    /**
     * Constructor - initializes database connection and checks doctor authentication
     */
    public function __construct()
    {
        // DB Connection
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('patient');

        // Session check for doctor
        $session = session();
        if (!$session->get('isLoggedIn') || $session->get('role') !== 'doctor') {
            redirect()->to(base_url('/login'))->send();
            exit();
        }

        // Get doctor_id from staff_id
        $staffId = $session->get('staff_id');
        if ($staffId) {
            $doctor = $this->db->table('doctor')->where('staff_id', $staffId)->get()->getRowArray();
            $this->doctorId = $doctor ? $doctor['doctor_id'] : null;
        }
    }

    public function dashboard()
    {
        return view ('doctor/dashboard');
    }

    public function prescriptions()
    {
        // Fetch patients assigned to this doctor for the dropdown
        $patients = [];
        try {
            $patients = $this->db->table('patient')
                ->select('patient_id, first_name, last_name')
                ->where('primary_doctor_id', $this->doctorId)
                ->get()
                ->getResultArray();
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            log_message('error', 'Failed to fetch patients: ' . $e->getMessage());
        }

        // Fetch prescriptions created by this doctor
        $prescriptions = [];
        try {
            $prescriptions = $this->db->table('prescriptions')
                ->select('prescriptions.*, patient.first_name, patient.last_name, patient.patient_id as pat_id')
                ->join('patient', 'patient.patient_id = prescriptions.patient_id')
                ->where('prescriptions.doctor_id', $this->doctorId)
                ->orderBy('prescriptions.created_at', 'DESC')
                ->get()
                ->getResultArray();
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            log_message('error', 'Failed to fetch prescriptions: ' . $e->getMessage());
        }

        // Calculate metrics
        $today = date('Y-m-d');
        $totalToday = 0;
        $pending = 0; // active
        $sent = 0; // completed
        try {
            $totalToday = $this->db->table('prescriptions')->where('DATE(date_issued)', $today)->countAllResults();
            $pending = $this->db->table('prescriptions')->where('status', 'active')->countAllResults();
            $sent = $this->db->table('prescriptions')->where('status', 'completed')->countAllResults();
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            // Table might not exist yet
        }

        $data = [
            'patients' => $patients,
            'prescriptions' => $prescriptions,
            'totalToday' => $totalToday,
            'pending' => $pending,
            'sent' => $sent,
        ];

        return view('doctor/prescriptions', $data);
    }

    public function labResults()
    {
        return view('doctor/lab-results');
    }

    public function ehr()
    {
        return view('doctor/ehr');
    }

    public function schedule()
    {
        return view('doctor/schedule');
    }

    /**
     * Create a new prescription via POST
     */
    public function createPrescription()
    {
        // Expect POST data from form
        $input = $this->request->getPost();

        // Basic validation
        $validation = \Config\Services::validation();
        $validation->setRules([
            'patient_id'    => 'required|numeric',
            'date_issued'   => 'required|valid_date',
            'medication'    => 'required|max_length[255]',
            'dosage'        => 'required|max_length[100]',
            'frequency'     => 'required|max_length[100]',
            'duration'      => 'required|max_length[50]',
            'notes'         => 'permit_empty|max_length[1000]'
        ]);

        if (!$validation->run($input)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validation->getErrors(),
            ])->setStatusCode(422);
        }

        // Get doctor_id from session
        $doctorId = session()->get('staff_id');
        if (!$doctorId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Doctor not authenticated',
            ])->setStatusCode(401);
        }

        // Generate prescription_id
        $prescriptionId = 'RX' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        // Prepare data for insertion
        $data = [
            'prescription_id' => $prescriptionId,
            'patient_id'      => $input['patient_id'],
            'doctor_id'       => $doctorId,
            'medication'      => $input['medication'],
            'dosage'          => $input['dosage'],
            'frequency'       => $input['frequency'],
            'duration'        => $input['duration'],
            'notes'           => $input['notes'] ?? null,
            'date_issued'     => $input['date_issued'],
            'status'          => 'active',
            'created_at'      => date('Y-m-d H:i:s'),
        ];

        try {
            $builder = $this->db->table('prescriptions');
            $inserted = $builder->insert($data);
            if ($inserted) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Prescription created successfully',
                    'prescription_id' => $prescriptionId,
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Failed to create prescription: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to create prescription',
        ])->setStatusCode(500);
    }

    /**
     * Get available doctors for assignment
     */
    public function getDoctorsAPI()
    {
        try {
            // Get all staff members with doctor role
            $doctors = $this->db->table('staff')
                ->select('staff_id, first_name, last_name, department, designation')
                ->where('role', 'doctor')
                ->orderBy('first_name', 'ASC')
                ->get()
                ->getResultArray();

            // Format doctor names
            foreach ($doctors as &$doctor) {
                $doctor['full_name'] = trim($doctor['first_name'] . ' ' . $doctor['last_name']);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $doctors
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to fetch doctors API: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to load doctors',
                'data' => []
            ])->setStatusCode(500);
        }
    }

    /**
     * Assign doctor to patient
     */
    public function assignDoctor()
    {
        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        
        if (!isset($input['patient_id']) || !isset($input['doctor_id'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Missing required fields: patient_id and doctor_id'
            ])->setStatusCode(400);
        }
        
        $patientId = $input['patient_id'];
        $doctorId = $input['doctor_id'];
        
        try {
            // Verify patient exists
            $patient = $this->db->table('patient')
                ->where('patient_id', $patientId)
                ->get()
                ->getRowArray();
            
            if (!$patient) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Patient not found'
                ])->setStatusCode(404);
            }
            
            // Verify doctor exists
            $doctor = $this->db->table('staff')
                ->where('staff_id', $doctorId)
                ->where('role', 'doctor')
                ->get()
                ->getRowArray();
            
            if (!$doctor) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Doctor not found'
                ])->setStatusCode(404);
            }
            
            // Update patient's primary doctor
            $updated = $this->db->table('patient')
                ->where('patient_id', $patientId)
                ->update([
                    'primary_doctor_id' => $doctorId
                ]);
            
            if ($updated !== false) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Doctor assigned successfully',
                    'doctor_name' => trim($doctor['first_name'] . ' ' . $doctor['last_name'])
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to assign doctor'
                ])->setStatusCode(500);
            }
            
        } catch (\Throwable $e) {
            log_message('error', 'Failed to assign doctor: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}