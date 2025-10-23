<?php

namespace App\Services;

use CodeIgniter\Database\ConnectionInterface;

class DashboardService
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Get role-specific dashboard statistics
     */
    public function getDashboardStats($userRole, $staffId = null)
    {
        try {
            switch ($userRole) {
                case 'admin':
                    return $this->getAdminStats();
                case 'doctor':
                    return $this->getDoctorStats($staffId);
                case 'nurse':
                    return $this->getNurseStats($staffId);
                case 'receptionist':
                    return $this->getReceptionistStats();
                case 'accountant':
                    return $this->getAccountantStats();
                case 'pharmacist':
                    return $this->getPharmacistStats();
                case 'laboratorist':
                    return $this->getLaboratoristStats();
                case 'it_staff':
                    return $this->getITStats();
                default:
                    return $this->getDefaultStats();
            }
        } catch (\Exception $e) {
            log_message('error', 'Dashboard stats error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Admin dashboard statistics
     */
    private function getAdminStats()
    {
        $stats = [];

        // Total patients
        $stats['total_patients'] = $this->db->table('patient')->countAllResults();
        $stats['active_patients'] = $this->db->table('patient')
            ->where('status', 'Active')
            ->countAllResults();

        // Staff statistics
        $stats['total_staff'] = $this->db->table('staff')->countAllResults();
        $stats['total_doctors'] = $this->db->table('staff')
            ->where('role', 'doctor')
            ->countAllResults();

        // Appointments
        $today = date('Y-m-d');
        $stats['today_appointments'] = $this->db->table('appointments')
            ->where('appointment_date', $today)
            ->countAllResults();
        
        $stats['pending_appointments'] = $this->db->table('appointments')
            ->where('status', 'scheduled')
            ->countAllResults();
        
        $stats['completed_appointments'] = $this->db->table('appointments')
            ->where('status', 'completed')
            ->where('appointment_date', $today)
            ->countAllResults();

        // Users
        $stats['total_users'] = $this->db->table('users')->countAllResults();

        // Weekly and monthly stats
        $stats['weekly_appointments'] = $this->db->table('appointments')
            ->where('appointment_date >=', date('Y-m-d', strtotime('-7 days')))
            ->countAllResults();
        
        $stats['monthly_patients'] = $this->db->table('patient')
            ->where('date_registered >=', date('Y-m-d', strtotime('-30 days')))
            ->countAllResults();

        return $stats;
    }

    /**
     * Doctor dashboard statistics
     */
    private function getDoctorStats($staffId)
    {
        $stats = [];
        $today = date('Y-m-d');

        // Today's appointments
        $stats['today_appointments'] = $this->db->table('appointments')
            ->where('doctor_id', $staffId)
            ->where('appointment_date', $today)
            ->countAllResults();

        $stats['completed_today'] = $this->db->table('appointments')
            ->where('doctor_id', $staffId)
            ->where('appointment_date', $today)
            ->where('status', 'completed')
            ->countAllResults();

        $stats['pending_today'] = $this->db->table('appointments')
            ->where('doctor_id', $staffId)
            ->where('appointment_date', $today)
            ->whereIn('status', ['scheduled', 'in-progress'])
            ->countAllResults();

        // Patient statistics
        $stats['my_patients'] = $this->db->table('patient')
            ->where('primary_doctor_id', $staffId)
            ->countAllResults();

        $stats['new_patients_week'] = $this->db->table('patient')
            ->where('primary_doctor_id', $staffId)
            ->where('date_registered >=', date('Y-m-d', strtotime('-7 days')))
            ->countAllResults();

        $stats['critical_patients'] = $this->db->table('patient')
            ->where('primary_doctor_id', $staffId)
            ->where('patient_type', 'emergency')
            ->countAllResults();

        // Prescriptions
        $stats['prescriptions_pending'] = $this->db->table('prescriptions')
            ->where('doctor_id', $staffId)
            ->where('status', 'active')
            ->countAllResults();

        $stats['prescriptions_today'] = $this->db->table('prescriptions')
            ->where('doctor_id', $staffId)
            ->where('DATE(created_at)', $today)
            ->countAllResults();

        // Weekly stats
        $stats['weekly_appointments'] = $this->db->table('appointments')
            ->where('doctor_id', $staffId)
            ->where('appointment_date >=', date('Y-m-d', strtotime('-7 days')))
            ->countAllResults();

        $stats['monthly_patients'] = $this->db->table('patient')
            ->where('primary_doctor_id', $staffId)
            ->where('date_registered >=', date('Y-m-d', strtotime('-30 days')))
            ->countAllResults();

        return $stats;
    }

    /**
     * Nurse dashboard statistics
     */
    private function getNurseStats($staffId)
    {
        $stats = [];

        // Get nurse department
        $nurse = $this->db->table('staff')
            ->select('department')
            ->where('staff_id', $staffId)
            ->get()
            ->getRow();

        $department = $nurse->department ?? null;

        if ($department) {
            // Department patients
            $stats['department_patients'] = $this->db->table('patient p')
                ->join('staff s', 's.staff_id = p.primary_doctor_id', 'left')
                ->where('s.department', $department)
                ->countAllResults();

            $stats['critical_patients'] = $this->db->table('patient p')
                ->join('staff s', 's.staff_id = p.primary_doctor_id', 'left')
                ->where('s.department', $department)
                ->where('p.patient_type', 'emergency')
                ->countAllResults();
        } else {
            $stats['department_patients'] = 0;
            $stats['critical_patients'] = 0;
        }

        // Medications
        $today = date('Y-m-d');
        $stats['medications_due'] = $this->db->table('prescriptions')
            ->where('DATE(next_dose)', $today)
            ->where('status', 'active')
            ->countAllResults();

        $stats['medications_overdue'] = $this->db->table('prescriptions')
            ->where('DATE(next_dose) <', $today)
            ->where('status', 'active')
            ->countAllResults();

        return $stats;
    }

    /**
     * Receptionist dashboard statistics
     */
    private function getReceptionistStats()
    {
        $stats = [];
        $today = date('Y-m-d');

        // Appointments
        $stats['total_appointments'] = $this->db->table('appointments')
            ->where('appointment_date', $today)
            ->countAllResults();

        $stats['scheduled_today'] = $this->db->table('appointments')
            ->where('appointment_date', $today)
            ->where('status', 'scheduled')
            ->countAllResults();

        $stats['cancelled_today'] = $this->db->table('appointments')
            ->where('appointment_date', $today)
            ->where('status', 'cancelled')
            ->countAllResults();

        // Patients
        $stats['new_patients_today'] = $this->db->table('patient')
            ->where('DATE(date_registered)', $today)
            ->countAllResults();

        $stats['total_patients'] = $this->db->table('patient')->countAllResults();

        // Weekly and monthly stats
        $stats['weekly_appointments'] = $this->db->table('appointments')
            ->where('appointment_date >=', date('Y-m-d', strtotime('-7 days')))
            ->countAllResults();

        $stats['monthly_patients'] = $this->db->table('patient')
            ->where('date_registered >=', date('Y-m-d', strtotime('-30 days')))
            ->countAllResults();

        return $stats;
    }

    /**
     * Get recent activities based on user role
     */
    public function getRecentActivities($userRole, $staffId = null, $limit = 10)
    {
        $activities = [];

        try {
            switch ($userRole) {
                case 'admin':
                    $activities = $this->getAdminActivities($limit);
                    break;
                case 'doctor':
                    $activities = $this->getDoctorActivities($staffId, $limit);
                    break;
                case 'nurse':
                    $activities = $this->getNurseActivities($staffId, $limit);
                    break;
                case 'receptionist':
                    $activities = $this->getReceptionistActivities($limit);
                    break;
                default:
                    $activities = $this->getDefaultActivities($limit);
            }
        } catch (\Exception $e) {
            log_message('error', 'Recent activities error: ' . $e->getMessage());
        }

        return $activities;
    }

    /**
     * Get upcoming events based on user role
     */
    public function getUpcomingEvents($userRole, $staffId = null, $limit = 5)
    {
        $events = [];

        try {
            switch ($userRole) {
                case 'doctor':
                    $events = $this->getDoctorUpcomingEvents($staffId, $limit);
                    break;
                case 'nurse':
                    $events = $this->getNurseUpcomingEvents($staffId, $limit);
                    break;
                case 'receptionist':
                    $events = $this->getReceptionistUpcomingEvents($limit);
                    break;
                default:
                    $events = $this->getDefaultUpcomingEvents($limit);
            }
        } catch (\Exception $e) {
            log_message('error', 'Upcoming events error: ' . $e->getMessage());
        }

        return $events;
    }

    /**
     * Get admin recent activities
     */
    private function getAdminActivities($limit)
    {
        // Get recent appointments, patient registrations, staff additions
        $activities = [];

        // Recent appointments
        $appointments = $this->db->table('appointments a')
            ->select('a.created_at, p.first_name, p.last_name, s.first_name as doctor_first_name, s.last_name as doctor_last_name')
            ->join('patient p', 'p.patient_id = a.patient_id')
            ->join('staff s', 's.staff_id = a.doctor_id')
            ->orderBy('a.created_at', 'DESC')
            ->limit(3)
            ->get()
            ->getResultArray();

        foreach ($appointments as $appointment) {
            $activities[] = [
                'message' => "New appointment scheduled for {$appointment['first_name']} {$appointment['last_name']} with Dr. {$appointment['doctor_first_name']} {$appointment['doctor_last_name']}",
                'time' => $appointment['created_at'],
                'icon' => 'fas fa-calendar-plus',
                'color' => 'blue'
            ];
        }

        // Recent patient registrations
        $patients = $this->db->table('patient')
            ->select('first_name, last_name, date_registered')
            ->orderBy('date_registered', 'DESC')
            ->limit(2)
            ->get()
            ->getResultArray();

        foreach ($patients as $patient) {
            $activities[] = [
                'message' => "New patient registered: {$patient['first_name']} {$patient['last_name']}",
                'time' => $patient['date_registered'],
                'icon' => 'fas fa-user-plus',
                'color' => 'green'
            ];
        }

        // Sort by time and limit
        usort($activities, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        return array_slice($activities, 0, $limit);
    }

    /**
     * Get doctor recent activities
     */
    private function getDoctorActivities($staffId, $limit)
    {
        $activities = [];

        // Recent appointments
        $appointments = $this->db->table('appointments a')
            ->select('a.created_at, a.status, p.first_name, p.last_name')
            ->join('patient p', 'p.patient_id = a.patient_id')
            ->where('a.doctor_id', $staffId)
            ->orderBy('a.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        foreach ($appointments as $appointment) {
            $statusText = ucfirst($appointment['status']);
            $activities[] = [
                'message' => "Appointment {$statusText}: {$appointment['first_name']} {$appointment['last_name']}",
                'time' => $appointment['created_at'],
                'icon' => 'fas fa-calendar-check',
                'color' => $appointment['status'] === 'completed' ? 'green' : 'blue'
            ];
        }

        return $activities;
    }

    /**
     * Get doctor upcoming events (appointments)
     */
    private function getDoctorUpcomingEvents($staffId, $limit)
    {
        $events = [];

        $appointments = $this->db->table('appointments a')
            ->select('a.appointment_date, a.appointment_time, p.first_name, p.last_name, a.appointment_type')
            ->join('patient p', 'p.patient_id = a.patient_id')
            ->where('a.doctor_id', $staffId)
            ->where('a.appointment_date >=', date('Y-m-d'))
            ->where('a.status', 'scheduled')
            ->orderBy('a.appointment_date, a.appointment_time')
            ->limit($limit)
            ->get()
            ->getResultArray();

        foreach ($appointments as $appointment) {
            $events[] = [
                'title' => "{$appointment['appointment_type']} - {$appointment['first_name']} {$appointment['last_name']}",
                'date' => $appointment['appointment_date'],
                'time' => $appointment['appointment_time']
            ];
        }

        return $events;
    }

    /**
     * Placeholder methods for other roles
     */
    private function getAccountantStats() { return []; }
    private function getPharmacistStats() { return []; }
    private function getLaboratoristStats() { return []; }
    private function getITStats() { return []; }
    private function getDefaultStats() { return []; }
    
    private function getNurseActivities($staffId, $limit) { return []; }
    private function getReceptionistActivities($limit) { return []; }
    private function getDefaultActivities($limit) { return []; }
    
    private function getNurseUpcomingEvents($staffId, $limit) { return []; }
    private function getReceptionistUpcomingEvents($limit) { return []; }
    private function getDefaultUpcomingEvents($limit) { return []; }

    /**
     * Get system health data (admin only)
     */
    public function getSystemHealth()
    {
        return [
            'database_status' => 'healthy',
            'server_load' => '45%',
            'memory_usage' => '62%',
            'disk_space' => '78%'
        ];
    }

    /**
     * Get today's schedule
     */
    public function getTodaySchedule($userRole, $staffId)
    {
        $today = date('Y-m-d');
        
        return $this->db->table('appointments a')
            ->select('a.*, p.first_name, p.last_name')
            ->join('patient p', 'p.patient_id = a.patient_id')
            ->where('a.doctor_id', $staffId)
            ->where('a.appointment_date', $today)
            ->orderBy('a.appointment_time')
            ->get()
            ->getResultArray();
    }

    /**
     * Get quick stats
     */
    public function getQuickStats($userRole, $staffId)
    {
        return [
            'weekly_appointments' => 25,
            'monthly_patients' => 150
        ];
    }

    /**
     * Update user preferences
     */
    public function updateUserPreferences($userId, $preferences)
    {
        try {
            return $this->db->table('user_preferences')
                ->replace([
                    'user_id' => $userId,
                    'preferences' => json_encode($preferences),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
        } catch (\Exception $e) {
            log_message('error', 'Update preferences error: ' . $e->getMessage());
            return false;
        }
    }
}
