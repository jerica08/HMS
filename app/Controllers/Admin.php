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
        // Redirect legacy route to the consolidated user management page
        return redirect()->to(base_url('admin/user-management'));
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

            // Add role-specific validation
            $designation = $this->request->getPost('designation');
            if ($designation === 'doctor') {
                $validation->setRules([
                    'doctor_specialization' => 'required|min_length[2]|max_length[100]',
                    'doctor_license_no'     => 'permit_empty|max_length[50]',
                    'doctor_consultation_fee' => 'permit_empty|decimal'
                ]);
            } elseif ($designation === 'nurse') {
                $validation->setRules([
                    'nurse_license_no' => 'required|max_length[100]'
                ]);
            } elseif ($designation === 'pharmacist') {
                $validation->setRules([
                    'pharmacist_license_no' => 'required|max_length[100]'
                ]);
            } elseif ($designation === 'laboratorist') {
                $validation->setRules([
                    'laboratorist_license_no' => 'required|max_length[100]',
                    'laboratorist_specialization' => 'permit_empty|max_length[150]',
                    'laboratorist_lab_room_no' => 'permit_empty|max_length[50]'
                ]);
            } elseif ($designation === 'accountant') {
                $validation->setRules([
                    'accountant_license_no' => 'required|max_length[100]'
                ]);
            } elseif ($designation === 'receptionist') {
                $validation->setRules([
                    'receptionist_desk_no' => 'permit_empty|max_length[50]'
                ]);
            } elseif ($designation === 'it_staff') {
                $validation->setRules([
                    'it_expertise' => 'permit_empty|max_length[150]'
                ]);
            }

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

            // Insert data into staff table
            $success = $this->builder->insert($data);
            
            // Handle success/failure responses
            if ($success) {
                // Insert into role-specific table
                $staffId = (int)$this->db->insertID();
                try {
                    switch ($designation) {
                        case 'doctor':
                            $this->db->table('doctor')->insert([
                                'staff_id' => $staffId,
                                'specialization' => $this->request->getPost('doctor_specialization'),
                                'license_no' => $this->request->getPost('doctor_license_no') ?: null,
                                'consultation_fee' => $this->request->getPost('doctor_consultation_fee') ?: null,
                                'status' => 'Active',
                            ]);
                            break;
                        case 'nurse':
                            $this->db->table('nurse')->insert([
                                'staff_id' => $staffId,
                                'license_no' => $this->request->getPost('nurse_license_no'),
                                'specialization' => $this->request->getPost('nurse_specialization') ?: null,
                            ]);
                            break;
                        case 'pharmacist':
                            $this->db->table('pharmacist')->insert([
                                'staff_id' => $staffId,
                                'license_no' => $this->request->getPost('pharmacist_license_no'),
                                'specialization' => $this->request->getPost('pharmacist_specialization') ?: null,
                            ]);
                            break;
                        case 'laboratorist':
                            $this->db->table('laboratorist')->insert([
                                'staff_id' => $staffId,
                                'license_no' => $this->request->getPost('laboratorist_license_no'),
                                'specialization' => $this->request->getPost('laboratorist_specialization') ?: null,
                                'lab_room_no' => $this->request->getPost('laboratorist_lab_room_no') ?: null,
                            ]);
                            break;
                        case 'accountant':
                            $this->db->table('accountant')->insert([
                                'staff_id' => $staffId,
                                'license_no' => $this->request->getPost('accountant_license_no'),
                            ]);
                            break;
                        case 'receptionist':
                            $this->db->table('receptionist')->insert([
                                'staff_id' => $staffId,
                                'desk_no' => $this->request->getPost('receptionist_desk_no') ?: null,
                            ]);
                            break;
                        case 'it_staff':
                            $this->db->table('it_staff')->insert([
                                'staff_id' => $staffId,
                                'expertise' => $this->request->getPost('it_expertise') ?: null,
                            ]);
                            break;
                        case 'admin':
                            // No automatic user account here; admin table expects username/password.
                            // Skip creating admin row to avoid invalid data; handled via Users feature.
                            break;
                    }
                } catch (\Throwable $e) {
                    log_message('error', 'Failed inserting role-specific record for staff_id ' . $staffId . ': ' . $e->getMessage());
                    // Continue, but inform via flashdata
                    session()->setFlashdata('warning', 'Staff saved, but role details could not be created. You can edit later.');
                }
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

        // Fetch once and guard types
        $users = $userModel->getAllUsersWithStaff();
        if (!is_array($users)) { $users = []; }

        $adminCount = 0;
        foreach ($users as $u) {
            if (($u['role'] ?? '') === 'admin') { $adminCount++; }
        }

        $data = [
            'title' => 'User Management',
            'users' => $users,
            'staff' => $staffModel->getStaffWithoutUsers(),
            'stats' => [
                'total_users' => count($users),
                'admin_users' => $adminCount,
            ],
        ];

        return view('admin/user-management', $data);
    }
    public function saveUser(){
        $userModel = new UserModel();
        $staffModel = new StaffModel();

        $rules = [
            'staff_id' => 'required|integer|is_unique[users.staff_id]',
            'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
            'password' => 'required|min_length[6]',
            'confirm_password' => 'required|matches[password]',
            'status' => 'required|in_list[active,inactive]',
        ];

        if(!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $staff = $staffModel->find($this->request->getPost('staff_id'));
        if (!$staff){
            return redirect()->back()->withInput()->with('error', 'Invalid staff selected');
        }

        // Optional: prevent duplicate email if users.email has a unique constraint
        $staffEmail = $staff['email'] ?? null;
        // Login uses email, ensure staff has a valid email
        if (empty($staffEmail)) {
            return redirect()->back()->withInput()->with('error', 'Selected staff has no email. Please add an email to the staff record before creating the user.');
        }
        if ($staffEmail) {
            $existingByEmail = $userModel->where('email', $staffEmail)->first();
            if ($existingByEmail) {
                return redirect()->back()->withInput()->with('error', 'A user with the same email already exists. Please update the staff email or choose a different staff/username.');
            }
        }
        // Determine role from staff record (prefer staff.role, fallback to staff.designation)
        $derivedRole = strtolower(trim((string)($staff['role'] ?? '')));
        if ($derivedRole === '') {
            $derivedRole = strtolower(trim((string)($staff['designation'] ?? '')));
        }
        $validRoles = ['admin','doctor','nurse','receptionist','laboratorist','pharmacist','accountant','it_staff'];
        if ($derivedRole === '' || !in_array($derivedRole, $validRoles, true)) {
            return redirect()->back()->withInput()->with('error', 'Selected staff has no valid role/designation. Please update the staff record.');
        }

        $data = [
            'staff_id'   => $this->request->getPost('staff_id'),
            'username'   => $this->request->getPost('username'),
            'email'      => $staff['email'] ?? null, // Email from staff record
            'first_name' => $staff['first_name'] ?? null,
            'last_name'  => $staff['last_name'] ?? null,
            'password'   => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role'       => $derivedRole,
            'status'     => $this->request->getPost('status') ?: 'active',
        ];

        try {
            if ($userModel->insert($data)) {
                return redirect()->to('admin/user-management')->with('success', 'User added successfully.');
            }
            // If insert returned false, capture model errors
            $modelErrors = $userModel->errors();
            if (!empty($modelErrors)) {
                return redirect()->back()->withInput()->with('errors', $modelErrors)->with('error', 'Failed to add user. Please fix the highlighted errors.');
            }
        } catch (\Throwable $e) {
            // Fall through to db error reporting
        }

        // As a last resort, surface DB error if available
        $dbError = $this->db->error();
        $dbMsg = !empty($dbError['message']) ? $dbError['message'] : 'Unknown database error';
        return redirect()->back()->withInput()->with('error', 'Failed to add user: ' . $dbMsg);
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

    /**
     * Delete a user by ID
     */
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
     * Reset a user's password and show the temporary password via flash message
     */
    public function resetUserPassword($id = null)
    {
        if (!$id) {
            session()->setFlashdata('error', 'Invalid user ID for reset.');
            return redirect()->to(base_url('admin/user-management'));
        }

        $userModel = new UserModel();
        $user = $userModel->find($id);
        if (!$user) {
            session()->setFlashdata('error', 'User not found.');
            return redirect()->to(base_url('admin/user-management'));
        }

        // Generate a temporary password (12 chars)
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%^*';
        $temp = '';
        for ($i = 0; $i < 12; $i++) {
            $temp .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        $ok = $userModel->update($id, [
            'password' => password_hash($temp, PASSWORD_DEFAULT),
        ]);

        if ($ok) {
            session()->setFlashdata('success', 'Password reset successfully. Temporary password: ' . $temp);
        } else {
            session()->setFlashdata('error', 'Failed to reset password.');
        }

        return redirect()->to(base_url('admin/user-management'));
    }

    /**
     * Patient management page - displays all patients
     */
    public function patientManagement() {
        try {
            // Use correct table name 'patient' and join doctor/staff to get assigned doctor name
            $patients = $this->db->table('patient')
                ->select("patient.*, CONCAT(s.first_name, ' ', COALESCE(s.last_name, '')) AS primary_doctor_name")
                ->join('doctor d', 'd.doctor_id = patient.primary_doctor_id', 'left')
                ->join('staff s', 's.staff_id = d.staff_id', 'left')
                ->get()
                ->getResultArray();
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

        // Resolve and sanitize optional primary_doctor_id
        $primaryDoctorId = null;
        if (isset($input['primary_doctor_id']) && $input['primary_doctor_id'] !== '') {
            $candidate = (int)$input['primary_doctor_id'];
            if ($candidate > 0) {
                try {
                    $exists = $this->db->table('doctor')->where('doctor_id', $candidate)->countAllResults();
                    if ($exists > 0) { $primaryDoctorId = $candidate; }
                } catch (\Throwable $e) { /* leave null if check fails */ }
            }
        }

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
            'primary_doctor_id'  => $primaryDoctorId,
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
     * Update an existing patient record via JSON POST
     * Route: POST admin/patients/update
     */
    public function updatePatient()
    {
        // Expect JSON payload but also allow form POST
        $input = $this->request->getJSON(true) ?? $this->request->getPost();

        if (!is_array($input) || empty($input['patient_id'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'patient_id is required',
            ])->setStatusCode(422);
        }

        $id = (int) $input['patient_id'];

        // Basic rules (aligning with createPatient non-nullables where applicable)
        $validation = \Config\Services::validation();
        $validation->setRules([
            'first_name'              => 'permit_empty|min_length[1]|max_length[100]',
            'last_name'               => 'permit_empty|min_length[1]|max_length[100]',
            'gender'                  => 'permit_empty|in_list[male,female,other,MALE,FEMALE,OTHER,Male,Female,Other]',
            'date_of_birth'           => 'permit_empty|valid_date',
            'phone'                   => 'permit_empty|max_length[50]',
            'email'                   => 'permit_empty|valid_email',
            'address'                 => 'permit_empty',
            'province'                => 'permit_empty|max_length[100]',
            'city'                    => 'permit_empty|max_length[100]',
            'barangay'                => 'permit_empty|max_length[100]',
            'zip_code'                => 'permit_empty|max_length[20]',
            'emergency_contact_name'  => 'permit_empty|max_length[100]',
            'emergency_contact_phone' => 'permit_empty|max_length[50]',
            'patient_type'            => 'permit_empty|in_list[outpatient,inpatient,emergency,Outpatient,Inpatient,Emergency]',
            'status'                  => 'permit_empty|in_list[Active,Inactive,active,inactive]',
            'primary_doctor_id'       => 'permit_empty|integer'
        ]);

        if (!$validation->run($input)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $validation->getErrors(),
            ])->setStatusCode(422);
        }

        // Normalize values
        $gender = $input['gender'] ?? null;
        $status = $input['status'] ?? null;
        $gender = $gender ? ucfirst(strtolower($gender)) : null; // Male/Female/Other
        $status = $status ? ucfirst(strtolower($status)) : null;  // Active/Inactive

        // Map incoming fields to DB schema
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
            'medical_notes'      => $input['medical_notes'] ?? null,
            'status'             => $status,
        ];

        // Optionally update primary_doctor_id if provided and valid
        if (isset($input['primary_doctor_id']) && $input['primary_doctor_id'] !== '') {
            $candidate = (int)$input['primary_doctor_id'];
            if ($candidate > 0) {
                try {
                    $exists = $this->db->table('doctor')->where('doctor_id', $candidate)->countAllResults();
                    if ($exists > 0) { $data['primary_doctor_id'] = $candidate; }
                } catch (\Throwable $e) { /* ignore invalid */ }
            } else {
                // Allow clearing assignment when explicitly set to empty string/0
                $data['primary_doctor_id'] = null;
            }
        }

        // Remove nulls to avoid overwriting with null unintentionally
        $data = array_filter($data, function($v) { return $v !== null; });

        try {
            $builder = $this->db->table('patient');
            $ok = $builder->where('patient_id', $id)->update($data);
            if ($ok) {
                return $this->response->setJSON([
                    'status'  => 'success',
                    'message' => 'Patient updated successfully',
                    'id'      => $id,
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Failed to update patient: ' . $e->getMessage());
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Database error: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }

        return $this->response->setJSON([
            'status'  => 'error',
            'message' => 'Failed to update patient',
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
     * API: Return all doctors for selection (joins staff for names)
     * Route: GET admin/doctors/api
     */
    public function getDoctorsAPI()
    {
        try {
            $rows = $this->db->table('doctor d')
                ->select('d.doctor_id, s.staff_id, s.first_name, s.last_name, s.department, d.specialization, d.status')
                ->join('staff s', 's.staff_id = d.staff_id', 'left')
                ->orderBy('s.first_name', 'ASC')
                ->get()->getResultArray();
            $data = array_map(function($r){
                $r['name'] = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
                return $r;
            }, $rows);
            return $this->response->setJSON(['status' => 'success', 'data' => $data]);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to load doctors: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Failed to load doctors']);
        }
    }

    /**
     * API: Return all doctor shifts as JSON
     * Route: GET admin/doctor-shifts/api
     */
    public function getDoctorShiftsAPI()
    {
        try {
            $builder = $this->db->table('doctor_shift ds')
                ->select('ds.shift_id as id, ds.shift_date as date, ds.shift_start as start, ds.shift_end as end, ds.department, ds.status, ds.shift_type, ds.duration_hours, ds.room_ward, ds.notes, d.doctor_id, s.first_name, s.last_name')
                ->join('doctor d', 'd.doctor_id = ds.doctor_id', 'left')
                ->join('staff s', 's.staff_id = d.staff_id', 'left')
                ->orderBy('ds.shift_date', 'DESC')
                ->orderBy('ds.shift_start', 'DESC');

            $rows = $builder->get()->getResultArray();
            $data = array_map(function ($r) {
                $r['doctor_name'] = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
                return $r;
            }, $rows);

            return $this->response->setJSON(['status' => 'success', 'data' => $data]);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to load doctor shifts: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Failed to load doctor shifts']);
        }
    }

    /**
     * API: Get single doctor shift by ID
     * Route: GET admin/doctor-shifts/{id}
     */
    public function getDoctorShift($id = null)
    {
        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Missing shift id']);
        }
        try {
            $r = $this->db->table('doctor_shift ds')
                ->select('ds.shift_id as id, ds.shift_date as date, ds.shift_start as start, ds.shift_end as end, ds.department, ds.status, ds.shift_type, ds.duration_hours, ds.room_ward, ds.notes, ds.doctor_id, s.first_name, s.last_name')
                ->join('doctor d', 'd.doctor_id = ds.doctor_id', 'left')
                ->join('staff s', 's.staff_id = d.staff_id', 'left')
                ->where('ds.shift_id', (int)$id)
                ->get()->getRowArray();
            if (!$r) {
                return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Shift not found']);
            }
            $r['doctor_name'] = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
            return $this->response->setJSON(['status' => 'success', 'data' => $r]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Failed to load shift']);
        }
    }

    /**
     * API: Create doctor shift
     * Route: POST admin/doctor-shifts/create
     */
    public function createDoctorShift()
    {
        $input = $this->request->getJSON(true) ?? $this->request->getPost();

        $validation = \Config\Services::validation();
        $validation->setRules([
            'doctor_id'    => 'required|integer',
            'shift_date'   => 'required|valid_date',
            'shift_start'  => 'required',
            'shift_end'    => 'required',
            'department'   => 'permit_empty|max_length[100]',
            'status'       => 'permit_empty|in_list[Scheduled,Completed,Cancelled]',
            'shift_type'   => 'permit_empty|max_length[50]',
            'room_ward'    => 'permit_empty|max_length[100]',
            'notes'        => 'permit_empty',
        ]);
        if (!$validation->run($input)) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validation->getErrors(),
                'csrf' => [ 'name' => csrf_token(), 'value' => csrf_hash() ],
            ]);
        }

        // Compute duration (in hours, handle overnight end < start)
        $duration = null;
        try {
            $start = new \DateTime($input['shift_date'].' '.$input['shift_start']);
            $end   = new \DateTime($input['shift_date'].' '.$input['shift_end']);
            if ($end < $start) { $end->modify('+1 day'); }
            $diff = $end->getTimestamp() - $start->getTimestamp();
            $duration = round($diff / 3600, 2);
        } catch (\Throwable $e) {
            $duration = null;
        }

        try {
            // Ensure doctor exists to avoid FK violation
            $doctorId = (int)$input['doctor_id'];
            $exists = $this->db->table('doctor')->where('doctor_id', $doctorId)->countAllResults();
            if ($exists === 0) {
                log_message('warning', 'Create shift blocked: doctor_id not found: ' . $doctorId);
                return $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'message' => 'Selected doctor does not exist. Please create a doctor profile first.',
                    'csrf' => [ 'name' => csrf_token(), 'value' => csrf_hash() ],
                ]);
            }
            // If no department provided, default to the doctor's staff department
            $dept = $input['department'] ?? null;
            if (empty($dept)) {
                try {
                    $s = $this->db->table('doctor d')
                        ->select('s.department')
                        ->join('staff s', 's.staff_id = d.staff_id', 'left')
                        ->where('d.doctor_id', $doctorId)
                        ->get()->getRowArray();
                    $dept = $s['department'] ?? null;
                } catch (\Throwable $e) {
                    $dept = null;
                }
            }

            $data = [
                'doctor_id'      => $doctorId,
                'shift_date'     => $input['shift_date'],
                'shift_start'    => $input['shift_start'],
                'shift_end'      => $input['shift_end'],
                'department'     => $dept,
                'status'         => $input['status'] ?? 'Scheduled',
                'shift_type'     => $input['shift_type'] ?? null,
                'room_ward'      => $input['room_ward'] ?? null,
                'notes'          => $input['notes'] ?? null,
                'duration_hours' => $duration,
            ];
            $ok = $this->db->table('doctor_shift')->insert($data);
            if ($ok) {
                return $this->response->setJSON([
                    'status'  => 'success',
                    'message' => 'Shift created',
                    'id'      => $this->db->insertID(),
                    'csrf'    => [ 'name' => csrf_token(), 'value' => csrf_hash() ],
                ]);
            }
            // Insert failed without exception: return DB error for debugging
            $dbErr = $this->db->error();
            log_message('error', 'Create doctor shift failed: ' . json_encode($dbErr));
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to create shift',
                'db_error' => $dbErr,
                'csrf' => [ 'name' => csrf_token(), 'value' => csrf_hash() ],
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to create doctor shift: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to create shift',
                'exception' => $e->getMessage(),
                'csrf' => [ 'name' => csrf_token(), 'value' => csrf_hash() ],
            ]);
        }
        // Fallback (should not reach here)
        return $this->response->setStatusCode(500)->setJSON([
            'status' => 'error',
            'message' => 'Failed to create shift',
            'csrf' => [ 'name' => csrf_token(), 'value' => csrf_hash() ],
        ]);
    }

    /**
     * API: Update doctor shift
     * Route: POST admin/doctor-shifts/update
     */
    public function updateDoctorShift()
    {
        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        if (!is_array($input) || empty($input['id'])) {
            return $this->response->setStatusCode(422)->setJSON(['status' => 'error', 'message' => 'id is required']);
        }
        $id = (int)$input['id'];

        // Ensure record exists
        $exists = $this->db->table('doctor_shift')->where('shift_id', $id)->countAllResults();
        if ($exists === 0) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Shift not found',
                'csrf' => [ 'name' => csrf_token(), 'value' => csrf_hash() ],
            ]);
        }

        $data = [
            'shift_date'     => $input['shift_date']    ?? null,
            'shift_start'    => $input['shift_start']   ?? null,
            'shift_end'      => $input['shift_end']     ?? null,
            'department'     => $input['department']    ?? null,
            'status'         => $input['status']        ?? null,
            'shift_type'     => $input['shift_type']    ?? null,
            'room_ward'      => $input['room_ward']     ?? null,
            'notes'          => $input['notes']         ?? null,
        ];
        // Recompute duration if times provided
        if (!empty($input['shift_date']) && !empty($input['shift_start']) && !empty($input['shift_end'])) {
            try {
                $start = new \DateTime($input['shift_date'].' '.$input['shift_start']);
                $end   = new \DateTime($input['shift_date'].' '.$input['shift_end']);
                if ($end < $start) { $end->modify('+1 day'); }
                $diff = $end->getTimestamp() - $start->getTimestamp();
                $data['duration_hours'] = round($diff / 3600, 2);
            } catch (\Throwable $e) {}
        }

        // Remove nulls
        $data = array_filter($data, function($v) { return $v !== null; });
        if (empty($data)) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => 'No fields to update',
                'csrf' => [ 'name' => csrf_token(), 'value' => csrf_hash() ],
            ]);
        }
        try {
            $ok = $this->db->table('doctor_shift')->where('shift_id', $id)->update($data);
            if ($ok) {
                $affected = $this->db->affectedRows();
                return $this->response->setJSON([
                    'status'   => 'success',
                    'message'  => $affected > 0 ? 'Shift updated' : 'No changes applied',
                    'affected' => $affected,
                    'csrf'     => [ 'name' => csrf_token(), 'value' => csrf_hash() ],
                ]);
            }
            $dbErr = $this->db->error();
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to update shift',
                'db_error' => $dbErr,
                'csrf' => [ 'name' => csrf_token(), 'value' => csrf_hash() ],
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to update shift',
                'exception' => $e->getMessage(),
                'csrf' => [ 'name' => csrf_token(), 'value' => csrf_hash() ],
            ]);
        }
    }

    /**
     * API: Delete doctor shift
     * Route: POST admin/doctor-shifts/delete
     */
    public function deleteDoctorShift()
    {
        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        $id = (int)($input['id'] ?? 0);
        if ($id <= 0) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => 'id is required',
                'csrf' => [ 'name' => csrf_token(), 'value' => csrf_hash() ],
            ]);
        }
        // Ensure exists first
        $exists = $this->db->table('doctor_shift')->where('shift_id', $id)->countAllResults();
        if ($exists === 0) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Shift not found',
                'csrf' => [ 'name' => csrf_token(), 'value' => csrf_hash() ],
            ]);
        }
        try {
            $ok = $this->db->table('doctor_shift')->where('shift_id', $id)->delete();
            if ($ok) {
                $affected = $this->db->affectedRows();
                return $this->response->setJSON([
                    'status' => $affected > 0 ? 'success' : 'error',
                    'message' => $affected > 0 ? 'Shift deleted' : 'Shift not found',
                    'affected' => $affected,
                    'csrf' => [ 'name' => csrf_token(), 'value' => csrf_hash() ],
                ]);
            }
            $dbErr = $this->db->error();
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to delete shift',
                'db_error' => $dbErr,
                'csrf' => [ 'name' => csrf_token(), 'value' => csrf_hash() ],
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to delete shift',
                'exception' => $e->getMessage(),
                'csrf' => [ 'name' => csrf_token(), 'value' => csrf_hash() ],
            ]);
        }
    }

    /**
     * System Settings page
     */
    public function systemSettings()
    {
        $data = [
            'title' => 'System Settings',
        ];
        return view('admin/system-settings', $data);
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

    /**
     * API: Return all staff as JSON
     * Route: GET admin/staff/api
     */
    public function getStaffAPI()
    {
        try {
            // Fetch all staff
            $rows = $this->builder->orderBy('last_name', 'ASC')->orderBy('first_name', 'ASC')->get()->getResultArray();
            // Normalize payload to include a generic 'id' and 'full_name'
            $staff = array_map(function ($s) {
                $first = $s['first_name'] ?? '';
                $last  = $s['last_name'] ?? '';
                $s['id'] = $s['staff_id'] ?? null;
                $s['full_name'] = trim($first . ' ' . $last);
                return $s;
            }, $rows);

            return $this->response->setJSON($staff);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to load staff: ' . $e->getMessage());
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['status' => 'error', 'message' => 'Failed to load staff']);
        }
    }

    /**
     * Update staff member (AJAX)
     * Route: POST admin/edit-staff/{id}
     */
    public function editStaff($id = null)
    {
        $id = (int) ($id ?? 0);
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Invalid staff ID'
            ]);
        }

        // Ensure staff exists
        $existing = $this->builder->where('staff_id', $id)->get()->getRowArray();
        if (!$existing) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Staff not found'
            ]);
        }

        // Accept form POST (multipart) or JSON
        $input = $this->request->getPost();
        if (!$input) {
            $input = $this->request->getJSON(true) ?? [];
        }

        // Minimal validation rules
        $validation = \Config\Services::validation();
        $validation->setRules([
            'first_name'  => 'permit_empty|min_length[1]|max_length[100]',
            'last_name'   => 'permit_empty|max_length[100]',
            'gender'      => 'permit_empty|in_list[male,female,other,Male,Female,Other,MALE,FEMALE,OTHER]',
            'dob'         => 'permit_empty|valid_date',
            'contact_no'  => 'permit_empty|max_length[255]',
            'email'       => 'permit_empty|valid_email',
            'address'     => 'permit_empty',
            'department'  => 'permit_empty|max_length[255]',
            'designation' => 'permit_empty|in_list[admin,doctor,nurse,pharmacist,receptionist,laboratorist,it_staff,accountant]'
        ]);

        if (!$validation->run($input)) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validation->getErrors(),
            ]);
        }

        // Prepare update data; only include provided fields
        $data = [];
        $map = [
            'employee_id' => 'employee_id',
            'first_name'  => 'first_name',
            'last_name'   => 'last_name',
            'gender'      => 'gender',
            'dob'         => 'dob',
            'contact_no'  => 'contact_no',
            'email'       => 'email',
            'address'     => 'address',
            'department'  => 'department',
            'designation' => 'designation',
        ];
        foreach ($map as $in => $col) {
            if (array_key_exists($in, $input) && $input[$in] !== '') {
                $data[$col] = $input[$in];
            }
        }
        if (isset($data['gender'])) {
            $data['gender'] = strtolower($data['gender']);
        }
        if (isset($data['designation'])) {
            // Keep role in sync with designation when provided
            $data['role'] = $data['designation'];
        }

        if (empty($data)) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'No changes submitted',
            ]);
        }

        try {
            $ok = $this->builder->where('staff_id', $id)->update($data);
            if ($ok) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Staff updated successfully',
                    'id' => $id,
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Failed to update staff '.$id.': '.$e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage(),
            ]);
        }

        return $this->response->setStatusCode(500)->setJSON([
            'status' => 'error',
            'message' => 'Failed to update staff',
        ]);
    }
}
