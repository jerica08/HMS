<?php

namespace App\Services;

use CodeIgniter\Database\ConnectionInterface;

class RoomService
{
    protected ConnectionInterface $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function getRoomStats(): array
    {
        if (! $this->db->tableExists('room')) {
            return [
                'total_rooms' => 0,
                'occupied_rooms' => 0,
                'available_rooms' => 0,
                'maintenance_rooms' => 0,
            ];
        }

        $builder = $this->db->table('room');
        return [
            'total_rooms' => (int) $builder->countAllResults(),
            'occupied_rooms' => (int) $builder->where('status', 'occupied')->countAllResults(),
            'available_rooms' => (int) $builder->where('status', 'available')->countAllResults(),
            'maintenance_rooms' => (int) $builder->where('status', 'maintenance')->countAllResults(),
        ];
    }

    public function getRooms(): array
    {
        if (! $this->db->tableExists('room')) {
            return [];
        }

        $builder = $this->db->table('room r')
            ->select([
                'r.room_id',
                'r.room_number',
                'r.room_name',
                'r.room_type_id',
                'r.floor_number',
                'r.department_id',
                'r.status',
                'r.bed_capacity',
                'r.rate_range',
                'r.hourly_rate',
                'r.extra_person_charge',
                'r.overtime_charge_per_hour',
            ])
            ->orderBy('r.room_number', 'ASC');

        if ($this->db->tableExists('room_type')) {
            $builder->select('rt.type_name')
                ->join('room_type rt', 'rt.room_type_id = r.room_type_id', 'left');
        }

        if ($this->db->tableExists('department')) {
            $builder->select('d.name as department_name')
                ->join('department d', 'd.department_id = r.department_id', 'left');
        }

        return $builder->get()->getResultArray();
    }

    public function createRoom(array $input): array
    {
        $builder = $this->db->table('room');

        try {
            $roomTypeId = $this->resolveRoomTypeId($input);
            $data = $this->mapRoomPayload($input, $roomTypeId);
        } catch (\Throwable $e) {
            log_message('error', 'RoomService::createRoom resolveRoomTypeId failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Could not create room type: ' . $e->getMessage(),
            ];
        }

        try {
            $builder->insert($data);

            return [
                'success' => true,
                'message' => 'Room added successfully',
                'id' => $this->db->insertID(),
            ];
        } catch (\Throwable $e) {
            log_message('error', 'RoomService::createRoom failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Could not create room: ' . $e->getMessage(),
            ];
        }
    }

    public function dischargeRoom(int $roomId, ?int $staffId = null): array
    {
        if ($roomId <= 0) {
            return [
                'success' => false,
                'message' => 'Invalid room ID',
            ];
        }

        if (! $this->db->tableExists('room') || ! $this->db->tableExists('room_assignment')) {
            return [
                'success' => false,
                'message' => 'Room or room_assignment table is missing',
            ];
        }

        $assignment = $this->db->table('room_assignment')
            ->where('room_id', $roomId)
            ->where('status', 'active')
            ->orderBy('assignment_id', 'DESC')
            ->get()
            ->getRowArray();

        if (! $assignment) {
            return [
                'success' => false,
                'message' => 'No active room assignment found for this room',
            ];
        }

        $now = new \DateTime();
        try {
            $dateIn = new \DateTime($assignment['date_in']);
        } catch (\Throwable $e) {
            $dateIn = clone $now;
        }

        $interval   = $dateIn->diff($now);
        $totalDays  = (int) $interval->days;
        $totalHours = $totalDays * 24 + (int) $interval->h + (int) floor($interval->i / 60);

        if ($totalDays <= 0 && $totalHours > 0) {
            $totalDays = 1;
        } elseif ($totalDays <= 0) {
            $totalDays = 1;
        }

        $updatePayload = [
            'date_out'    => $now->format('Y-m-d H:i:s'),
            'total_days'  => $totalDays,
            'total_hours' => $totalHours,
            'status'      => 'completed',
        ];

        if ($this->db->fieldExists('updated_at', 'room_assignment')) {
            $updatePayload['updated_at'] = $now->format('Y-m-d H:i:s');
        }

        try {
            $this->db->transStart();

            $this->db->table('room_assignment')
                ->where('assignment_id', $assignment['assignment_id'])
                ->update($updatePayload);

            $this->db->table('room')
                ->where('room_id', $roomId)
                ->update(['status' => 'available']);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Failed to discharge room in transaction');
            }

            return [
                'success'        => true,
                'message'        => 'Room discharged successfully',
                'assignment_id'  => (int) $assignment['assignment_id'],
                'patient_id'     => (int) ($assignment['patient_id'] ?? 0),
                'admission_id'   => isset($assignment['admission_id']) ? (int) $assignment['admission_id'] : null,
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'RoomService::dischargeRoom failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Could not discharge room: ' . $e->getMessage(),
            ];
        }
    }

    public function assignRoomToPatient(int $roomId, int $patientId, ?int $assignedByStaffId = null, ?int $admissionId = null): array
    {
        if ($roomId <= 0 || $patientId <= 0) {
            return [
                'success' => false,
                'message' => 'Invalid room or patient ID',
            ];
        }

        if (! $this->db->tableExists('room') || ! $this->db->tableExists('patients')) {
            return [
                'success' => false,
                'message' => 'Room or patients table is missing',
            ];
        }

        if (! $this->db->tableExists('room_assignment')) {
            return [
                'success' => false,
                'message' => 'Room assignment table is missing',
            ];
        }

        $room = $this->db->table('room')
            ->where('room_id', $roomId)
            ->get()
            ->getRowArray();

        if (! $room) {
            return [
                'success' => false,
                'message' => 'Room not found',
            ];
        }

        if (! empty($room['status']) && $room['status'] === 'occupied') {
            return [
                'success' => false,
                'message' => 'Room is already occupied',
            ];
        }

        $patient = $this->db->table('patients')
            ->where('patient_id', $patientId)
            ->get()
            ->getRowArray();

        if (! $patient) {
            return [
                'success' => false,
                'message' => 'Patient not found',
            ];
        }

        $builder = $this->db->table('room_assignment');

        $payload = [
            'patient_id'      => $patientId,
            'room_id'         => $roomId,
            'bed_id'          => null,
            'admission_id'    => $admissionId,
            'assigned_by'     => $assignedByStaffId,
            'date_in'         => date('Y-m-d H:i:s'),
            'date_out'        => null,
            'total_days'      => 0,
            'total_hours'     => 0,
            'room_rate_at_time' => $room['rate_range'] ?? null,
            'bed_rate_at_time'  => null,
            'status'          => 'active',
        ];

        try {
            $this->db->transStart();

            $builder->insert($payload);

            $this->db->table('room')
                ->where('room_id', $roomId)
                ->update(['status' => 'occupied']);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Failed to assign room in transaction');
            }

            return [
                'success' => true,
                'message' => 'Room assigned successfully',
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'RoomService::assignRoomToPatient failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Could not assign room: ' . $e->getMessage(),
            ];
        }
    }

    public function updateRoom(int $roomId, array $input): array
    {
        if ($roomId <= 0) {
            return [
                'success' => false,
                'message' => 'Invalid room ID provided',
            ];
        }

        try {
            $roomTypeId = $this->resolveRoomTypeId($input);
            $data = $this->mapRoomPayload($input, $roomTypeId);
        } catch (\Throwable $e) {
            log_message('error', 'RoomService::updateRoom resolveRoomTypeId failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Could not update room type: ' . $e->getMessage(),
            ];
        }

        try {
            $updated = $this->db->table('room')
                ->where('room_id', $roomId)
                ->update($data);

            if (! $updated) {
                return [
                    'success' => false,
                    'message' => 'Room was not updated. Please try again.',
                ];
            }

            return [
                'success' => true,
                'message' => 'Room updated successfully',
            ];
        } catch (\Throwable $e) {
            log_message('error', 'RoomService::updateRoom failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Could not update room: ' . $e->getMessage(),
            ];
        }
    }

    public function deleteRoom(int $roomId): array
    {
        if ($roomId <= 0) {
            return [
                'success' => false,
                'message' => 'Invalid room ID provided',
            ];
        }

        try {
            $deleted = $this->db->table('room')
                ->where('room_id', $roomId)
                ->delete();

            if (! $deleted || ! $this->db->affectedRows()) {
                return [
                    'success' => false,
                    'message' => 'Room not found or already deleted',
                ];
            }

            return [
                'success' => true,
                'message' => 'Room deleted successfully',
            ];
        } catch (\Throwable $e) {
            log_message('error', 'RoomService::deleteRoom failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Could not delete room: ' . $e->getMessage(),
            ];
        }
    }

    private function mapRoomPayload(array $input, ?int $roomTypeId = null): array
    {
        return [
            'room_number' => trim($input['room_number'] ?? ''),
            'room_name' => trim($input['room_name'] ?? ''),
            'room_type_id' => $roomTypeId,
            'floor_number' => trim($input['floor_number'] ?? ''),
            'department_id' => !empty($input['department_id']) ? (int) $input['department_id'] : null,
            'bed_capacity' => !empty($input['bed_capacity']) ? (int) $input['bed_capacity'] : 1,
            'status' => $input['status'] ?? 'available',
            'rate_range' => trim($input['rate_range'] ?? ''),
            'hourly_rate' => $this->sanitizeDecimal($input['hourly_rate'] ?? null),
            'extra_person_charge' => $this->sanitizeDecimal($input['extra_person_charge'] ?? 0),
            'overtime_charge_per_hour' => $this->sanitizeDecimal($input['overtime_charge_per_hour'] ?? null),
        ];
    }

    private function resolveRoomTypeId(array $input): ?int
    {
        if (!empty($input['room_type_id'])) {
            return (int) $input['room_type_id'];
        }

        $customType = trim($input['custom_room_type'] ?? '');
        if ($customType === '') {
            return null;
        }

        if (! $this->db->tableExists('room_type')) {
            throw new \RuntimeException('Room type table does not exist.');
        }

        $roomTypeTable = $this->db->table('room_type');
        $existing = $roomTypeTable
            ->select('room_type_id')
            ->where('type_name', $customType)
            ->get()
            ->getRowArray();

        if ($existing) {
            return (int) $existing['room_type_id'];
        }

        $payload = $this->buildRoomTypePayload($customType, $input);

        if ($existing) {
            $roomTypeTable
                ->where('room_type_id', $existing['room_type_id'])
                ->update($payload);

            return (int) $existing['room_type_id'];
        }

        $roomTypeTable->insert($payload);

        return (int) $this->db->insertID();
    }

    private function buildRoomTypePayload(string $typeName, array $input): array
    {
        $dailyRate = $this->sanitizeDecimal($input['rate_range'] ?? null);
        $hourlyRate = $this->sanitizeDecimal($input['hourly_rate'] ?? null);
        $notes = trim($input['notes'] ?? '');

        return [
            'type_name' => $typeName,
            'description' => $notes ?: null,
            'base_daily_rate' => $dailyRate ?? 0,
            'base_hourly_rate' => $hourlyRate,
            'additional_facility_charge' => null,
        ];
    }

    private function sanitizeDecimal($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return number_format((float) $value, 2, '.', '');
    }
}
