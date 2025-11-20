<?php

namespace App\Controllers;

use App\Services\ResourceService;
use App\Libraries\PermissionManager;

class ResourceManagement extends BaseController
{
    protected $resourceService;
    protected $permissionManager;

    public function __construct()
    {
        $this->resourceService = new ResourceService();
        $this->permissionManager = new PermissionManager();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userRole = session()->get('role');
        $staffId = session()->get('staff_id');

        if (!$this->permissionManager->hasPermission($userRole, 'resources', 'view')) {
            return redirect()->to($this->getRedirectUrl($userRole))->with('error', 'Access denied');
        }

        $stats = $this->resourceService->getResourceStats($userRole, $staffId);
        $categories = $this->resourceService->getCategories($userRole);
        $staff = $this->resourceService->getStaffForAssignment();
        $resources = $this->resourceService->getResources($userRole, $staffId);

        $data = [
            'title' => $this->getPageTitle($userRole),
            'userRole' => $userRole,
            'permissions' => $this->permissionManager->getRolePermissions($userRole),
            'stats' => $stats,
            'categories' => $categories,
            'staff' => $staff,
            'resources' => $resources,
            'redirectUrl' => $this->getRedirectUrl($userRole)
        ];

        return view('unified/resource-management', $data);
    }

    public function getResourcesAPI()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Not authenticated']);
        }

        $userRole = session()->get('role');
        $staffId = session()->get('staff_id');

        $filters = [
            'category' => $this->request->getGet('category'),
            'status' => $this->request->getGet('status'),
            'location' => $this->request->getGet('location'),
            'search' => $this->request->getGet('search')
        ];

        $resources = $this->resourceService->getResources($userRole, $staffId, $filters);
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $resources
        ]);
    }

    public function create()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Not authenticated']);
        }

        $userRole = session()->get('role');
        $staffId = session()->get('staff_id');

        // Use standard POST data; avoid getJSON(true) to prevent JSON parse errors
        $data = $this->request->getPost();
        
        $result = $this->resourceService->createResource($data, $userRole, $staffId);
        
        return $this->response->setJSON($result);
    }

    public function update()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Not authenticated']);
        }

        $userRole = session()->get('role');
        $staffId = session()->get('staff_id');

        // Use standard POST data; avoid getJSON(true) to prevent JSON parse errors
        $data = $this->request->getPost();
        $resourceId = $data['id'] ?? null;

        if (!$resourceId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Resource ID is required']);
        }

        $result = $this->resourceService->updateResource($resourceId, $data, $userRole, $staffId);
        
        return $this->response->setJSON($result);
    }

    public function delete($resourceId = null)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Not authenticated']);
        }

        $userRole = session()->get('role');
        $staffId = session()->get('staff_id');

        if (!$resourceId) {
            // Use standard POST data; avoid getJSON(true) to prevent JSON parse errors
            $data = $this->request->getPost();
            $resourceId = $data['id'] ?? null;
        }

        if (!$resourceId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Resource ID is required']);
        }

        $result = $this->resourceService->deleteResource($resourceId, $userRole, $staffId);
        
        return $this->response->setJSON($result);
    }

    private function getPageTitle($role)
    {
        switch ($role) {
            case 'admin':
                return 'Resource Management';
            case 'doctor':
            case 'nurse':
                return 'Medical Resources';
            case 'pharmacist':
                return 'Pharmacy Resources';
            case 'laboratorist':
                return 'Lab Resources';
            case 'receptionist':
                return 'Office Resources';
            case 'it_staff':
                return 'IT Resource Management';
            default:
                return 'Resources';
        }
    }

    private function getRedirectUrl($role)
    {
        return match($role) {
            'admin' => '/admin/dashboard',
            'doctor' => '/doctor/dashboard',
            'nurse' => '/nurse/dashboard',
            'pharmacist' => '/pharmacist/dashboard',
            'laboratorist' => '/laboratorist/dashboard',
            'receptionist' => '/receptionist/dashboard',
            'it_staff' => '/it-staff/dashboard',
            default => '/login'
        };
    }

    public function add()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $userRole = session()->get('role');
        
        if (!$this->permissionManager->hasPermission($userRole, 'resources', 'add')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied']);
        }

        $validation = \Config\Services::validation();
        
        $rules = [
            'equipment_name' => 'required|min_length[2]|max_length[255]',
            'category' => 'required|in_list[Medical Equipment,Medical Supplies,Diagnostic Equipment,Lab Equipment,Pharmacy Equipment,Medications,Office Equipment,IT Equipment,Furniture,Vehicles,Other]',
            'quantity' => 'required|integer|greater_than[0]',
            'status' => 'required|in_list[Available,In Use,Maintenance,Out of Order]',
            'location' => 'required|min_length[2]|max_length[255]',
            'date_acquired' => 'required|valid_date[Y-m-d]',
            'supplier' => 'permit_empty|max_length[255]',
            'maintenance_schedule' => 'permit_empty|valid_date[Y-m-d]',
            'remarks' => 'permit_empty|max_length[1000]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'errors' => $this->validator->getErrors()
            ]);
        }

        try {
            $db = \Config\Database::connect();
            
            $data = [
                'equipment_name' => $this->request->getPost('equipment_name'),
                'category' => $this->request->getPost('category'),
                'quantity' => $this->request->getPost('quantity'),
                'status' => $this->request->getPost('status'),
                'location' => $this->request->getPost('location'),
                'date_acquired' => $this->request->getPost('date_acquired'),
                'supplier' => $this->request->getPost('supplier'),
                'maintenance_schedule' => $this->request->getPost('maintenance_schedule'),
                'remarks' => $this->request->getPost('remarks')
            ];

            $result = $db->table('resources')->insert($data);
            
            if ($result) {
                log_message('info', 'Resource added: ' . $data['equipment_name'] . ' by ' . session()->get('username'));
                
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Resource added successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Failed to add resource'
                ]);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Add resource error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Database error occurred'
            ]);
        }
    }
}