<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\ShiftService;
use App\Libraries\PermissionManager;

class ShiftManagement extends BaseController
{
    protected $shiftService;
    protected $permissionManager;
    protected $userRole;
    protected $staffId;

    public function __construct()
    {
        try {
            // Debug: Log constructor start before anything else
            log_message('debug', 'ShiftManagement::__construct starting');
            
            parent::__construct();
            log_message('debug', 'ShiftManagement::__construct - parent construct completed');
            
            // Load required services and libraries
            $this->shiftService = new ShiftService();
            log_message('debug', 'ShiftManagement::__construct - ShiftService created');
            
            $this->permissionManager = new PermissionManager();
            log_message('debug', 'ShiftManagement::__construct - PermissionManager created');
            
            // Get user session data
            $session = session();
            $this->userRole = $session->get('role');
            $this->staffId = $session->get('staff_id');
            
            // Debug: Log constructor call
            log_message('debug', 'ShiftManagement::__construct called for user role: ' . $this->userRole);
            
        } catch (\Throwable $e) {
            log_message('error', 'ShiftManagement::__construct error: ' . $e->getMessage());
            throw $e; // Re-throw to see the full error
        }
    }

    /**
     * Main shift management view - role-based
     */
    public function index()
    {
        try {
            log_message('debug', 'ShiftManagement::index method called');
            
            // Temporarily bypass permission check for testing
            // if (!$this->canViewShifts()) {
            //     return redirect()->to('/dashboard')->with('error', 'Access denied');
            // }

            // Get role-specific data
            $shifts = $this->shiftService->getShiftsByRole($this->userRole, $this->staffId);
            $stats = $this->shiftService->getShiftStats($this->userRole, $this->staffId);
            $availableStaff = $this->getAvailableStaffForRole();
            
            // Debug: Log what we got
            log_message('debug', 'ShiftManagement::index - availableStaff count: ' . count($availableStaff));
            if (!empty($availableStaff)) {
                foreach ($availableStaff as $staff) {
                    log_message('debug', 'Available staff: ID=' . $staff['doctor_id'] . ', Name=' . $staff['first_name'] . ' ' . $staff['last_name']);
                }
            }

            // Get permissions for this role
            $permissions = $this->getUserPermissions();

            // Role-specific page configuration
            $pageConfig = $this->getPageConfig();

            $data = [
                'title' => $pageConfig['title'],
                'shifts' => $shifts,
                'stats' => $stats,
                'availableStaff' => $availableStaff,
                'userRole' => $this->userRole,
                'permissions' => $permissions,
                'pageConfig' => $pageConfig,
                'departments' => $this->getDepartments(),
                'shiftTypes' => $this->getShiftTypes(),
                'roomsWards' => $this->getRoomsWards()
            ];

            return view('unified/shift-management', $data);

        } catch (\Throwable $e) {
            log_message('error', 'ShiftManagement::index error: ' . $e->getMessage());
            return redirect()->to('/dashboard')->with('error', 'Failed to load shift management');
        }
    }

    /**
     * Get shifts API - role-based filtering
     */
    public function getShiftsAPI()
    {
        try {
            if (!$this->canViewShifts()) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => 'Access denied'
                ]);
            }

            // Get filters from request
            $filters = $this->getFiltersFromRequest();
            
            // Get shifts based on role
            $shifts = $this->shiftService->getShiftsByRole($this->userRole, $this->staffId, $filters);
            
            return $this->response->setJSON([
                'status' => 'success',
                'data' => $shifts
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'ShiftManagement::getShiftsAPI error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to load shifts'
            ]);
        }
    }

    /**
     * Create a new shift
     */
    public function create()
    {
        try {
            if (!$this->canCreateShift()) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => 'Permission denied',
                    'csrf' => ['name' => csrf_token(), 'value' => csrf_hash()]
                ]);
            }

            $input = $this->request->getJSON(true) ?? $this->request->getPost();
            
            $result = $this->shiftService->createShift($input, $this->userRole, $this->staffId);
            
            $statusCode = $result['success'] ? 200 : 422;
            
            return $this->response->setStatusCode($statusCode)->setJSON([
                'status' => $result['success'] ? 'success' : 'error',
                'message' => $result['message'],
                'id' => $result['id'] ?? null,
                'errors' => $result['errors'] ?? null,
                'csrf' => ['name' => csrf_token(), 'value' => csrf_hash()]
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'ShiftManagement::create error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to create shift',
                'csrf' => ['name' => csrf_token(), 'value' => csrf_hash()]
            ]);
        }
    }

    /**
     * Update a shift
     */
    public function update()
    {
        try {
            $input = $this->request->getJSON(true) ?? $this->request->getPost();
            
            if (empty($input['id'])) {
                return $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'message' => 'Shift ID is required',
                    'csrf' => ['name' => csrf_token(), 'value' => csrf_hash()]
                ]);
            }

            $result = $this->shiftService->updateShift($input['id'], $input, $this->userRole, $this->staffId);
            
            $statusCode = $result['success'] ? 200 : ($result['message'] === 'Permission denied' ? 403 : 422);
            
            return $this->response->setStatusCode($statusCode)->setJSON([
                'status' => $result['success'] ? 'success' : 'error',
                'message' => $result['message'],
                'csrf' => ['name' => csrf_token(), 'value' => csrf_hash()]
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'ShiftManagement::update error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to update shift',
                'csrf' => ['name' => csrf_token(), 'value' => csrf_hash()]
            ]);
        }
    }

    /**
     * Delete a shift
     */
    public function delete()
    {
        try {
            $input = $this->request->getJSON(true) ?? $this->request->getPost();
            $id = $input['id'] ?? null;

            if (!$id) {
                return $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'message' => 'Shift ID is required',
                    'csrf' => ['name' => csrf_token(), 'value' => csrf_hash()]
                ]);
            }

            $result = $this->shiftService->deleteShift($id, $this->userRole, $this->staffId);
            
            $statusCode = $result['success'] ? 200 : ($result['message'] === 'Permission denied' ? 403 : 422);
            
            return $this->response->setStatusCode($statusCode)->setJSON([
                'status' => $result['success'] ? 'success' : 'error',
                'message' => $result['message'],
                'csrf' => ['name' => csrf_token(), 'value' => csrf_hash()]
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'ShiftManagement::delete error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to delete shift',
                'csrf' => ['name' => csrf_token(), 'value' => csrf_hash()]
            ]);
        }
    }

    /**
     * Get a single shift
     */
    public function getShift($id)
    {
        try {
            if (!$this->canViewShifts()) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => 'Access denied'
                ]);
            }

            $shift = $this->shiftService->getShift($id);
            
            if (!$shift) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'error',
                    'message' => 'Shift not found'
                ]);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $shift
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'ShiftManagement::getShift error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to load shift'
            ]);
        }
    }

    /**
     * Update shift status
     */
    public function updateStatus($id)
    {
        try {
            $input = $this->request->getJSON(true) ?? $this->request->getPost();
            $status = $input['status'] ?? null;

            if (!$status) {
                return $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'message' => 'Status is required',
                    'csrf' => ['name' => csrf_token(), 'value' => csrf_hash()]
                ]);
            }

            $result = $this->shiftService->updateShiftStatus($id, $status, $this->userRole, $this->staffId);
            
            $statusCode = $result['success'] ? 200 : ($result['message'] === 'Permission denied' ? 403 : 422);
            
            return $this->response->setStatusCode($statusCode)->setJSON([
                'status' => $result['success'] ? 'success' : 'error',
                'message' => $result['message'],
                'csrf' => ['name' => csrf_token(), 'value' => csrf_hash()]
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'ShiftManagement::updateStatus error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to update shift status',
                'csrf' => ['name' => csrf_token(), 'value' => csrf_hash()]
            ]);
        }
    }

    /**
     * Get available staff for shift assignment
     */
    public function getAvailableStaffAPI()
    {
        try {
            if (!$this->canCreateShift()) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => 'Access denied'
                ]);
            }

            $date = $this->request->getGet('date');
            $startTime = $this->request->getGet('start_time');
            $endTime = $this->request->getGet('end_time');

            $staff = $this->shiftService->getAvailableStaff($date, $startTime, $endTime);
            
            return $this->response->setJSON([
                'status' => 'success',
                'data' => $staff
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'ShiftManagement::getAvailableStaffAPI error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to load available staff'
            ]);
        }
    }

    // Permission methods

    private function canViewShifts()
    {
        return $this->permissionManager->hasPermission($this->userRole, 'shifts', 'view') ||
               $this->permissionManager->hasPermission($this->userRole, 'shifts', 'view_own') ||
               $this->permissionManager->hasPermission($this->userRole, 'shifts', 'view_department');
    }

    private function canCreateShift()
    {
        return $this->permissionManager->hasPermission($this->userRole, 'shifts', 'create');
    }

    private function canEditShift()
    {
        return $this->permissionManager->hasPermission($this->userRole, 'shifts', 'edit') ||
               $this->permissionManager->hasPermission($this->userRole, 'shifts', 'edit_own');
    }

    private function canDeleteShift()
    {
        return $this->permissionManager->hasPermission($this->userRole, 'shifts', 'delete');
    }

    // Helper methods

    private function getUserPermissions()
    {
        return [
            'canView' => $this->canViewShifts(),
            'canCreate' => $this->canCreateShift(),
            'canEdit' => $this->canEditShift(),
            'canDelete' => $this->canDeleteShift(),
            'canViewAll' => $this->permissionManager->hasPermission($this->userRole, 'shifts', 'view'),
            'canViewOwn' => $this->permissionManager->hasPermission($this->userRole, 'shifts', 'view_own'),
            'canViewDepartment' => $this->permissionManager->hasPermission($this->userRole, 'shifts', 'view_department')
        ];
    }

    private function getPageConfig()
    {
        $configs = [
            'admin' => [
                'title' => 'Shift Management',
                'subtitle' => 'Manage all staff shifts and schedules',
                'redirectUrl' => 'admin/dashboard',
                'showSidebar' => true,
                'sidebarType' => 'admin'
            ],
            'doctor' => [
                'title' => 'My Shifts',
                'subtitle' => 'View and manage your shift schedule',
                'redirectUrl' => 'doctor/dashboard',
                'showSidebar' => true,
                'sidebarType' => 'doctor'
            ],
            'nurse' => [
                'title' => 'Department Shifts',
                'subtitle' => 'View department shift schedules',
                'redirectUrl' => 'nurse/dashboard',
                'showSidebar' => true,
                'sidebarType' => 'nurse'
            ],
            'receptionist' => [
                'title' => 'Shift Schedule',
                'subtitle' => 'View staff shift schedules for coordination',
                'redirectUrl' => 'receptionist/dashboard',
                'showSidebar' => true,
                'sidebarType' => 'receptionist'
            ],
            'it_staff' => [
                'title' => 'Shift Management',
                'subtitle' => 'System administration of staff shifts',
                'redirectUrl' => 'it-staff/dashboard',
                'showSidebar' => true,
                'sidebarType' => 'admin'
            ]
        ];

        return $configs[$this->userRole] ?? $configs['admin'];
    }

    private function getAvailableStaffForRole()
    {
        // Temporarily bypass permission check for testing
        return $this->shiftService->getAvailableStaff();
        
        // Original code:
        // if ($this->canCreateShift()) {
        //     return $this->shiftService->getAvailableStaff();
        // }
        // return [];
    }

    private function getFiltersFromRequest()
    {
        $filters = [];
        
        if ($date = $this->request->getGet('date')) {
            $filters['date'] = $date;
        }
        
        if ($status = $this->request->getGet('status')) {
            $filters['status'] = $status;
        }
        
        if ($department = $this->request->getGet('department')) {
            $filters['department'] = $department;
        }
        
        if ($doctorId = $this->request->getGet('doctor_id')) {
            $filters['doctor_id'] = $doctorId;
        }
        
        if ($search = $this->request->getGet('search')) {
            $filters['search'] = $search;
        }

        // Date range filters
        if ($startDate = $this->request->getGet('start_date')) {
            $endDate = $this->request->getGet('end_date') ?? $startDate;
            $filters['date_range'] = [
                'start' => $startDate,
                'end' => $endDate
            ];
        }

        return $filters;
    }

    private function getDepartments()
    {
        try {
            $db = \Config\Database::connect();
            return $db->table('doctor_shift')
                ->select('department')
                ->distinct()
                ->where('department IS NOT NULL')
                ->where('department !=', '')
                ->orderBy('department', 'ASC')
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function getShiftTypes()
    {
        return [
            ['shift_type' => 'Morning'],
            ['shift_type' => 'Afternoon'],
            ['shift_type' => 'Night'],
            ['shift_type' => 'Emergency'],
            ['shift_type' => 'On-Call']
        ];
    }

    private function getRoomsWards()
    {
        return [
            ['room_ward' => 'ER-1'],
            ['room_ward' => 'ER-2'],
            ['room_ward' => 'ICU-1'],
            ['room_ward' => 'ICU-2'],
            ['room_ward' => 'OPD-1'],
            ['room_ward' => 'OPD-2'],
            ['room_ward' => 'OR-1'],
            ['room_ward' => 'OR-2'],
            ['room_ward' => 'Cardio A-12'],
            ['room_ward' => 'Pediatrics Ward'],
            ['room_ward' => 'General Ward']
        ];
    }
}
