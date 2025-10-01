<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Auth extends BaseController
{
    public function login()
    {
        $db = \Config\Database::connect();
        $session = session();

        // if user is already login, redirect to dashboard
        if ($session->get('user_id')) {
            return redirect()->to('/dashboard');
        }

        if ($this->request->getMethod() == 'POST') {
            $rules = [
                'email'    => 'required|valid_email',
                'password' => 'required'
            ];

            if (!$this->validate($rules)) {
                return view('auth/login', ['validation' => $this->validator]);
            }

            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');

            // fetch user
            $builder = $db->table('users');
            $user = $builder->where('email', $email)->get()->getRowArray();

            if ($user && password_verify($password, $user['password'])) {
                $session->set([
                    'user_id'   => $user['user_id'],
                    'staff_id'  => $user['staff_id'],
                    'email'     => $user['email'],
                    'role'      => $user['role'],
                    'isLoggedIn'=> true
                ]);

                switch ($user['role']) {
                    case 'admin':
                        return redirect()->to('/admin/dashboard');
                    case 'doctor':
                        return redirect()->to('/doctor/dashboard');
                    default:
                        $session->setFlashdata('error', 'Your account role is not recognized');
                        $session->destroy();
                        return redirect()->to(base_url('/login'));
                }
            }

            // wrong email or password
            $session->setFlashdata('error', 'Invalid email or password');
            return redirect()->to(base_url('/login'));
        }

        // default GET request
        return view('auth/login');
    }

    public function logout()
    {
        $session = session();
        $session->destroy();
        return redirect()->to(base_url('/login'))->with('success', 'You have been logged out successfully');
    }
}