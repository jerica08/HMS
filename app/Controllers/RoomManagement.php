<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\RoomService;
use CodeIgniter\Database\ConnectionInterface;

class RoomManagement extends BaseController
{
    protected RoomService $roomService;
    protected ConnectionInterface $db;
    private array $defaultRoomTypes = [];
    private array $defaultRoomTypeMetadata = [
        'Emergency Bed' => [
            'floor_label' => 'G/F',
            'classification' => 'Emergency',
            'rate_range' => '0–500',
            'notes' => 'Short stay only',
        ],
        'Trauma / Resuscitation Room' => [
            'floor_label' => 'G/F',
            'classification' => 'Critical',
            'rate_range' => '0–1,000',
            'notes' => 'High equipment',
        ],
        'Negative Pressure Isolation Room' => [
            'floor_label' => '7/F',
            'classification' => 'Infectious (airborne)',
            'rate_range' => '3,000–6,000',
            'notes' => 'TB, COVID, measles',
        ],
        'General Isolation Room' => [
            'floor_label' => '7/F',
            'classification' => 'Infectious',
            'rate_range' => '2,000–4,000',
            'notes' => 'Influenza, diarrhea',
        ],
        'ICU' => [
            'floor_label' => '2/F',
            'classification' => 'Critical',
            'rate_range' => '7,000–15,000',
            'notes' => '1:1 nursing',
        ],
        'CCU (Cardiac Care Unit)' => [
            'floor_label' => '2/F',
            'classification' => 'Critical/Cardiac',
            'rate_range' => '7,500–15,000',
            'notes' => 'Heart cases',
        ],
        'SICU (Surgical ICU)' => [
            'floor_label' => '2/F',
            'classification' => 'Post-surgical critical',
            'rate_range' => '8,000–16,000',
            'notes' => 'After major surgery',
        ],
        'MICU (Medical ICU)' => [
            'floor_label' => '2/F',
            'classification' => 'Critical medical',
            'rate_range' => '7,000–14,000',
            'notes' => 'Organ failure, sepsis',
        ],
        'PICU (Pediatric ICU)' => [
            'floor_label' => '2/F',
            'classification' => 'Pediatric critical',
            'rate_range' => '8,000–18,000',
            'notes' => 'Children',
        ],
        'NICU (Neonatal ICU)' => [
            'floor_label' => '2/F',
            'classification' => 'Newborn critical',
            'rate_range' => '10,000–25,000',
            'notes' => 'Incubators required',
        ],
        'HDU (High-Dependency Unit)' => [
            'floor_label' => '2/F',
            'classification' => 'Semi-critical',
            'rate_range' => '4,000–7,000',
            'notes' => 'Close monitoring',
        ],
        'Private Room' => [
            'floor_label' => '4/F',
            'classification' => 'Stable',
            'rate_range' => '2,000–4,000',
            'notes' => '1 bed',
        ],
        'Semi-Private Room' => [
            'floor_label' => '4/F',
            'classification' => 'Stable',
            'rate_range' => '1,200–2,000',
            'notes' => '2 beds',
        ],
        'Ward Room' => [
            'floor_label' => '4/F',
            'classification' => 'Stable',
            'rate_range' => '500–900',
            'notes' => '4–10 beds',
        ],
        'Maternity Room' => [
            'floor_label' => '5/F',
            'classification' => 'Pregnant (stable)',
            'rate_range' => '2,000–3,000',
            'notes' => 'Pre-delivery',
        ],
        'Labor Room' => [
            'floor_label' => '5/F',
            'classification' => 'Pregnant (active labor)',
            'rate_range' => '1,500–3,000',
            'notes' => 'Short stay',
        ],
        'Delivery Room' => [
            'floor_label' => '5/F',
            'classification' => 'Delivery',
            'rate_range' => '3,000–6,000',
            'notes' => 'Birth',
        ],
        'Post-Partum Room' => [
            'floor_label' => '5/F',
            'classification' => 'After delivery',
            'rate_range' => '1,500–2,500',
            'notes' => 'Mother recovery',
        ],
        'Pediatric Private Room' => [
            'floor_label' => '6/F',
            'classification' => 'Kids stable',
            'rate_range' => '2,000–3,500',
            'notes' => '1 bed',
        ],
        'Pediatric Semi-Private Room' => [
            'floor_label' => '6/F',
            'classification' => 'Kids stable',
            'rate_range' => '1,200–2,000',
            'notes' => '2 beds',
        ],
        'Pediatric Ward' => [
            'floor_label' => '6/F',
            'classification' => 'Kids stable',
            'rate_range' => '500–900',
            'notes' => 'Multiple beds',
        ],
        'Rehabilitation Room' => [
            'floor_label' => '8/F',
            'classification' => 'Rehab/Stroke',
            'rate_range' => '1,000–2,000',
            'notes' => 'PT/OT stay',
        ],
        'Long-Term Care Room' => [
            'floor_label' => '8/F',
            'classification' => 'Long-term patients',
            'rate_range' => '1,000–2,500',
            'notes' => 'Long recovery',
        ],
        'Palliative Care Room' => [
            'floor_label' => '7/F',
            'classification' => 'Terminal care',
            'rate_range' => '1,000–2,500',
            'notes' => 'Quiet area',
        ],
    ];

    public function __construct()
    {
        $this->roomService = new RoomService();
        $this->db = \Config\Database::connect();
        $this->defaultRoomTypes = array_keys($this->defaultRoomTypeMetadata);
    }

    public function index()
    {
        $this->ensureDefaultRoomTypes();
        $roomStats = $this->roomService->getRoomStats();
        if ($this->db->tableExists('room_type')) {
            $roomTypeRecords = $this->db->table('room_type')
                ->select('room_type_id, type_name')
                ->orderBy('type_name', 'ASC')
                ->get()
                ->getResultArray();
        } else {
            $roomTypeRecords = [];
        }

        $roomTypes = [];
        $roomTypeMap = [];
        foreach ($roomTypeRecords as $record) {
            $roomTypeMap[$record['type_name']] = $record;
        }

        foreach ($this->defaultRoomTypes as $typeName) {
            if (isset($roomTypeMap[$typeName])) {
                $roomTypes[] = $roomTypeMap[$typeName];
                unset($roomTypeMap[$typeName]);
            }
        }

        foreach ($roomTypeMap as $additionalType) {
            $roomTypes[] = $additionalType;
        }

        $departments = $this->db->table('department')
            ->select('department_id, name')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();

        return view('unified/room-management', [
            'title' => 'Room Management',
            'roomStats' => $roomStats,
            'roomTypes' => $roomTypes,
            'departments' => $departments,
            'roomTypeMetadata' => $this->buildRoomTypeMetadata($roomTypes),
        ]);
    }

    public function getRoomsAPI()
    {
        $rooms = $this->roomService->getRooms();
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $rooms,
        ]);
    }

    public function createRoom()
    {
        if (! $this->request->is('post')) {
            return $this->response->setStatusCode(405)->setJSON(['status' => 'error', 'message' => 'Method not allowed']);
        }

        $input = $this->request->getPost();
        if (empty($input)) {
            $jsonBody = $this->request->getJSON(true);
            $input = is_array($jsonBody) ? $jsonBody : [];
        }

        $result = $this->roomService->createRoom($input);

        $statusCode = $result['success'] ? 200 : 400;
        return $this->response->setStatusCode($statusCode)->setJSON($result);
    }

    private function ensureDefaultRoomTypes(): void
    {
        if (! $this->db->tableExists('room_type')) {
            return;
        }

        foreach ($this->defaultRoomTypes as $typeName) {
            $exists = $this->db->table('room_type')
                ->where('type_name', $typeName)
                ->countAllResults();

            if (!$exists) {
                $this->db->table('room_type')->insert([
                    'type_name' => $typeName,
                    'description' => null,
                    'base_daily_rate' => 0,
                    'base_hourly_rate' => null,
                    'additional_facility_charge' => null,
                ]);
            }
        }
    }

    private function buildRoomTypeMetadata(array $roomTypes): array
    {
        $metadata = [];
        foreach ($roomTypes as $type) {
            $name = $type['type_name'] ?? null;
            if (!$name) {
                continue;
            }

            $roomTypeMeta = $this->defaultRoomTypeMetadata[$name] ?? null;
            if (!$roomTypeMeta) {
                continue;
            }

            $metadata[$type['room_type_id']] = [
                'floor_label' => $roomTypeMeta['floor_label'],
                'room_number_template' => $this->buildRoomNumberTemplate($roomTypeMeta['floor_label'], $name),
                'classification' => $roomTypeMeta['classification'] ?? null,
                'rate_range' => $roomTypeMeta['rate_range'] ?? null,
                'notes' => $roomTypeMeta['notes'] ?? null,
            ];
        }

        return $metadata;
    }

    private function buildRoomNumberTemplate(string $floorLabel, string $typeName): string
    {
        $floorCode = str_replace('/', '', $floorLabel);
        if ($floorCode === '') {
            $floorCode = 'RM';
        }

        $words = preg_split('/[^a-zA-Z0-9]+/', $typeName, -1, PREG_SPLIT_NO_EMPTY);
        $initials = array_map(fn ($word) => strtoupper($word[0]), $words);
        $acronym = implode('', $initials);
        if ($acronym === '') {
            $acronym = 'RM';
        }

        return sprintf('%s-%s-01', $floorCode, $acronym);
    }
}
