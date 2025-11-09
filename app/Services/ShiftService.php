<?php

namespace App\Services;

use App\Libraries\PermissionManager;

class ShiftService
{
    protected $db;
    protected $permissionManager;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->permissionManager = new PermissionManager();
    }

    /**
     * Get shifts based on user role and permissions
     */
    public function getShiftsByRole($userRole, $staffId = null, $filters = [])
    {
        try {
            $builder = $this->db->table('doctor_shift ds')
                ->select([
                    'ds.shift_id as id',
                    'ds.doctor_id',
                    'ds.shift_date as date',
                    'ds.shift_start as start',
                    'ds.shift_end as end',
                    'ds.department',
                    'ds.duration_hours',
                    'ds.room_ward',
                    'ds.status',
                    'ds.notes',
                    'ds.created_at',
                    'ds.updated_at',
                    "CONCAT(COALESCE(s.first_name,''),' ',COALESCE(s.last_name,'')) as doctor_name",
                    's.staff_id',
                    's.phone as doctor_phone',
                    's.email as doctor_email'
                ])
                ->join('doctor d', 'd.doctor_id = ds.doctor_id', 'left')
                ->join('staff s', 's.staff_id = d.staff_id', 'left');

            // Apply role-based filtering
            switch ($userRole) {
                case 'admin':
                case 'it_staff':
                    // Admin and IT staff see all shifts
                    break;
                    
                case 'doctor':
                    // Doctors see only their own shifts
                    if ($staffId) {
                        $builder->where('s.staff_id', $staffId);
                    }
                    break;
                    
                case 'nurse':
                    // Nurses see shifts in their department
                    if ($staffId) {
                        $userDept = $this->getUserDepartment($staffId);
                        if ($userDept) {
                            $builder->where('ds.department', $userDept);
                        }
                    }
                    break;
                    
                case 'receptionist':
                    // Receptionists see all shifts for scheduling coordination
                    break;
                    
                default:
                    // Other roles see no shifts
                    $builder->where('1', '0');
                    break;
            }

            // Apply additional filters
            if (!empty($filters['date'])) {
                $builder->where('ds.shift_date', $filters['date']);
            }
            
            if (!empty($filters['status'])) {
                $builder->where('ds.status', $filters['status']);
            }
            
            if (!empty($filters['department'])) {
                $builder->where('ds.department', $filters['department']);
            }
            
            if (!empty($filters['doctor_id'])) {
                $builder->where('ds.doctor_id', $filters['doctor_id']);
            }

            if (!empty($filters['date_range'])) {
                $builder->where('ds.shift_date >=', $filters['date_range']['start']);
                $builder->where('ds.shift_date <=', $filters['date_range']['end']);
            }

            // Search functionality
            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $builder->groupStart()
                    ->like("CONCAT(s.first_name, ' ', s.last_name)", $search)
                    ->orLike('ds.department', $search)
                    ->orLike('ds.room_ward', $search)
                    ->groupEnd();
            }

            $builder->orderBy('ds.shift_date', 'DESC')
                ->orderBy('ds.shift_start', 'ASC');

            return $builder->get()->getResultArray();

        } catch (\Throwable $e) {
            log_message('error', 'ShiftService::getShiftsByRole error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get shift statistics based on user role
     */
    public function getShiftStats($userRole, $staffId = null)
    {
        try {
            $stats = [];
            $today = date('Y-m-d');
            $thisWeek = [
                'start' => date('Y-m-d', strtotime('monday this week')),
                'end' => date('Y-m-d', strtotime('sunday this week'))
            ];

            switch ($userRole) {
                case 'admin':
                case 'it_staff':
                    $stats = [
                        'total_shifts' => $this->getTotalShifts(),
                        'today_shifts' => $this->getShiftsCount(['date' => $today]),
                        'week_shifts' => $this->getShiftsCount(['date_range' => $thisWeek]),
                        'scheduled_shifts' => $this->getShiftsCount(['status' => 'Scheduled']),
                        'completed_shifts' => $this->getShiftsCount(['status' => 'Completed']),
                        'cancelled_shifts' => $this->getShiftsCount(['status' => 'Cancelled']),
                        'departments' => $this->getDepartmentStats(),
                        'active_doctors' => $this->getActiveDoctorsCount()
                    ];
                    break;
                    
                case 'doctor':
                    $doctorShifts = $this->getShiftsByRole('doctor', $staffId);
                    $stats = [
                        'my_shifts' => count($doctorShifts),
                        'today_shifts' => $this->getShiftsCount(['date' => $today], 'doctor', $staffId),
                        'week_shifts' => $this->getShiftsCount(['date_range' => $thisWeek], 'doctor', $staffId),
                        'scheduled_shifts' => $this->getShiftsCount(['status' => 'Scheduled'], 'doctor', $staffId),
                        'completed_shifts' => $this->getShiftsCount(['status' => 'Completed'], 'doctor', $staffId),
                        'upcoming_shifts' => $this->getUpcomingShifts($staffId)
                    ];
                    break;
                    
                case 'nurse':
                    $userDept = $this->getUserDepartment($staffId);
                    $stats = [
                        'department_shifts' => $this->getShiftsCount(['department' => $userDept]),
                        'today_shifts' => $this->getShiftsCount(['date' => $today, 'department' => $userDept]),
                        'week_shifts' => $this->getShiftsCount(['date_range' => $thisWeek, 'department' => $userDept]),
                        'scheduled_shifts' => $this->getShiftsCount(['status' => 'Scheduled', 'department' => $userDept]),
                        'department' => $userDept
                    ];
                    break;
                    
                case 'receptionist':
                    $stats = [
                        'total_shifts' => $this->getTotalShifts(),
                        'today_shifts' => $this->getShiftsCount(['date' => $today]),
                        'scheduled_shifts' => $this->getShiftsCount(['status' => 'Scheduled']),
                        'departments' => $this->getDepartmentStats()
                    ];
                    break;
                    
                default:
                    $stats = ['message' => 'No shift access for this role'];
                    break;
            }

            return $stats;

        } catch (\Throwable $e) {
            log_message('error', 'ShiftService::getShiftStats error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a new shift
     */
    public function createShift($data, $userRole, $staffId = null)
    {
        try {
            // Validate permissions
            if (!$this->permissionManager->hasPermission($userRole, 'shifts', 'create')) {
                return ['success' => false, 'message' => 'Permission denied'];
            }

            // Validate required fields
            $validation = \Config\Services::validation();
            $validation->setRules([
                'doctor_id' => 'required|integer',
                'shift_date' => 'required|valid_date',
                'shift_start' => 'required',
                'shift_end' => 'required',
                'department' => 'permit_empty|max_length[100]',
                'status' => 'permit_empty|in_list[Scheduled,Completed,Cancelled]',
                'room_ward' => 'permit_empty|max_length[100]',
                'notes' => 'permit_empty'
            ]);

            if (!$validation->run($data)) {
                return [
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validation->getErrors()
                ];
            }

            // Check for shift conflicts
            $conflictCheck = $this->checkShiftConflict(
                $data['doctor_id'],
                $data['shift_date'],
                $data['shift_start'],
                $data['shift_end']
            );

            if (!$conflictCheck['success']) {
                return $conflictCheck;
            }

            // Normalize times
            $start = $this->normalizeTime($data['shift_start']);
            $end = $this->normalizeTime($data['shift_end']);

            // Calculate duration
            $duration = $this->calculateDuration($data['shift_date'], $start, $end);

            // Get doctor's department if not provided
            $department = $data['department'] ?? $this->getDoctorDepartment($data['doctor_id']);

            $shiftData = [
                'doctor_id' => (int)$data['doctor_id'],
                'shift_date' => $data['shift_date'],
                'shift_start' => $start,
                'shift_end' => $end,
                'department' => $department,
                'status' => $data['status'] ?? 'Scheduled',
                'room_ward' => $data['room_ward'] ?? null,
                'notes' => $data['notes'] ?? null,
                'duration_hours' => $duration,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($this->db->table('doctor_shift')->insert($shiftData)) {
                return [
                    'success' => true,
                    'message' => 'Shift created successfully',
                    'id' => $this->db->insertID()
                ];
            }

            return ['success' => false, 'message' => 'Failed to create shift'];

        } catch (\Throwable $e) {
            log_message('error', 'ShiftService::createShift error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create shift'];
        }
    }

    /**
     * Update a shift
     */
    public function updateShift($id, $data, $userRole, $staffId = null)
    {
        try {
            // Get existing shift
            $existingShift = $this->getShift($id);
            if (!$existingShift) {
                return ['success' => false, 'message' => 'Shift not found'];
            }

            // Check permissions
            if (!$this->canEditShift($existingShift, $userRole, $staffId)) {
                return ['success' => false, 'message' => 'Permission denied'];
            }

            // Prepare update data
            $updateData = array_filter([
                'shift_date' => $data['shift_date'] ?? null,
                'shift_start' => isset($data['shift_start']) ? $this->normalizeTime($data['shift_start']) : null,
                'shift_end' => isset($data['shift_end']) ? $this->normalizeTime($data['shift_end']) : null,
                'department' => $data['department'] ?? null,
                'status' => $data['status'] ?? null,
                'room_ward' => $data['room_ward'] ?? null,
                'notes' => $data['notes'] ?? null,
                'updated_at' => date('Y-m-d H:i:s')
            ], function($v) { return $v !== null; });

            if (empty($updateData)) {
                return ['success' => false, 'message' => 'No fields to update'];
            }

            // Recalculate duration if time fields are updated
            if (isset($updateData['shift_date']) || isset($updateData['shift_start']) || isset($updateData['shift_end'])) {
                $date = $updateData['shift_date'] ?? $existingShift['date'];
                $start = $updateData['shift_start'] ?? $existingShift['start'];
                $end = $updateData['shift_end'] ?? $existingShift['end'];
                $updateData['duration_hours'] = $this->calculateDuration($date, $start, $end);
            }

            if ($this->db->table('doctor_shift')->where('shift_id', $id)->update($updateData)) {
                return ['success' => true, 'message' => 'Shift updated successfully'];
            }

            return ['success' => false, 'message' => 'Failed to update shift'];

        } catch (\Throwable $e) {
            log_message('error', 'ShiftService::updateShift error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update shift'];
        }
    }

    /**
     * Delete a shift
     */
    public function deleteShift($id, $userRole, $staffId = null)
    {
        try {
            // Get existing shift
            $existingShift = $this->getShift($id);
            if (!$existingShift) {
                return ['success' => false, 'message' => 'Shift not found'];
            }

            // Check permissions
            if (!$this->canDeleteShift($existingShift, $userRole, $staffId)) {
                return ['success' => false, 'message' => 'Permission denied'];
            }

            if ($this->db->table('doctor_shift')->where('shift_id', $id)->delete()) {
                return ['success' => true, 'message' => 'Shift deleted successfully'];
            }

            return ['success' => false, 'message' => 'Failed to delete shift'];

        } catch (\Throwable $e) {
            log_message('error', 'ShiftService::deleteShift error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to delete shift'];
        }
    }

    /**
     * Get a single shift
     */
    public function getShift($id)
    {
        try {
            return $this->db->table('doctor_shift ds')
                ->select([
                    'ds.shift_id as id',
                    'ds.doctor_id',
                    'ds.shift_date as date',
                    'ds.shift_start as start',
                    'ds.shift_end as end',
                    'ds.department',
                    'ds.duration_hours',
                    'ds.room_ward',
                    'ds.status',
                    'ds.notes',
                    'ds.created_at',
                    'ds.updated_at',
                    "CONCAT(COALESCE(s.first_name,''),' ',COALESCE(s.last_name,'')) as doctor_name",
                    's.staff_id'
                ])
                ->join('doctor d', 'd.doctor_id = ds.doctor_id', 'inner') // Changed to inner join for consistency
                ->join('staff s', 's.staff_id = d.staff_id', 'inner') // Changed to inner join for consistency
                ->where('ds.shift_id', $id)
                ->get()
                ->getRowArray();

        } catch (\Throwable $e) {
            log_message('error', 'ShiftService::getShift error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get available staff for shift assignment
     */
    public function getAvailableStaff($date = null, $startTime = null, $endTime = null)
    {
        try {
            log_message('debug', 'ShiftService::getAvailableStaff called');
            
            // Pull doctors ONLY from the doctor table; join staff for names
            $builder = $this->db->table('doctor d')
                ->select([
                    'd.doctor_id',
                    's.staff_id',
                    's.first_name',
                    's.last_name',
                    'd.specialization'
                ])
                ->join('staff s', 's.staff_id = d.staff_id', 'inner') // Inner join to ensure we only get doctors with staff records
                ->orderBy('s.first_name', 'ASC');

            log_message('debug', 'ShiftService query built, executing...');

            // Check availability if date and time provided
            if ($date && $startTime && $endTime) {
                log_message('debug', 'Checking availability for date: ' . $date);
                $conflictingDoctors = $this->db->table('doctor_shift')
                    ->select('doctor_id')
                    ->where('shift_date', $date)
                    ->where('status !=', 'Cancelled')
                    ->groupStart()
                        ->where('shift_start <', $endTime)
                        ->where('shift_end >', $startTime)
                    ->groupEnd()
                    ->get()
                    ->getResultArray();

                if (!empty($conflictingDoctors)) {
                    $conflictingIds = array_column($conflictingDoctors, 'doctor_id');
                    if (!empty($conflictingIds)) {
                        $builder->whereNotIn('d.doctor_id', $conflictingIds);
                        log_message('debug', 'Excluding ' . count($conflictingIds) . ' conflicting doctors');
                    }
                }
            }

            $result = $builder->get()->getResultArray();
            log_message('debug', 'ShiftService::getAvailableStaff found ' . count($result) . ' doctors from doctor table');
            
            // Log the actual results for debugging
            if (!empty($result)) {
                foreach ($result as $doctor) {
                    log_message('debug', 'Doctor found: ID=' . $doctor['doctor_id'] . ', Name=' . $doctor['first_name'] . ' ' . $doctor['last_name'] . ', Spec=' . ($doctor['specialization'] ?? 'None'));
                }
            }
            
            return $result;

        } catch (\Throwable $e) {
            log_message('error', 'ShiftService::getAvailableStaff error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Update shift status
     */
    public function updateShiftStatus($id, $status, $userRole, $staffId = null)
    {
        try {
            // Get existing shift
            $existingShift = $this->getShift($id);
            if (!$existingShift) {
                return ['success' => false, 'message' => 'Shift not found'];
            }

            // Check permissions
            if (!$this->canEditShift($existingShift, $userRole, $staffId)) {
                return ['success' => false, 'message' => 'Permission denied'];
            }

            // Validate status
            $validStatuses = ['Scheduled', 'Completed', 'Cancelled'];
            if (!in_array($status, $validStatuses)) {
                return ['success' => false, 'message' => 'Invalid status'];
            }

            $updateData = [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($this->db->table('doctor_shift')->where('shift_id', $id)->update($updateData)) {
                return ['success' => true, 'message' => 'Shift status updated successfully'];
            }

            return ['success' => false, 'message' => 'Failed to update shift status'];

        } catch (\Throwable $e) {
            log_message('error', 'ShiftService::updateShiftStatus error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update shift status'];
        }
    }

    // Private helper methods

    private function getTotalShifts()
    {
        return $this->db->table('doctor_shift')->countAllResults();
    }

    private function getShiftsCount($filters = [], $userRole = null, $staffId = null)
    {
        $builder = $this->db->table('doctor_shift ds');
        
        if ($userRole === 'doctor' && $staffId) {
            $builder->join('doctor d', 'd.doctor_id = ds.doctor_id')
                ->join('staff s', 's.staff_id = d.staff_id')
                ->where('s.staff_id', $staffId);
        }

        if (!empty($filters['date'])) {
            $builder->where('ds.shift_date', $filters['date']);
        }
        
        if (!empty($filters['status'])) {
            $builder->where('ds.status', $filters['status']);
        }
        
        if (!empty($filters['department'])) {
            $builder->where('ds.department', $filters['department']);
        }

        if (!empty($filters['date_range'])) {
            $builder->where('ds.shift_date >=', $filters['date_range']['start']);
            $builder->where('ds.shift_date <=', $filters['date_range']['end']);
        }

        return $builder->countAllResults();
    }

    private function getDepartmentStats()
    {
        try {
            return $this->db->table('doctor_shift')
                ->select('department, COUNT(*) as count')
                ->where('department IS NOT NULL')
                ->groupBy('department')
                ->orderBy('count', 'DESC')
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function getActiveDoctorsCount()
    {
        try {
            return $this->db->table('staff')
                ->where('role', 'doctor')
                ->where('status', 'Active')
                ->countAllResults();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function getUpcomingShifts($staffId)
    {
        try {
            return $this->db->table('doctor_shift ds')
                ->join('doctor d', 'd.doctor_id = ds.doctor_id')
                ->join('staff s', 's.staff_id = d.staff_id')
                ->where('s.staff_id', $staffId)
                ->where('ds.shift_date >=', date('Y-m-d'))
                ->where('ds.status', 'Scheduled')
                ->countAllResults();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function getUserDepartment($staffId)
    {
        try {
            $result = $this->db->table('staff')
                ->select('department')
                ->where('staff_id', $staffId)
                ->get()
                ->getRowArray();
            return $result['department'] ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function checkShiftConflict($doctorId, $date, $startTime, $endTime, $excludeShiftId = null)
    {
        try {
            $builder = $this->db->table('doctor_shift')
                ->where('doctor_id', $doctorId)
                ->where('shift_date', $date)
                ->where('status !=', 'Cancelled')
                ->groupStart()
                    ->where('shift_start <', $endTime)
                    ->where('shift_end >', $startTime)
                ->groupEnd();

            if ($excludeShiftId) {
                $builder->where('shift_id !=', $excludeShiftId);
            }

            $conflicts = $builder->get()->getResultArray();

            if (!empty($conflicts)) {
                return [
                    'success' => false,
                    'message' => 'Shift conflict detected. Doctor already has a shift during this time.',
                    'conflicts' => $conflicts
                ];
            }

            return ['success' => true];

        } catch (\Throwable $e) {
            log_message('error', 'ShiftService::checkShiftConflict error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error checking shift conflicts'];
        }
    }

    private function calculateDuration($date, $start, $end)
    {
        try {
            $startTime = new \DateTime($date . ' ' . $start);
            $endTime = new \DateTime($date . ' ' . $end);
            
            // Handle overnight shifts
            if ($endTime < $startTime) {
                $endTime->modify('+1 day');
            }
            
            $diff = $endTime->getTimestamp() - $startTime->getTimestamp();
            return round($diff / 3600, 2);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function normalizeTime($time)
    {
        if (preg_match('/^\d{2}:\d{2}$/', $time)) {
            return $time . ':00';
        }
        return $time;
    }

    private function getDoctorDepartment($doctorId)
    {
        try {
            $result = $this->db->table('doctor d')
                ->select('s.department')
                ->join('staff s', 's.staff_id = d.staff_id', 'left')
                ->where('d.doctor_id', $doctorId)
                ->get()
                ->getRowArray();
            return $result['department'] ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function canEditShift($shift, $userRole, $staffId = null)
    {
        // Admin and IT staff can edit all shifts
        if (in_array($userRole, ['admin', 'it_staff'])) {
            return true;
        }

        // Doctors can edit their own shifts
        if ($userRole === 'doctor' && $staffId && $shift['staff_id'] == $staffId) {
            return true;
        }

        return false;
    }

    private function canDeleteShift($shift, $userRole, $staffId = null)
    {
        // Only admin can delete shifts
        return $userRole === 'admin';
    }
}
