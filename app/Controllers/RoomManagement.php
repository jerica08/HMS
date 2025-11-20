<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\RoomService;
use CodeIgniter\Database\ConnectionInterface;

class RoomManagement extends BaseController
{
    protected RoomService $roomService;
    protected ConnectionInterface $db;

    public function __construct()
    {
        $this->roomService = new RoomService();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $roomStats = $this->roomService->getRoomStats();
        $roomTypes = [];
        if ($this->db->tableExists('room_type')) {
            $roomTypes = $this->db->table('room_type')
                ->select('room_type_id, type_name')
                ->orderBy('type_name', 'ASC')
                ->get()
                ->getResultArray();
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
            'roomTypeMetadata' => [],
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
        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON($this->appendCsrfHash($result));
    }

    public function updateRoom(int $roomId)
    {
        if (! $this->request->is('post')) {
            return $this->response->setStatusCode(405)->setJSON($this->appendCsrfHash([
                'success' => false,
                'message' => 'Method not allowed',
            ]));
        }

        $input = $this->request->getPost();
        if (empty($input)) {
            $jsonBody = $this->request->getJSON(true);
            $input = is_array($jsonBody) ? $jsonBody : [];
        }

        $result = $this->roomService->updateRoom($roomId, $input);
        $statusCode = $result['success'] ? 200 : 400;

        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON($this->appendCsrfHash($result));
    }

    public function deleteRoom(int $roomId)
    {
        if (! $this->request->is('post')) {
            return $this->response->setStatusCode(405)->setJSON($this->appendCsrfHash([
                'success' => false,
                'message' => 'Method not allowed',
            ]));
        }

        $input = $this->request->getPost();
        if (empty($input)) {
            $jsonBody = $this->request->getJSON(true);
            $input = is_array($jsonBody) ? $jsonBody : [];
        }

        $tokenName = csrf_token();
        if (! isset($input[$tokenName]) || $input[$tokenName] !== csrf_hash()) {
            return $this->response->setStatusCode(403)->setJSON($this->appendCsrfHash([
                'success' => false,
                'message' => 'Invalid CSRF token',
            ]));
        }

        $result = $this->roomService->deleteRoom($roomId);
        $statusCode = $result['success'] ? 200 : 400;

        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON($this->appendCsrfHash($result));
    }


    private function appendCsrfHash(array $payload): array
    {
        $payload['csrf_hash'] = csrf_hash();
        return $payload;
    }
}
