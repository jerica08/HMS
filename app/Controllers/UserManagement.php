<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\UserService;
use App\Libraries\PermissionManager;

class UserManagement extends BaseController
{
    protected $userService;

    public function __construct()
    {
        $this->userService = new UserService();
        // Authentication is now handled by the roleauth filter in routes
    }

    /**
     * Main unified user management - Role-based
     */
    public function index()
    {
        $session = session();
        $userRole = $session->get('role');
        $staffId = $session->get('staff_id');
        
        // Temporary: Set default role if not logged in
        if (!$userRole) {
            $userRole = 'admin';
            $staffId = null;
            log_message('debug', 'UserManagement: No session role found, using admin for testing');
        }
        
        log_message('debug', 'UserManagement: User role = ' . $userRole . ', Staff ID = ' . $staffId);
        
        // Get user data based on role permissions
        $users = $this->userService->getUsersByRole($userRole, $staffId);
        $stats = $this->userService->getUserStats($userRole, $staffId);
        $availableStaff = $this->userService->getAvailableStaff($userRole);
        
        log_message('debug', 'UserManagement: Found ' . count($users) . ' users to display');
        
        // Debug: Log first user if available
        if (!empty($users)) {
            log_message('debug', 'UserManagement: First user data = ' . json_encode($users[0]));
        }
        
        $data = [
            'title' => $this->getPageTitle($userRole),
            'userRole' => $userRole,
            'users' => $users,
            'userStats' => $stats,
            'availableStaff' => $availableStaff,
            'permissions' => $this->getUserPermissions($userRole),
        ];

        // Use unified view that adapts to user role
        return view('unified/user-management', $data);
    }

    public function create()
    {
        $session = session();
        $userRole = $session->get('role');
        
        $isAjax = $this->request->isAJAX() || 
                  $this->request->getHeaderLine('Accept') == 'application/json' ||
                  $this->request->getHeaderLine('X-Requested-With') == 'XMLHttpRequest';

        try {
            $data = $this->request->getPost() ?: $this->request->getJSON(true) ?? [];
            
            $result = $this->userService->createUser($data, $userRole);
            
            if ($isAjax) {
                return $this->response->setJSON($result);
            }
            
            session()->setFlashdata('success', $result['message']);
            return redirect()->to($this->getRedirectUrl($userRole));
            
        } catch (\Exception $e) {
            if ($isAjax) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);
            }
            
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function update($userId = null)
    {
        $session = session();
        $userRole = $session->get('role');
        
        $userId = $userId ?? $this->request->getPost('user_id');
        
        $isAjax = $this->request->isAJAX() || 
                  $this->request->getHeaderLine('Accept') == 'application/json' ||
                  $this->request->getHeaderLine('X-Requested-With') == 'XMLHttpRequest';

        try {
            $data = $this->request->getPost() ?: $this->request->getJSON(true) ?? [];
            
            $result = $this->userService->updateUser($userId, $data, $userRole);
            
            if ($isAjax) {
                return $this->response->setJSON($result);
            }
            
            session()->setFlashdata('success', $result['message']);
            return redirect()->to($this->getRedirectUrl($userRole));
            
        } catch (\Exception $e) {
            if ($isAjax) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);
            }
            
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function delete($userId = null)
    {
        $session = session();
        $userRole = $session->get('role');
        
        $isAjax = $this->request->isAJAX() || 
                  $this->request->getHeaderLine('Accept') == 'application/json' ||
                  $this->request->getHeaderLine('X-Requested-With') == 'XMLHttpRequest';
        
        try {
            $result = $this->userService->deleteUser($userId, $userRole);
            
            if ($isAjax) {
                return $this->response->setJSON($result);
            }
            
            session()->setFlashdata('success', $result['message']);
            return redirect()->to($this->getRedirectUrl($userRole));
            
        } catch (\Exception $e) {
            if ($isAjax) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);
            }
            
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to($this->getRedirectUrl($userRole));
        }
    }

    public function resetPassword($userId = null)
    {
        $session = session();
        $userRole = $session->get('role');
        
        try {
            // Generate temporary password
            $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%^*';
            $tempPassword = '';
            for ($i = 0; $i < 12; $i++) {
                $tempPassword .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            
            $result = $this->userService->resetPassword($userId, $tempPassword, $userRole);
            
            session()->setFlashdata('success', $result['message'] . ' Temporary password: ' . $tempPassword);
            return redirect()->to($this->getRedirectUrl($userRole));
            
        } catch (\Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to($this->getRedirectUrl($userRole));
        }
    }

    // API Methods
    public function getUsersAPI()
    {
        $session = session();
        $userRole = $session->get('role');
        $staffId = $session->get('staff_id');
        
        try {
            $users = $this->userService->getUsersByRole($userRole, $staffId);
            return $this->response->setJSON(['status' => 'success', 'data' => $users]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    public function getUser($userId = null)
    {
        $session = session();
        $userRole = $session->get('role');
        
        try {
            $user = $this->userService->getUser($userId, $userRole);
            return $this->response->setJSON(['status' => 'success', 'data' => $user]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    public function getAvailableStaffAPI()
    {
        $session = session();
        $userRole = $session->get('role');
        
        try {
            $staff = $this->userService->getAvailableStaff($userRole);
            return $this->response->setJSON(['status' => 'success', 'data' => $staff]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // Helper Methods
    private function getPageTitle($userRole)
    {
        switch ($userRole) {
            case 'admin':
                return 'User Management';
            case 'it_staff':
                return 'System User Management';
            case 'doctor':
                return 'Department Users';
            default:
                return 'User Directory';
        }
    }
    
    private function getRedirectUrl($userRole)
    {
        switch ($userRole) {
            case 'admin':
                return 'admin/user-management';
            case 'it_staff':
                return 'it-staff/users';
            case 'doctor':
                return 'doctor/users';
            default:
                return 'admin/user-management';
        }
    }
    
    private function getUserPermissions($userRole)
    {
        return [
            'canCreate' => PermissionManager::hasPermission($userRole, 'users', 'create'),
            'canEdit' => PermissionManager::hasPermission($userRole, 'users', 'edit'),
            'canDelete' => PermissionManager::hasPermission($userRole, 'users', 'delete'),
            'canView' => PermissionManager::hasPermission($userRole, 'users', 'view'),
            'canResetPassword' => PermissionManager::hasPermission($userRole, 'users', 'edit'),
            'canViewAll' => in_array($userRole, ['admin', 'it_staff']),
        ];
    }
}