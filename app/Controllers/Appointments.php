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
        // DB Connection
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('appointments');

        // Session check for doctor
        $session = session();
        if (!$session->get('isLoggedIn') || $session->get('role') !== 'doctor') {
            redirect()->to(base_url('/login'))->send();
            exit();
        }

        // Get doctor_id (which is actually staff_id in appointments table)
        $staffId = $session->get('staff_id');
        if ($staffId) {
            // Verify this staff member is actually a doctor
            $doctor = $this->db->table('staff')
                ->where('staff_id', $staffId)
                ->where('role', 'doctor')
                ->get()
                ->getRowArray();
            
            if ($doctor) {
                // For appointments table, doctor_id is actually the staff_id
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

    /**
     * Main appointments view with dashboard data
     */
    public function appointments()
    {
        // Fetch all patients for the dropdown (doctors can schedule appointments for any patient)
        $patients = [];
        try {
            $patients = $this->db->table('patient')
                ->select('patient_id, first_name, last_name, patient_type')
                ->where('status', 'active') // Only show active patients
                ->orderBy('first_name', 'ASC')
                ->orderBy('last_name', 'ASC')
                ->get()
                ->getResultArray();
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            log_message('error', 'Failed to fetch patients: ' . $e->getMessage());
        }

        // Get today's appointments
        $todayAppointments = $this->getTodayAppointments($this->doctorId);

        // Calculate statistics
        $todayStats = $this->calculateTodayStats($this->doctorId);
        $weekStats = $this->calculateWeekStats($this->doctorId);
        $scheduleStats = $this->calculateScheduleStats($this->doctorId);

        $data = [
            'patients' => $patients,
            'todayAppointments' => $todayAppointments,
            'todayStats' => $todayStats,
            'weekStats' => $weekStats,
            'scheduleStats' => $scheduleStats,
        ];

        return view('doctor/appointments', $data);
    }

    /**
     * Schedule a new appointment via JSON POST
     */
    public function postScheduleAppointment()
    {
        // Expect JSON payload
        $input = $this->request->getJSON(true) ?? $this->request->getPost();

        // Basic validation
        $validation = \Config\Services::validation();
        $validation->setRules([
            'patient_id'    => 'required|numeric',
            'date'          => 'required|valid_date',
            'time'          => 'required',
            'type'          => 'required|in_list[Consultation,Follow-up,Check-up,Emergency]',
            'reason'        => 'permit_empty|max_length[255]',
            'duration'      => 'required|numeric|greater_than[0]'
        ]);

        if (!$validation->run($input)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validation->getErrors(),
            ])->setStatusCode(422);
        }

        // Use the doctor_id set in constructor
        if (!$this->doctorId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Doctor not authenticated',
            ])->setStatusCode(401);
        }

        // Prepare data for insertion
        $data = [
            'patient_id'        => $input['patient_id'],
            'doctor_id'         => $this->doctorId,
            'appointment_date'  => $input['date'],
            'appointment_time'  => $input['time'],
            'appointment_type'  => $input['type'],
            'reason'            => $input['reason'] ?? null,
            'duration'          => $input['duration'],
            'status'            => 'scheduled',
            'created_at'        => date('Y-m-d H:i:s'),
        ];

        try {
            $builder = $this->db->table('appointments');
            $inserted = $builder->insert($data);
            if ($inserted) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Appointment scheduled successfully',
                    'id'      => $this->db->insertID(),
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Failed to schedule appointment: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to schedule appointment',
        ])->setStatusCode(500);
    }

    /**
     * Get appointment data for AJAX requests
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

        $appointments = [];
        $stats = [];

        try {
            switch ($view) {
                case 'today':
                    $appointments = $this->getAppointmentsByDate($this->doctorId, $date);
                    break;
                case 'week':
                    $appointments = $this->getAppointmentsByWeek($this->doctorId, $date);
                    break;
                case 'month':
                    $appointments = $this->getAppointmentsByMonth($this->doctorId, $date);
                    break;
            }

            // Recalculate stats
            $stats = [
                'today' => $this->calculateTodayStats($this->doctorId),
                'week' => $this->calculateWeekStats($this->doctorId)
            ];

        } catch (\Throwable $e) {
            log_message('error', 'Failed to get appointment data: ' . $e->getMessage());
        }

        return $this->response->setJSON([
            'success' => true,
            'appointments' => $appointments,
            'stats' => $stats
        ]);
    }

    /**
     * Update appointment status
     */
    public function updateAppointmentStatus()
    {
        $input = $this->request->getJSON(true);

        if (!isset($input['appointment_id']) || !isset($input['status'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Missing required fields'
            ])->setStatusCode(400);
        }

        $appointmentId = $input['appointment_id'];
        $status = $input['status'];

        // Validate status
        $validStatuses = ['scheduled', 'in-progress', 'completed', 'cancelled', 'no-show'];
        if (!in_array($status, $validStatuses)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid status'
            ])->setStatusCode(400);
        }

        try {
            // Verify appointment belongs to this doctor
            $appointment = $this->db->table('appointments')
                ->where('id', $appointmentId)
                ->where('doctor_id', $this->doctorId)
                ->get()
                ->getRowArray();

            if (!$appointment) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Appointment not found'
                ])->setStatusCode(404);
            }

            // Update status
            $updated = $this->db->table('appointments')
                ->where('id', $appointmentId)
                ->update([
                    'status' => $status,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            if ($updated) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Appointment status updated successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update appointment status'
                ])->setStatusCode(500);
            }

        } catch (\Throwable $e) {
            log_message('error', 'Failed to update appointment status: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Database error'
            ])->setStatusCode(500);
        }
    }

    /**
     * Get today's appointments for the doctor
     */
    private function getTodayAppointments($doctorId)
    {
        try {
            $today = date('Y-m-d');
            return $this->db->table('appointments a')
                ->select('a.*, p.first_name as patient_first_name, p.last_name as patient_last_name, p.patient_id, p.date_of_birth')
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
            
            $total = $this->db->table('appointments')
                ->where('doctor_id', $doctorId)
                ->where('appointment_date', $today)
                ->countAllResults();
            
            $completed = $this->db->table('appointments')
                ->where('doctor_id', $doctorId)
                ->where('appointment_date', $today)
                ->where('status', 'completed')
                ->countAllResults();
            
            $pending = $this->db->table('appointments')
                ->where('doctor_id', $doctorId)
                ->where('appointment_date', $today)
                ->where('status', 'scheduled')
                ->countAllResults();
            
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
            
            // Get next appointment
            $nextAppointment = $this->db->table('appointments')
                ->where('doctor_id', $doctorId)
                ->where('appointment_date', $today)
                ->where('appointment_time >', $currentTime)
                ->where('status', 'scheduled')
                ->orderBy('appointment_time', 'ASC')
                ->limit(1)
                ->get()
                ->getRowArray();
            
            $nextAppointmentTime = 'None';
            if ($nextAppointment) {
                $nextAppointmentTime = date('g:i A', strtotime($nextAppointment['appointment_time']));
            }
            
            // Calculate total scheduled hours today
            $appointments = $this->db->table('appointments')
                ->where('doctor_id', $doctorId)
                ->where('appointment_date', $today)
                ->where('status !=', 'cancelled')
                ->get()
                ->getResultArray();
            
            $totalMinutes = 0;
            foreach ($appointments as $appointment) {
                $totalMinutes += $appointment['duration'] ?? 30;
            }
            $hoursScheduled = round($totalMinutes / 60, 1);
            
            return [
                'next_appointment' => $nextAppointmentTime,
                'hours_scheduled' => $hoursScheduled
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Failed to calculate schedule stats: ' . $e->getMessage());
            return ['next_appointment' => 'None', 'hours_scheduled' => 0];
        }
    }

    /**
     * Get appointments by specific date
     */
    private function getAppointmentsByDate($doctorId, $date)
    {
        try {
            $appointments = $this->db->table('appointments a')
                ->select('a.*, p.first_name as patient_first_name, p.last_name as patient_last_name, p.patient_id, p.date_of_birth')
                ->join('patient p', 'p.patient_id = a.patient_id')
                ->where('a.doctor_id', $doctorId)
                ->where('a.appointment_date', $date)
                ->orderBy('a.appointment_time', 'ASC')
                ->get()
                ->getResultArray();
            
            // Calculate age for each patient
            foreach ($appointments as &$appointment) {
                if ($appointment['date_of_birth']) {
                    $dob = new \DateTime($appointment['date_of_birth']);
                    $now = new \DateTime();
                    $appointment['patient_age'] = $now->diff($dob)->y;
                } else {
                    $appointment['patient_age'] = 'N/A';
                }
            }
            
            return $appointments;
        } catch (\Throwable $e) {
            log_message('error', 'Failed to fetch appointments by date: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get appointments by week
     */
    private function getAppointmentsByWeek($doctorId, $date)
    {
        try {
            $startOfWeek = date('Y-m-d', strtotime('monday this week', strtotime($date)));
            $endOfWeek = date('Y-m-d', strtotime('sunday this week', strtotime($date)));
            
            $appointments = $this->db->table('appointments a')
                ->select('a.*, p.first_name as patient_first_name, p.last_name as patient_last_name, p.patient_id, p.date_of_birth')
                ->join('patient p', 'p.patient_id = a.patient_id')
                ->where('a.doctor_id', $doctorId)
                ->where('a.appointment_date >=', $startOfWeek)
                ->where('a.appointment_date <=', $endOfWeek)
                ->orderBy('a.appointment_date', 'ASC')
                ->orderBy('a.appointment_time', 'ASC')
                ->get()
                ->getResultArray();
            
            // Calculate age for each patient
            foreach ($appointments as &$appointment) {
                if ($appointment['date_of_birth']) {
                    $dob = new \DateTime($appointment['date_of_birth']);
                    $now = new \DateTime();
                    $appointment['patient_age'] = $now->diff($dob)->y;
                } else {
                    $appointment['patient_age'] = 'N/A';
                }
            }
            
            return $appointments;
        } catch (\Throwable $e) {
            log_message('error', 'Failed to fetch appointments by week: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get appointments by month
     */
    private function getAppointmentsByMonth($doctorId, $date)
    {
        try {
            $startOfMonth = date('Y-m-01', strtotime($date));
            $endOfMonth = date('Y-m-t', strtotime($date));
            
            $appointments = $this->db->table('appointments a')
                ->select('a.*, p.first_name as patient_first_name, p.last_name as patient_last_name, p.patient_id, p.date_of_birth')
                ->join('patient p', 'p.patient_id = a.patient_id')
                ->where('a.doctor_id', $doctorId)
                ->where('a.appointment_date >=', $startOfMonth)
                ->where('a.appointment_date <=', $endOfMonth)
                ->orderBy('a.appointment_date', 'ASC')
                ->orderBy('a.appointment_time', 'ASC')
                ->get()
                ->getResultArray();
            
            // Calculate age for each patient
            foreach ($appointments as &$appointment) {
                if ($appointment['date_of_birth']) {
                    $dob = new \DateTime($appointment['date_of_birth']);
                    $now = new \DateTime();
                    $appointment['patient_age'] = $now->diff($dob)->y;
                } else {
                    $appointment['patient_age'] = 'N/A';
                }
            }
            
            return $appointments;
        } catch (\Throwable $e) {
            log_message('error', 'Failed to fetch appointments by month: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get appointment details for viewing
     */
    public function getAppointmentDetails($appointmentId)
    {
        if (!$this->doctorId) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'Doctor not authenticated'
            ]);
        }

        try {
            // Determine the correct primary key field
            $fields = $this->db->getFieldNames('appointments');
            $primaryKeyField = in_array('appointment_id', $fields) ? 'appointment_id' : 'id';
            
            // Build the select statement dynamically based on available fields
            $selectFields = [
                'a.' . $primaryKeyField . ' as appointment_id',
                'a.appointment_date',
                'a.appointment_time', 
                'a.appointment_type',
                'a.reason',
                'a.duration',
                'a.status',
                'a.doctor_id',
                'a.patient_id',
                'p.first_name as patient_first_name',
                'p.last_name as patient_last_name',
                'p.date_of_birth'
            ];
            
            // Add patient fields that exist
            $patientFields = $this->db->getFieldNames('patient');
            if (in_array('contact_no', $patientFields)) {
                $selectFields[] = 'p.contact_no as patient_phone';
            }
            if (in_array('email', $patientFields)) {
                $selectFields[] = 'p.email as patient_email';
            }
            if (in_array('address', $patientFields)) {
                $selectFields[] = 'p.address';
            }
            if (in_array('gender', $patientFields)) {
                $selectFields[] = 'p.gender';
            }
            if (in_array('blood_group', $patientFields)) {
                $selectFields[] = 'p.blood_group as blood_type';
            }
            if (in_array('medical_notes', $patientFields)) {
                $selectFields[] = 'p.medical_notes as medical_history';
            }
            if (in_array('emergency_contact', $patientFields)) {
                $selectFields[] = 'p.emergency_contact as emergency_contact_name';
            }
            if (in_array('emergency_phone', $patientFields)) {
                $selectFields[] = 'p.emergency_phone as emergency_contact_phone';
            }
            
            // Fetch appointment with patient information
            $appointment = $this->db->table('appointments a')
                ->select(implode(', ', $selectFields))
                ->join('patient p', 'p.patient_id = a.patient_id', 'left')
                ->where('a.' . $primaryKeyField, $appointmentId)
                ->where('a.doctor_id', $this->doctorId)
                ->get()
                ->getRowArray();

            if (!$appointment) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Appointment not found or access denied'
                ]);
            }

            // Calculate patient age
            if (!empty($appointment['date_of_birth'])) {
                try {
                    $dob = new \DateTime($appointment['date_of_birth']);
                    $now = new \DateTime();
                    $appointment['patient_age'] = $now->diff($dob)->y;
                } catch (\Exception $e) {
                    $appointment['patient_age'] = 'N/A';
                }
            } else {
                $appointment['patient_age'] = 'N/A';
            }

            // Format date and time for display
            if (!empty($appointment['appointment_date'])) {
                $appointment['formatted_date'] = date('F j, Y', strtotime($appointment['appointment_date']));
            }
            if (!empty($appointment['appointment_time'])) {
                $appointment['formatted_time'] = date('g:i A', strtotime($appointment['appointment_time']));
            }
            
            return $this->response->setJSON([
                'success' => true,
                'appointment' => $appointment
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'Failed to fetch appointment details: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }
}