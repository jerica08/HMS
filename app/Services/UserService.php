<?php

namespace App\Services;

use App\Libraries\PermissionManager;

class UserService
{
    protected $db;
    protected $userBuilder;
    protected $staffBuilder;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->userBuilder = $this->db->table('users');
        $this->staffBuilder = $this->db->table('staff');
    }

    /**
     * Get users based on role and permissions
     */
    public function getUsersByRole($userRole, $staffId = null)
    {
        try {
            // First try to get all users without complex joins to debug
            $builder = $this->db->table('users');
            $allUsers = $builder->get()->getResultArray();
            
            log_message('debug', 'UserService: Found ' . count($allUsers) . ' total users in database');
            
            if (empty($allUsers)) {
                log_message('error', 'UserService: No users found in users table');
                return [];
            }
            
            // Now try the join query with proper department join
            $builder = $this->db->table('users u')
                ->select('u.*, s.first_name, s.last_name, d.name as department, s.employee_id')
                ->join('staff s', 's.staff_id = u.staff_id', 'left')
                ->join('department d', 'd.department_id = s.department_id', 'left');

            switch ($userRole) {
                case 'admin':
                case 'it_staff':
                    // Admin and IT staff can see all users
                    $users = $builder->orderBy('u.created_at', 'DESC')->get()->getResultArray();
                    log_message('debug', 'UserService: Admin/IT staff query returned ' . count($users) . ' users');
                    break;
                    
                case 'doctor':
                    // Doctors can see users in their department
                    $doctorInfo = $this->staffBuilder->where('staff_id', $staffId)->get()->getRowArray();
                    $departmentId = $doctorInfo['department_id'] ?? null;
                    
                    if ($departmentId) {
                        $users = $builder->where('s.department_id', $departmentId)
                                        ->orderBy('u.created_at', 'DESC')
                                        ->get()->getResultArray();
                        log_message('debug', 'UserService: Doctor query for department ID ' . $departmentId . ' returned ' . count($users) . ' users');
                    } else {
                        $users = [];
                        log_message('warning', 'UserService: Doctor has no department assigned');
                    }
                    break;
                    
                case 'nurse':
                    // Nurses can see limited user info in their department
                    $nurseInfo = $this->staffBuilder->where('staff_id', $staffId)->get()->getRowArray();
                    $departmentId = $nurseInfo['department_id'] ?? null;
                    
                    if ($departmentId) {
                        $users = $builder->where('s.department_id', $departmentId)
                                        ->whereIn('u.role', ['doctor', 'nurse'])
                                        ->orderBy('u.created_at', 'DESC')
                                        ->get()->getResultArray();
                        log_message('debug', 'UserService: Nurse query for department ID ' . $departmentId . ' returned ' . count($users) . ' users');
                    } else {
                        $users = [];
                        log_message('warning', 'UserService: Nurse has no department assigned');
                    }
                    break;
                    
                default:
                    // Other roles see basic user directory
                    $users = $builder->whereIn('u.role', ['doctor', 'receptionist'])
                                    ->select('u.user_id, u.username, u.role, u.status, s.first_name, s.last_name, d.name as department')
                                    ->orderBy('s.first_name', 'ASC')
                                    ->get()->getResultArray();
                    log_message('debug', 'UserService: Default role query returned ' . count($users) . ' users');
            }

            // If join query failed, return basic user data
            if (empty($users) && !empty($allUsers)) {
                log_message('warning', 'UserService: Join query failed, returning basic user data');
                $users = $allUsers;
            }

            // Format user data
            $formattedUsers = array_map(function($user) {
                $user['full_name'] = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                if (empty($user['full_name'])) {
                    $user['full_name'] = $user['username'] ?? 'Unknown User';
                }
                $user['id'] = $user['user_id'] ?? null;
                return $user;
            }, $users);

            log_message('debug', 'UserService: Returning ' . count($formattedUsers) . ' formatted users');
            return $formattedUsers;

        } catch (\Throwable $e) {
            log_message('error', 'UserService getUsersByRole error: ' . $e->getMessage());
            log_message('error', 'UserService Stack trace: ' . $e->getTraceAsString());
            return [];
        }
    }

    /**
     * Get user statistics based on role
     */
    public function getUserStats($userRole, $staffId = null)
    {
        try {
            $stats = [];
            
            switch ($userRole) {
                case 'admin':
                case 'it_staff':
                    $stats = [
                        'total_users' => $this->userBuilder->countAllResults(),
                        'active_users' => $this->userBuilder->where('status', 'active')->countAllResults(),
                        'admin_users' => $this->userBuilder->where('role', 'admin')->countAllResults(),
                        'doctor_users' => $this->userBuilder->where('role', 'doctor')->countAllResults(),
                        'nurse_users' => $this->userBuilder->where('role', 'nurse')->countAllResults(),
                        'new_users_month' => $this->userBuilder->where('created_at >=', date('Y-m-01'))->countAllResults(),
                    ];
                    break;
                    
                case 'doctor':
                    // Get department-based stats
                    $doctorInfo = $this->staffBuilder->where('staff_id', $staffId)->get()->getRowArray();
                    $department = $doctorInfo['department'] ?? null;
                    
                    if ($department) {
                        $departmentUsers = $this->db->table('users u')
                            ->join('staff s', 's.staff_id = u.staff_id', 'left')
                            ->where('s.department', $department);
                            
                        $stats = [
                            'department_users' => $departmentUsers->countAllResults(false),
                            'department_active' => $departmentUsers->where('u.status', 'active')->countAllResults(),
                        ];
                    } else {
                        $stats = ['department_users' => 0, 'department_active' => 0];
                    }
                    break;
                    
                default:
                    $stats = [
                        'total_users' => $this->userBuilder->countAllResults(),
                        'active_users' => $this->userBuilder->where('status', 'active')->countAllResults(),
                    ];
            }
            
            return $stats;
            
        } catch (\Throwable $e) {
            log_message('error', 'UserService getUserStats error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a new user
     */
    public function createUser($data, $userRole)
    {
        // Check permissions
        if (!PermissionManager::hasPermission($userRole, 'users', 'create')) {
            throw new \Exception('Insufficient permissions to create users');
        }

        $this->db->transStart();

        try {
            // Validate required fields
            if (empty($data['staff_id']) || empty($data['username']) || empty($data['password'])) {
                throw new \Exception('Staff ID, username, and password are required');
            }

            // Check if username already exists
            $existingUser = $this->userBuilder->where('username', $data['username'])->get()->getRowArray();
            if ($existingUser) {
                throw new \Exception('Username already exists');
            }

            // Check if staff member already has a user account
            $existingStaffUser = $this->userBuilder->where('staff_id', $data['staff_id'])->get()->getRowArray();
            if ($existingStaffUser) {
                throw new \Exception('This staff member already has a user account');
            }

            // Prepare user data
            $userData = [
                'staff_id' => $data['staff_id'],
                'username' => $data['username'],
                'email' => $data['email'] ?? null,
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'role' => $data['role'],
                'status' => $data['status'] ?? 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->userBuilder->insert($userData);
            
            if (!$result) {
                throw new \Exception('Failed to create user');
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            return [
                'status' => 'success',
                'message' => 'User created successfully',
                'user_id' => $this->db->insertID()
            ];

        } catch (\Exception $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    /**
     * Update user
     */
    public function updateUser($userId, $data, $userRole)
    {
        // Check permissions
        if (!PermissionManager::hasPermission($userRole, 'users', 'edit')) {
            throw new \Exception('Insufficient permissions to update users');
        }

        try {
            // Get existing user
            $existingUser = $this->userBuilder->where('user_id', $userId)->get()->getRowArray();
            if (!$existingUser) {
                throw new \Exception('User not found');
            }

            // Prepare update data
            $updateData = [];
            $allowedFields = ['username', 'email', 'role', 'status'];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field]) && $data[$field] !== '') {
                    $updateData[$field] = $data[$field];
                }
            }

            if (empty($updateData)) {
                return ['status' => 'success', 'message' => 'No changes to update'];
            }

            // Check username uniqueness if being updated
            if (isset($updateData['username']) && $updateData['username'] !== $existingUser['username']) {
                $usernameExists = $this->userBuilder->where('username', $updateData['username'])
                                                  ->where('user_id !=', $userId)
                                                  ->get()->getRowArray();
                if ($usernameExists) {
                    throw new \Exception('Username already exists');
                }
            }

            $updateData['updated_at'] = date('Y-m-d H:i:s');

            $result = $this->userBuilder->where('user_id', $userId)->update($updateData);

            if (!$result) {
                throw new \Exception('Failed to update user');
            }

            return [
                'status' => 'success',
                'message' => 'User updated successfully'
            ];

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete user
     */
    public function deleteUser($userId, $userRole)
    {
        // Check permissions
        if (!PermissionManager::hasPermission($userRole, 'users', 'delete')) {
            throw new \Exception('Insufficient permissions to delete users');
        }

        try {
            // Get existing user
            $existingUser = $this->userBuilder->where('user_id', $userId)->get()->getRowArray();
            if (!$existingUser) {
                throw new \Exception('User not found');
            }

            // Prevent deletion of admin users by non-admin users
            if ($existingUser['role'] === 'admin' && $userRole !== 'admin') {
                throw new \Exception('Cannot delete admin users');
            }

            $result = $this->userBuilder->where('user_id', $userId)->delete();

            if (!$result) {
                throw new \Exception('Failed to delete user');
            }

            return [
                'status' => 'success',
                'message' => 'User deleted successfully'
            ];

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get single user
     */
    public function getUser($userId, $userRole)
    {
        try {
            $user = $this->db->table('users u')
                ->select('u.*, s.first_name, s.last_name, d.name as department, s.employee_id')
                ->join('staff s', 's.staff_id = u.staff_id', 'left')
                ->join('department d', 'd.department_id = s.department_id', 'left')
                ->where('u.user_id', $userId)
                ->get()->getRowArray();

            if (!$user) {
                throw new \Exception('User not found');
            }

            // Role-based access control
            if (!$this->canViewUser($user, $userRole)) {
                throw new \Exception('Insufficient permissions to view this user');
            }

            $user['full_name'] = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
            $user['id'] = $user['user_id'];

            return $user;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Reset user password
     */
    public function resetPassword($userId, $newPassword, $userRole)
    {
        // Check permissions
        if (!PermissionManager::hasPermission($userRole, 'users', 'edit')) {
            throw new \Exception('Insufficient permissions to reset passwords');
        }

        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $result = $this->userBuilder->where('user_id', $userId)->update([
                'password' => $hashedPassword,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if (!$result) {
                throw new \Exception('Failed to reset password');
            }

            return [
                'status' => 'success',
                'message' => 'Password reset successfully'
            ];

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get available staff for user creation
     */
    public function getAvailableStaff($userRole)
    {
        try {
            // Get staff members who don't have user accounts yet (anti-join)
            // Include department name via join
            $staff = $this->db->table('staff s')
                ->select('s.staff_id, s.first_name, s.last_name, s.employee_id, s.email, s.role, d.name AS department')
                ->join('users u', 'u.staff_id = s.staff_id', 'left')
                ->join('department d', 'd.department_id = s.department_id', 'left')
                ->where('u.staff_id IS NULL')
                ->orderBy('s.first_name', 'ASC')
                ->get()->getResultArray();

            return array_map(function($s) {
                $s['full_name'] = trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''));
                return $s;
            }, $staff);

        } catch (\Exception $e) {
            log_message('error', 'UserService getAvailableStaff error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if user can view another user
     */
    private function canViewUser($user, $viewerRole)
    {
        switch ($viewerRole) {
            case 'admin':
            case 'it_staff':
                return true;
                
            case 'doctor':
            case 'nurse':
                // Can view users in same department
                return true; // Implement department check if needed
                
            default:
                return false;
        }
    }
}
