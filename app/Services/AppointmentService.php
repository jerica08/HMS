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
        $doctorId = $this->determineDoctorId($input, $userRole, $staffId);
        if (!$doctorId) {
            return ['success' => false, 'message' => 'No doctor available for assignment'];
        }

        $validation = \Config\Services::validation();
        $validation->setRules($this->getValidationRules($userRole));
        if (!$validation->run($input)) {
            return ['success' => false, 'message' => 'Validation failed', 'errors' => $validation->getErrors()];
        }

        $data = $this->prepareAppointmentData($input, $doctorId, $userRole);
        $appointmentDate = $data['appointment_date'] ?? null;
        if (!$appointmentDate || ($timestamp = strtotime($appointmentDate)) === false) {
            return ['success' => false, 'message' => 'Invalid appointment date'];
        }

        $weekdayName = date('N', $timestamp);
        if (!$this->db->table('staff_schedule')->where('staff_id', $doctorId)->where('weekday', $weekdayName)->where('status', 'active')->countAllResults()) {
            return ['success' => false, 'message' => 'Selected doctor has no shift on this day'];
        }

        if ($this->db->table('appointments')->where('doctor_id', $doctorId)->where('appointment_date', $appointmentDate)->whereIn('status', ['scheduled', 'in-progress'])->countAllResults() > 0) {
            return ['success' => false, 'message' => 'Doctor already has an active appointment on this date'];
        }

        try {
            $this->db->table('appointments')->insert($data);
            $insertId = $this->db->insertID();
            return [
                'success' => true,
                'message' => 'Appointment scheduled successfully',
                'id' => $insertId,
                'appointment_id' => 'APT-' . date('Ymd') . '-' . str_pad($insertId, 4, '0', STR_PAD_LEFT)
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Failed to create appointment: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Get appointments with optional filtering
     */
    public function getAppointments($filters = [])
    {
        try {
            $builder = $this->buildAppointmentQuery();
            foreach (['doctor_id', 'date', 'status', 'patient_id'] as $key) {
                if (isset($filters[$key])) {
                    $builder->where('a.' . ($key === 'date' ? 'appointment_date' : ($key === 'patient_id' ? 'patient_id' : $key)), $filters[$key]);
                }
            }
            $appointments = $builder->orderBy('a.appointment_date', 'DESC')->orderBy('a.appointment_time', 'DESC')->get()->getResultArray();
            return ['success' => true, 'data' => $appointments];
        } catch (\Throwable $e) {
            log_message('error', 'Error fetching appointments: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to fetch appointments', 'data' => []];
        }
    }

    /**
     * Get single appointment by ID (DB primary key `id`, aliased as appointment_id)
     */
    public function getAppointment($id)
    {
        try {
            $appointment = $this->buildAppointmentQuery()->select('a.id as appointment_id')->where('a.id', $id)->get()->getRowArray();
            if (!$appointment) {
                return ['success' => false, 'message' => 'Appointment not found'];
            }
            return ['success' => true, 'appointment' => $appointment];
        } catch (\Throwable $e) {
            log_message('error', 'Error fetching appointment: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Database error'];
        }
    }

    /**
     * Update appointment status (DB primary key `id`)
     */
    public function updateAppointmentStatus($id, $status, $userRole = null, $staffId = null)
    {
        if (!in_array($status, ['scheduled', 'in-progress', 'completed', 'cancelled', 'no-show'], true)) {
            return ['success' => false, 'message' => 'Invalid status value'];
        }

        try {
            $builder = $this->db->table('appointments')->where('id', $id);
            if ($userRole === 'doctor' && $staffId) {
                $builder->where('doctor_id', $staffId);
            }
            $updated = $builder->update(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
            return $updated && $this->db->affectedRows() > 0
                ? ['success' => true, 'message' => 'Appointment status updated successfully']
                : ['success' => false, 'message' => 'Appointment not found or no permission to update'];
        } catch (\Throwable $e) {
            log_message('error', 'Failed to update appointment status: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Delete appointment (DB primary key `id`)
     */
    public function deleteAppointment($id, $userRole = null, $staffId = null)
    {
        try {
            $builder = $this->db->table('appointments')->where('id', $id);
            if ($userRole === 'doctor' && $staffId) {
                $builder->where('doctor_id', $staffId);
            }
            $deleted = $builder->delete();
            return $deleted && $this->db->affectedRows() > 0
                ? ['success' => true, 'message' => 'Appointment deleted successfully']
                : ['success' => false, 'message' => 'Appointment not found or no permission to delete'];
        } catch (\Throwable $e) {
            log_message('error', 'Failed to delete appointment: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }


    private function determineDoctorId($input, $userRole, $staffId)
    {
        if ($userRole === 'doctor') return $staffId;
        if (in_array($userRole, ['receptionist', 'admin'])) {
            if (!empty($input['doctor_id'])) return (int)$input['doctor_id'];
            $firstDoctor = $this->db->table('staff')->select('staff_id')->where('role', 'doctor')->where('status', 'active')->get()->getRowArray();
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
        $baseData = ['patient_id' => $input['patient_id'], 'doctor_id' => $doctorId, 'status' => 'scheduled', 'created_at' => date('Y-m-d H:i:s')];
        if ($userRole === 'doctor') {
            $baseData['appointment_date'] = $input['date'];
            $baseData['appointment_time'] = $input['time'];
            $baseData['appointment_type'] = $input['type'];
            $baseData['reason'] = $input['reason'] ?? null;
            $baseData['duration'] = $input['duration'];
        } else {
            $baseData['appointment_date'] = $input['appointment_date'];
            $baseData['appointment_time'] = '09:00:00';
            $baseData['appointment_type'] = $input['appointment_type'];
            $baseData['reason'] = $input['notes'] ?? null;
            $baseData['duration'] = 30;
        }
        return $baseData;
    }

    private function buildAppointmentQuery()
    {
        return $this->db->table('appointments a')
            ->select('a.*, p.patient_id, p.first_name as patient_first_name, p.last_name as patient_last_name, p.email as patient_email, p.date_of_birth, TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) as patient_age, CONCAT(p.first_name, " ", p.last_name) as patient_full_name, s.staff_id as doctor_id, s.first_name as doctor_first_name, s.last_name as doctor_last_name, CONCAT(s.first_name, " ", s.last_name) as doctor_name, DATE_FORMAT(a.appointment_date, "%W, %M %d, %Y") as formatted_date, TIME_FORMAT(a.appointment_time, "%h:%i %p") as formatted_time')
            ->join('patients p', 'p.patient_id = a.patient_id', 'left')
            ->join('staff s', 's.staff_id = a.doctor_id', 'left');
    }
}