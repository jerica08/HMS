<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Admin extends BaseController
{
    protected $db;
    protected $builder;

    public function __construct()
    {
        // DB Connection
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('staff');

        // Session check for admin
        $session = session();
        if (!$session->get('isLoggedIn') || $session->get('role') !== 'admin') {
            redirect()->to(base_url('/login'))->send();
            exit();
        }
    }

    // Admin dashboard
    public function dashboard()
    {
        $session = session();
        $data = [
            'title' => 'Admin Dashboard',
            'user' => [
                'user_id' => $session->get('user_id'),
                'staff_id' => $session->get('staff_id'),
                'email' => $session->get('email'),
                'role' => $session->get('role')
            ]
        ];
        
        return view('admin/dashboard', $data);
    }

    // Manage users
    public function users()
    {
        $users = $this->db->table('users')->get()->getResultArray();
        
        $data = [
            'title' => 'Manage Users',
            'users' => $users
        ];
        
        return view('admin/users', $data);
    }

    // Staff management
    public function staffManagement()
    {
        $staff = $this->builder->get()->getResultArray();

        $data = [
            'title' => 'Staff Management',
            'staff' => $staff
        ];

        return view('admin/staff-management', $data);
    }
}
