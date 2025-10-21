<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Appointments extends BaseController
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
        $this->builder = $this->db->table('appointments');

        // Session check for doctor authentication
        $session = session();
        if (!$session->get('isLoggedIn') || $session->get('role') !== 'doctor') {
            redirect()->to(base_url('/login'))->send();
            exit();
        }

        // Verify doctor identity
        $staffId = $session->get('staff_id');
        if ($staffId) {
            $doctor = $this->db->table('staff')
                ->where('staff_id', $staffId)
                ->where('role', 'doctor')
                ->get()
                ->getRowArray();

            $this->doctorId = $doctor ? $staffId : null;
        } else {
            $this->doctorId = null;
        }
    }

    /**
     * Main appointments dashboard view
     */
    public function appointments()
    {
        try {
            $patients = $this->db->table('patient')
                ->select('patient_id, first_name, last_name, patient_type')
                ->where('status', 'active')
                ->orderBy('first_name', 'ASC')
                ->orderBy('last_name', 'ASC')
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'Failed to fetch patients: ' . $e->getMessage());
            $patients = [];
        }

        $todayAppointments = $this->getTodayAppointments($this->doctorId);
        $todayStats = $this->calculateTodayStats($this->doctorId);
        $weekStats = $this->calculateWeekStats($this->doctorId);
        $scheduleStats = $this->calculateScheduleStats($this->doctorId);

        return view('doctor/appointments', [
            'patients' => $patients,
            'todayAppointments' => $todayAppointments,
            'todayStats' => $todayStats,
            'weekStats' => $weekStats,
            'scheduleStats' => $scheduleStats
        ]);
    }

    /**
     * Schedule a new appointment
     */
    public function postScheduleAppointment()
{
    $input = $this->request->getJSON(true) ?? $this->request->getPost();
    $session = session();
    
    $appointmentService = new \App\Services\AppointmentService();
    $result = $appointmentService->createAppointment(
        $input, 
        $session->get('role'), 
        $session->get('staff_id')
    );
    
    return $this->response->setJSON($result)
        ->setStatusCode($result['success'] ? 200 : 422);
}

    /**
     * Get appointment data for AJAX requests (Today/Week/Month view)
     */
    public function getAppointmentData()
    {
        if (!$this->doctorId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Doctor not authenticated'
            ])->setStatusCode(401);
        }

        $view = $this->request->getGet('view') ?? 'today';
        $date = $this->request->getGet('date') ?? date('Y-m-d');
        $status = $this->request->getGet('status') ?? '';

        try {
            $appointments = [];
            $stats = [];

            switch ($view) {
                case 'today':
                    $appointments = $this->getAppointmentsByDate($this->doctorId, $date, $status);
                    $stats['today'] = $this->calculateTodayStats($this->doctorId);
                    break;
                case 'week':
                    $appointments = $this->getAppointmentsByWeek($this->doctorId, $date, $status);
                    $stats['week'] = $this->calculateWeekStats($this->doctorId);
                    break;
                case 'month':
                    $appointments = $this->getAppointmentsByMonth($this->doctorId, $date, $status);
                    break;
                default:
                    $appointments = $this->getAppointmentsByDate($this->doctorId, $date, $status);
                    $stats['today'] = $this->calculateTodayStats($this->doctorId);
            }

            return $this->response->setJSON([
                'success' => true,
                'appointments' => $appointments,
                'stats' => $stats
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to fetch appointment data: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error fetching appointments: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Update appointment status
     */
    public function updateAppointmentStatus()
{
    $input = $this->request->getJSON(true) ?? $this->request->getPost();
    $session = session();
    
    if (!isset($input['appointment_id']) || !isset($input['status'])) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Appointment ID and status are required'
        ])->setStatusCode(422);
    }
    
    $appointmentService = new \App\Services\AppointmentService();
    $result = $appointmentService->updateAppointmentStatus(
        $input['appointment_id'],
        $input['status'],
        $session->get('role'),
        $session->get('staff_id')
    );
    
    return $this->response->setJSON($result);
}
    /**
 * Delete appointment - Using unified service
 */
public function deleteAppointment()
{
    $input = $this->request->getJSON(true) ?? $this->request->getPost();
    $session = session();
    
    if (!isset($input['appointment_id'])) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Appointment ID is required'
        ])->setStatusCode(422);
    }
    
    $appointmentService = new \App\Services\AppointmentService();
    $result = $appointmentService->deleteAppointment(
        $input['appointment_id'],
        $session->get('role'),
        $session->get('staff_id')
    );
    
    return $this->response->setJSON($result);
}

    /**
     * Get appointment details for modal view
     */
    public function getAppointmentDetails($appointmentId)
    {
        if (!$this->doctorId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Doctor not authenticated'
            ])->setStatusCode(401);
        }

        try {
            $appointment = $this->db->table('appointments a')
                ->select('a.*, p.first_name as patient_first_name, p.last_name as patient_last_name, p.patient_id, p.phone as patient_phone, p.email as patient_email, p.date_of_birth, p.gender, p.address, p.emergency_contact_name, p.emergency_contact_phone, p.medical_history')
                ->join('patient p', 'p.patient_id = a.patient_id')
                ->where('a.appointment_id', $appointmentId)
                ->where('a.doctor_id', $this->doctorId)
                ->get()
                ->getRowArray();

            if (!$appointment) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Appointment not found'
                ])->setStatusCode(404);
            }

            // Calculate patient age
            if (!empty($appointment['date_of_birth'])) {
                $dob = new \DateTime($appointment['date_of_birth']);
                $now = new \DateTime();
                $appointment['patient_age'] = $now->diff($dob)->y;
            } else {
                $appointment['patient_age'] = 'N/A';
            }

            // Format dates and times
            $appointment['formatted_date'] = date('F j, Y', strtotime($appointment['appointment_date']));
            $appointment['formatted_time'] = date('g:i A', strtotime($appointment['appointment_time']));

            return $this->response->setJSON([
                'success' => true,
                'appointment' => $appointment
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to fetch appointment details: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error fetching appointment details'
            ])->setStatusCode(500);
        }
    }

    /**
     * Get today's appointments
     */
    private function getTodayAppointments($doctorId)
    {
        try {
            $today = date('Y-m-d');
            return $this->db->table('appointments a')
                ->select('a.*, p.first_name, p.last_name')
                ->join('patient p', 'p.patient_id = a.patient_id')
                ->where('a.doctor_id', $doctorId)
                ->where('a.appointment_date', $today)
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
            $total = $this->db->table('appointments')->where(['doctor_id' => $doctorId, 'appointment_date' => $today])->countAllResults();
            $completed = $this->db->table('appointments')->where(['doctor_id' => $doctorId, 'appointment_date' => $today, 'status' => 'completed'])->countAllResults();
            $pending = $this->db->table('appointments')->where(['doctor_id' => $doctorId, 'appointment_date' => $today, 'status' => 'scheduled'])->countAllResults();

            return compact('total', 'completed', 'pending');
        } catch (\Throwable $e) {
            log_message('error', 'Failed to calculate today stats: ' . $e->getMessage());
            return ['total' => 0, 'completed' => 0, 'pending' => 0];
        }
    }

    /**
     * Calculate weekly appointment statistics
     */
    private function calculateWeekStats($doctorId)
    {
        try {
            $startOfWeek = date('Y-m-d', strtotime('monday this week'));
            $endOfWeek = date('Y-m-d', strtotime('sunday this week'));

            $total = $this->db->table('appointments')
                ->where('doctor_id', $doctorId)
                ->where('appointment_date >=', $startOfWeek)
                ->where('appointment_date <=', $endOfWeek)
                ->countAllResults();

            $cancelled = $this->db->table('appointments')
                ->where('doctor_id', $doctorId)
                ->where('appointment_date >=', $startOfWeek)
                ->where('appointment_date <=', $endOfWeek)
                ->where('status', 'cancelled')
                ->countAllResults();

            $noShows = $this->db->table('appointments')
                ->where('doctor_id', $doctorId)
                ->where('appointment_date >=', $startOfWeek)
                ->where('appointment_date <=', $endOfWeek)
                ->where('status', 'no-show')
                ->countAllResults();

            return [
                'total' => $total,
                'cancelled' => $cancelled,
                'no_shows' => $noShows
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Failed to calculate week stats: ' . $e->getMessage());
            return ['total' => 0, 'cancelled' => 0, 'no_shows' => 0];
        }
    }

    /**
     * Calculate schedule statistics
     */
    private function calculateScheduleStats($doctorId)
    {
        try {
            $today = date('Y-m-d');
            $currentTime = date('H:i:s');

            // Find the next appointment for the day
            $nextAppointment = $this->db->table('appointments')
                ->where('doctor_id', $doctorId)
                ->where('appointment_date', $today)
                ->where('appointment_time >', $currentTime)
                ->where('status', 'scheduled')
                ->orderBy('appointment_time', 'ASC')
                ->limit(1)
                ->get()
                ->getRowArray();

            $nextAppointmentTime = $nextAppointment
                ? date('g:i A', strtotime($nextAppointment['appointment_time']))
                : 'None';

            // Compute total scheduled hours today
            $appointments = $this->db->table('appointments')
                ->where('doctor_id', $doctorId)
                ->where('appointment_date', $today)
                ->where('status !=', 'cancelled')
                ->get()
                ->getResultArray();

            $totalMinutes = array_sum(array_column($appointments, 'duration'));
            $hoursScheduled = round($totalMinutes / 60, 1);

            return [
                'next_appointment' => $nextAppointmentTime,
                'hours_scheduled' => $hoursScheduled
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Failed to calculate schedule stats: ' . $e->getMessage());
            return ['next_appointment' => 'Error', 'hours_scheduled' => 0];
        }
    }
}
