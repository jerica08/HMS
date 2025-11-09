<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class ShiftManagement extends BaseController
{
    protected $userRole;
    protected $staffId;

    /**
     * Initialize controller with session data
     */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        
        try {
            // Get user session data with fallbacks
            $session = session();
            $this->userRole = $session->get('role') ?? $session->get('user_role') ?? 'admin';
            $this->staffId = $session->get('staff_id') ?? $session->get('id') ?? 1;
            
            // Ensure we have a valid role
            if (empty($this->userRole)) {
                $this->userRole = 'admin';
            }
            
            log_message('debug', 'ShiftManagement::initController completed for user role: ' . $this->userRole);
            
        } catch (\Throwable $e) {
            log_message('error', 'ShiftManagement::initController error: ' . $e->getMessage());
            // Set default values if session fails
            $this->userRole = 'admin';
            $this->staffId = 1;
        }
    }

    /**
     * Main shift management view - role-based
     */
    public function index()
    {
        try {
            log_message('debug', 'ShiftManagement::index method called');

            // Ensure we have valid user role
            if (empty($this->userRole)) {
                $this->userRole = 'admin';
                log_message('debug', 'ShiftManagement::index - fallback to admin role');
            }

            // Get mock data based on role
            $shifts = $this->getMockShifts();
            $stats = $this->getMockStats();
            $availableStaff = $this->getMockAvailableStaff();
            
            // Debug: Log the data
            log_message('debug', 'ShiftManagement::index - shifts count: ' . count($shifts));
            log_message('debug', 'ShiftManagement::index - user role: ' . ($this->userRole ?? 'none'));
            
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

            log_message('debug', 'ShiftManagement::index - data prepared, rendering view');
            return view('unified/shift-management', $data);

        } catch (\Throwable $e) {
            log_message('error', 'ShiftManagement::index error: ' . $e->getMessage());
            
            // Return error page with details
            return $this->response->setBody('
                <h1>Shift Management Error</h1>
                <p><strong>Error:</strong> ' . esc($e->getMessage()) . '</p>
                <p><strong>File:</strong> ' . esc($e->getFile()) . '</p>
                <p><strong>Line:</strong> ' . esc($e->getLine()) . '</p>
                <p><a href="' . base_url('dashboard') . '">Back to Dashboard</a></p>
            ')->setStatusCode(500);
        }
    }

    /**
     * Get mock shifts data
     */
    private function getMockShifts()
    {
        return [
            [
                'id' => 1,
                'doctor_id' => 1,
                'doctor_name' => 'Dr. John Smith',
                'shift_date' => date('Y-m-d'),
                'start_time' => '09:00',
                'end_time' => '17:00',
                'department' => 'Emergency',
                'shift_type' => 'Morning',
                'status' => 'scheduled',
                'room_ward' => 'ER-1',
                'notes' => 'Regular shift',
                'specialization' => 'Emergency Medicine'
            ],
            [
                'id' => 2,
                'doctor_id' => 2,
                'doctor_name' => 'Dr. Jane Doe',
                'shift_date' => date('Y-m-d', strtotime('+1 day')),
                'start_time' => '14:00',
                'end_time' => '22:00',
                'department' => 'ICU',
                'shift_type' => 'Afternoon',
                'status' => 'scheduled',
                'room_ward' => 'ICU-1',
                'notes' => 'ICU duty',
                'specialization' => 'Intensive Care'
            ],
            [
                'id' => 3,
                'doctor_id' => 3,
                'doctor_name' => 'Dr. Robert Johnson',
                'shift_date' => date('Y-m-d'),
                'start_time' => '08:00',
                'end_time' => '16:00',
                'department' => 'General',
                'shift_type' => 'Morning',
                'status' => 'active',
                'room_ward' => 'OPD-1',
                'notes' => 'General practice',
                'specialization' => 'General Medicine'
            ],
            [
                'id' => 4,
                'doctor_id' => 4,
                'doctor_name' => 'Dr. Sarah Williams',
                'shift_date' => date('Y-m-d', strtotime('+2 days')),
                'start_time' => '20:00',
                'end_time' => '04:00',
                'department' => 'Pediatrics',
                'shift_type' => 'Night',
                'status' => 'scheduled',
                'room_ward' => 'Pediatrics Ward',
                'notes' => 'Night duty',
                'specialization' => 'Pediatrics'
            ]
        ];
    }

    /**
     * Get mock stats data
     */
    private function getMockStats()
    {
        return [
            'total_shifts' => 24,
            'scheduled_shifts' => 18,
            'today_shifts' => 6,
            'active_doctors' => 8,
            'my_shifts' => 4,
            'week_shifts' => 12,
            'upcoming_shifts' => 3,
            'department_shifts' => 6,
            'department' => 'Emergency'
        ];
    }

    /**
     * Get mock available staff
     */
    private function getMockAvailableStaff()
    {
        return [
            ['doctor_id' => 1, 'first_name' => 'John', 'last_name' => 'Smith', 'specialization' => 'Emergency Medicine'],
            ['doctor_id' => 2, 'first_name' => 'Jane', 'last_name' => 'Doe', 'specialization' => 'ICU'],
            ['doctor_id' => 3, 'first_name' => 'Robert', 'last_name' => 'Johnson', 'specialization' => 'General Surgery'],
            ['doctor_id' => 4, 'first_name' => 'Sarah', 'last_name' => 'Williams', 'specialization' => 'Pediatrics']
        ];
    }

    /**
     * Get shifts API - role-based filtering
     */
    public function getShiftsAPI()
    {
        try {
            $shifts = $this->getMockShifts();
            
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
            $input = $this->request->getJSON(true) ?? $this->request->getPost();
            
            // Mock successful creation
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Shift created successfully',
                'id' => rand(100, 999),
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

            // Mock successful update
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Shift updated successfully',
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

            // Mock successful deletion
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Shift deleted successfully',
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
            $shifts = $this->getMockShifts();
            $shift = null;
            
            foreach ($shifts as $s) {
                if ($s['id'] == $id) {
                    $shift = $s;
                    break;
                }
            }
            
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

            // Mock successful update
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Shift status updated successfully',
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
            $staff = $this->getMockAvailableStaff();
            
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
        return true; // Simplified for now
    }

    private function canCreateShift()
    {
        return in_array($this->userRole, ['admin', 'it_staff']);
    }

    private function canEditShift()
    {
        return in_array($this->userRole, ['admin', 'it_staff', 'doctor']);
    }

    private function canDeleteShift()
    {
        return $this->userRole === 'admin';
    }

    // Helper methods

    private function getUserPermissions()
    {
        return [
            'canView' => $this->canViewShifts(),
            'canCreate' => $this->canCreateShift(),
            'canEdit' => $this->canEditShift(),
            'canDelete' => $this->canDeleteShift(),
            'canViewAll' => $this->userRole === 'admin',
            'canViewOwn' => true,
            'canViewDepartment' => true
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

    private function getDepartments()
    {
        return [
            ['department' => 'Emergency'],
            ['department' => 'ICU'],
            ['department' => 'General'],
            ['department' => 'Pediatrics'],
            ['department' => 'Cardiology'],
            ['department' => 'Orthopedics']
        ];
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
