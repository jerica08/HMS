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

        // Session check for doctor authentication
        $session = session();
        if (!$session->get('isLoggedIn') || $session->get('role') !== 'doctor') {
            redirect()->to(base_url('/login'))->send();
            exit();
        }

        // Identify logged-in doctor (by staff_id)
        $staffId = $session->get('staff_id');
        if ($staffId) {
            $doctor = $this->db->table('staff')
                ->where('staff_id', $staffId)
                ->where('role', 'doctor')
                ->get()
                ->getRowArray();

            if ($doctor) {
                $this->doctorId = $staffId;
            } else {
                log_message('error', "Staff ID {$staffId} is not registered as a doctor.");
                $this->doctorId = null;
            }
        } else {
            log_message('error', 'Doctor authentication failed: no staff_id in session.');
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
     * Assign a doctor to a patient
     */
    public function assignDoctor()
    {
        $input = $this->request->getJSON(true) ?? $this->request->getPost();

        if (empty($input['patient_id']) || empty($input['doctor_id'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Missing required fields: patient_id and doctor_id',
            ])->setStatusCode(400);
        }

        $patientId = (int)$input['patient_id'];
        $doctorId = (int)$input['doctor_id'];

        try {
            // Validate patient
            $patient = $this->db->table('patient')
                ->where('patient_id', $patientId)
                ->get()
                ->getRowArray();

            if (!$patient) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Patient not found',
                ])->setStatusCode(404);
            }

            // Validate doctor
            $doctor = $this->db->table('staff')
                ->where('staff_id', $doctorId)
                ->where('role', 'doctor')
                ->get()
                ->getRowArray();

            if (!$doctor) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Doctor not found',
                ])->setStatusCode(404);
            }

            // Assign doctor to patient
            $updated = $this->db->table('patient')
                ->where('patient_id', $patientId)
                ->update(['primary_doctor_id' => $doctorId]);

            if ($updated !== false) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Doctor assigned successfully',
                    'doctor_name' => trim($doctor['first_name'] . ' ' . $doctor['last_name']),
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update patient record',
            ])->setStatusCode(500);
        } catch (\Throwable $e) {
            log_message('error', 'Doctor assignment error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Database error occurred',
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
            $staffId = session()->get('staff_id');

            return [
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

    /* 
    // Optional future features:
    public function deleteDoctor($id) { ... }
    public function searchDoctor($keyword) { ... }
    */
}
