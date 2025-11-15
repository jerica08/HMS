<?php

namespace App\Services;

class StaffService
{
    protected $db;
    // Cache table columns to avoid repeated metadata lookups
    private $tableColumnsCache = [];

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Get staff with role-based filtering
     */
    public function getStaffByRole($userRole, $staffId = null, $filters = [])
    {
        try {
            $builder = $this->db->table('staff s')
                ->select('s.*, 
                         s.staff_id as id,
                         s.department_id,
                         dpt.name as department,
                         CONCAT(s.first_name, " ", s.last_name) as full_name,
                         TIMESTAMPDIFF(YEAR, s.dob, CURDATE()) as age,
                         DATE_FORMAT(s.date_joined, "%M %d, %Y") as formatted_date_joined,
                         d.specialization as doctor_specialization,
                         d.license_no as doctor_license_no,
                         d.consultation_fee as doctor_consultation_fee,
                         n.license_no as nurse_license_no,
                         p.license_no as pharmacist_license_no,
                         l.license_no as laboratorist_license_no,
                         l.specialization as laboratorist_specialization,
                         a.license_no as accountant_license_no,
                         r.desk_no as receptionist_desk_no,
                         i.expertise as it_expertise,
                         s.role_id,
                         rl.slug as role_slug,
                         rl.name as role_name')
                ->join('department dpt', 'dpt.department_id = s.department_id', 'left')
                ->join('doctor d', 'd.staff_id = s.staff_id', 'left')
                ->join('nurse n', 'n.staff_id = s.staff_id', 'left')
                ->join('pharmacist p', 'p.staff_id = s.staff_id', 'left')
                ->join('laboratorist l', 'l.staff_id = s.staff_id', 'left')
                ->join('accountant a', 'a.staff_id = s.staff_id', 'left')
                ->join('receptionist r', 'r.staff_id = s.staff_id', 'left')
                ->join('it_staff i', 'i.staff_id = s.staff_id', 'left')
                ->join('roles rl', 'rl.role_id = s.role_id', 'left');

            // Role-based filtering
            switch ($userRole) {
                case 'admin':
                case 'it_staff':
                    // Admin and IT staff can see all staff
                    break;
                case 'doctor':
                    // Doctors can see staff in their department and nurses
                    $doctorInfo = $this->db->table('staff')->where('staff_id', $staffId)->get()->getRowArray();
                    if ($doctorInfo && !empty($doctorInfo['department_id'])) {
                        $builder->where('s.department_id', $doctorInfo['department_id']);
                    }
                    break;
                case 'nurse':
                    // Nurses can see staff in their department
                    $nurseInfo = $this->db->table('staff')->where('staff_id', $staffId)->get()->getRowArray();
                    if ($nurseInfo && !empty($nurseInfo['department_id'])) {
                        $builder->where('s.department_id', $nurseInfo['department_id']);
                    }
                    break;
                case 'receptionist':
                    // Receptionists can see doctors and nurses for scheduling
                    $builder->whereIn('rl.slug', ['doctor', 'nurse']);
                    break;
                default:
                    // Other roles see limited staff
                    $builder->where('s.staff_id', $staffId);
                    break;
            }

            // Apply additional filters
            if (isset($filters['department'])) {
                // Filter by department name via join
                $builder->where('dpt.name', $filters['department']);
            }
            
            if (!empty($filters['role'])) {
                // Filter by role slug via roles table
                $builder->where('rl.slug', $filters['role']);
            }
            
            if (isset($filters['status'])) {
                $builder->where('s.status', $filters['status']);
            }

            if (isset($filters['search'])) {
                $search = $filters['search'];
                $builder->groupStart()
                    ->like('s.first_name', $search)
                    ->orLike('s.last_name', $search)
                    ->orLike('s.employee_id', $search)
                    ->orLike('s.email', $search)
                    ->orLike('dpt.name', $search)
                    ->groupEnd();
            }

            $staff = $builder->orderBy('s.first_name', 'ASC')
                ->get()
                ->getResultArray();

            return [
                'success' => true,
                'data' => $staff,
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Error fetching staff: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch staff',
                'data' => [],
            ];
        }
    }

    /**
     * Get single staff member by ID
     */
    public function getStaff($id)
    {
        try {
            $staff = $this->db->table('staff s')
                ->select('s.*, 
                         CONCAT(s.first_name, " ", s.last_name) as full_name,
                         TIMESTAMPDIFF(YEAR, s.dob, CURDATE()) as age,
                         d.specialization as doctor_specialization,
                         d.license_no as doctor_license_no,
                         d.consultation_fee as doctor_consultation_fee,
                         n.license_no as nurse_license_no,
                         p.license_no as pharmacist_license_no,
                         l.license_no as laboratorist_license_no,
                         l.specialization as laboratorist_specialization,
                         a.license_no as accountant_license_no,
                         r.desk_no as receptionist_desk_no,
                         i.expertise as it_expertise,
                         s.role_id,
                         rl.slug as role_slug,
                         rl.name as role_name')
                ->join('doctor d', 'd.staff_id = s.staff_id', 'left')
                ->join('nurse n', 'n.staff_id = s.staff_id', 'left')
                ->join('pharmacist p', 'p.staff_id = s.staff_id', 'left')
                ->join('laboratorist l', 'l.staff_id = s.staff_id', 'left')
                ->join('accountant a', 'a.staff_id = s.staff_id', 'left')
                ->join('receptionist r', 'r.staff_id = s.staff_id', 'left')
                ->join('it_staff i', 'i.staff_id = s.staff_id', 'left')
                ->join('roles rl', 'rl.role_id = s.role_id', 'left')
                ->where('s.staff_id', $id)
                ->get()
                ->getRowArray();

            if (!$staff) {
                return [
                    'success' => false,
                    'message' => 'Staff member not found',
                ];
            }

            // Add ID alias for frontend compatibility
            $staff['id'] = $staff['staff_id'];

            return [
                'success' => true,
                'staff' => $staff,
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Error fetching staff member: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Database error',
            ];
        }
    }

    /**
     * Create new staff member
     */
    public function createStaff($input, $userRole)
    {
        // Check permissions
        if (!$this->canCreateStaff($userRole)) {
            return [
                'success' => false,
                'message' => 'Permission denied',
            ];
        }

        $input = $this->normalizeInput($input);

        // Validation
        $validation = \Config\Services::validation();
        $validation->setRules($this->getValidationRules($input['role'] ?? 'staff'));

        if (!$validation->run($input)) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors(),
            ];
        }

        // Check for duplicate employee ID
        $existing = $this->db->table('staff')
            ->where('employee_id', $input['employee_id'])
            ->get()
            ->getRowArray();

        if ($existing) {
            return [
                'success' => false,
                'message' => 'Employee ID already exists',
            ];
        }

        try {
            if (!empty($input['department'])) {
                $this->ensureDepartmentExists($input['department']);
            }
            // Insert the core staff record in its own transaction
            $this->db->transStart();

            // Prepare staff data
            $staffData = $this->prepareStaffData($input);
            
            // Insert staff record (filter to existing columns)
            $staffData = $this->filterToExistingColumns('staff', $staffData);
            $this->db->table('staff')->insert($staffData);
            $staffId = $this->db->insertID();

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                $dbError = $this->db->error();
                $errMsg = !empty($dbError['message']) ? $dbError['message'] : 'Failed to create staff member';
                return [
                    'success' => false,
                    'message' => $errMsg,
                ];
            }

            // After staff row is saved, try role-specific insert without transaction
            $this->insertRoleSpecificData($input['role'], $staffId, $input);

            return [
                'success' => true,
                'message' => 'Staff member created successfully',
                'id' => $staffId,
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Failed to create staff: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Update staff member
     */
    public function updateStaff($id, $input, $userRole)
    {
        // Check permissions
        if (!$this->canEditStaff($id, $userRole)) {
            return [
                'success' => false,
                'message' => 'Permission denied',
            ];
        }

        // Get existing staff
        $existingStaff = $this->getStaff($id);
        if (!$existingStaff['success']) {
            return $existingStaff;
        }

        // Merge required defaults from existing record if not provided
        $input = array_merge([
            'employee_id' => $existingStaff['staff']['employee_id'] ?? null,
            'role' => $existingStaff['staff']['role'] ?? null,
        ], $input);

        // Normalize input prior to validation
        $input = $this->normalizeInput($input);

        // Validation
        $validation = \Config\Services::validation();
        $validation->setRules($this->getUpdateValidationRules($input['role'] ?? 'staff', $id));

        if (!$validation->run($input)) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors(),
            ];
        }

        try {
            if (!empty($input['department'])) {
                $this->ensureDepartmentExists($input['department']);
            }
            // Prepare update data and filter to existing columns
            $staffData = $this->prepareStaffData($input);
            $staffData = $this->filterToExistingColumns('staff', $staffData);
            // Add timestamp only if column exists
            $staffColumns = $this->getTableColumns('staff');
            if (in_array('updated_at', $staffColumns, true)) {
                $staffData['updated_at'] = date('Y-m-d H:i:s');
            }

            // Update staff record
            $this->db->table('staff')
                ->where('staff_id', $id)
                ->update($staffData);

            return [
                'success' => true,
                'message' => 'Staff member updated successfully',
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Failed to update staff: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Delete staff member
     */
    public function deleteStaff($id, $userRole)
    {
        // Only admin can delete staff
        if ($userRole !== 'admin') {
            return [
                'success' => false,
                'message' => 'Only administrators can delete staff members',
            ];
        }

        try {
            $this->db->transStart();

            // Get staff info for role-specific cleanup
            $staff = $this->getStaff($id);
            if ($staff['success']) {
                $role = $staff['staff']['role'];
                
                // Delete role-specific data
                $this->deleteRoleSpecificData($role, $id);
            }

            // Delete staff record
            $result = $this->db->table('staff')
                ->where('staff_id', $id)
                ->delete();

            $this->db->transComplete();

            if ($this->db->transStatus() === false || !$result) {
                return [
                    'success' => false,
                    'message' => 'Failed to delete staff member',
                ];
            }

            return [
                'success' => true,
                'message' => 'Staff member deleted successfully',
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Failed to delete staff: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get staff statistics
     */
    public function getStaffStats($userRole = null, $staffId = null)
    {
        $stats = [
            'total_staff' => 0,
            'active_staff' => 0,
            'inactive_staff' => 0,
            'doctors' => 0,
            'nurses' => 0,
            'pharmacists' => 0,
            'receptionists' => 0,
            'laboratorists' => 0,
            'accountants' => 0,
            'it_staff' => 0,
            'new_this_month' => 0,
            'department_staff' => 0,
        ];

        try {
            $builder = $this->db->table('staff');

            // Role-based filtering for stats
            if ($userRole === 'nurse' && $staffId) {
                $nurseInfo = $this->db->table('staff')->where('staff_id', $staffId)->get()->getRowArray();
                if ($nurseInfo && !empty($nurseInfo['department'])) {
                    $builder->where('department', $nurseInfo['department']);
                }
            } elseif ($userRole === 'doctor' && $staffId) {
                $doctorInfo = $this->db->table('staff')->where('staff_id', $staffId)->get()->getRowArray();
                if ($doctorInfo && !empty($doctorInfo['department'])) {
                    $builder->where('department', $doctorInfo['department']);
                }
            }

            // Total staff
            $stats['total_staff'] = (clone $builder)->countAllResults(false);

            // Active/Inactive
            $stats['active_staff'] = (clone $builder)->where('status', 'active')->countAllResults(false);
            $stats['inactive_staff'] = (clone $builder)->where('status', 'inactive')->countAllResults(false);

            // Role counts
            $stats['doctors'] = (clone $builder)->where('role', 'doctor')->countAllResults(false);
            $stats['nurses'] = (clone $builder)->where('role', 'nurse')->countAllResults(false);
            $stats['pharmacists'] = (clone $builder)->where('role', 'pharmacist')->countAllResults(false);
            $stats['receptionists'] = (clone $builder)->where('role', 'receptionist')->countAllResults(false);
            $stats['laboratorists'] = (clone $builder)->where('role', 'laboratorist')->countAllResults(false);
            $stats['accountants'] = (clone $builder)->where('role', 'accountant')->countAllResults(false);
            $stats['it_staff'] = (clone $builder)->where('role', 'it_staff')->countAllResults();

            // New this month
            $firstDayOfMonth = date('Y-m-01');
            $stats['new_this_month'] = (clone $builder)
                ->where('date_joined >=', $firstDayOfMonth)
                ->countAllResults();

            // Department-specific stats
            if ($userRole === 'nurse' || $userRole === 'doctor') {
                $userInfo = $this->db->table('staff')->where('staff_id', $staffId)->get()->getRowArray();
                if ($userInfo && !empty($userInfo['department'])) {
                    $stats['department_staff'] = $this->db->table('staff')
                        ->where('department', $userInfo['department'])
                        ->countAllResults();
                }
            }

        } catch (\Throwable $e) {
            log_message('error', 'Staff stats error: ' . $e->getMessage());
        }

        return $stats;
    }

    // Private helper methods

    private function canCreateStaff($userRole)
    {
        return in_array($userRole, ['admin', 'it_staff']);
    }

    private function canEditStaff($staffId, $userRole)
    {
        return in_array($userRole, ['admin', 'it_staff']);
    }

    /**
     * Normalize incoming input before validation and persistence
     */
    private function normalizeInput(array $input): array
    {
        // Map date_of_birth -> dob if provided
        if (!isset($input['dob']) && isset($input['date_of_birth'])) {
            $input['dob'] = $input['date_of_birth'];
        }

        // Coerce gender to lowercase when present
        if (isset($input['gender']) && is_string($input['gender'])) {
            $input['gender'] = strtolower(trim($input['gender']));
        }

        // Backward compatibility: map 'designation' -> 'role' if role is missing
        if (!isset($input['role']) && isset($input['designation'])) {
            $input['role'] = is_string($input['designation']) ? strtolower(trim($input['designation'])) : $input['designation'];
        }

        // Trim common string fields
        foreach (['employee_id','first_name','last_name','email','department','address'] as $k) {
            if (isset($input[$k]) && is_string($input[$k])) {
                $input[$k] = trim($input[$k]);
            }
        }

        // Normalize DOB to Y-m-d if parseable
        if (!empty($input['dob'])) {
            $ts = strtotime($input['dob']);
            if ($ts !== false) {
                $input['dob'] = date('Y-m-d', $ts);
            } else {
                // If invalid date string, unset to avoid DB/validation errors
                unset($input['dob']);
            }
        }

        // Normalize date_joined
        if (!empty($input['date_joined'])) {
            $ts = strtotime($input['date_joined']);
            if ($ts !== false) {
                $input['date_joined'] = date('Y-m-d', $ts);
            } else {
                unset($input['date_joined']);
            }
        }

        return $input;
    }

    private function getValidationRules($role)
    {
        $baseRules = [
            'employee_id' => 'required|min_length[3]|max_length[255]|is_unique[staff.employee_id]',
            'first_name' => 'required|min_length[2]|max_length[100]',
            'last_name' => 'permit_empty|max_length[100]',
            'gender' => 'permit_empty|in_list[male,female,other,Male,Female,Other]',
            'dob' => 'permit_empty|valid_date',
            'contact_no' => 'permit_empty|max_length[255]',
            'email' => 'permit_empty|valid_email',
            'address' => 'permit_empty',
            'department' => 'permit_empty|max_length[255]',
            'role' => 'required|in_list[admin,doctor,nurse,pharmacist,receptionist,laboratorist,accountant,it_staff]',
            'date_joined' => 'permit_empty|valid_date',
        ];

        // Role-specific validation
        switch ($role) {
            case 'doctor':
                $baseRules['doctor_specialization'] = 'required|min_length[2]|max_length[100]';
                $baseRules['doctor_license_no'] = 'permit_empty|max_length[50]';
                $baseRules['doctor_consultation_fee'] = 'permit_empty|decimal';
                break;
            case 'nurse':
                $baseRules['nurse_license_no'] = 'required|max_length[100]';
                break;
            case 'pharmacist':
                $baseRules['pharmacist_license_no'] = 'required|max_length[100]';
                break;
            case 'laboratorist':
                $baseRules['laboratorist_license_no'] = 'required|max_length[100]';
                $baseRules['laboratorist_specialization'] = 'permit_empty|max_length[150]';
                $baseRules['lab_room_no'] = 'permit_empty|max_length[50]';
                break;
            case 'accountant':
                $baseRules['accountant_license_no'] = 'required|max_length[100]';
                break;
            case 'receptionist':
                $baseRules['receptionist_desk_no'] = 'permit_empty|max_length[50]';
                break;
            case 'it_staff':
                $baseRules['it_expertise'] = 'permit_empty|max_length[150]';
                break;
        }

        return $baseRules;
    }

    private function getUpdateValidationRules($role, $excludeId)
    {
        $rules = $this->getValidationRules($role);
        // Relax some rules for updates
        $rules['employee_id'] = 'permit_empty|min_length[3]|max_length[255]|is_unique[staff.employee_id,staff_id,' . $excludeId . ']';
        $rules['role'] = 'permit_empty|in_list[admin,doctor,nurse,pharmacist,receptionist,laboratorist,accountant,it_staff]';

        // Role-specific: make required fields optional on update
        switch ($role) {
            case 'doctor':
                $rules['doctor_specialization'] = 'permit_empty|max_length[100]';
                $rules['doctor_license_no'] = 'permit_empty|max_length[50]';
                $rules['doctor_consultation_fee'] = 'permit_empty|decimal';
                break;
            case 'nurse':
                $rules['nurse_license_no'] = 'permit_empty|max_length[100]';
                break;
            case 'pharmacist':
                $rules['pharmacist_license_no'] = 'permit_empty|max_length[100]';
                break;
            case 'laboratorist':
                $rules['laboratorist_license_no'] = 'permit_empty|max_length[100]';
                $rules['laboratorist_specialization'] = 'permit_empty|max_length[150]';
                $rules['lab_room_no'] = 'permit_empty|max_length[50]';
                break;
            case 'accountant':
                $rules['accountant_license_no'] = 'permit_empty|max_length[100]';
                break;
            case 'receptionist':
                $rules['receptionist_desk_no'] = 'permit_empty|max_length[50]';
                break;
            case 'it_staff':
                $rules['it_expertise'] = 'permit_empty|max_length[150]';
                break;
        }

        return $rules;
    }

    private function prepareStaffData($input)
    {
        // Determine role_id from explicit input or from role slug
        $roleId = null;

        if (!empty($input['role_id'])) {
            $roleId = (int) $input['role_id'];
        } elseif (!empty($input['role'])) {
            $db   = \Config\Database::connect();
            $role = $db->table('roles')->where('slug', $input['role'])->get()->getRowArray();
            if ($role && isset($role['role_id'])) {
                $roleId = (int) $role['role_id'];
            }
        }

        return [
            'employee_id' => $input['employee_id'] ?? null, 
            'first_name' => $input['first_name'] ?? null,
            'last_name' => $input['last_name'] ?? null,
            'gender' => isset($input['gender']) ? strtolower($input['gender']) : null,
            // Accept both 'dob' and 'date_of_birth' from the form
            'dob' => $input['dob'] ?? ($input['date_of_birth'] ?? null),
            'contact_no' => $input['contact_no'] ?? null,
            'email' => $input['email'] ?? null,
            'address' => $input['address'] ?? null,
            'department' => $input['department'] ?? null,
            'department_id' => $input['department_id'] ?? null,
            'role' => $input['role'] ?? null,
            'role_id' => $roleId,
            'date_joined' => $input['date_joined'] ?? date('Y-m-d'),
            // Note: 'status' and timestamp columns are not present in current migration; do not include
        ];
    }

    private function insertRoleSpecificData($role, $staffId, $input)
    {
        try {
            switch ($role) {
                case 'doctor':
                    $this->db->table('doctor')->insert([
                        'staff_id' => $staffId,
                        // Doctor table requires specialization NOT NULL; default to 'General' when missing
                        'specialization' => ($input['doctor_specialization'] ?? $input['specialization'] ?? 'General'),
                        'license_no' => $input['doctor_license_no'] ?? null,
                        'consultation_fee' => $input['doctor_consultation_fee'] ?? null,
                        // 'status' column may not exist depending on schema; omit
                    ]);
                    break;
                case 'admin':
                    // Create admin record with derived credentials if not provided
                    $username = $input['username'] ?? ($input['email'] ?? $input['employee_id'] ?? ('admin_' . $staffId));
                    $rawPassword = $input['password'] ?? ($input['employee_id'] ?? ('Adm' . $staffId . '!'));
                    $passwordHash = password_hash($rawPassword, PASSWORD_DEFAULT);
                    $this->db->table('admin')->insert([
                        'staff_id' => $staffId,
                        'username' => $username,
                        'password' => $passwordHash,
                    ]);
                    break;
                case 'nurse':
                    $this->db->table('nurse')->insert([
                        'staff_id' => $staffId,
                        'license_no' => $input['nurse_license_no'] ?? null,
                        'specialization' => $input['nurse_specialization'] ?? null,
                    ]);
                    break;
                case 'pharmacist':
                    $this->db->table('pharmacist')->insert([
                        'staff_id' => $staffId,
                        'license_no' => $input['pharmacist_license_no'] ?? null,
                        'specialization' => $input['pharmacist_specialization'] ?? null,
                    ]);
                    break;
                case 'laboratorist':
                    $this->db->table('laboratorist')->insert([
                        'staff_id' => $staffId,
                        'license_no' => $input['laboratorist_license_no'],
                        'specialization' => $input['laboratorist_specialization'] ?? null,
                        'lab_room_no' => $input['lab_room_no'] ?? null,
                    ]);
                    break;
                case 'accountant':
                    $this->db->table('accountant')->insert([
                        'staff_id' => $staffId,
                        'license_no' => $input['accountant_license_no'] ?? null,
                    ]);
                    break;
                case 'receptionist':
                    $this->db->table('receptionist')->insert([
                        'staff_id' => $staffId,
                        'desk_no' => $input['receptionist_desk_no'] ?? null,
                    ]);
                    break;
                case 'it_staff':
                    $this->db->table('it_staff')->insert([
                        'staff_id' => $staffId,
                        'expertise' => $input['it_expertise'] ?? null,
                    ]);
                    break;
            }
        } catch (\Throwable $e) {
            log_message('warning', 'Role-specific insert skipped for staff_id ' . $staffId . ': ' . $e->getMessage());
            // Do not throw; keep staff creation successful
        }
    }

    private function deleteRoleSpecificData($role, $staffId)
    {
        $tables = [
            'doctor' => 'doctor',
            'nurse' => 'nurse',
            'pharmacist' => 'pharmacist',
            'laboratorist' => 'laboratorist',
            'accountant' => 'accountant',
            'receptionist' => 'receptionist',
            'it_staff' => 'it_staff',
        ];

        if (isset($tables[$role])) {
            $this->db->table($tables[$role])
                ->where('staff_id', $staffId)
                ->delete();
        }
    }

    private function ensureDepartmentExists($name)
    {
        $dept = trim((string)$name);
        if ($dept === '') {
            return;
        }
        $exists = $this->db->table('department')->where('name', $dept)->get()->getRowArray();
        if (!$exists) {
            $this->db->table('department')->insert([
                'name' => $dept,
            ]);
        }
    }

    /**
     * Get list of columns for a table, with in-memory cache
     */
    private function getTableColumns(string $table): array
    {
        if (isset($this->tableColumnsCache[$table])) {
            return $this->tableColumnsCache[$table];
        }

        $columns = [];
        try {
            if (method_exists($this->db, 'getFieldNames')) {
                $columns = $this->db->getFieldNames($table) ?? [];
            }
            if (empty($columns) && method_exists($this->db, 'getFieldData')) {
                $fields = $this->db->getFieldData($table) ?? [];
                foreach ($fields as $f) {
                    if (is_object($f) && isset($f->name)) {
                        $columns[] = $f->name;
                    }
                }
            }
        } catch (\Throwable $e) {
            // If metadata lookup fails, default to no columns
            $columns = [];
        }

        return $this->tableColumnsCache[$table] = $columns;
    }

    /**
     * Filter associative array to keys that match actual table columns
     */
    private function filterToExistingColumns(string $table, array $data): array
    {
        $columns = $this->getTableColumns($table);
        if (empty($columns)) {
            return $data; // If we cannot detect columns, fail open to avoid dropping data silently
        }
        return array_intersect_key($data, array_flip($columns));
    }
}
