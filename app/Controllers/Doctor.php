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