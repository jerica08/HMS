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
        
        // Get expiring and expired medications for notifications
        $expiringMedications = $this->resourceService->getExpiringMedications(30); // 30 days ahead
        $expiredMedications = $this->resourceService->getExpiredMedications();

        $data = [
            'title' => $this->getPageTitle($userRole),
            'userRole' => $userRole,
            'permissions' => $this->permissionManager->getRolePermissions($userRole),
            'stats' => $stats,
            'categories' => $categories,
            'staff' => $staff,
            'resources' => $resources,
            'expiringMedications' => $expiringMedications,
            'expiredMedications' => $expiredMedications,
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

    public function export()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userRole = session()->get('role');
        $staffId = session()->get('staff_id');

        if (!$this->permissionManager->hasPermission($userRole, 'resources', 'view')) {
            return redirect()->to($this->getRedirectUrl($userRole))->with('error', 'Access denied');
        }

        $filters = [
            'category' => $this->request->getGet('category'),
            'status' => $this->request->getGet('status'),
            'location' => $this->request->getGet('location'),
            'search' => $this->request->getGet('search')
        ];

        $resources = $this->resourceService->getResources($userRole, $staffId, $filters);

        // Set headers for CSV download
        $this->response->setHeader('Content-Type', 'text/csv; charset=utf-8');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="resources_export_' . date('Y-m-d') . '.csv"');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setHeader('Expires', '0');

        // Create CSV output
        $output = fopen('php://output', 'w');

        // Add BOM for UTF-8 Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // CSV Headers
        fputcsv($output, [
            'ID',
            'Resource Name',
            'Category',
            'Quantity',
            'Status',
            'Location',
            'Serial Number',
            'Batch Number',
            'Expiry Date',
            'Remarks'
        ]);

        // CSV Data rows
        foreach ($resources as $resource) {
            fputcsv($output, [
                $resource['id'] ?? '',
                $resource['equipment_name'] ?? '',
                $resource['category'] ?? '',
                $resource['quantity'] ?? '',
                $resource['status'] ?? '',
                $resource['location'] ?? '',
                $resource['serial_number'] ?? '',
                $resource['batch_number'] ?? '',
                $resource['expiry_date'] ?? '',
                $resource['remarks'] ?? ''
            ]);
        }

        fclose($output);
        exit;
    }

}