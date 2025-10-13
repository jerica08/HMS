<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Doctor extends BaseController
{
    protected $db;
    protected $builder;

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
    }

    public function dashboard()
    {
        return view ('doctor/dashboard');
    }

    public function patients()
    {
        // Fetch patient statistics
        $totalPatients = 0;
        $inPatients = 0;
        $outPatients = 0;
        try {
            $totalPatients = $this->db->table('patient')->countAllResults();
            $inPatients = $this->db->table('patient')->where('patient_type', 'inpatient')->countAllResults();
            $outPatients = $this->db->table('patient')->where('patient_type', 'outpatient')->countAllResults();
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            log_message('error', 'Patients table does not exist: ' . $e->getMessage());
        }

        // Fetch patients list
        $patients = [];
        try {
            $patients = $this->db->table('patient')
                ->select('patient_id as id, first_name, middle_name, last_name, date_of_birth, gender, contact_no as phone, email, patient_type, status')
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

    public function appointments()
    {
        // Get doctor ID from session
        $doctorId = session()->get('staff_id');
        
        // Fetch patients for the dropdown
        $patients = [];
        try {
            $patients = $this->db->table('patient')
                ->select('patient_id, first_name, last_name')
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
        // Fetch patients for the dropdown
        $patients = [];
        try {
            $patients = $this->db->table('patient')->select('patient_id, first_name, last_name')->get()->getResultArray();
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            log_message('error', 'Failed to fetch patients: ' . $e->getMessage());
        }

        // Fetch prescriptions
        $prescriptions = [];
        try {
            $prescriptions = $this->db->table('prescriptions')
                ->select('prescriptions.*, patient.first_name, patient.last_name, patient.patient_id as pat_id')
                ->join('patient', 'patient.patient_id = prescriptions.patient_id')
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

    /**
     * Update a patient record
     */
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
}