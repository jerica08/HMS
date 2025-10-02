<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Admin extends BaseController
{
    protected $db;
    protected $builder;

    /**
     * Constructor - initializes database connection and checks admin authentication
     */
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

    /**
     * Admin dashboard page
     */
    public function dashboard()
    {
        $session = session();
        $data = [
            'title' => 'Admin Dashboard',
            'user' => [
                'user_id'   => $session->get('user_id'),
                'staff_id'  => $session->get('staff_id'),
                'email'     => $session->get('email'),
                'role'      => $session->get('role')
            ]
        ];
        
        return view('admin/dashboard', $data);
    }

    /**
     * User management page
     */
    public function users()
    {
        $users = $this->db->table('users')->get()->getResultArray();
        
        $data = [
            'title' => 'Manage Users',
            'users' => $users
        ];
        
        return view('admin/users', $data);
    }

    /**
     * Staff management page - lists all staff members
     */
    public function staffManagement()
    {
        $staff = $this->builder->get()->getResultArray();

        $data = [
            'title' => 'Staff Management',
            'staff' => $staff
        ];

        return view('admin/staff-management', $data);
    }

    /**
     * Add staff - handles both form display and submission
     */
    public function addStaff()
    {
        if ($this->request->getMethod() === 'POST') {
            $validation = \Config\Services::validation();

            $validation->setRules([
                'employee_id' => 'required|min_length[3]|max_length[255]|is_unique[staff.employee_id]',
                'first_name'  => 'required|min_length[2]|max_length[100]',
                'last_name'   => 'permit_empty|max_length[100]',
                'gender'      => 'permit_empty|in_list[Male,Female,Other]',
                'dob'         => 'permit_empty|valid_date',
                'contact_no'  => 'permit_empty|max_length[255]',
                'email'       => 'permit_empty|valid_email',
                'address'     => 'permit_empty',
                'department'  => 'permit_empty|max_length[255]',
                'designation' => 'required|in_list[admin,doctor,nurse,pharmacist,receptionist,laboratorist,it_staff,accountant]',
                'date_joined' => 'permit_empty|valid_date'
            ]);

            // Check if this is an AJAX request expecting JSON
            $isAjax = $this->request->isAJAX() || 
                      $this->request->getHeaderLine('Accept') == 'application/json' ||
                      $this->request->getHeaderLine('X-Requested-With') == 'XMLHttpRequest';

            // Handle validation errors
            if (!$validation->withRequest($this->request)->run()) {
                if ($isAjax) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Validation failed',
                        'errors' => $validation->getErrors()
                    ]);
                }
                
                session()->setFlashdata('errors', $validation->getErrors());
                return redirect()->back()->withInput();
            }

            // Prepare data for insertion
            $data = [
                'employee_id' => $this->request->getPost('employee_id'),
                'first_name'  => $this->request->getPost('first_name'),
                'last_name'   => $this->request->getPost('last_name') ?: null,
                'gender'      => $this->request->getPost('gender') ? strtolower($this->request->getPost('gender')) : null,
                'dob'         => $this->request->getPost('dob') ?: null,
                'contact_no'  => $this->request->getPost('contact_no') ?: null,
                'email'       => $this->request->getPost('email') ?: null,
                'address'     => $this->request->getPost('address') ?: null,
                'department'  => $this->request->getPost('department') ?: null,
                'designation' => $this->request->getPost('designation'),
                'role'        => $this->request->getPost('designation'),
                'date_joined' => $this->request->getPost('date_joined') ?: date('Y-m-d')
            ];

            // Insert data into database
            $success = $this->builder->insert($data);
            
            // Handle success/failure responses
            if ($success) {
                if ($isAjax) {
                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => 'Staff member added successfully!'
                    ]);
                }
                
                session()->setFlashdata('success', 'Staff member added successfully!');
            } else {
                if ($isAjax) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Failed to add staff member.'
                    ]);
                }
                
                session()->setFlashdata('error', 'Failed to add staff member.');
            }

            // Redirect for non-AJAX requests
            if (!$isAjax) {
                return redirect()->to(base_url('admin/staff-management'));
            }
            
            // Final JSON response for AJAX requests
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Staff member added successfully!'
            ]);
        }

        // Show add staff form
        $data = ['title' => 'Add Staff'];
        return view('admin/add-staff', $data);
    }

    /**
     * Delete staff member by ID
     */
    public function deleteStaff($id = null)
    {
        if ($id && $this->builder->where('staff_id', $id)->delete()) {
            session()->setFlashdata('success', 'Staff member deleted successfully!');
        } else {
            session()->setFlashdata('error', 'Failed to delete staff member.');
        }

        return redirect()->to(base_url('admin/staff-management'));
    }

    /**
     * View staff details by ID
     */
    public function viewStaff($id = null)
    {
        if (!$id) {
            return redirect()->to(base_url('admin/staff-management'));
        }

        $staff = $this->builder->where('staff_id', $id)->get()->getRowArray();
        if (!$staff) {
            session()->setFlashdata('error', 'Staff member not found.');
            return redirect()->to(base_url('admin/staff-management'));
        }

        $data = [
            'title' => 'Staff Details',
            'staff' => $staff
        ];
        return view('admin/view-staff', $data);
    }
    
    /**
     * User management page - displays all users with their staff details
     */
    public function userManagement() {
        $userModel = new \App\Models\UserModel();
        $users = $userModel->getAllUsersWithStaff();
        $session = session();
        
        // Get current user data for the header display
        $currentUser = [
            'user_id'   => $session->get('user_id'),
            'staff_id'  => $session->get('staff_id'),
            'email'     => $session->get('email'),
            'role'      => $session->get('role')
        ];
        
        $data = [
            'title' => 'User Management',
            'users' => $users,
            'currentUser' => $currentUser
        ];

        return view('admin/user-management', $data);
    }
}