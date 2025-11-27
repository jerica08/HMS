<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\LabService;
use App\Services\FinancialService;

class LabManagement extends BaseController
{
    protected $labService;
    protected $financialService;

    public function __construct()
    {
        $this->labService = new LabService();
        $this->financialService = new FinancialService();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userRole = session()->get('role');
        $staffId  = session()->get('staff_id');

        $stats = $this->labService->getLabStats($userRole, $staffId);

        $data = [
            'title'       => 'Lab Management',
            'userRole'    => $userRole,
            'stats'       => $stats,
        ];

        return view('unified/lab-management', $data);
    }

    public function getLabOrdersAPI()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([]);
        }

        $userRole = session()->get('role');
        $staffId  = session()->get('staff_id');

        $filters = [
            'status'     => $this->request->getGet('status'),
            'priority'   => $this->request->getGet('priority'),
            'date'       => $this->request->getGet('date'),
            'patient_id' => $this->request->getGet('patient_id'),
            'search'     => $this->request->getGet('search'),
        ];

        $orders = $this->labService->getLabOrdersByRole($userRole, $staffId, $filters);

        return $this->response->setJSON($orders);
    }

    public function getLabPatientsAPI()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Not authenticated']);
        }

        $db = \Config\Database::connect();
        if (!$db->tableExists('patients')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Patients table not found', 'data' => []]);
        }

        $patients = $db->table('patients p')
            ->select('p.patient_id, p.first_name, p.last_name, p.date_of_birth')
            ->orderBy('p.first_name', 'ASC')
            ->orderBy('p.last_name', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON(['status' => 'success', 'data' => $patients]);
    }

    public function getLabTestsAPI()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Not authenticated']);
        }

        $db = \Config\Database::connect();
        if (!$db->tableExists('lab_tests')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Lab tests table not found', 'data' => []]);
        }

        $tests = $db->table('lab_tests')
            ->where('status', 'active')
            ->orderBy('category', 'ASC')
            ->orderBy('test_name', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON(['status' => 'success', 'data' => $tests]);
    }

    public function createLabTest()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Not authenticated']);
        }

        $userRole = session()->get('role');
        if (!in_array($userRole, ['admin', 'it_staff'], true)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Permission denied']);
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

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Lab test created',
                'id' => $db->insertID(),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'LabManagement::createLabTest error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to create lab test',
            ]);
        }
    }

    public function updateLabTest($id)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Not authenticated']);
        }

        $userRole = session()->get('role');
        if (!in_array($userRole, ['admin', 'it_staff'], true)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Permission denied']);
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
            return $this->response->setJSON(['status' => 'error', 'message' => 'Nothing to update']);
        }

        $update['updated_at'] = date('Y-m-d H:i:s');

        try {
            $db->table('lab_tests')
                ->where('lab_test_id', (int)$id)
                ->update($update);

            return $this->response->setJSON(['status' => 'success', 'message' => 'Lab test updated']);
        } catch (\Throwable $e) {
            log_message('error', 'LabManagement::updateLabTest error: ' . $e->getMessage());
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update lab test']);
        }
    }

    public function deleteLabTest($id)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Not authenticated']);
        }

        $userRole = session()->get('role');
        if (!in_array($userRole, ['admin', 'it_staff'], true)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Permission denied']);
        }

        $db = \Config\Database::connect();
        if (!$db->tableExists('lab_tests')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Lab tests table not found']);
        }

        try {
            $db->table('lab_tests')
                ->where('lab_test_id', (int)$id)
                ->delete();

            return $this->response->setJSON(['status' => 'success', 'message' => 'Lab test deleted']);
        } catch (\Throwable $e) {
            log_message('error', 'LabManagement::deleteLabTest error: ' . $e->getMessage());
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete lab test']);
        }
    }

    public function getLabOrder($id)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Not authenticated']);
        }

        $order = $this->labService->getLabOrder((int) $id);

        if (!$order) {
            return $this->response->setJSON(['success' => false, 'message' => 'Lab order not found']);
        }

        return $this->response->setJSON(['success' => true, 'data' => $order]);
    }

    public function createLabOrder()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Not authenticated']);
        }

        $userRole = session()->get('role');
        $staffId  = session()->get('staff_id');

        $input = $this->request->getJSON(true) ?? $this->request->getPost();

        $result = $this->labService->createLabOrder($input, $userRole, $staffId);

        return $this->response->setJSON($result);
    }

    public function updateLabOrder()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Not authenticated']);
        }

        $userRole = session()->get('role');
        $staffId  = session()->get('staff_id');

        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        $labOrderId = (int) ($input['lab_order_id'] ?? 0);

        if ($labOrderId <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid lab order ID']);
        }

        $result = $this->labService->updateLabOrder($labOrderId, $input, $userRole, $staffId);

        return $this->response->setJSON($result);
    }

    public function updateStatus($id)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Not authenticated']);
        }

        $userRole = session()->get('role');
        $staffId  = session()->get('staff_id');

        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        $status = $input['status'] ?? '';

        $result = $this->labService->updateStatus((int) $id, $status, $userRole, $staffId);

        // Auto-billing when completed
        if ($result['success'] ?? false && $status === 'completed') {
            $this->handleAutoBilling((int) $id, $userRole, $staffId);
        }

        return $this->response->setJSON($result);
    }

    public function deleteLabOrder($id)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Not authenticated']);
        }

        $userRole = session()->get('role');
        $staffId  = session()->get('staff_id');

        $result = $this->labService->deleteLabOrder((int) $id, $userRole, $staffId);

        return $this->response->setJSON($result);
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

