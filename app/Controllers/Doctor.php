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
     * Constructor - initializes database connection and verifies doctor authentication
     */
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('patient');

        // Authentication is now handled by the roleauth filter in routes
        $session = session();

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
     * Dashboard - Displays doctor statistics (appointments + patients)
     */
    public function dashboard()
    {
        $todayStats = $this->getTodayAppointmentStats();
        $patientStats = $this->getPatientStats();

        $data = [
            'scheduledToday' => $todayStats['scheduled'],
            'completedToday' => $todayStats['completed'],
            'pendingToday' => $todayStats['pending'],
            'totalPatients' => $patientStats['total'],
            'newPatientsThisWeek' => $patientStats['newThisWeek'],
            'criticalPatients' => $patientStats['critical'],
        ];

        return view('doctor/dashboard', $data);
    }

    /**
     * Static doctor views
     */
    public function labResults() { return view('doctor/lab-results'); }
    public function ehr() { return view('doctor/ehr'); }
    public function schedule() { return view('doctor/schedule'); }

    /**
     * REST API - Fetch all doctors (staff with role='doctor')
     */
    public function getDoctorsAPI()
    {
        try {
            $doctors = $this->db->table('staff')
                ->select('staff_id, first_name, last_name, department, designation')
                ->where('role', 'doctor')
                ->orderBy('first_name', 'ASC')
                ->get()
                ->getResultArray();

            // Add full_name field for easier display
            foreach ($doctors as &$doctor) {
                $doctor['full_name'] = trim($doctor['first_name'] . ' ' . $doctor['last_name']);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $doctors,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Error loading doctors: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unable to load doctors list',
                'data' => [],
            ])->setStatusCode(500);
        }
    }


    /**
     * Compute today's appointment statistics
     */
    private function getTodayAppointmentStats()
    {
        try {
            $today = date('Y-m-d');
            $staffId = session()->get('staff_id'); // Use staff_id like Appointments controller

            if (!$staffId) {
                return ['scheduled' => 0, 'completed' => 0, 'pending' => 0];
            }

            $stats = [
                'scheduled' => $this->db->table('appointments')
                    ->where(['doctor_id' => $staffId, 'appointment_date' => $today, 'status' => 'scheduled'])
                    ->countAllResults(),
                'completed' => $this->db->table('appointments')
                    ->where(['doctor_id' => $staffId, 'appointment_date' => $today, 'status' => 'completed'])
                    ->countAllResults(),
                'pending' => $this->db->table('appointments')
                    ->where('doctor_id', $staffId)
                    ->where('appointment_date', $today)
                    ->whereIn('status', ['scheduled', 'in-progress'])
                    ->countAllResults(),
            ];
            
            // Debug logging
            log_message('info', 'Appointment stats for staff_id ' . $staffId . ' on ' . $today . ': ' . json_encode($stats));
            
            return $stats;
        } catch (\Throwable $e) {
            log_message('error', 'Error fetching today appointment stats: ' . $e->getMessage());
            return ['scheduled' => 0, 'completed' => 0, 'pending' => 0];
        }
    }

    /**
     * Compute patient statistics for doctor
     */
    private function getPatientStats()
    {
        try {
            $weekAgo = date('Y-m-d', strtotime('-7 days'));

            return [
                'total' => $this->db->table('patient')
                    ->where('primary_doctor_id', $this->doctorId)
                    ->countAllResults(),
                'newThisWeek' => $this->db->table('patient')
                    ->where('primary_doctor_id', $this->doctorId)
                    ->where('date_registered >=', $weekAgo)
                    ->countAllResults(),
                'critical' => $this->db->table('patient')
                    ->where('primary_doctor_id', $this->doctorId)
                    ->where('patient_type', 'emergency')
                    ->countAllResults(),
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Error fetching patient stats: ' . $e->getMessage());
            return ['total' => 0, 'newThisWeek' => 0, 'critical' => 0];
        }
    }
}
