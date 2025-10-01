<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Admin extends BaseController
{
    public function __construct()
    {
        // Check if user is logged in and has admin role
        $session = session();
        if (!$session->get('isLoggedIn') || $session->get('role') !== 'admin') {
            redirect()->to(base_url('/login'))->send();
            exit();
        }
    }

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

    public function users()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('users');
        $users = $builder->get()->getResultArray();
        
        $data = [
            'title' => 'Manage Users',
            'users' => $users
        ];
        
        return view('admin/users', $data);
    }
}
