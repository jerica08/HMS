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

   

    public function appointments()
    {
        // Get doctor ID from session
        $doctorId = session()->get('staff_id');

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

        // Get today's appointments
        $todayAppointments = $this->getTodayAppointments($doctorId);
        
        // Calculate statistics
        $todayStats = $this->calculateTodayStats($doctorId);
        $weekStats = $this->calculateWeekStats($doctorId);
        $scheduleStats = $this->calculateScheduleStats($doctorId);

        $data = [
            'patients' => $patients,
            'todayAppointments' => $todayAppointments,
            'todayStats' => $todayStats,
            'weekStats' => $weekStats,
            'scheduleStats' => $scheduleStats,
        ];

        return view('doctor/appointments', $data);
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
     * Create a new patient record via JSON POST
     */
    
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

        // Get doctor_id from session
        $doctorId = session()->get('staff_id');
        if (!$doctorId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Doctor not authenticated',
            ])->setStatusCode(401);
        }

        // Prepare data for insertion
        $data = [
            'patient_id'        => $input['patient_id'],
            'doctor_id'         => $doctorId,
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
     * Get a single patient by ID (for view/edit)
     */
    

    /**
     * Update a patient record
     */
   
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
     * Get patients data for AJAX requests (API endpoint)
     */
  

    /**
     * Get appointment data for AJAX requests
     */
    public function getAppointmentData()
    {
        $doctorId = session()->get('staff_id');
        $view = $this->request->getGet('view') ?? 'today';
        $date = $this->request->getGet('date') ?? date('Y-m-d');
        
        $appointments = [];
        $stats = [];
        
        try {
            switch ($view) {
                case 'today':
                    $appointments = $this->getAppointmentsByDate($doctorId, $date);
                    break;
                case 'week':
                    $appointments = $this->getAppointmentsByWeek($doctorId, $date);
                    break;
                case 'month':
                    $appointments = $this->getAppointmentsByMonth($doctorId, $date);
                    break;
            }
            
            // Recalculate stats
            $stats = [
                'today' => $this->calculateTodayStats($doctorId),
                'week' => $this->calculateWeekStats($doctorId)
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
        $doctorId = session()->get('staff_id');
        
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
                ->where('doctor_id', $doctorId)
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