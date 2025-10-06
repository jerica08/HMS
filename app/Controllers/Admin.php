<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\StaffModel;

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

        // Get total patients count
        $totalPatients = 0;
        $totalDoctors = 0;
        try {
            $totalPatients = $this->db->table('patient')->countAllResults();
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            log_message('error', 'Patients table does not exist: ' . $e->getMessage());
        }

        try{
            $totalDoctors = $this->db->table('users')->where('role', 'doctor')->countAllResults();
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
            'total_patients' => $totalPatients,
            'total_doctors' => $totalDoctors,
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
            'staff' => $staff,
            'total_staff' => count($staff),
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
        $userModel = new UserModel();
        $staffModel = new StaffModel();

        $data =[
            'title' => 'User Management',
            'users' => $userModel->getAllUsersWithStaff(),
            'staff' => $staffModel->getStaffWithoutUsers(),
            'stats' => [
                'total_users' => count($userModel->getAllUsersWithStaff()),
                 'admin_users' => count(array_filter($userModel->getAllUsersWithStaff(), function($user) {
                    return $user['role'] == 'admin';
                })),
            ],
        ];

        return view('admin/user-management', $data);
    }
    public function saveUser(){
        $userModel = new UserModel();
        $staffModel = new StaffModel();

        $rules = [
            'staff_id' => 'required|integer',
            'username' => 'required|min_length[3]|max_length[50]',
            'password' => 'required|min_length[6]',
            'confirm_password' => 'required|matches[password]',
            'role' => 'required|in_list[admin,doctor,nurse,receptionist,laboratorist,pharmacist,accountant,it_staff]',
            'status' => 'required|in_list[active,inactive]',
        ];

        if(!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $staff = $staffModel->find($this->request->getPost('staff_id'));
        if (!$staff){
            return redirect()->back()->withInput()->with('error', 'Invalid staff selected');
        }
        $data = [
            'staff_id'   => $this->request->getPost('staff_id'),
            'username'   => $this->request->getPost('username'),
            'email'      => $staff['email'] ?? null, // Email from staff record
            'first_name' => $staff['first_name'] ?? null,
            'last_name'  => $staff['last_name'] ?? null,
            'password'   => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role'       => $this->request->getPost('role'),
            'status'     => $this->request->getPost('status') ?: 'active',
        ];

        if ($userModel->insert($data)) {
            return redirect()->to('admin/user-management')->with('success', 'User added successfully.');
        } else{
            return redirect()->back()->withInput()->with('error', 'Failed to add user');
        }
    }

    public function getStaff($id){
        $staffModel = new StaffModel();
        $staff = $staffModel->find($id);
        if ($staff){
            return $this->response->setJSON($staff);
        }
        return $this->response->setStatusCode(404)->setJSON(['error' => 'Staff not found']);
    }

    public function getUsers(){
        $userModel = new UserModel();
        $users = $userModel->getAllUsersWithStaff();
        return $this->response->setJSON($users);
    }

    public function getUser($id){
        $userModel = new UserModel();
        $user = $userModel->find($id);
        if ($user){
            return $this->response->setJSON($user);
        }
        return $this->response->setStatusCode(404)->setJSON(['error' => 'User not found']);
    }

    public function updateUser(){
        $userModel = new UserModel();
        $staffModel = new StaffModel();

        $rules = [
            'user_id' => 'required|integer',
            'username' => 'required|min_length[3]|max_length[50]',
            'role' => 'required|in_list[admin,doctor,nurse,receptionist,laboratorist,pharmacist,accountant,it_staff]',
            'status' => 'required|in_list[active,inactive]',
        ];

        if(!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userId = $this->request->getPost('user_id');
        $user = $userModel->find($userId);
        if (!$user){
            return redirect()->back()->withInput()->with('error', 'User not found');
        }

        $data = [
            'username'   => $this->request->getPost('username'),
            'role'       => $this->request->getPost('role'),
            'status'     => $this->request->getPost('status') ?: 'active',
        ];

        if ($userModel->update($userId, $data)) {
            return redirect()->to('admin/user-management')->with('success', 'User updated successfully.');
        } else{
            return redirect()->back()->withInput()->with('error', 'Failed to update user');
        }
    }

    public function deleteUser($id = null)
{
    $userModel = new UserModel();
    if ($id && $userModel->delete($id)) {
        session()->setFlashdata('success', 'User deleted successfully!');
    } else {
        session()->setFlashdata('error', 'Failed to delete user.');
    }
    return redirect()->to(base_url('admin/user-management'));
}
    
    /**
     * Patient management page - displays all patients
     */
    public function patientManagement() {
        try {
            // Use correct table name 'patient' as per migration
            $patients = $this->db->table('patient')->get()->getResultArray();
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            // Handle missing table gracefully
            $patients = [];
            log_message('error', 'Patients table does not exist: ' . $e->getMessage());
        }

       $data = [
        'title' => 'Patient Management',
        'patients' => $patients,
        'patientStats' => [
            'total_patients' => count($patients),
        ],
    ];

        return view('admin/patient-management', $data);
    }

    /**
     * Create a new patient record via JSON POST
     */
    public function createPatient()
    {
        // Expect JSON payload
        $input = $this->request->getJSON(true) ?? $this->request->getPost();

        // Basic validation for non-nullable fields in migration
        $validation = \Config\Services::validation();
        $validation->setRules([
            'first_name'              => 'required|min_length[2]|max_length[100]',
            'last_name'               => 'required|min_length[2]|max_length[100]',
            'gender'                  => 'required|in_list[male,female,other,MALE,FEMALE,OTHER,Male,Female,Other]',
            'date_of_birth'           => 'required|valid_date',
            'civil_status'            => 'required',
            'phone'                   => 'required|max_length[50]',
            'email'                   => 'permit_empty|valid_email',
            'address'                 => 'required',
            'province'                => 'required|max_length[100]',
            'city'                    => 'required|max_length[100]',
            'barangay'                => 'required|max_length[100]',
            'zip_code'                => 'required|max_length[20]',
            'emergency_contact_name'  => 'required|max_length[100]',
            'emergency_contact_phone' => 'required|max_length[50]',
            'patient_type'            => 'permit_empty|in_list[outpatient,inpatient,emergency,Outpatient,Inpatient,Emergency]'
        ]);

        if (!$validation->run($input)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $validation->getErrors(),
            ])->setStatusCode(422);
        }

        // Map incoming fields to DB schema
        $gender = $input['gender'] ?? null;
        $status = $input['status'] ?? 'Active';

        // Normalize enum values to match migration
        $gender = $gender ? ucfirst(strtolower($gender)) : null; // Male/Female/Other
        $status = $status ? ucfirst(strtolower($status)) : 'Active'; // Active/Inactive

        $data = [
            'first_name'         => $input['first_name'] ?? null,
            'middle_name'        => $input['middle_name'] ?? null,
            'last_name'          => $input['last_name'] ?? null,
            'gender'             => $gender,
            'civil_status'       => $input['civil_status'] ?? null,
            'date_of_birth'      => $input['date_of_birth'] ?? null,
            'contact_no'         => $input['phone'] ?? ($input['contact_no'] ?? null),
            'email'              => $input['email'] ?? null,
            'address'            => $input['address'] ?? null,
            'province'           => $input['province'] ?? null,
            'city'               => $input['city'] ?? null,
            'barangay'           => $input['barangay'] ?? null,
            'zip_code'           => $input['zip_code'] ?? null,
            'insurance_provider' => $input['insurance_provider'] ?? null,
            'insurance_number'   => $input['insurance_number'] ?? null,
            'emergency_contact'  => $input['emergency_contact_name'] ?? ($input['emergency_contact'] ?? null),
            'emergency_phone'    => $input['emergency_contact_phone'] ?? ($input['emergency_phone'] ?? null),
            'patient_type'       => $input['patient_type'] ?? null,
            // Optional/unavailable in form: blood_group
            'blood_group'        => $input['blood_group'] ?? null,
            'medical_notes'      => $input['medical_notes'] ?? null,
            'date_registered'    => date('Y-m-d'),
            'status'             => $status,
        ];

        try {
            $builder = $this->db->table('patient');
            $ok = $builder->insert($data);
            if ($ok) {
                return $this->response->setJSON([
                    'status'  => 'success',
                    'message' => 'Patient saved successfully',
                    'id'      => $this->db->insertID(),
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Failed to insert patient: ' . $e->getMessage());
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Database error: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }

        return $this->response->setJSON([
            'status'  => 'error',
            'message' => 'Failed to save patient',
        ])->setStatusCode(500);
    }

    /**
     * Resource management page
     */
    public function resourceManagement()
    {
        $data = [
            'title' => 'Resource Management',
        ];
        return view('admin/resource-management', $data);
    }

    /**
     * Financial management page
     */
    public function financialManagement()
    {
        $data = [
            'title' => 'Financial Management',
        ];
        return view('admin/financial-management', $data);
    }

    /**
     * Communication & Notifications page
     */
    public function communication()
    {
        $data = [
            'title' => 'Communication & Notifications',
        ];
        return view('admin/communication', $data);
    }

    /**
     * Analytics & Reports page
     */
    public function analytics()
    {
        $data = [
            'title' => 'Analytics & Reports',
        ];
        return view('admin/analytics-reports', $data);
    }

    /**
     * System Settings page
     */
    public function systemSettings()
    {
        $data = [
            'title' => 'System Settings',
        ];
        return view('admin/system-setting', $data);
    }

    /**
     * Security & Access page
     */
    public function securityAccess()
    {
        $data = [
            'title' => 'Security & Access',
        ];
        return view('admin/security-access', $data);
    }

    /**
     * Audit Logs page
     */
    public function auditLogs()
    {
        $data = [
            'title' => 'Audit Logs',
        ];
        return view('admin/audit-logs', $data);
    }

    public function logout()
    {
        $session = session();
        $session->destroy();
        return redirect()->to(base_url('/login'));
    }
}
