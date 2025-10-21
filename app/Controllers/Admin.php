<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Admin extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();

        $session = session();
        if (!$session->get('isLoggedIn') || $session->get('role') !== 'admin') {
            redirect()->to(base_url('/login'))->send();
            exit();
        }
    }

    /**
     * Admin dashboard page
     */
    public function dashboard()
    {
        $session = session();

        // Get dashboard statistics
        $totalPatients = 0;
        $totalDoctors = 0;
        $totalStaff = 0;
        $totalUsers = 0;

        try {
            $totalPatients = $this->db->table('patient')->countAllResults();
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            log_message('error', 'Patients table does not exist: ' . $e->getMessage());
        }

        try {
            $totalDoctors = $this->db->table('users')->where('role', 'doctor')->countAllResults();
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            log_message('error', 'Users table does not exist: ' . $e->getMessage());
        }

        try {
            $totalStaff = $this->db->table('staff')->countAllResults();
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            log_message('error', 'Staff table does not exist: ' . $e->getMessage());
        }

        try {
            $totalUsers = $this->db->table('users')->countAllResults();
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            log_message('error', 'Users table does not exist: ' . $e->getMessage());
        }

        $data = [
            'title' => 'Admin Dashboard',
            'user' => [
                'user_id'   => $session->get('user_id'),
                'staff_id'  => $session->get('staff_id'),
                'email'     => $session->get('email'),
                'role'      => $session->get('role')
            ],
            'stats' => [
                'total_patients' => $totalPatients,
                'total_doctors' => $totalDoctors,
                'total_staff' => $totalStaff,
                'total_users' => $totalUsers,
            ]
        ];
        
        return view('admin/dashboard', $data);
    }

    /**
     * Legacy redirect methods for backward compatibility
     */
    public function users()
    {
        return redirect()->to(base_url('admin/user-management'));
    }

    public function staffManagement()
    {
        return redirect()->to(base_url('admin/staff-management'));
    }

    public function resourceManagement()
    {
        return redirect()->to(base_url('admin/resource-management'));
    }

    public function patientManagement()
    {
        return redirect()->to(base_url('admin/patient-management'));
    }

    /**
     * Navigation pages (keeping these in Admin for now)
     */
    public function financialManagement()
    {
        $data = ['title' => 'Financial Management'];
        return view('admin/financial-management', $data);
    }

    public function communication()
    {
        $data = ['title' => 'Communication & Notifications'];
        return view('admin/communication', $data);
    }

    public function analytics()
    {
        $data = ['title' => 'Analytics & Reports'];
        return view('admin/analytics-reports', $data);
    }

    public function systemSettings()
    {
        $data = ['title' => 'System Settings'];
        return view('admin/system-settings', $data);
    }

    public function securityAccess()
    {
        $data = ['title' => 'Security & Access'];
        return view('admin/security-access', $data);
    }

    public function auditLogs()
    {
        $data = ['title' => 'Audit Logs'];
        return view('admin/audit-logs', $data);
    }
}