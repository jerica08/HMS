<?php

namespace App\Services;

class AppointmentService
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Create appointment with role-based doctor assignment
     */
    public function createAppointment($input, $userRole, $staffId = null)
    {
        // Determine doctor assignment based on user role
        $doctorId = $this->determineDoctorId($input, $userRole, $staffId);
        
        if (!$doctorId) {
            return [
                'success' => false,
                'message' => 'No doctor available for assignment',
            ];
        }

        // Validation
        $validation = \Config\Services::validation();
        $validation->setRules($this->getValidationRules($userRole));

        if (!$validation->run($input)) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors(),
            ];
        }

        // Prepare appointment data
        $data = $this->prepareAppointmentData($input, $doctorId, $userRole);

        $appointmentDate = $data['appointment_date'] ?? null;

        if (!$appointmentDate) {
            return [
                'success' => false,
                'message' => 'Invalid appointment date',
            ];
        }

        $timestamp = strtotime($appointmentDate);

        if ($timestamp === false) {
            return [
                'success' => false,
                'message' => 'Invalid appointment date',
            ];
        }

        $weekdayName = date('N', $timestamp); // 1 (Mon) - 7 (Sun)

        $hasShift = $this->db->table('staff_schedule')
            ->where('staff_id', $doctorId)
            ->where('weekday', $weekdayName)
            ->where('status', 'active')
            ->countAllResults() > 0;

        if (!$hasShift) {
            return [
                'success' => false,
                'message' => 'Selected doctor has no shift on this day',
            ];
        }

       
        $appointmentsCount = $this->db->table('appointments')
            ->where('doctor_id', $doctorId)
            ->where('appointment_date', $appointmentDate)
            ->whereIn('status', ['scheduled', 'in-progress'])
            ->countAllResults();

        if ($appointmentsCount > 0) {
            return [
                'success' => false,
                'message' => 'Doctor already has an active appointment on this date',
            ];
        }

        try {
            $this->db->table('appointments')->insert($data);
            return [
                'success' => true,
                'message' => 'Appointment scheduled successfully',
                'id' => $this->db->insertID(),
                'appointment_id' => 'APT-' . date('Ymd') . '-' . str_pad($this->db->insertID(), 4, '0', STR_PAD_LEFT)
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Failed to create appointment: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get appointments with optional filtering
     */
    public function getAppointments($filters = [])
    {
        try {
            $builder = $this->db->table('appointments a')
                ->select('a.*, 
                         p.patient_id,
                         p.first_name as patient_first_name, 
                         p.last_name as patient_last_name,
                         p.email as patient_email,
                         p.date_of_birth,
                         TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) as patient_age,
                         CONCAT(p.first_name, " ", p.last_name) as patient_full_name,
                         s.staff_id as doctor_id,
                         s.first_name as doctor_first_name,
                         s.last_name as doctor_last_name,
                         CONCAT(s.first_name, " ", s.last_name) as doctor_name,
                         DATE_FORMAT(a.appointment_date, "%W, %M %d, %Y") as formatted_date,
                         TIME_FORMAT(a.appointment_time, "%h:%i %p") as formatted_time')
                ->join('patients p', 'p.patient_id = a.patient_id', 'left')
                ->join('staff s', 's.staff_id = a.doctor_id', 'left');

            // Apply filters
            if (isset($filters['doctor_id'])) {
                $builder->where('a.doctor_id', $filters['doctor_id']);
            }
            
            if (isset($filters['date'])) {
                $builder->where('a.appointment_date', $filters['date']);
            }
            
            if (isset($filters['status'])) {
                $builder->where('a.status', $filters['status']);
            }
            
            if (isset($filters['patient_id'])) {
                $builder->where('a.patient_id', $filters['patient_id']);
            }

            $appointments = $builder->orderBy('a.appointment_date', 'DESC')
                ->orderBy('a.appointment_time', 'DESC')
                ->get()
                ->getResultArray();

            return [
                'success' => true,
                'data' => $appointments,
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Error fetching appointments: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch appointments',
                'data' => [],
            ];
        }
    }

    /**
     * Get single appointment by ID
     */
    public function getAppointment($id)
    {
        try {
            $appointment = $this->db->table('appointments a')
                ->select('a.*,
                         p.patient_id,
                         p.first_name as patient_first_name,
                         p.last_name as patient_last_name,
                         p.email as patient_email,
                         p.date_of_birth,
                         TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) as patient_age,
                         CONCAT(p.first_name, " ", p.last_name) as patient_full_name,
                         s.staff_id as doctor_id,
                         s.first_name as doctor_first_name,
                         s.last_name as doctor_last_name,
                         CONCAT(s.first_name, " ", s.last_name) as doctor_name')
                ->join('patients p', 'p.patient_id = a.patient_id', 'left')
                ->join('staff s', 's.staff_id = a.doctor_id', 'left')
                ->where('a.appointment_id', $id)
                ->get()
                ->getRowArray();

            if (!$appointment) {
                return [
                    'success' => false,
                    'message' => 'Appointment not found',
                ];
            }

            return [
                'success' => true,
                'appointment' => $appointment,
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Error fetching appointment: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Database error',
            ];
        }
    }

    /**
     * Update appointment status
     */
    public function updateAppointmentStatus($id, $status, $userRole = null, $staffId = null)
    {
        $validStatuses = ['scheduled', 'in-progress', 'completed', 'cancelled', 'no-show'];
        
        if (!in_array($status, $validStatuses)) {
            return [
                'success' => false,
                'message' => 'Invalid status',
            ];
        }

        try {
            $builder = $this->db->table('appointments');
            
            // For doctors, ensure they can only update their own appointments
            if ($userRole === 'doctor' && $staffId) {
                $builder->where('doctor_id', $staffId);
            }
            
            $result = $builder->where('appointment_id', $id)
                ->update(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Appointment status updated successfully',
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Appointment not found or no permission to update',
                ];
            }
        } catch (\Throwable $e) {
            log_message('error', 'Failed to update appointment status: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Delete appointment
     */
    public function deleteAppointment($id, $userRole = null, $staffId = null)
    {
        try {
            $builder = $this->db->table('appointments');
            
            // For doctors, ensure they can only delete their own appointments
            if ($userRole === 'doctor' && $staffId) {
                $builder->where('doctor_id', $staffId);
            }
            
            $result = $builder->where('appointment_id', $id)->delete();

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Appointment deleted successfully',
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Appointment not found or no permission to delete',
                ];
            }
        } catch (\Throwable $e) {
            log_message('error', 'Failed to delete appointment: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get available time slots for a doctor on a specific date
     */
    public function getAvailableTimeSlots($doctorId, $date)
    {
        try {
            // Get existing appointments for the doctor on the date
            $existingAppointments = $this->db->table('appointments')
                ->select('appointment_time')
                ->where('doctor_id', $doctorId)
                ->where('appointment_date', $date)
                ->where('status !=', 'cancelled')
                ->get()
                ->getResultArray();

            $bookedTimes = array_column($existingAppointments, 'appointment_time');

            // Generate available time slots (9 AM to 5 PM, 30-minute intervals)
            $allSlots = [
                '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
                '14:00', '14:30', '15:00', '15:30', '16:00', '16:30'
            ];

            $availableSlots = array_diff($allSlots, $bookedTimes);

            return [
                'success' => true,
                'time_slots' => array_values($availableSlots),
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Error fetching time slots: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to get time slots',
                'time_slots' => [],
            ];
        }
    }

    private function determineDoctorId($input, $userRole, $staffId)
    {
        if ($userRole === 'doctor') {
            // For doctors, use their staff_id directly
            return $staffId;
        } 
        
        if ($userRole === 'receptionist' || $userRole === 'admin') {
            // For receptionist/admin, use specified doctor or fallback
            if (!empty($input['doctor_id'])) {
                return (int)$input['doctor_id'];
            }
            
            // Fallback to first available doctor
            $firstDoctor = $this->db->table('staff')
                ->select('staff_id')
                ->where('role', 'doctor')
                ->where('status', 'active')
                ->get()
                ->getRowArray();
            return $firstDoctor ? $firstDoctor['staff_id'] : null;
        }

        return null;
    }

    private function getValidationRules($userRole)
    {
        $baseRules = [
            'patient_id' => 'required|numeric',
            'appointment_date' => 'required|valid_date',
            'appointment_type' => 'required|in_list[Consultation,Follow-up,Check-up]',
        ];

        if ($userRole === 'doctor') {
            // Doctor-specific validation (from Appointments controller)
            $baseRules['date'] = 'required|valid_date';
            $baseRules['time'] = 'required';
            $baseRules['type'] = 'required|in_list[Consultation,Follow-up,Check-up,Emergency]';
            $baseRules['duration'] = 'required|numeric|greater_than[0]';
            unset($baseRules['appointment_date'], $baseRules['appointment_time']);
        } else {
            // Receptionist/Admin validation for unified modal
            $baseRules['doctor_id'] = 'required|numeric';
        }

        return $baseRules;
    }

    private function prepareAppointmentData($input, $doctorId, $userRole)
    {
        $baseData = [
            'patient_id' => $input['patient_id'],
            'doctor_id' => $doctorId,
            'status' => 'scheduled',
            'created_at' => date('Y-m-d H:i:s')
        ];

        if ($userRole === 'doctor') {
            // Doctor format (from Appointments controller)
            $baseData['appointment_date'] = $input['date'];
            $baseData['appointment_time'] = $input['time'];
            $baseData['appointment_type'] = $input['type'];
            $baseData['reason'] = $input['reason'] ?? null;
            $baseData['duration'] = $input['duration'];
        } else {
            // Receptionist/Admin format for unified modal
            $baseData['appointment_date'] = $input['appointment_date'];
            $baseData['appointment_time'] = '09:00:00';
            $baseData['appointment_type'] = $input['appointment_type'];
            $baseData['reason'] = $input['notes'] ?? null;
            $baseData['duration'] = 30;
        }

        return $baseData;
    }
}