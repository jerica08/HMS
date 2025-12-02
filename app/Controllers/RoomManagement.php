<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\RoomService;
use App\Services\FinancialService;
use CodeIgniter\Database\ConnectionInterface;

class RoomManagement extends BaseController
{
    protected RoomService $roomService;
    protected FinancialService $financialService;
    protected ConnectionInterface $db;

    public function __construct()
    {
        $this->roomService = new RoomService();
        $this->financialService = new FinancialService();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $roomStats = $this->roomService->getRoomStats();
        $roomTypes = [];
        if ($this->db->tableExists('room_type')) {
            $roomTypes = $this->db->table('room_type')
                ->select('room_type_id, type_name, accommodation_type')
                ->orderBy('type_name', 'ASC')
                ->get()
                ->getResultArray();
        }

        $departments = [];
        if ($this->db->tableExists('department')) {
            $departments = $this->db->table('department')
                ->select('department_id, name, floor')
                ->orderBy('name', 'ASC')
                ->get()
                ->getResultArray();
        }

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

    public function getPatientsAPI()
    {
        $db = $this->db;

        $tableName = null;
        if ($db->tableExists('patients')) {
            $tableName = 'patients';
        } elseif ($db->tableExists('patient')) {
            $tableName = 'patient';
        }

        if ($tableName === null) {
            return $this->response->setJSON([
                'status' => 'success',
                'data'   => [],
            ]);
        }

        $patients = $db->table($tableName)
            ->select('patient_id, first_name, last_name, CONCAT(first_name, " ", last_name) AS full_name')
            ->orderBy('first_name', 'ASC')
            ->orderBy('last_name', 'ASC')
            ->limit(200)
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $patients,
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

    public function dischargeRoom()
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

        $roomId  = (int) ($input['room_id'] ?? 0);
        $staffId = (int) (session()->get('staff_id') ?? 0);

        $dischargeResult = $this->roomService->dischargeRoom($roomId, $staffId);
        if (! $dischargeResult['success']) {
            $statusCode = 400;
            return $this->response
                ->setStatusCode($statusCode)
                ->setJSON($this->appendCsrfHash($dischargeResult));
        }

        $assignmentId = (int) ($dischargeResult['assignment_id'] ?? 0);
        $patientId    = (int) ($dischargeResult['patient_id'] ?? 0);
        $admissionId  = $dischargeResult['admission_id'] ?? null;

        $billingMessage = null;
        if ($assignmentId > 0 && $patientId > 0) {
            $account = $this->financialService->getOrCreateBillingAccountForPatient($patientId, $admissionId, $staffId);

            if ($account && ! empty($account['billing_id'])) {
                $billingId = (int) $account['billing_id'];
                $billingResult = $this->financialService->addItemFromRoomAssignment($billingId, $assignmentId, null, $staffId);

                if (! empty($billingResult['success'])) {
                    $billingMessage = 'Room stay added to billing account.';
                } else {
                    $billingMessage = $billingResult['message'] ?? 'Room discharged but could not add to billing.';
                }
            } else {
                $billingMessage = 'Room discharged but no billing account could be created.';
            }
        }

        $payload = $dischargeResult;
        if ($billingMessage) {
            $payload['billing_message'] = $billingMessage;
        }

        $statusCode = 200;
        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON($this->appendCsrfHash($payload));
    }

    public function assignRoom()
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

        $roomId    = (int) ($input['room_id'] ?? 0);
        $patientId = (int) ($input['patient_id'] ?? 0);

        $staffId = (int) (session()->get('staff_id') ?? 0);

        $result = $this->roomService->assignRoomToPatient($roomId, $patientId, $staffId, null);
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
