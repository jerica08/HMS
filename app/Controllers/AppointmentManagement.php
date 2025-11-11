<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\AppointmentService;

class AppointmentManagement extends BaseController
{
    protected $db;
    protected $appointmentService;
    protected $userRole;
    protected $staffId;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->appointmentService = new AppointmentService();
        
        $session = session();
        $this->userRole = $session->get('role');
        $this->staffId = $session->get('staff_id');
    }

    /**
     * Main appointment management view - Role-based
     */
    public function index()
    {
        try {
            $stats = $this->getAppointmentStats();
            $appointments = $this->getAppointments();
            $doctors = $this->getDoctorsListData(); // Always fetch doctors like ShiftManagement
            $availablePatients = $this->getAvailablePatients();
            
            // Debug: Log the data
            log_message('debug', 'AppointmentManagement::index - doctors count: ' . count($doctors));
            log_message('debug', 'AppointmentManagement::index - user role: ' . ($this->userRole ?? 'none'));
            
            $data = [
                'title' => $this->getPageTitle(),
                'appointmentStats' => $stats,
                'appointments' => $appointments,
                'doctors' => $doctors,
                'availablePatients' => $availablePatients,
                'userRole' => $this->userRole,
                'permissions' => $this->getUserPermissions()
            ];

            // Use unified view that adapts to user role
            return view('unified/appointments', $data);
            
        } catch (\Exception $e) {
            log_message('error', 'AppointmentManagement index error: ' . $e->getMessage());
            
            // Fallback data
            $data = [
                'title' => 'Appointments',
                'appointmentStats' => [],
                'appointments' => [],
                'doctors' => [],
                'availablePatients' => [],
                'userRole' => $this->userRole ?? 'guest',
                'permissions' => []
            ];
            
            return view('unified/appointments', $data);
        }
    }

    /**
     * Create Appointment - All authorized roles
     */
    public function createAppointment()
    {
        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        
        $result = $this->appointmentService->createAppointment(
            $input,
            $this->userRole,
            $this->staffId
        );
        
        return $this->response->setJSON($result);
    }

    public function updateAppointment($appointmentId = null)
    {
        $appointmentId = $appointmentId ?? $this->request->getPost('appointment_id');
        
        if (!$this->canEditAppointment($appointmentId)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Permission denied'
            ]);
        }

        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        
        try {
            $updateData = [];
            
            if (isset($input['appointment_date'])) {
                $updateData['appointment_date'] = $input['appointment_date'];
            }
            if (isset($input['appointment_time'])) {
                $updateData['appointment_time'] = $input['appointment_time'];
            }
            if (isset($input['reason'])) {
                $updateData['reason'] = $input['reason'];
            }
            if (isset($input['notes'])) {
                $updateData['notes'] = $input['notes'];
            }
            
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            
            $builder = $this->db->table('appointments');
            
            // Role-based filtering
            if ($this->userRole === 'doctor') {
                $builder->where('doctor_id', $this->staffId);
            }
            
            $result = $builder->where('appointment_id', $appointmentId)->update($updateData);
            
            if ($result) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Appointment updated successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Failed to update appointment'
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Update appointment error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Database error'
            ]);
        }
    }

    /**
     * Get Single Appointment
     */
    public function getAppointment($appointmentId)
    {
        if (!$this->canViewAppointment($appointmentId)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Permission denied'
            ]);
        }

        $result = $this->appointmentService->getAppointment($appointmentId);
        
        if ($result['success']) {
            return $this->response->setJSON([
                'status' => 'success',
                'data' => $result['appointment']
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $result['message']
            ]);
        }
    }

    /**
     * Delete Appointment - Admin only
     */
    public function deleteAppointment($appointmentId)
    {
        if (!$this->canDeleteAppointment($appointmentId)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Permission denied'
            ]);
        }

        $result = $this->appointmentService->deleteAppointment(
            $appointmentId,
            $this->userRole,
            $this->staffId
        );
        
        return $this->response->setJSON($result);
    }

    /**
     * API Endpoint for Appointment List
     */
    public function getAppointmentsAPI()
    {
        $filters = [];
        
        // Role-based filtering
        if ($this->userRole === 'doctor') {
            $filters['doctor_id'] = $this->staffId;
        }
        
        // Optional filters from request
        if ($this->request->getGet('date')) {
            $filters['date'] = $this->request->getGet('date');
        }
        if ($this->request->getGet('status')) {
            $filters['status'] = $this->request->getGet('status');
        }
        if ($this->request->getGet('patient_id')) {
            $filters['patient_id'] = $this->request->getGet('patient_id');
        }
        if ($this->request->getGet('doctor_id') && $this->userRole === 'admin') {
            $filters['doctor_id'] = $this->request->getGet('doctor_id');
        }
        
        $result = $this->appointmentService->getAppointments($filters);
        
        return $this->response->setJSON([
            'status' => $result['success'] ? 'success' : 'error',
            'data' => $result['data'] ?? [],
            'message' => $result['message'] ?? null
        ]);
    }

    /**
     * Get appointments for a specific patient
     */
    public function getPatientAppointments($patientId)
    {
        if (!$this->canViewPatientAppointments($patientId)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Permission denied'
            ]);
        }

        $filters = ['patient_id' => $patientId];
        
        // Role-based filtering
        if ($this->userRole === 'doctor') {
            $filters['doctor_id'] = $this->staffId;
        }
        
        $result = $this->appointmentService->getAppointments($filters);
        
        return $this->response->setJSON([
            'status' => $result['success'] ? 'success' : 'error',
            'data' => $result['data'] ?? [],
            'message' => $result['message'] ?? null
        ]);
    }

    /**
     * Update Appointment Status (Complete, Cancel, etc.)
     */
    public function updateAppointmentStatus($appointmentId)
    {
        if (!$this->canEditAppointment($appointmentId)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Permission denied'
            ]);
        }

        $status = $this->request->getPost('status');
        
        $result = $this->appointmentService->updateAppointmentStatus(
            $appointmentId,
            $status,
            $this->userRole,
            $this->staffId
        );
        
        return $this->response->setJSON($result);
    }

    // ===================================================================
    // PRIVATE HELPER METHODS
    // ===================================================================

    /**
     * Get Appointments based on user role
     */
    private function getAppointments()
    {
        $filters = [];
        
        // Role-based filtering
        if ($this->userRole === 'doctor') {
            $filters['doctor_id'] = $this->staffId;
        }
        
        $result = $this->appointmentService->getAppointments($filters);
        return $result['data'] ?? [];
    }

    /**
     * Get Appointment Statistics
     */
    private function getAppointmentStats()
    {
        $stats = [
            'today_total' => 0,
            'today_completed' => 0,
            'today_pending' => 0,
            'week_total' => 0,
            'week_cancelled' => 0,
            'week_no_shows' => 0,
            'next_appointment' => 'None',
            'hours_scheduled' => 0
        ];
        
        try {
            $today = date('Y-m-d');
            $weekStart = date('Y-m-d', strtotime('monday this week'));
            $weekEnd = date('Y-m-d', strtotime('sunday this week'));
            
            $builder = $this->db->table('appointments');
            
            // Apply role-based filtering
            if ($this->userRole === 'doctor') {
                $builder->where('doctor_id', $this->staffId);
            } elseif ($this->userRole === 'nurse') {
                // Filter by department
                $builder->join('staff', 'staff.staff_id = appointments.doctor_id')
                        ->join('staff as nurse_staff', 'nurse_staff.staff_id = ' . $this->staffId)
                        ->where('staff.department = nurse_staff.department');
            }
            
            // Today's stats
            $todayBuilder = clone $builder;
            $stats['today_total'] = $todayBuilder->where('appointment_date', $today)->countAllResults(false);
            $stats['today_completed'] = $todayBuilder->where('status', 'completed')->countAllResults(false);
            $stats['today_pending'] = $todayBuilder->whereIn('status', ['scheduled', 'in-progress'])->countAllResults();
            
            // Week stats
            $weekBuilder = clone $builder;
            $stats['week_total'] = $weekBuilder->where('appointment_date >=', $weekStart)
                                             ->where('appointment_date <=', $weekEnd)
                                             ->countAllResults(false);
            $stats['week_cancelled'] = $weekBuilder->where('status', 'cancelled')->countAllResults(false);
            $stats['week_no_shows'] = $weekBuilder->where('status', 'no-show')->countAllResults();
            
            // Next appointment
            $nextBuilder = clone $builder;
            $nextAppointment = $nextBuilder->select('appointment_time')
                                          ->where('appointment_date', $today)
                                          ->where('appointment_time >', date('H:i:s'))
                                          ->whereIn('status', ['scheduled', 'in-progress'])
                                          ->orderBy('appointment_time', 'ASC')
                                          ->limit(1)
                                          ->get()
                                          ->getRow();
            
            if ($nextAppointment) {
                $stats['next_appointment'] = date('g:i A', strtotime($nextAppointment->appointment_time));
            }
            
            // Hours scheduled today
            $hoursBuilder = clone $builder;
            $appointments = $hoursBuilder->select('duration')
                                        ->where('appointment_date', $today)
                                        ->whereIn('status', ['scheduled', 'in-progress', 'completed'])
                                        ->get()
                                        ->getResultArray();
            
            $totalMinutes = 0;
            foreach ($appointments as $appointment) {
                $totalMinutes += (int)($appointment['duration'] ?? 30);
            }
            $stats['hours_scheduled'] = round($totalMinutes / 60, 1);
            
        } catch (\Throwable $e) {
            log_message('error', 'Appointment stats error: ' . $e->getMessage());
        }
        
        return $stats;
    }

    /**
     * Get User Permissions for UI
     */
    private function getUserPermissions()
    {
        return [
            'canCreate' => in_array($this->userRole, ['admin', 'receptionist', 'doctor']),
            'canEdit' => in_array($this->userRole, ['admin', 'receptionist', 'doctor']),
            'canDelete' => in_array($this->userRole, ['admin']),
            'canViewAll' => in_array($this->userRole, ['admin', 'receptionist']),
            'canUpdateStatus' => in_array($this->userRole, ['admin', 'doctor', 'nurse']),
            'canSchedule' => in_array($this->userRole, ['admin', 'receptionist', 'doctor']),
            'canReschedule' => in_array($this->userRole, ['admin', 'receptionist', 'doctor'])
        ];
    }

    /**
     * Check if user can view appointment
     */
    private function canViewAppointment($appointmentId)
    {
        switch ($this->userRole) {
            case 'admin':
            case 'receptionist':
                return true;
            case 'doctor':
                $appointment = $this->db->table('appointments')
                    ->where('appointment_id', $appointmentId)
                    ->where('doctor_id', $this->staffId)
                    ->get()->getRow();
                return !empty($appointment);
            case 'nurse':
                return $this->isAppointmentInNurseDepartment($appointmentId);
            default:
                return false;
        }
    }

    /**
     * Check if user can edit appointment
     */
    private function canEditAppointment($appointmentId)
    {
        switch ($this->userRole) {
            case 'admin':
            case 'receptionist':
                return true;
            case 'doctor':
                return $this->canViewAppointment($appointmentId);
            case 'nurse':
                // Nurses can update status but not reschedule
                return $this->isAppointmentInNurseDepartment($appointmentId);
            default:
                return false;
        }
    }

    /**
     * Check if user can delete appointment
     */
    private function canDeleteAppointment($appointmentId)
    {
        return $this->userRole === 'admin';
    }

    /**
     * Get page title based on user role
     */
    private function getPageTitle()
    {
        $titles = [
            'admin' => 'System Appointments',
            'doctor' => 'My Appointments',
            'nurse' => 'Department Appointments',
            'receptionist' => 'Appointment Booking'
        ];
        
        return $titles[$this->userRole] ?? 'Appointments';
    }

    /**
     * Get doctors list for admin filtering
     */
    private function getDoctorsListData()
    {
        try {
            log_message('debug', 'AppointmentManagement::getDoctorsListData called');
            
            $doctors = $this->db->table('doctor d')
                ->select('s.staff_id, s.first_name, s.last_name, d.specialization')
                ->join('staff s', 's.staff_id = d.staff_id', 'inner')
                ->where('d.status', 'Active') // Filter by doctor status instead of staff status
                ->get()
                ->getResultArray();
                
            log_message('debug', 'AppointmentManagement::getDoctorsListData found ' . count($doctors) . ' doctors');
            
            return $doctors;
        } catch (\Exception $e) {
            log_message('error', 'Get doctors list error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get patients list for prescription modal
     */
    private function getAvailablePatients()
    {
        try {
            return $this->db->table('patient p')
                ->select('p.patient_id, p.first_name, p.last_name, p.date_of_birth')
                ->orderBy('p.first_name', 'ASC')
                ->get()
                ->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'Get available patients error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get patients list for appointment booking (API)
     */
    public function getPatientsList()
    {
        try {
            $patients = $this->getAvailablePatients();
            return $this->response->setJSON([
                'status' => 'success',
                'data' => $patients
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Get patients list error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to load patients'
            ]);
        }
    }

    /**
     * Get doctors list for appointment booking
     */
    public function getDoctorsList()
    {
        try {
            $doctors = $this->getDoctorsListData();
            return $this->response->setJSON([
                'status' => 'success',
                'data' => $doctors
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Get doctors API error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to load doctors'
            ]);
        }
    }

    /**
     * Check if appointment is in nurse's department
     */
    private function isAppointmentInNurseDepartment($appointmentId)
    {
        try {
            $result = $this->db->table('appointments a')
                ->join('staff ns', 'ns.staff_id', $this->staffId)
                ->join('staff ds', 'ds.staff_id = a.doctor_id')
                ->where('a.appointment_id', $appointmentId)
                ->where('ns.department = ds.department')
                ->get()->getRow();
                
            return !empty($result);
        } catch (\Throwable $e) {
            log_message('error', 'Check nurse department error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user can view patient appointments
     */
    private function canViewPatientAppointments($patientId)
    {
        switch ($this->userRole) {
            case 'admin':
            case 'receptionist':
                return true;
            case 'doctor':
                // Check if patient is assigned to this doctor
                $patient = $this->db->table('patient')
                    ->where('patient_id', $patientId)
                    ->where('primary_doctor_id', $this->staffId)
                    ->get()
                    ->getRow();
                return !empty($patient);
            case 'nurse':
                // Check if patient's doctor is in same department
                $result = $this->db->table('patient p')
                    ->join('staff ps', 'ps.staff_id = p.primary_doctor_id')
                    ->join('staff ns', 'ns.staff_id = ' . $this->staffId)
                    ->where('p.patient_id', $patientId)
                    ->where('ps.department = ns.department')
                    ->get()
                    ->getRow();
                return !empty($result);
            default:
                return false;
        }
    }
}