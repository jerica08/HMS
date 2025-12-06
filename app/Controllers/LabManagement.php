<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\LabService;
use App\Services\FinancialService;

class LabManagement extends BaseController
{
    protected $labService;
    protected $financialService;

    protected $userRole;
    protected $staffId;

    public function __construct()
    {
        $this->labService = new LabService();
        $this->financialService = new FinancialService();
        $this->userRole = session()->get('role');
        $this->staffId = session()->get('staff_id');
    }

    private function isAjaxRequest(): bool
    {
        return $this->request->isAJAX() || strpos($this->request->getHeaderLine('Accept'), 'application/json') !== false;
    }

    private function jsonResponse(array $data, int $statusCode = 200)
    {
        return $this->response->setStatusCode($statusCode)->setJSON($data);
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        return view('unified/lab-management', [
            'title'    => 'Lab Management',
            'userRole' => $this->userRole,
            'stats'    => $this->labService->getLabStats($this->userRole, $this->staffId),
        ]);
    }

    public function getLabOrdersAPI()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->jsonResponse([]);
        }

        $filters = array_filter([
            'status'     => $this->request->getGet('status'),
            'priority'   => $this->request->getGet('priority'),
            'date'       => $this->request->getGet('date'),
            'patient_id' => $this->request->getGet('patient_id'),
            'search'     => $this->request->getGet('search'),
        ]);

        return $this->jsonResponse($this->labService->getLabOrdersByRole($this->userRole, $this->staffId, $filters));
    }

    public function getLabPatientsAPI()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Not authenticated'], 401);
        }

        $db = \Config\Database::connect();
        if (!$db->tableExists('patients')) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Patients table not found', 'data' => []]);
        }

        $patients = $db->table('patients p')
            ->select('p.patient_id, p.first_name, p.last_name, p.date_of_birth')
            ->orderBy('p.first_name', 'ASC')
            ->orderBy('p.last_name', 'ASC')
            ->get()
            ->getResultArray();

        return $this->jsonResponse(['status' => 'success', 'data' => $patients]);
    }

    public function getLabTestsAPI()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Not authenticated'], 401);
        }

        $db = \Config\Database::connect();
        if (!$db->tableExists('lab_tests')) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Lab tests table not found', 'data' => []]);
        }

        $tests = $db->table('lab_tests')
            ->where('status', 'active')
            ->orderBy('category', 'ASC')
            ->orderBy('test_name', 'ASC')
            ->get()
            ->getResultArray();

        return $this->jsonResponse(['status' => 'success', 'data' => $tests]);
    }

    public function createLabTest()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Not authenticated'], 401);
        }

        if (!in_array($this->userRole, ['admin', 'it_staff'], true)) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Permission denied'], 403);
        }

        $db = \Config\Database::connect();
        if (!$db->tableExists('lab_tests')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Lab tests table not found']);
        }

        $input = $this->request->getJSON(true) ?? $this->request->getPost();

        $testCode = trim((string)($input['test_code'] ?? ''));
        $testName = trim((string)($input['test_name'] ?? ''));
        $defaultPrice = isset($input['default_price']) ? (float)$input['default_price'] : 500.00;
        $category = trim((string)($input['category'] ?? ''));
        $status = ($input['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';

        if ($testCode === '' || $testName === '') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Test code and name are required',
            ]);
        }

        try {
            $builder = $db->table('lab_tests');
            $builder->insert([
                'test_code'      => $testCode,
                'test_name'      => $testName,
                'default_price'  => $defaultPrice,
                'category'       => $category ?: null,
                'status'         => $status,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);

            return $this->jsonResponse(['status' => 'success', 'message' => 'Lab test created', 'id' => $db->insertID()]);
        } catch (\Throwable $e) {
            log_message('error', 'LabManagement::createLabTest error: ' . $e->getMessage());
            return $this->jsonResponse(['status' => 'error', 'message' => 'Failed to create lab test'], 500);
        }
    }

    public function updateLabTest($id)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Not authenticated'], 401);
        }

        if (!in_array($this->userRole, ['admin', 'it_staff'], true)) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Permission denied'], 403);
        }

        $db = \Config\Database::connect();
        if (!$db->tableExists('lab_tests')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Lab tests table not found']);
        }

        $input = $this->request->getJSON(true) ?? $this->request->getPost();

        $update = [];
        if (isset($input['test_code'])) {
            $update['test_code'] = trim((string)$input['test_code']);
        }
        if (isset($input['test_name'])) {
            $update['test_name'] = trim((string)$input['test_name']);
        }
        if (isset($input['default_price'])) {
            $update['default_price'] = (float)$input['default_price'];
        }
        if (isset($input['category'])) {
            $update['category'] = trim((string)$input['category']) ?: null;
        }
        if (isset($input['status'])) {
            $update['status'] = $input['status'] === 'inactive' ? 'inactive' : 'active';
        }

        if (empty($update)) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Nothing to update'], 400);
        }

        $update['updated_at'] = date('Y-m-d H:i:s');

        try {
            $db->table('lab_tests')->where('lab_test_id', (int)$id)->update($update);
            return $this->jsonResponse(['status' => 'success', 'message' => 'Lab test updated']);
        } catch (\Throwable $e) {
            log_message('error', 'LabManagement::updateLabTest error: ' . $e->getMessage());
            return $this->jsonResponse(['status' => 'error', 'message' => 'Failed to update lab test'], 500);
        }
    }

    public function deleteLabTest($id)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Not authenticated'], 401);
        }

        if (!in_array($this->userRole, ['admin', 'it_staff'], true)) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Permission denied'], 403);
        }

        $db = \Config\Database::connect();
        if (!$db->tableExists('lab_tests')) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Lab tests table not found'], 404);
        }

        try {
            $db->table('lab_tests')->where('lab_test_id', (int)$id)->delete();
            return $this->jsonResponse(['status' => 'success', 'message' => 'Lab test deleted']);
        } catch (\Throwable $e) {
            log_message('error', 'LabManagement::deleteLabTest error: ' . $e->getMessage());
            return $this->jsonResponse(['status' => 'error', 'message' => 'Failed to delete lab test'], 500);
        }
    }

    public function getLabOrder($id)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->jsonResponse(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $order = $this->labService->getLabOrder((int) $id);
        return $this->jsonResponse($order 
            ? ['success' => true, 'data' => $order]
            : ['success' => false, 'message' => 'Lab order not found'], $order ? 200 : 404);
    }

    public function createLabOrder()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->jsonResponse(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        return $this->jsonResponse($this->labService->createLabOrder($input, $this->userRole, $this->staffId));
    }

    public function updateLabOrder()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->jsonResponse(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        $labOrderId = (int) ($input['lab_order_id'] ?? 0);

        if ($labOrderId <= 0) {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid lab order ID'], 400);
        }

        return $this->jsonResponse($this->labService->updateLabOrder($labOrderId, $input, $this->userRole, $this->staffId));
    }

    public function updateStatus($id)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->jsonResponse(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        $status = $input['status'] ?? '';
        $result = $this->labService->updateStatus((int) $id, $status, $this->userRole, $this->staffId);

        if (($result['success'] ?? false) && $status === 'completed') {
            $this->handleAutoBilling((int) $id, $this->userRole, $this->staffId);
        }

        return $this->jsonResponse($result);
    }

    public function deleteLabOrder($id)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->jsonResponse(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        return $this->jsonResponse($this->labService->deleteLabOrder((int) $id, $this->userRole, $this->staffId));
    }

    public function addToBilling($id)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Not authenticated']);
        }

        $userRole = session()->get('role');
        $staffId  = session()->get('staff_id');

        $input = $this->request->getJSON(true) ?? $this->request->getPost();

        $labOrderId = (int) $id;
        $unitPrice  = isset($input['unit_price']) ? (float) $input['unit_price'] : 0.0;

        if ($labOrderId <= 0 || $unitPrice <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid lab order or price']);
        }

        $order = $this->labService->getLabOrder($labOrderId);
        if (!$order) {
            return $this->response->setJSON(['success' => false, 'message' => 'Lab order not found']);
        }

        $patientId = (int) ($order['patient_id'] ?? 0);
        if ($patientId <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Lab order has no patient linked']);
        }

        $admissionId = null; // Extend later if you link lab orders to admissions

        $account = $this->financialService->getOrCreateBillingAccountForPatient($patientId, $admissionId, $staffId);

        if (!$account || empty($account['billing_id'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to create/find billing account']);
        }

        $billingId = (int) $account['billing_id'];

        $billingResult = method_exists($this->financialService, 'addItemFromLabOrder')
            ? $this->financialService->addItemFromLabOrder($billingId, $labOrderId, $unitPrice, $staffId)
            : ['success' => false, 'message' => 'addItemFromLabOrder not available'];

        return $this->response->setJSON($billingResult);
    }

    private function handleAutoBilling(int $labOrderId, string $userRole, ?int $staffId): void
    {
        if (!in_array($userRole, ['admin', 'accountant', 'doctor', 'laboratorist', 'it_staff'], true)) {
            return;
        }

        $order = $this->labService->getLabOrder($labOrderId);
        if (!$order) {
            return;
        }

        $patientId = (int) ($order['patient_id'] ?? 0);
        if ($patientId <= 0) {
            return;
        }

        // For now, use a simple default price; front-end or config can override later.
        $unitPrice = 0.0;
        if (!empty($order['test_code'])) {
            // Simple example mapping; real implementation should use a price table.
            $unitPrice = 500.00; // default lab test price
        }

        if ($unitPrice <= 0) {
            return;
        }

        $admissionId = null;
        $account = $this->financialService->getOrCreateBillingAccountForPatient($patientId, $admissionId, $staffId ?? 0);
        if (!$account || empty($account['billing_id'])) {
            return;
        }

        $billingId = (int) $account['billing_id'];

        if (method_exists($this->financialService, 'addItemFromLabOrder')) {
            $this->financialService->addItemFromLabOrder($billingId, $labOrderId, $unitPrice, $staffId ?? 0);
        }
    }
}

