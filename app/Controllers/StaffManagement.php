<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\StaffModel;

class StaffManagement extends BaseController
{
    protected $db;
    protected $builder;
    protected $userModel;
    protected $staffModel;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('staff');
        $this->userModel = new UserModel();
        $this->staffModel = new StaffModel();

        // Authentication is now handled by the roleauth filter in routes
    }

    // Fetch a single staff member as JSON (for modals)
    public function getStaff($id = null)
    {
        $id = (int)($id ?? 0);
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Invalid staff ID']);
        }
        $row = $this->builder->where('staff_id', $id)->get()->getRowArray();
        if (!$row) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Staff not found']);
        }
        $row['id'] = $row['staff_id'];
        return $this->response->setJSON($row);
    }

    /**
     * Main unified staff management - Role-based
     */
    public function index()
    {
        $session = session();
        $userRole = $session->get('role');
        $staffId = $session->get('staff_id');
        
        // Get staff data based on role permissions
        $staff = $this->getStaffByRole($userRole, $staffId);
        $stats = $this->getStaffStats($userRole, $staffId);
        
        $data = [
            'title' => 'Staff Management',
            'userRole' => $userRole,
            'staff' => $staff,
            'staffStats' => $stats,
            'permissions' => $this->getStaffPermissions($userRole),
            'total_staff' => count($staff),
        ];

        // Use unified view that adapts to user role
        return view('staff-management/staff-management', $data);
    }

    public function create()
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

            $designation = $this->request->getPost('designation');
            if ($designation === 'doctor') {
                $validation->setRules([
                    'doctor_specialization' => 'required|min_length[2]|max_length[100]',
                    'doctor_license_no'     => 'permit_empty|max_length[50]',
                    'doctor_consultation_fee' => 'permit_empty|decimal'
                ]);
            } elseif ($designation === 'nurse') {
                $validation->setRules(['nurse_license_no' => 'required|max_length[100]']);
            } elseif ($designation === 'pharmacist') {
                $validation->setRules(['pharmacist_license_no' => 'required|max_length[100]']);
            } elseif ($designation === 'laboratorist') {
                $validation->setRules([
                    'laboratorist_license_no' => 'required|max_length[100]',
                    'laboratorist_specialization' => 'permit_empty|max_length[150]',
                    'laboratorist_lab_room_no' => 'permit_empty|max_length[50]'
                ]);
            } elseif ($designation === 'accountant') {
                $validation->setRules(['accountant_license_no' => 'required|max_length[100]']);
            } elseif ($designation === 'receptionist') {
                $validation->setRules(['receptionist_desk_no' => 'permit_empty|max_length[50]']);
            } elseif ($designation === 'it_staff') {
                $validation->setRules(['it_expertise' => 'permit_empty|max_length[150]']);
            }

            $isAjax = $this->request->isAJAX() || 
                      $this->request->getHeaderLine('Accept') == 'application/json' ||
                      $this->request->getHeaderLine('X-Requested-With') == 'XMLHttpRequest';

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

            if ($this->builder->insert($data)) {
                $staffId = (int)$this->db->insertID();
                $this->insertRoleSpecificData($designation, $staffId);
                
                if ($isAjax) {
                    return $this->response->setJSON(['status' => 'success', 'message' => 'Staff member added successfully!']);
                }
                session()->setFlashdata('success', 'Staff member added successfully!');
            } else {
                if ($isAjax) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to add staff member.']);
                }
                session()->setFlashdata('error', 'Failed to add staff member.');
            }

            if (!$isAjax) {
                return redirect()->to(base_url('admin/staff-management'));
            }
        }

        $data = ['title' => 'Add Staff'];
        return view('admin/add-staff', $data);
    }

    private function insertRoleSpecificData($designation, $staffId)
    {
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
            }
        } catch (\Throwable $e) {
            log_message('error', 'Failed inserting role-specific record for staff_id ' . $staffId . ': ' . $e->getMessage());
            session()->setFlashdata('warning', 'Staff saved, but role details could not be created.');
        }
    }

    public function update($id = null)
    {
        $id = (int) ($id ?? ($this->request->getPost('staff_id') ?? 0));
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Invalid staff ID']);
        }

        $existing = $this->builder->where('staff_id', $id)->get()->getRowArray();
        if (!$existing) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Staff not found']);
        }

        $input = $this->request->getPost() ?: $this->request->getJSON(true) ?? [];

        $validation = \Config\Services::validation();
        $validation->setRules([
            'first_name'  => 'permit_empty|min_length[1]|max_length[100]',
            'last_name'   => 'permit_empty|max_length[100]',
            'gender'      => 'permit_empty|in_list[male,female,other,Male,Female,Other]',
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

        $data = [];
        $fields = ['employee_id', 'first_name', 'last_name', 'gender', 'dob', 'contact_no', 'email', 'address', 'department', 'designation'];
        foreach ($fields as $field) {
            if (array_key_exists($field, $input) && $input[$field] !== '') {
                $data[$field] = $input[$field];
            }
        }

        if (isset($data['gender'])) {
            $data['gender'] = strtolower($data['gender']);
        }
        if (isset($data['designation'])) {
            $data['role'] = $data['designation'];
        }

        if (empty($data)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'No changes submitted']);
        }

        try {
            if ($this->builder->where('staff_id', $id)->update($data)) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Staff updated successfully', 'id' => $id]);
            }
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }

        return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Failed to update staff']);
    }

    public function delete($id = null)
    {
        if ($id && $this->builder->where('staff_id', $id)->delete()) {
            session()->setFlashdata('success', 'Staff member deleted successfully!');
        } else {
            session()->setFlashdata('error', 'Failed to delete staff member.');
        }
        return redirect()->to(base_url('admin/staff-management'));
    }

    public function view($id = null)
    {
        if (!$id) {
            return redirect()->to(base_url('admin/staff-management'));
        }

        $staff = $this->builder->where('staff_id', $id)->get()->getRowArray();
        if (!$staff) {
            session()->setFlashdata('error', 'Staff member not found.');
            return redirect()->to(base_url('admin/staff-management'));
        }

        $data = ['title' => 'Staff Details', 'staff' => $staff];
        return view('admin/view-staff', $data);
    }

    // API Methods
    public function getStaffAPI()
    {
        try {
            $rows = $this->builder->orderBy('last_name', 'ASC')->orderBy('first_name', 'ASC')->get()->getResultArray();
            $staff = array_map(function ($s) {
                $s['id'] = $s['staff_id'] ?? null;
                $s['full_name'] = trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''));
                return $s;
            }, $rows);
            return $this->response->setJSON($staff);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Failed to load staff']);
        }
    }

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
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Failed to load doctors']);
        }
    }

    // ===================================================================
    // UNIFIED STAFF MANAGEMENT HELPER METHODS
    // ===================================================================

    /**
     * Get staff data based on user role and permissions
     */
    private function getStaffByRole($userRole, $staffId)
    {
        try {
            $builder = $this->db->table('staff s');
            
            switch ($userRole) {
                case 'admin':
                    // Admin can see all staff
                    $staff = $builder->orderBy('first_name', 'ASC')->get()->getResultArray();
                    break;
                    
                case 'doctor':
                    // Doctors can see staff in their department
                    $doctorInfo = $this->db->table('staff')->where('staff_id', $staffId)->get()->getRowArray();
                    $department = $doctorInfo['department'] ?? null;
                    
                    if ($department) {
                        $staff = $builder->where('department', $department)
                                        ->orderBy('first_name', 'ASC')
                                        ->get()->getResultArray();
                    } else {
                        $staff = [];
                    }
                    break;
                    
                case 'nurse':
                    // Nurses can see staff in their department
                    $nurseInfo = $this->db->table('staff')->where('staff_id', $staffId)->get()->getRowArray();
                    $department = $nurseInfo['department'] ?? null;
                    
                    if ($department) {
                        $staff = $builder->where('department', $department)
                                        ->whereIn('designation', ['doctor', 'nurse', 'receptionist'])
                                        ->orderBy('first_name', 'ASC')
                                        ->get()->getResultArray();
                    } else {
                        $staff = [];
                    }
                    break;
                    
                case 'receptionist':
                    // Receptionists can see doctors and other receptionists
                    $staff = $builder->whereIn('designation', ['doctor', 'receptionist'])
                                    ->orderBy('first_name', 'ASC')
                                    ->get()->getResultArray();
                    break;
                    
                default:
                    // Other roles see limited staff info
                    $staff = $builder->whereIn('designation', ['doctor', 'receptionist'])
                                    ->select('staff_id, first_name, last_name, designation, department')
                                    ->orderBy('first_name', 'ASC')
                                    ->get()->getResultArray();
            }
            
            // Add full name and format data
            return array_map(function($s) {
                $s['full_name'] = trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''));
                $s['id'] = $s['staff_id'] ?? null;
                return $s;
            }, $staff);
            
        } catch (\Throwable $e) {
            log_message('error', 'Staff data fetch error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get staff statistics based on user role
     */
    private function getStaffStats($userRole, $staffId)
    {
        try {
            $stats = [];
            
            switch ($userRole) {
                case 'admin':
                    $stats = [
                        'total_staff' => $this->db->table('staff')->countAllResults(),
                        'total_doctors' => $this->db->table('staff')->where('designation', 'doctor')->countAllResults(),
                        'total_nurses' => $this->db->table('staff')->where('designation', 'nurse')->countAllResults(),
                        'total_receptionists' => $this->db->table('staff')->where('designation', 'receptionist')->countAllResults(),
                        'total_pharmacists' => $this->db->table('staff')->where('designation', 'pharmacist')->countAllResults(),
                        'total_laboratorists' => $this->db->table('staff')->where('designation', 'laboratorist')->countAllResults(),
                        'active_staff' => $this->db->table('staff')->where('status', 'Active')->countAllResults(),
                        'new_staff_month' => $this->db->table('staff')->where('date_joined >=', date('Y-m-01'))->countAllResults(),
                    ];
                    break;
                    
                case 'doctor':
                case 'nurse':
                    // Get department-based stats
                    $userInfo = $this->db->table('staff')->where('staff_id', $staffId)->get()->getRowArray();
                    $department = $userInfo['department'] ?? null;
                    
                    if ($department) {
                        $stats = [
                            'department_staff' => $this->db->table('staff')->where('department', $department)->countAllResults(),
                            'department_doctors' => $this->db->table('staff')->where('department', $department)->where('designation', 'doctor')->countAllResults(),
                            'department_nurses' => $this->db->table('staff')->where('department', $department)->where('designation', 'nurse')->countAllResults(),
                        ];
                    }
                    break;
                    
                case 'receptionist':
                    $stats = [
                        'total_doctors' => $this->db->table('staff')->where('designation', 'doctor')->countAllResults(),
                        'total_receptionists' => $this->db->table('staff')->where('designation', 'receptionist')->countAllResults(),
                        'active_doctors' => $this->db->table('doctor')->where('status', 'Active')->countAllResults(),
                    ];
                    break;
                    
                default:
                    $stats = [
                        'total_staff' => $this->db->table('staff')->countAllResults(),
                        'total_doctors' => $this->db->table('staff')->where('designation', 'doctor')->countAllResults(),
                    ];
            }
            
            return $stats;
            
        } catch (\Throwable $e) {
            log_message('error', 'Staff stats error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get staff permissions based on user role
     */
    private function getStaffPermissions($userRole)
    {
        return [
            'canCreate' => in_array($userRole, ['admin']),
            'canEdit' => in_array($userRole, ['admin']),
            'canDelete' => in_array($userRole, ['admin']),
            'canView' => in_array($userRole, ['admin', 'doctor', 'nurse', 'receptionist']),
            'canViewSalary' => in_array($userRole, ['admin', 'accountant']),
            'canManageSchedule' => in_array($userRole, ['admin', 'doctor']),
            'canViewDepartment' => in_array($userRole, ['admin', 'doctor', 'nurse']),
            'canExport' => in_array($userRole, ['admin']),
        ];
    }
}