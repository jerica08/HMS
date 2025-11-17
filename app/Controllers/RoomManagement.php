<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\RoomService;

class RoomManagement extends BaseController
{
    protected RoomService $roomService;
    protected \CodeIgniter\Database\ConnectionInterface $db;

    public function __construct()
    {
        $this->roomService = new RoomService();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $roomStats = $this->roomService->getRoomStats();
        $roomTypes = $this->db->table('room_type')
            ->select('room_type_id, type_name')
            ->orderBy('type_name', 'ASC')
            ->get()
            ->getResultArray();

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
        if ($this->request->getMethod() !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['status' => 'error', 'message' => 'Method not allowed']);
        }

        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        $result = $this->roomService->createRoom($input);

        $statusCode = $result['success'] ? 200 : 400;
        return $this->response->setStatusCode($statusCode)->setJSON($result);
    }
}
