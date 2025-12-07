<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\AppointmentService;
use App\Services\FinancialService;

class AppointmentManagement extends BaseController
{
    protected $db;
    protected $appointmentService;
    protected $financialService;

    protected $userRole;
    protected $staffId;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->appointmentService = new AppointmentService();
        $this->financialService = new FinancialService();
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
            $data = [
                'title' => $this->getPageTitle(),
                'appointmentStats' => $this->getAppointmentStats(),
                'appointments' => $this->getAppointments(),
                'doctors' => $this->getDoctorsListData(),
                'availablePatients' => $this->getAvailablePatients(),
                'userRole' => $this->userRole,
                'permissions' => $this->getUserPermissions()
            ];
            return view('unified/appointments', $data);
        } catch (\Exception $e) {
            log_message('error', 'AppointmentManagement index error: ' . $e->getMessage());
            return view('unified/appointments', [
                'title' => 'Appointments',
                'appointmentStats' => [],
                'appointments' => [],
                'doctors' => [],
                'availablePatients' => [],
                'userRole' => $this->userRole ?? 'guest',
                'permissions' => []
            ]);
        }
    }

    /**
     * Create Appointment - All authorized roles
     */
    public function createAppointment()
    {
        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        return $this->response->setJSON($this->appointmentService->createAppointment($input, $this->userRole, $this->staffId));
    }

    public function updateAppointment($appointmentId = null)
    {
        $appointmentId = $appointmentId ?? $this->request->getPost('appointment_id');
        if (!$this->canEditAppointment($appointmentId)) {
            return $this->jsonResponse('error', 'Permission denied');
        }

        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        try {
            $updateData = array_filter([
                'appointment_date' => $input['appointment_date'] ?? null,
                'appointment_time' => $input['appointment_time'] ?? null,
                'reason' => $input['reason'] ?? $input['notes'] ?? null
            ], fn($v) => $v !== null);
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            
            $builder = $this->db->table('appointments');
            if ($this->userRole === 'doctor') {
                $builder->where('doctor_id', $this->staffId);
            }
            $result = $builder->where('appointment_id', $appointmentId)->update($updateData);
            return $this->jsonResponse($result ? 'success' : 'error', $result ? 'Appointment updated successfully' : 'Failed to update appointment');
        } catch (\Throwable $e) {
            log_message('error', 'Update appointment error: ' . $e->getMessage());
            return $this->jsonResponse('error', 'Database error');
        }
    }

    public function getAppointment($appointmentId)
    {
        if (!$this->canViewAppointment($appointmentId)) {
            return $this->jsonResponse('error', 'Permission denied');
        }
        $result = $this->appointmentService->getAppointment($appointmentId);
        return $this->response->setJSON($result['success'] ? ['status' => 'success', 'data' => $result['appointment']] : ['status' => 'error', 'message' => $result['message']]);
    }

    public function deleteAppointment($appointmentId)
    {
        if (!$this->canDeleteAppointment($appointmentId)) {
            return $this->jsonResponse('error', 'Permission denied');
        }
        return $this->response->setJSON($this->appointmentService->deleteAppointment($appointmentId, $this->userRole, $this->staffId));
    }

    public function getAppointmentsAPI()
    {
        $filters = [];
        if ($this->userRole === 'doctor') {
            $filters['doctor_id'] = $this->staffId;
        }
        foreach (['date', 'status', 'patient_id'] as $key) {
            if ($value = $this->request->getGet($key)) {
                $filters[$key] = $value;
            }
        }
        if ($this->userRole === 'admin' && ($doctorId = $this->request->getGet('doctor_id'))) {
            $filters['doctor_id'] = $doctorId;
        }
        $result = $this->appointmentService->getAppointments($filters);
        return $this->response->setJSON(['status' => $result['success'] ? 'success' : 'error', 'data' => $result['data'] ?? [], 'message' => $result['message'] ?? null]);
    }

    public function getPatientAppointments($patientId)
    {
        if (!$this->canViewPatientAppointments($patientId)) {
            return $this->jsonResponse('error', 'Permission denied');
        }
        $filters = ['patient_id' => $patientId];
        if ($this->userRole === 'doctor') {
            $filters['doctor_id'] = $this->staffId;
        }
        $result = $this->appointmentService->getAppointments($filters);
        return $this->response->setJSON(['status' => $result['success'] ? 'success' : 'error', 'data' => $result['data'] ?? [], 'message' => $result['message'] ?? null]);
    }

    public function updateAppointmentStatus($appointmentId)
    {
        if (!$this->canEditAppointment($appointmentId)) {
            return $this->jsonResponse('error', 'Permission denied');
        }
        return $this->response->setJSON($this->appointmentService->updateAppointmentStatus($appointmentId, $this->request->getPost('status'), $this->userRole, $this->staffId));
    }

    /**
     * Add an appointment charge to patient's billing account (admin/accountant)
     */
    public function addToBilling($appointmentId)
    {
        if (!in_array($this->userRole, ['admin', 'accountant'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Permission denied'
            ]);
        }

        try {
            $input = $this->request->getJSON(true) ?? $this->request->getPost();

            $unitPrice = isset($input['unit_price']) ? (float)$input['unit_price'] : 0.0;
            $quantity  = isset($input['quantity']) ? (int)$input['quantity'] : 1;

            if ($unitPrice <= 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Invalid unit price'
                ]);
            }

            // Load appointment to get patient
            $appointment = $this->db->table('appointments')
                ->where('appointment_id', $appointmentId)
                ->get()
                ->getRowArray();

            if (!$appointment || empty($appointment['patient_id'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Appointment or patient not found'
                ]);
            }

            $patientId = (int)$appointment['patient_id'];

            // Get or create billing account for this patient
            $account = $this->financialService->getOrCreateBillingAccountForPatient($patientId, null, (int)$this->staffId);

            if (!$account || empty($account['billing_id'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Unable to create or load billing account'
                ]);
            }

            $billingId = (int)$account['billing_id'];

            // Add appointment item to billing
            $result = $this->financialService->addItemFromAppointment(
                $billingId,
                (int)$appointmentId,
                $unitPrice,
                $quantity,
                (int)$this->staffId
            );

            return $this->response->setJSON($result);
        } catch (\Throwable $e) {
            log_message('error', 'AppointmentManagement::addToBilling error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to add appointment to billing'
            ]);
        }
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

    private function getUserPermissions()
    {
        $role = $this->userRole;
        return [
            'canCreate' => in_array($role, ['admin', 'receptionist', 'doctor']),
            'canEdit' => in_array($role, ['admin', 'receptionist', 'doctor']),
            'canDelete' => $role === 'admin',
            'canViewAll' => in_array($role, ['admin', 'receptionist']),
            'canUpdateStatus' => in_array($role, ['admin', 'doctor', 'nurse']),
            'canSchedule' => in_array($role, ['admin', 'receptionist', 'doctor']),
            'canReschedule' => in_array($role, ['admin', 'receptionist', 'doctor'])
        ];
    }

    private function canViewAppointment($appointmentId)
    {
        return match($this->userRole) {
            'admin', 'receptionist' => true,
            'doctor' => !empty($this->db->table('appointments')->where('appointment_id', $appointmentId)->where('doctor_id', $this->staffId)->get()->getRow()),
            'nurse' => $this->isAppointmentInNurseDepartment($appointmentId),
            default => false
        };
    }

    private function canEditAppointment($appointmentId)
    {
        return match($this->userRole) {
            'admin', 'receptionist' => true,
            'doctor' => $this->canViewAppointment($appointmentId),
            'nurse' => $this->isAppointmentInNurseDepartment($appointmentId),
            default => false
        };
    }

    private function canDeleteAppointment($appointmentId)
    {
        return $this->userRole === 'admin';
    }

    private function getPageTitle()
    {
        return match($this->userRole) {
            'admin' => 'System Appointments',
            'doctor' => 'My Appointments',
            'nurse' => 'Department Appointments',
            'receptionist' => 'Appointment Booking',
            default => 'Appointments'
        };
    }

    private function getDoctorsListData()
    {
        try {
            return $this->db->table('doctor d')
                ->select('s.staff_id, s.first_name, s.last_name, d.specialization')
                ->join('staff s', 's.staff_id = d.staff_id', 'inner')
                ->where('d.status', 'Active')
                ->get()
                ->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'Get doctors list error: ' . $e->getMessage());
            return [];
        }
    }

    private function getAvailablePatients()
    {
        try {
            // Determine which table name to use
            $tableName = $this->db->tableExists('patients') ? 'patients' : ($this->db->tableExists('patient') ? 'patient' : 'patients');
            
            $selectFields = 'p.patient_id, p.first_name, p.last_name, p.date_of_birth';
            if ($this->db->fieldExists('patient_type', $tableName)) {
                $selectFields .= ', p.patient_type';
            }
            
            $patients = $this->db->table($tableName . ' p')
                ->select($selectFields)
                ->orderBy('p.first_name', 'ASC')
                ->get()
                ->getResultArray();
            
            // Always try to derive patient_type if not set or if column doesn't exist
            if ($this->db->tableExists('inpatient_admissions')) {
                foreach ($patients as &$patient) {
                    if (!isset($patient['patient_type']) || empty($patient['patient_type'])) {
                        $hasActiveAdmission = $this->db->table('inpatient_admissions')
                            ->where('patient_id', $patient['patient_id'])
                            ->groupStart()
                                ->where('discharge_date', null)
                                ->orWhere('discharge_date', '')
                            ->groupEnd()
                            ->countAllResults() > 0;
                        
                        $patient['patient_type'] = $hasActiveAdmission ? 'Inpatient' : 'Outpatient';
                    } else {
                        $patient['patient_type'] = ucfirst(strtolower(trim($patient['patient_type'])));
                    }
                }
            } else {
                foreach ($patients as &$patient) {
                    if (!isset($patient['patient_type']) || empty($patient['patient_type'])) {
                        $patient['patient_type'] = 'Outpatient';
                    } else {
                        $patient['patient_type'] = ucfirst(strtolower(trim($patient['patient_type'])));
                    }
                }
            }
            
            return $patients;
        } catch (\Exception $e) {
            log_message('error', 'Get available patients error: ' . $e->getMessage());
            return [];
        }
    }

    public function getPatientsList()
    {
        try {
            return $this->response->setJSON(['status' => 'success', 'data' => $this->getAvailablePatients()]);
        } catch (\Exception $e) {
            log_message('error', 'Get patients list error: ' . $e->getMessage());
            return $this->jsonResponse('error', 'Failed to load patients');
        }
    }

    public function getDoctorsList()
    {
        try {
            return $this->response->setJSON(['status' => 'success', 'data' => $this->getDoctorsListData()]);
        } catch (\Exception $e) {
            log_message('error', 'Get doctors API error: ' . $e->getMessage());
            return $this->jsonResponse('error', 'Failed to load doctors');
        }
    }

    public function getAvailableDoctorsByDate()
    {
        try {
            $date = $this->request->getGet('date') ?: date('Y-m-d');
            if (!($timestamp = strtotime($date))) {
                return $this->jsonResponse('error', 'Invalid date');
            }
            $weekday = (int) date('N', $timestamp);
            $doctors = $this->db->table('staff_schedule ss')
                ->select('s.staff_id, s.first_name, s.last_name, d.specialization')
                ->join('doctor d', 'd.staff_id = ss.staff_id', 'inner')
                ->join('staff s', 's.staff_id = ss.staff_id', 'inner')
                ->where('ss.status', 'active')
                ->where('ss.weekday', $weekday)
                ->where('d.status', 'Active')
                ->groupBy('s.staff_id')
                ->orderBy('s.first_name', 'ASC')
                ->get()
                ->getResultArray();
            return $this->response->setJSON(['status' => 'success', 'data' => $doctors]);
        } catch (\Throwable $e) {
            log_message('error', 'Get available doctors by date error: ' . $e->getMessage());
            return $this->jsonResponse('error', 'Failed to load available doctors');
        }
    }

    private function isAppointmentInNurseDepartment($appointmentId)
    {
        try {
            return !empty($this->db->table('appointments a')
                ->join('staff ns', 'ns.staff_id', $this->staffId)
                ->join('staff ds', 'ds.staff_id = a.doctor_id')
                ->where('a.appointment_id', $appointmentId)
                ->where('ns.department = ds.department')
                ->get()->getRow());
        } catch (\Throwable $e) {
            log_message('error', 'Check nurse department error: ' . $e->getMessage());
            return false;
        }
    }

    private function canViewPatientAppointments($patientId)
    {
        return match($this->userRole) {
            'admin', 'receptionist' => true,
            'doctor' => !empty($this->db->table('patient')->where('patient_id', $patientId)->where('primary_doctor_id', $this->staffId)->get()->getRow()),
            'nurse' => !empty($this->db->table('patient p')->join('staff ps', 'ps.staff_id = p.primary_doctor_id')->join('staff ns', 'ns.staff_id = ' . $this->staffId)->where('p.patient_id', $patientId)->where('ps.department = ns.department')->get()->getRow()),
            default => false
        };
    }

    private function jsonResponse($status, $message, $data = null)
    {
        $response = ['status' => $status, 'message' => $message];
        if ($data !== null) $response['data'] = $data;
        return $this->response->setJSON($response);
    }
}