<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class DashboardManagement extends BaseController
{
    protected $db;
    protected $userRole;
    protected $staffId;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        
        $session = session();
        $this->userRole = $session->get('role');
        $this->staffId = $session->get('staff_id');
    }

    /**
     * Main unified dashboard - Role-based
     */
    public function index()
    {
        $stats = $this->getDashboardStats();
        $recentActivities = $this->getRecentActivities();
        $quickActions = $this->getQuickActions();
        
        $data = [
            'title' => 'Dashboard',
            'userRole' => $this->userRole,
            'dashboardStats' => $stats,
            'recentActivities' => $recentActivities,
            'quickActions' => $quickActions,
            'permissions' => $this->getUserPermissions()
        ];

        // Use unified view that adapts to user role
        return view('dashboard', $data);
    }

    
    /**
     * Get Dashboard Statistics based on user role
     */
    private function getDashboardStats()
    {
        $stats = [];
        
        try {
            switch ($this->userRole) {
                case 'admin':
                    $stats = $this->getAdminStats();
                    break;
                case 'doctor':
                    $stats = $this->getDoctorStats();
                    break;
                case 'nurse':
                    $stats = $this->getNurseStats();
                    break;
                case 'receptionist':
                    $stats = $this->getReceptionistStats();
                    break;
                default:
                    $stats = $this->getBasicStats();
            }
        } catch (\Throwable $e) {
            log_message('error', 'Dashboard stats error: ' . $e->getMessage());
            $stats = $this->getBasicStats();
        }
        
        return $stats;
    }

    /**
     * Admin Dashboard Statistics
     */
    private function getAdminStats()
    {
        return [
            'total_patients' => $this->db->table('patient')->countAllResults(),
            'total_doctors' => $this->db->table('staff')->where('role', 'doctor')->countAllResults(),
            'total_staff' => $this->db->table('staff')->countAllResults(),
            'total_users' => $this->db->table('users')->countAllResults(),
            'total_appointments' => $this->db->table('appointments')->countAllResults(),
            'today_appointments' => $this->db->table('appointments')->where('appointment_date', date('Y-m-d'))->countAllResults(),
            'active_patients' => $this->db->table('patient')->where('status', 'Active')->countAllResults(),
            'pending_appointments' => $this->db->table('appointments')->where('status', 'scheduled')->countAllResults(),
            'completed_appointments' => $this->db->table('appointments')->where('status', 'completed')->countAllResults(),
            'revenue_today' => 0, // TODO: Implement billing integration
            'revenue_month' => 0, // TODO: Implement billing integration
            'bed_occupancy' => 0, // TODO: Implement bed management
        ];
    }

    /**
     * Doctor Dashboard Statistics
     */
    private function getDoctorStats()
    {
        $today = date('Y-m-d');
        
        return [
            'my_patients' => $this->db->table('patient')->where('primary_doctor_id', $this->staffId)->countAllResults(),
            'today_appointments' => $this->db->table('appointments')->where('doctor_id', $this->staffId)->where('appointment_date', $today)->countAllResults(),
            'scheduled_today' => $this->db->table('appointments')->where('doctor_id', $this->staffId)->where('appointment_date', $today)->where('status', 'scheduled')->countAllResults(),
            'completed_today' => $this->db->table('appointments')->where('doctor_id', $this->staffId)->where('appointment_date', $today)->where('status', 'completed')->countAllResults(),
            'pending_today' => $this->db->table('appointments')->where('doctor_id', $this->staffId)->where('appointment_date', $today)->whereIn('status', ['scheduled', 'in-progress'])->countAllResults(),
            'total_appointments' => $this->db->table('appointments')->where('doctor_id', $this->staffId)->countAllResults(),
            'new_patients_week' => $this->db->table('patient')->where('primary_doctor_id', $this->staffId)->where('created_at >=', date('Y-m-d', strtotime('-7 days')))->countAllResults(),
            'critical_patients' => $this->db->table('patient')->where('primary_doctor_id', $this->staffId)->where('patient_type', 'emergency')->countAllResults(),
            'prescriptions_pending' => $this->db->table('prescriptions')->where('doctor_id', $this->staffId)->where('status', 'active')->countAllResults(),
        ];
    }

    /**
     * Nurse Dashboard Statistics
     */
    private function getNurseStats()
    {
        // Get nurse's department
        $nurse = $this->db->table('staff')->where('staff_id', $this->staffId)->get()->getRowArray();
        $department = $nurse['department'] ?? null;
        
        $stats = [
            'department_patients' => 0,
            'today_appointments' => 0,
            'medications_due' => 0,
            'vitals_pending' => 0,
            'shift_patients' => 0,
        ];
        
        if ($department) {
            // Get patients in same department as nurse
            $stats['department_patients'] = $this->db->table('patient p')
                ->join('staff s', 's.staff_id = p.primary_doctor_id')
                ->where('s.department', $department)
                ->countAllResults();
                
            $stats['today_appointments'] = $this->db->table('appointments a')
                ->join('staff s', 's.staff_id = a.doctor_id')
                ->where('s.department', $department)
                ->where('a.appointment_date', date('Y-m-d'))
                ->countAllResults();
        }
        
        return $stats;
    }

    /**
     * Receptionist Dashboard Statistics
     */
    private function getReceptionistStats()
    {
        $today = date('Y-m-d');
        
        return [
            'total_appointments' => $this->db->table('appointments')->where('appointment_date', $today)->countAllResults(),
            'scheduled_today' => $this->db->table('appointments')->where('appointment_date', $today)->where('status', 'scheduled')->countAllResults(),
            'completed_today' => $this->db->table('appointments')->where('appointment_date', $today)->where('status', 'completed')->countAllResults(),
            'cancelled_today' => $this->db->table('appointments')->where('appointment_date', $today)->where('status', 'cancelled')->countAllResults(),
            'new_patients_today' => $this->db->table('patient')->where('DATE(created_at)', $today)->countAllResults(),
            'total_patients' => $this->db->table('patient')->countAllResults(),
            'pending_registrations' => 0, // TODO: Implement pending registrations
            'walk_ins_today' => $this->db->table('appointments')->where('appointment_date', $today)->where('appointment_type', 'Walk-in')->countAllResults(),
        ];
    }

    /**
     * Basic Statistics (fallback)
     */
    private function getBasicStats()
    {
        return [
            'total_patients' => $this->db->table('patient')->countAllResults(),
            'total_appointments' => $this->db->table('appointments')->countAllResults(),
            'today_appointments' => $this->db->table('appointments')->where('appointment_date', date('Y-m-d'))->countAllResults(),
        ];
    }

    /**
     * Get Recent Activities based on user role
     */
    private function getRecentActivities()
    {
        $activities = [];
        
        try {
            switch ($this->userRole) {
                case 'admin':
                    $activities = $this->getAdminActivities();
                    break;
                case 'doctor':
                    $activities = $this->getDoctorActivities();
                    break;
                case 'nurse':
                    $activities = $this->getNurseActivities();
                    break;
                case 'receptionist':
                    $activities = $this->getReceptionistActivities();
                    break;
            }
        } catch (\Throwable $e) {
            log_message('error', 'Recent activities error: ' . $e->getMessage());
        }
        
        return $activities;
    }

    /**
     * Get Admin Recent Activities
     */
    private function getAdminActivities()
    {
        $activities = [];
        
        // Recent staff additions
        $recentStaff = $this->db->table('staff')
            ->select('first_name, last_name, role, created_at')
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();
            
        foreach ($recentStaff as $staff) {
            $activities[] = [
                'type' => 'staff_added',
                'message' => "New {$staff['role']} added: {$staff['first_name']} {$staff['last_name']}",
                'time' => $staff['created_at'],
                'icon' => 'fas fa-user-plus',
                'color' => 'success'
            ];
        }
        
        // Recent appointments
        $recentAppointments = $this->db->table('appointments a')
            ->select('a.appointment_date, a.appointment_time, p.first_name, p.last_name, a.created_at')
            ->join('patient p', 'p.patient_id = a.patient_id')
            ->orderBy('a.created_at', 'DESC')
            ->limit(3)
            ->get()
            ->getResultArray();
            
        foreach ($recentAppointments as $apt) {
            $activities[] = [
                'type' => 'appointment_scheduled',
                'message' => "Appointment scheduled for {$apt['first_name']} {$apt['last_name']}",
                'time' => $apt['created_at'],
                'icon' => 'fas fa-calendar-plus',
                'color' => 'info'
            ];
        }
        
        // Sort by time
        usort($activities, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });
        
        return array_slice($activities, 0, 8);
    }

    /**
     * Get Doctor Recent Activities
     */
    private function getDoctorActivities()
    {
        $activities = [];
        
        // Today's appointments
        $todayAppointments = $this->db->table('appointments a')
            ->select('a.appointment_time, a.status, p.first_name, p.last_name, a.reason')
            ->join('patient p', 'p.patient_id = a.patient_id')
            ->where('a.doctor_id', $this->staffId)
            ->where('a.appointment_date', date('Y-m-d'))
            ->orderBy('a.appointment_time', 'ASC')
            ->limit(5)
            ->get()
            ->getResultArray();
            
        foreach ($todayAppointments as $apt) {
            $activities[] = [
                'type' => 'appointment',
                'message' => "{$apt['appointment_time']} - {$apt['first_name']} {$apt['last_name']} ({$apt['status']})",
                'time' => date('Y-m-d') . ' ' . $apt['appointment_time'],
                'icon' => 'fas fa-calendar-check',
                'color' => $apt['status'] === 'completed' ? 'success' : 'warning'
            ];
        }
        
        return $activities;
    }

    /**
     * Get Nurse Recent Activities
     */
    private function getNurseActivities()
    {
        // TODO: Implement nurse-specific activities
        return [];
    }

    /**
     * Get Receptionist Recent Activities
     */
    private function getReceptionistActivities()
    {
        $activities = [];
        
        // Recent patient registrations
        $recentPatients = $this->db->table('patient')
            ->select('first_name, last_name, created_at')
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();
            
        foreach ($recentPatients as $patient) {
            $activities[] = [
                'type' => 'patient_registered',
                'message' => "New patient registered: {$patient['first_name']} {$patient['last_name']}",
                'time' => $patient['created_at'],
                'icon' => 'fas fa-user-plus',
                'color' => 'success'
            ];
        }
        
        return $activities;
    }

    /**
     * Get Quick Actions based on user role
     */
    private function getQuickActions()
    {
        switch ($this->userRole) {
            case 'admin':
                return [
                    ['title' => 'Add Staff', 'url' => 'admin/staff-management', 'icon' => 'fas fa-user-plus', 'color' => 'success'],
                    ['title' => 'View Reports', 'url' => 'admin/analytics', 'icon' => 'fas fa-chart-bar', 'color' => 'info'],
                    ['title' => 'Manage Resources', 'url' => 'admin/resource-management', 'icon' => 'fas fa-cogs', 'color' => 'warning'],
                    ['title' => 'System Settings', 'url' => 'admin/system-settings', 'icon' => 'fas fa-sliders-h', 'color' => 'secondary'],
                ];
            case 'doctor':
                return [
                    ['title' => 'View Appointments', 'url' => 'doctor/appointments', 'icon' => 'fas fa-calendar-alt', 'color' => 'primary'],
                    ['title' => 'Manage Patients', 'url' => 'doctor/patients', 'icon' => 'fas fa-users', 'color' => 'success'],
                    ['title' => 'Prescriptions', 'url' => 'doctor/prescriptions', 'icon' => 'fas fa-prescription-bottle-alt', 'color' => 'info'],
                    ['title' => 'Lab Results', 'url' => 'doctor/lab-results', 'icon' => 'fas fa-flask', 'color' => 'warning'],
                ];
            case 'nurse':
                return [
                    ['title' => 'Patient Care', 'url' => 'nurse/patients', 'icon' => 'fas fa-user-nurse', 'color' => 'primary'],
                    ['title' => 'Medications', 'url' => 'nurse/medication', 'icon' => 'fas fa-pills', 'color' => 'success'],
                    ['title' => 'Vital Signs', 'url' => 'nurse/vitals', 'icon' => 'fas fa-heartbeat', 'color' => 'danger'],
                    ['title' => 'Shift Report', 'url' => 'nurse/shift-report', 'icon' => 'fas fa-clipboard-list', 'color' => 'info'],
                ];
            case 'receptionist':
                return [
                    ['title' => 'Book Appointment', 'url' => 'receptionist/appointments', 'icon' => 'fas fa-calendar-plus', 'color' => 'primary'],
                    ['title' => 'Register Patient', 'url' => 'receptionist/patient-registration', 'icon' => 'fas fa-user-plus', 'color' => 'success'],
                    ['title' => 'View Patients', 'url' => 'receptionist/patients', 'icon' => 'fas fa-users', 'color' => 'info'],
                    ['title' => 'Appointment Booking', 'url' => 'receptionist/appointment-booking', 'icon' => 'fas fa-calendar-check', 'color' => 'warning'],
                ];
            default:
                return [];
        }
    }

    /**
     * Get User Permissions for UI
     */
    private function getUserPermissions()
    {
        return [
            'canViewReports' => in_array($this->userRole, ['admin']),
            'canManageStaff' => in_array($this->userRole, ['admin']),
            'canManagePatients' => in_array($this->userRole, ['admin', 'doctor', 'receptionist']),
            'canViewAppointments' => in_array($this->userRole, ['admin', 'doctor', 'nurse', 'receptionist']),
            'canManageSystem' => in_array($this->userRole, ['admin']),
            'canViewFinancials' => in_array($this->userRole, ['admin', 'accountant']),
        ];
    }
}