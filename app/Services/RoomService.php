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
        return [
            'total_rooms' => (int) $this->db->table('room')->countAllResults(),
            'occupied_rooms' => (int) $this->db->table('room')->where('status', 'occupied')->countAllResults(),
            'available_rooms' => (int) $this->db->table('room')->where('status', 'available')->countAllResults(),
            'maintenance_rooms' => (int) $this->db->table('room')->where('status', 'maintenance')->countAllResults(),
        ];
    }

    public function getRooms(): array
    {
        return $this->db->table('room r')
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
                'rt.type_name',
                'd.name as department_name',
            ])
            ->join('room_type rt', 'rt.room_type_id = r.room_type_id', 'left')
            ->join('department d', 'd.department_id = r.department_id', 'left')
            ->orderBy('r.room_number', 'ASC')
            ->get()
            ->getResultArray();
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
