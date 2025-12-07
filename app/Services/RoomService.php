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
        if (!$this->db->tableExists('room')) {
            return ['total_rooms' => 0, 'occupied_rooms' => 0, 'available_rooms' => 0, 'maintenance_rooms' => 0];
        }

        $builder = $this->db->table('room');
        return [
            'total_rooms' => (int) $builder->countAllResults(),
            'occupied_rooms' => (int) (clone $builder)->where('status', 'occupied')->countAllResults(),
            'available_rooms' => (int) (clone $builder)->where('status', 'available')->countAllResults(),
            'maintenance_rooms' => (int) (clone $builder)->where('status', 'maintenance')->countAllResults(),
        ];
    }

    public function getRooms(): array
    {
        if (! $this->db->tableExists('room')) {
            return [];
        }

        $builder = $this->db->table('room r')
            ->select([
                'r.room_id', 'r.room_number', 'r.room_type', 'r.room_type_id',
                'r.floor_number', 'r.department_id', 'r.accommodation_type',
                'r.status', 'r.bed_capacity', 'r.bed_names',
            ])
            ->orderBy('r.room_number', 'ASC');

        if ($this->db->tableExists('room_type')) {
            $builder->select('rt.type_name')->join('room_type rt', 'rt.room_type_id = r.room_type_id', 'left');
        }
        if ($this->db->tableExists('department')) {
            $builder->select('d.name as department_name')->join('department d', 'd.department_id = r.department_id', 'left');
        }

        return $builder->get()->getResultArray();
    }

    public function createRoom(array $input): array
    {
        $builder = $this->db->table('room');

        try {
            $roomTypeId = $this->resolveRoomTypeId($input);
            $data       = $this->mapRoomPayload($input, $roomTypeId);

            $this->db->transStart();

            $builder->insert($data);
            $roomId = (int) $this->db->insertID();

            if ($roomId > 0) {
                $this->syncBedsForRoom($roomId, $data['bed_capacity'] ?? 0, $data['bed_names'] ?? null);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Failed to create room transaction');
            }

            return ['success' => true, 'message' => 'Room added successfully', 'id' => $roomId];
        } catch (\Throwable $e) {
            log_message('error', 'RoomService::createRoom failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not create room: ' . $e->getMessage()];
        }
    }

    public function dischargeRoom(int $roomId, ?int $staffId = null): array
    {
        if ($roomId <= 0) {
            return ['success' => false, 'message' => 'Invalid room ID'];
        }

        if (!$this->db->tableExists('room') || !$this->db->tableExists('room_assignment')) {
            return ['success' => false, 'message' => 'Room or room_assignment table is missing'];
        }

        $assignment = $this->db->table('room_assignment')
            ->where('room_id', $roomId)
            ->where('status', 'active')
            ->orderBy('assignment_id', 'DESC')
            ->get()
            ->getRowArray();

        if (!$assignment) {
            return ['success' => false, 'message' => 'No active room assignment found for this room'];
        }

        $now = new \DateTime();
        try {
            $dateIn = new \DateTime($assignment['date_in']);
        } catch (\Throwable $e) {
            $dateIn = clone $now;
        }

        $interval = $dateIn->diff($now);
        $totalDays = max(1, (int) $interval->days);
        $totalHours = $totalDays * 24 + (int) $interval->h + (int) floor($interval->i / 60);

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
                'success' => true,
                'message' => 'Room discharged successfully',
                'assignment_id' => (int) $assignment['assignment_id'],
                'patient_id' => (int) ($assignment['patient_id'] ?? 0),
                'admission_id' => isset($assignment['admission_id']) ? (int) $assignment['admission_id'] : null,
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'RoomService::dischargeRoom failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not discharge room: ' . $e->getMessage()];
        }
    }

    public function assignRoomToPatient(int $roomId, int $patientId, ?int $assignedByStaffId = null, ?int $admissionId = null): array
    {
        if ($roomId <= 0 || $patientId <= 0) {
            return ['success' => false, 'message' => 'Invalid room or patient ID'];
        }

        if (!$this->db->tableExists('room') || !$this->db->tableExists('patients')) {
            return ['success' => false, 'message' => 'Room or patients table is missing'];
        }

        if (!$this->db->tableExists('room_assignment')) {
            return ['success' => false, 'message' => 'Room assignment table is missing'];
        }

        $room = $this->db->table('room')
            ->where('room_id', $roomId)
            ->get()
            ->getRowArray();

        if (!$room) {
            return ['success' => false, 'message' => 'Room not found'];
        }

        if (($room['status'] ?? '') === 'occupied') {
            return ['success' => false, 'message' => 'Room is already occupied'];
        }

        $patient = $this->db->table('patients')->where('patient_id', $patientId)->get()->getRowArray();

        if (!$patient) {
            return ['success' => false, 'message' => 'Patient not found'];
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
            'room_rate_at_time' => null,
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

            return ['success' => true, 'message' => 'Room assigned successfully'];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'RoomService::assignRoomToPatient failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not assign room: ' . $e->getMessage()];
        }
    }

    public function updateRoom(int $roomId, array $input): array
    {
        if ($roomId <= 0) {
            return ['success' => false, 'message' => 'Invalid room ID provided'];
        }

        try {
            $roomTypeId = $this->resolveRoomTypeId($input);
            $data       = $this->mapRoomPayload($input, $roomTypeId);

            $this->db->transStart();

            $updated = $this->db->table('room')
                ->where('room_id', $roomId)
                ->update($data);

            if ($updated) {
                $this->syncBedsForRoom($roomId, $data['bed_capacity'] ?? 0, $data['bed_names'] ?? null);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false || ! $updated) {
                throw new \RuntimeException('Failed to update room and beds');
            }

            return ['success' => true, 'message' => 'Room updated successfully'];
        } catch (\Throwable $e) {
            log_message('error', 'RoomService::updateRoom failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not update room: ' . $e->getMessage()];
        }
    }

    public function deleteRoom(int $roomId): array
    {
        if ($roomId <= 0) {
            return ['success' => false, 'message' => 'Invalid room ID provided'];
        }

        try {
            $this->db->table('room')->where('room_id', $roomId)->delete();
            $deleted = $this->db->affectedRows() > 0;

            return $deleted
                ? ['success' => true, 'message' => 'Room deleted successfully']
                : ['success' => false, 'message' => 'Room not found or already deleted'];
        } catch (\Throwable $e) {
            log_message('error', 'RoomService::deleteRoom failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not delete room: ' . $e->getMessage()];
        }
    }

    private function mapRoomPayload(array $input, ?int $roomTypeId = null): array
    {
        $rawBedNames = $input['bed_names'] ?? [];
        $normalizedBedNames = [];

        if (is_array($rawBedNames)) {
            foreach ($rawBedNames as $name) {
                $name = trim((string) $name);
                if ($name !== '') {
                    $normalizedBedNames[] = $name;
                }
            }
        } elseif (is_string($rawBedNames) && $rawBedNames !== '') {
            // Fallback if a single string was submitted instead of an array
            $normalizedBedNames[] = trim($rawBedNames);
        }

        $bedNamesValue = $normalizedBedNames ? json_encode($normalizedBedNames) : null;

        return [
            'room_number' => trim($input['room_number'] ?? ''),
            'room_type' => trim($input['room_name'] ?? ''),
            'room_type_id' => $roomTypeId,
            'floor_number' => trim($input['floor_number'] ?? ''),
            'department_id' => !empty($input['department_id']) ? (int) $input['department_id'] : null,
            'accommodation_type' => $this->sanitizeString($input['accommodation_type'] ?? null),
            'bed_capacity' => !empty($input['bed_capacity']) ? (int) $input['bed_capacity'] : 1,
            'bed_names' => $bedNamesValue,
            'status' => $input['status'] ?? 'available',
        ];
    }

    /**
     * Normalize beds for a given room into the `bed` table based on capacity and names.
     */
    private function syncBedsForRoom(int $roomId, int $capacity, ?string $bedNamesJson = null): void
    {
        if ($roomId <= 0 || ! $this->db->tableExists('bed')) {
            return;
        }

        $capacity = max(1, (int) $capacity);

        $bedNames = [];
        if ($bedNamesJson) {
            $decoded = json_decode($bedNamesJson, true) ?? [];
            if (is_array($decoded)) {
                $bedNames = array_values(array_filter(array_map('strval', $decoded), static fn($n) => trim($n) !== ''));
            }
        }

        // Remove existing beds for this room and recreate based on current definition.
        $bedTable = $this->db->table('bed');
        $bedTable->where('room_id', $roomId)->delete();

        $rows = [];
        for ($i = 0; $i < $capacity; $i++) {
            $label = $bedNames[$i] ?? 'Bed ' . ($i + 1);
            $rows[] = [
                'room_id'            => $roomId,
                'bed_number'         => $label,
                'status'             => 'available',
                'assigned_patient_id'=> null,
            ];
        }

        if (! empty($rows)) {
            $bedTable->insertBatch($rows);
        }
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

        $roomTypeTable->insert($this->buildRoomTypePayload($customType, $input));
        return (int) $this->db->insertID();
    }

    private function buildRoomTypePayload(string $typeName, array $input): array
    {
        $notes = trim($input['notes'] ?? '');
        $accommodationType = $this->sanitizeString($input['accommodation_type'] ?? null, 100);

        return [
            'type_name' => $typeName,
            'description' => $notes ?: null,
            'accommodation_type' => $accommodationType,
        ];
    }

    private function sanitizeDecimal($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return number_format((float) $value, 2, '.', '');
    }

    private function sanitizeString(?string $value, ?int $maxLength = null): ?string
    {
        if ($value === null) {
            return null;
        }

        $clean = trim(strip_tags($value));
        if ($clean === '') {
            return null;
        }

        if ($maxLength !== null) {
            $clean = mb_substr($clean, 0, $maxLength);
        }

        return $clean;
    }
}
