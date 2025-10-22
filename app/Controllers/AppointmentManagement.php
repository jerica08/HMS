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
        $stats = $this->getAppointmentStats();
        $appointments = $this->getAppointments();
        
        $data = [
            'title' => 'Appointment Management',
            'appointmentStats' => $stats,
            'appointments' => $appointments,
            'userRole' => $this->userRole,
            'permissions' => $this->getUserPermissions()
        ];

        // Use unified view that adapts to user role
        return view('apppointments/appointments', $data);
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
            'total_appointments' => 0,
            'scheduled_appointments' => 0,
            'completed_appointments' => 0,
            'cancelled_appointments' => 0,
            'today_appointments' => 0
        ];
        
        try {
            $builder = $this->db->table('appointments');
            
            // Apply role-based filtering
            if ($this->userRole === 'doctor') {
                $builder->where('doctor_id', $this->staffId);
            }
            
            $stats['total_appointments'] = $builder->countAllResults(false);
            $stats['scheduled_appointments'] = $builder->where('status', 'scheduled')->countAllResults(false);
            $stats['completed_appointments'] = $builder->where('status', 'completed')->countAllResults(false);
            $stats['cancelled_appointments'] = $builder->where('status', 'cancelled')->countAllResults(false);
            $stats['today_appointments'] = $builder->where('appointment_date', date('Y-m-d'))->countAllResults();
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
}