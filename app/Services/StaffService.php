<?php

namespace App\Services;

class StaffService
{
    protected $db;

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
                         i.expertise as it_expertise')
                ->join('doctor d', 'd.staff_id = s.staff_id', 'left')
                ->join('nurse n', 'n.staff_id = s.staff_id', 'left')
                ->join('pharmacist p', 'p.staff_id = s.staff_id', 'left')
                ->join('laboratorist l', 'l.staff_id = s.staff_id', 'left')
                ->join('accountant a', 'a.staff_id = s.staff_id', 'left')
                ->join('receptionist r', 'r.staff_id = s.staff_id', 'left')
                ->join('it_staff i', 'i.staff_id = s.staff_id', 'left');

            // Role-based filtering
            switch ($userRole) {
                case 'admin':
                case 'it_staff':
                    // Admin and IT staff can see all staff
                    break;
                case 'doctor':
                    // Doctors can see staff in their department and nurses
                    $doctorInfo = $this->db->table('staff')->where('staff_id', $staffId)->get()->getRowArray();
                    if ($doctorInfo && !empty($doctorInfo['department'])) {
                        $builder->where('s.department', $doctorInfo['department']);
                    }
                    break;
                case 'nurse':
                    // Nurses can see staff in their department
                    $nurseInfo = $this->db->table('staff')->where('staff_id', $staffId)->get()->getRowArray();
                    if ($nurseInfo && !empty($nurseInfo['department'])) {
                        $builder->where('s.department', $nurseInfo['department']);
                    }
                    break;
                case 'receptionist':
                    // Receptionists can see doctors and nurses for scheduling
                    $builder->whereIn('s.role', ['doctor', 'nurse']);
                    break;
                default:
                    // Other roles see limited staff
                    $builder->where('s.staff_id', $staffId);
                    break;
            }

            // Apply additional filters
            if (isset($filters['department'])) {
                $builder->where('s.department', $filters['department']);
            }
            
            if (isset($filters['role'])) {
                $builder->where('s.role', $filters['role']);
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
                    ->orLike('s.department', $search)
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
                         i.expertise as it_expertise')
                ->join('doctor d', 'd.staff_id = s.staff_id', 'left')
                ->join('nurse n', 'n.staff_id = s.staff_id', 'left')
                ->join('pharmacist p', 'p.staff_id = s.staff_id', 'left')
                ->join('laboratorist l', 'l.staff_id = s.staff_id', 'left')
                ->join('accountant a', 'a.staff_id = s.staff_id', 'left')
                ->join('receptionist r', 'r.staff_id = s.staff_id', 'left')
                ->join('it_staff i', 'i.staff_id = s.staff_id', 'left')
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

        // Validation
        $validation = \Config\Services::validation();
        $validation->setRules($this->getValidationRules($input['designation'] ?? 'staff'));

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
            $this->db->transStart();

            // Prepare staff data
            $staffData = $this->prepareStaffData($input);
            
            // Insert staff record
            $this->db->table('staff')->insert($staffData);
            $staffId = $this->db->insertID();

            // Insert role-specific data
            $this->insertRoleSpecificData($input['designation'], $staffId, $input);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return [
                    'success' => false,
                    'message' => 'Failed to create staff member',
                ];
            }

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

        // Validation
        $validation = \Config\Services::validation();
        $validation->setRules($this->getUpdateValidationRules($input['designation'] ?? 'staff', $id));

        if (!$validation->run($input)) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors(),
            ];
        }

        try {
            // Prepare update data
            $staffData = $this->prepareStaffData($input);
            $staffData['updated_at'] = date('Y-m-d H:i:s');

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
            'designation' => 'required|in_list[admin,doctor,nurse,pharmacist,receptionist,laboratorist,accountant,it_staff]',
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
        $rules['employee_id'] = 'permit_empty|min_length[3]|max_length[255]|is_unique[staff.employee_id,staff_id,' . $excludeId . ']';
        $rules['designation'] = 'permit_empty|in_list[admin,doctor,nurse,pharmacist,receptionist,laboratorist,accountant,it_staff]';
        return $rules;
    }

    private function prepareStaffData($input)
    {
        return [
            'employee_id' => $input['employee_id'],
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'] ?? null,
            'gender' => isset($input['gender']) ? strtolower($input['gender']) : null,
            'dob' => $input['dob'] ?? null,
            'contact_no' => $input['contact_no'] ?? null,
            'email' => $input['email'] ?? null,
            'address' => $input['address'] ?? null,
            'department' => $input['department'] ?? null,
            'designation' => $input['designation'],
            'role' => $input['designation'],
            'date_joined' => $input['date_joined'] ?? date('Y-m-d'),
            'status' => $input['status'] ?? 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }

    private function insertRoleSpecificData($role, $staffId, $input)
    {
        switch ($role) {
            case 'doctor':
                $this->db->table('doctor')->insert([
                    'staff_id' => $staffId,
                    'specialization' => $input['doctor_specialization'] ?? null,
                    'license_no' => $input['doctor_license_no'] ?? null,
                    'consultation_fee' => $input['doctor_consultation_fee'] ?? null,
                    'status' => 'Active',
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
                    'license_no' => $input['laboratorist_license_no'] ?? null,
                    'specialization' => $input['laboratorist_specialization'] ?? null,
                    'lab_room_no' => $input['laboratorist_lab_room_no'] ?? null,
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
}
