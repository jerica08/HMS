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

        // Get doctor_id (which is actually staff_id for most operations)
        $staffId = $session->get('staff_id');
        if ($staffId) {
            // Verify this staff member is actually a doctor
            $doctor = $this->db->table('staff')
                ->where('staff_id', $staffId)
                ->where('role', 'doctor')
                ->get()
                ->getRowArray();
            
            if ($doctor) {
                // For most operations, doctor_id is actually the staff_id
                $this->doctorId = $staffId;
            } else {
                log_message('error', 'Staff member with ID ' . $staffId . ' is not a doctor or does not exist');
                $this->doctorId = null;
            }
        } else {
            log_message('error', 'No staff_id found in session for doctor authentication');
            $this->doctorId = null;
        }
    }

    public function dashboard()
    {
        // Get today's appointment statistics
        $todayStats = $this->getTodayAppointmentStats();
        
        // Get patient statistics
        $patientStats = $this->getPatientStats();
        
        $data = [
            'scheduledToday' => $todayStats['scheduled'],
            'completedToday' => $todayStats['completed'], 
            'pendingToday' => $todayStats['pending'],
            'totalPatients' => $patientStats['total'],
            'newPatientsThisWeek' => $patientStats['newThisWeek'],
            'criticalPatients' => $patientStats['critical']
        ];
        
        return view('doctor/dashboard', $data);
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

    /**
     * Get today's appointment statistics
     */
    private function getTodayAppointmentStats()
    {
        try {
            $today = date('Y-m-d');
            $staffId = session()->get('staff_id');
            
            // Get total scheduled appointments for today
            $scheduled = $this->db->table('appointments')
                ->where('doctor_id', $staffId)
                ->where('appointment_date', $today)
                ->where('status', 'scheduled')
                ->countAllResults();
            
            // Get completed appointments for today
            $completed = $this->db->table('appointments')
                ->where('doctor_id', $staffId)
                ->where('appointment_date', $today)
                ->where('status', 'completed')
                ->countAllResults();
            
            // Get pending appointments (scheduled but not completed)
            $pending = $this->db->table('appointments')
                ->where('doctor_id', $staffId)
                ->where('appointment_date', $today)
                ->whereIn('status', ['scheduled', 'in-progress'])
                ->countAllResults();
            
            return [
                'scheduled' => $scheduled,
                'completed' => $completed,
                'pending' => $pending
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Failed to fetch appointment stats: ' . $e->getMessage());
            return ['scheduled' => 0, 'completed' => 0, 'pending' => 0];
        }
    }

    /**
     * Get patient statistics
     */
    private function getPatientStats()
    {
        try {
            // Get total patients assigned to this doctor
            $total = $this->db->table('patient')
                ->where('primary_doctor_id', $this->doctorId)
                ->countAllResults();
            
            // Get new patients this week (registered in the last 7 days)
            $weekAgo = date('Y-m-d', strtotime('-7 days'));
            $newThisWeek = $this->db->table('patient')
                ->where('primary_doctor_id', $this->doctorId)
                ->where('date_registered >=', $weekAgo)
                ->countAllResults();
            
            // Get critical patients (assuming we have a field for this, or use patient_type = 'emergency')
            $critical = $this->db->table('patient')
                ->where('primary_doctor_id', $this->doctorId)
                ->where('patient_type', 'emergency')
                ->countAllResults();
            
            return [
                'total' => $total,
                'newThisWeek' => $newThisWeek,
                'critical' => $critical
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Failed to fetch patient stats: ' . $e->getMessage());
            return ['total' => 0, 'newThisWeek' => 0, 'critical' => 0];
        }
    }
}