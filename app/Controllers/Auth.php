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

        // if user is already logged in, redirect by role
        if ($session->get('user_id')) {
            $role = strtolower((string) $session->get('role'));
            switch ($role) {
                case 'admin':
                    return redirect()->to('/admin/dashboard');
                case 'doctor':
                    return redirect()->to('/doctor/dashboard');
                case 'nurse':
                    return redirect()->to('/nurse/dashboard');
                case 'receptionist':
                    return redirect()->to('/receptionist/dashboard');
                case 'it_staff':
                    return redirect()->to('/it/dashboard');
                case 'pharmacist':
                    return redirect()->to('/pharmacists/dashboard');
                case 'laboratorist':
                    return redirect()->to('/laboratorists/dashboard');
                case 'accountant':
                    return redirect()->to('/accountant/dashboard');
                default:
                    return redirect()->to('/');
            }
        }

        if ($this->request->getMethod() == 'POST') {
            $rules = [
                'login'    => 'required',
                'password' => 'required'
            ];

            if (!$this->validate($rules)) {
                return view('auth/login', ['validation' => $this->validator]);
            }

            $login = $this->request->getPost('login');
            $password = $this->request->getPost('password');

            // fetch user by email or username
            $builder = $db->table('users');
            $user = $builder->groupStart()
                           ->where('email', $login)
                           ->orWhere('username', $login)
                           ->groupEnd()
                           ->get()->getRowArray();

            if ($user) {
                $passwordMatches = false;

                // Check if password is hashed
                if (password_verify($password, $user['password'])) {
                    $passwordMatches = true;
                } elseif ($user['password'] === $password) {
                    // Plaintext password, hash it and update
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $builder->where('email', $user['email'])->update(['password' => $hashedPassword]);
                    $passwordMatches = true;
                }

                if ($passwordMatches) {
                    $session->set([
                        'user_id'   => $user['user_id'],
                        'staff_id'  => $user['staff_id'],
                        'username'  => $user['username'],
                        'email'     => $user['email'],
                        'role'      => $user['role'],
                        'isLoggedIn'=> true
                    ]);

                    $role = strtolower($user['role'] ?? '');
                    switch ($role) {
                        case 'admin':
                            return redirect()->to('/admin/dashboard');
                        case 'doctor':
                            return redirect()->to('/doctor/dashboard');
                        case 'nurse':
                            return redirect()->to('/nurse/dashboard');
                        case 'receptionist':
                            return redirect()->to('/receptionist/dashboard');
                        case 'it_staff':
                            return redirect()->to('/it/dashboard');
                        case 'pharmacist':
                            return redirect()->to('/pharmacists/dashboard');
                        case 'laboratorist':
                            return redirect()->to('/laboratorists/dashboard');
                        case 'accountant':
                            return redirect()->to('/accountant/dashboard');
                        default:
                            $session->setFlashdata('error', 'Your account role is not recognized');
                            $session->destroy();
                            return redirect()->to(base_url('/login'));
                    }
                }
            }

            // wrong email/username or password
            $session->setFlashdata('error', 'Invalid email/username or password');
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