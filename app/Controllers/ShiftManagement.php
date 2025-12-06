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
            $session = session();
            $this->userRole = $session->get('role') ?? $session->get('user_role') ?: 'admin';
            $this->staffId = $session->get('staff_id') ?? $session->get('id') ?? 1;
        } catch (\Throwable $e) {
            log_message('error', 'ShiftManagement::initController error: ' . $e->getMessage());
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
            $pageConfig = $this->getPageConfig();
            $data = [
                'title' => $pageConfig['title'],
                'shifts' => $this->getMockShifts(),
                'stats' => $this->getMockStats(),
                'availableStaff' => $this->getMockAvailableStaff(),
                'userRole' => $this->userRole,
                'permissions' => $this->getUserPermissions(),
                'pageConfig' => $pageConfig,
                'departments' => $this->getDepartments(),
                'shiftTypes' => $this->getShiftTypes(),
                'roomsWards' => $this->getRoomsWards(),
                'availableDoctors' => $this->getAvailableDoctors()
            ];
            return view('unified/shift-management', $data);
        } catch (\Throwable $e) {
            log_message('error', 'ShiftManagement::index error: ' . $e->getMessage());
            return $this->response->setBody('<h1>Shift Management Error</h1><p>' . esc($e->getMessage()) . '</p><p><a href="' . base_url('dashboard') . '">Back to Dashboard</a></p>')->setStatusCode(500);
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
        // Default fallback values (previous mock data)
        $fallback = [
            'total_shifts'      => 24,
            'scheduled_shifts'  => 18,
            'today_shifts'      => 6,
            'active_doctors'    => 8,
            'my_shifts'         => 4,
            'week_shifts'       => 12,
            'upcoming_shifts'   => 3,
            'department_shifts' => 6,
            'department'        => 'Emergency',
        ];

        try {
            $db = \Config\Database::connect();

            // Determine today's weekday (1 = Monday ... 7 = Sunday)
            $todayWeekday = (int) date('N');

            // Total active schedule entries (all doctors, all days)
            $totalSchedules = $db->table('staff_schedule')
                ->where('status', 'active')
                ->countAllResults();

            // Today's schedules (pattern for today's weekday)
            $todaySchedules = $db->table('staff_schedule')
                ->where('status', 'active')
                ->where('weekday', $todayWeekday)
                ->countAllResults();

            // Active doctors from doctor table
            $activeDoctors = $db->table('doctor')
                ->where('status', 'Active')
                ->countAllResults();

            // Current user's schedules (if staffId is set)
            $mySchedules = 0;
            if (!empty($this->staffId)) {
                $mySchedules = $db->table('staff_schedule')
                    ->where('status', 'active')
                    ->where('staff_id', $this->staffId)
                    ->countAllResults();
            }

            // For now, treat week_shifts as total active schedules,
            // and upcoming_shifts as today's schedules (pattern-based)
            $stats = [
                'total_shifts'      => $totalSchedules,
                'scheduled_shifts'  => $totalSchedules,
                'today_shifts'      => $todaySchedules,
                'active_doctors'    => $activeDoctors,
                'my_shifts'         => $mySchedules,
                'week_shifts'       => $totalSchedules,
                'upcoming_shifts'   => $todaySchedules,
                'department_shifts' => $todaySchedules,
                'department'        => 'All Departments',
            ];

            return $stats;

        } catch (\Throwable $e) {
            log_message('error', 'ShiftManagement::getMockStats (real stats) error: ' . $e->getMessage());
            // On error, return the original mock stats so the UI still works
            return $fallback;
        }
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
            $db = \Config\Database::connect();

            // Base query: schedules for doctors only
            $builder = $db->table('staff_schedule ss')
                ->select('ss.id, ss.staff_id, ss.weekday, ss.start_time, ss.end_time, ss.status, '
                    . "CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, '')) AS doctor_name, "
                    . 'd.specialization')
                ->join('doctor d', 'd.staff_id = ss.staff_id', 'inner')
                ->join('staff s', 's.staff_id = ss.staff_id', 'left')
                ->where('ss.status', 'active');

            // Role-based filtering
            if ($this->userRole === 'doctor' && !empty($this->staffId)) {
                // Doctor sees only their own schedules
                $builder->where('ss.staff_id', $this->staffId);
            }

            $result = $builder->get()->getResultArray();

            return $this->response->setJSON([
                'status' => 'success',
                'data'   => $result,
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'ShiftManagement::getShiftsAPI error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Failed to load schedule',
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

            $staffId = $input['staff_id'] ?? $input['doctor_id'] ?? null;

            // Normalize weekdays: accept either weekdays[] array (preferred)
            // or a single weekday value from older forms.
            $weekdays = [];
            if (isset($input['weekdays']) && is_array($input['weekdays'])) {
                $weekdays = $input['weekdays'];
            } elseif (isset($input['weekday']) && $input['weekday'] !== '') {
                $weekdays = [$input['weekday']];
            }

            // Validate required fields
            if (empty($staffId)) {
                return $this->response->setStatusCode(422)->setJSON([
                    'status'  => 'error',
                    'message' => "Field 'staff_id' (doctor) is required",
                    'csrf'    => ['name' => csrf_token(), 'value' => csrf_hash()],
                ]);
            }

            if (empty($weekdays) || empty($input['start_time']) || empty($input['end_time'])) {
                return $this->response->setStatusCode(422)->setJSON([
                    'status'  => 'error',
                    'message' => 'At least one weekday and both start and end time are required',
                    'csrf'    => ['name' => csrf_token(), 'value' => csrf_hash()],
                ]);
            }

            $db = \Config\Database::connect();

            // Ensure staff_id belongs to a doctor by checking doctor table
            // (staff table may not have a role column in this installation)
            $isDoctor = $db->table('doctor')
                ->where('staff_id', $staffId)
                ->countAllResults() > 0;

            if (!$isDoctor) {
                return $this->response->setStatusCode(422)->setJSON([
                    'status'  => 'error',
                    'message' => 'Only doctors can have schedules',
                    'csrf'    => ['name' => csrf_token(), 'value' => csrf_hash()],
                ]);
            }

            // Prepare common schedule data (without weekday)
            $baseData = [
                'staff_id'       => (int) $staffId,
                'start_time'     => $input['start_time'] ?? null,
                'end_time'       => $input['end_time'] ?? null,
                'status'         => 'active',
                'effective_from' => $input['effective_from'] ?? null,
                'effective_to'   => $input['effective_to'] ?? null,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ];

            $createdIds = [];

            foreach ($weekdays as $weekday) {
                $weekdayInt = (int) $weekday;
                if ($weekdayInt < 1 || $weekdayInt > 7) {
                    continue;
                }

                // Skip if a schedule already exists for this doctor/day/time range
                $existing = $db->table('staff_schedule')
                    ->where('staff_id', (int) $staffId)
                    ->where('weekday', $weekdayInt)
                    ->where('start_time', $input['start_time'] ?? null)
                    ->where('end_time', $input['end_time'] ?? null)
                    ->where('status', 'active')
                    ->countAllResults();

                if ($existing > 0) {
                    continue;
                }

                $scheduleData = $baseData;
                $scheduleData['weekday'] = $weekdayInt;

                $result = $db->table('staff_schedule')->insert($scheduleData);
                if ($result) {
                    $createdIds[] = $db->insertID();
                }
            }

            if (!empty($createdIds)) {
                return $this->response->setJSON([
                    'status'  => 'success',
                    'message' => 'Schedule entry created successfully for selected day(s)',
                    'ids'     => $createdIds,
                    'csrf'    => ['name' => csrf_token(), 'value' => csrf_hash()],
                ]);
            }

            return $this->response->setStatusCode(422)->setJSON([
                'status'  => 'error',
                'message' => 'No new schedule entries were created. There may already be active schedules for the selected day(s) and slot.',
                'csrf'    => ['name' => csrf_token(), 'value' => csrf_hash()],
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'ShiftManagement::create (schedule) error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Failed to create schedule entry: ' . $e->getMessage(),
                'csrf'    => ['name' => csrf_token(), 'value' => csrf_hash()],
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

            $id = (int) $input['id'];

            $db = \Config\Database::connect();

            // Build fields we allow to be updated from the schedule edit form
            $data = [];

            // Doctor/staff change (optional)
            if (!empty($input['staff_id']) || !empty($input['doctor_id'])) {
                $staffId = $input['staff_id'] ?? $input['doctor_id'];

                // Ensure provided staff_id belongs to a doctor
                $isDoctor = $db->table('doctor')
                    ->where('staff_id', $staffId)
                    ->countAllResults() > 0;

                if (!$isDoctor) {
                    return $this->response->setStatusCode(422)->setJSON([
                        'status'  => 'error',
                        'message' => 'Only doctors can have schedules',
                        'csrf'    => ['name' => csrf_token(), 'value' => csrf_hash()],
                    ]);
                }

                $data['staff_id'] = (int) $staffId;
            }

            if (isset($input['weekday']) && $input['weekday'] !== '') {
                $data['weekday'] = (int) $input['weekday'];
            }

            if (isset($input['start_time']) && $input['start_time'] !== '') {
                $data['start_time'] = $input['start_time'];
            }

            if (isset($input['end_time']) && $input['end_time'] !== '') {
                $data['end_time'] = $input['end_time'];
            }

            if (!empty($input['status'])) {
                
                $status = strtolower($input['status']);

                if (in_array($status, ['scheduled', 'active'], true)) {
                    $data['status'] = 'active';
                } else {
                   
                    $data['status'] = 'inactive';
                }
            }

            // Always update timestamp
            $data['updated_at'] = date('Y-m-d H:i:s');

            if (empty($data)) {
                return $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'message' => 'No valid fields provided for update',
                    'csrf' => ['name' => csrf_token(), 'value' => csrf_hash()]
                ]);
            }

            $db->table('staff_schedule')
                ->where('id', $id)
                ->update($data);

            if ($db->affectedRows() === 0) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'error',
                    'message' => 'Shift not found or no changes detected',
                    'csrf' => ['name' => csrf_token(), 'value' => csrf_hash()]
                ]);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Shift updated successfully',
                'csrf' => ['name' => csrf_token(), 'value' => csrf_hash()]
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'ShiftManagement::update error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to update shift: ' . $e->getMessage(),
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

            $db = \Config\Database::connect();

            // Delete from staff_schedule since we now store schedules there
            $db->table('staff_schedule')
                ->where('id', (int) $id)
                ->delete();

            if ($db->affectedRows() > 0) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Shift deleted successfully',
                    'csrf' => ['name' => csrf_token(), 'value' => csrf_hash()]
                ]);
            }

            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Shift not found or already deleted',
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

    private function canViewShifts() { return true; }
    private function canCreateShift() { return in_array($this->userRole, ['admin', 'it_staff']); }
    private function canEditShift() { return in_array($this->userRole, ['admin', 'it_staff', 'doctor']); }
    private function canDeleteShift() { return $this->userRole === 'admin'; }

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
        return match($this->userRole) {
            'admin' => ['title' => 'Schedule Management', 'subtitle' => 'Manage all staff schedules and shifts', 'redirectUrl' => 'admin/dashboard', 'showSidebar' => true, 'sidebarType' => 'admin'],
            'doctor' => ['title' => 'My Schedule', 'subtitle' => 'View and manage your work schedule', 'redirectUrl' => 'doctor/dashboard', 'showSidebar' => true, 'sidebarType' => 'doctor'],
            'nurse' => ['title' => 'Department Schedule', 'subtitle' => 'View department work schedules', 'redirectUrl' => 'nurse/dashboard', 'showSidebar' => true, 'sidebarType' => 'nurse'],
            'receptionist' => ['title' => 'Schedule Overview', 'subtitle' => 'View staff schedules for coordination', 'redirectUrl' => 'receptionist/dashboard', 'showSidebar' => true, 'sidebarType' => 'receptionist'],
            'it_staff' => ['title' => 'Schedule Management', 'subtitle' => 'System administration of staff schedules', 'redirectUrl' => 'it-staff/dashboard', 'showSidebar' => true, 'sidebarType' => 'admin'],
            default => ['title' => 'Schedule Management', 'subtitle' => 'Manage all staff schedules and shifts', 'redirectUrl' => 'admin/dashboard', 'showSidebar' => true, 'sidebarType' => 'admin']
        };
    }

    private function getDepartments()
    {
        try {
            $departments = \Config\Database::connect()->table('department')
                ->select('department_id, name')
                ->orderBy('name', 'ASC')
                ->get()
                ->getResultArray();
            return array_map(fn($dept) => ['department' => $dept['name']], $departments);
        } catch (\Throwable $e) {
            log_message('error', 'ShiftManagement::getDepartments error: ' . $e->getMessage());
            return [['department' => 'Emergency'], ['department' => 'ICU'], ['department' => 'General'], ['department' => 'Pediatrics'], ['department' => 'Cardiology'], ['department' => 'Orthopedics']];
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
            ['room_ward' => 'Ward-A'],
            ['room_ward' => 'Ward-B']
        ];
    }

    private function getDoctors()
    {
        try {
            $db = \Config\Database::connect();
            $doctors = $db->table('doctor d')
                ->select('d.doctor_id, s.staff_id, s.first_name, s.last_name, s.department, d.specialization, d.status')
                ->join('staff s', 's.staff_id = d.staff_id', 'left')
                ->where('d.status', 'Active')
                ->orderBy('s.first_name', 'ASC')
                ->get()
                ->getResultArray();
            
            return array_map(function($doctor) {
                $doctor['name'] = trim(($doctor['first_name'] ?? '') . ' ' . ($doctor['last_name'] ?? ''));
                return $doctor;
            }, $doctors);
        } catch (\Throwable $e) {
            log_message('error', 'ShiftManagement::getDoctors error: ' . $e->getMessage());
            return [];
        }
    }

    private function getAvailableDoctors()
    {
        try {
            return \Config\Database::connect()->table('doctor d')
                ->select('s.staff_id, s.first_name, s.last_name, d.specialization')
                ->join('staff s', 's.staff_id = d.staff_id', 'inner')
                ->where('d.status', 'Active')
                ->orderBy('s.first_name', 'ASC')
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'ShiftManagement::getAvailableDoctors error: ' . $e->getMessage());
            return [];
        }
    }

}
