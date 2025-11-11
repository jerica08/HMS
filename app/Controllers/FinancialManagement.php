<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\FinancialService;

class FinancialManagement extends BaseController
{
    protected $financialService;

    public function __construct()
    {
        $this->financialService = new FinancialService();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userRole = session()->get('role');
        $staffId = session()->get('staff_id');

        $permissions = $this->getPermissions($userRole);
        $stats = $this->financialService->getFinancialStats($userRole, $staffId);

        $data = [
            'title' => $this->getPageTitle($userRole),
            'userRole' => $userRole,
            'permissions' => $permissions,
            'stats' => $stats,
            'transactions' => $this->financialService->getAllTransactions($userRole, $staffId)
        ];

        return view('unified/financial-management', $data);
    }

    public function createBill()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Not authenticated']);
        }

        $userRole = session()->get('role');
        $staffId = session()->get('staff_id');

        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        $result = $this->financialService->createBill($input, $userRole, $staffId);

        return $this->response->setJSON($result);
    }

    public function processPayment()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Not authenticated']);
        }

        $userRole = session()->get('role');
        $staffId = session()->get('staff_id');

        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        $result = $this->financialService->processPayment($input, $userRole, $staffId);

        return $this->response->setJSON($result);
    }

    public function createExpense()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Not authenticated']);
        }

        $userRole = session()->get('role');
        $staffId = session()->get('staff_id');

        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        $result = $this->financialService->createExpense($input, $userRole, $staffId);

        return $this->response->setJSON($result);
    }

    public function createFinancialRecord()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Not authenticated']);
        }

        $userRole = session()->get('role');
        $staffId = session()->get('staff_id');

        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        // Validate permissions - check if user has any financial permissions
        $permissions = $this->getPermissions($userRole);
        if (empty($permissions) || !in_array('view', $permissions)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Insufficient permissions']);
        }

        $result = $this->financialService->createFinancialRecord($data, $userRole, $staffId);

        return $this->response->setJSON($result);
    }

    public function addTransaction()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Not authenticated']);
        }

        $userRole = session()->get('role');
        $staffId = session()->get('staff_id');

        $data = $this->request->getPost();

        // Validate permissions
        $permissions = $this->getPermissions($userRole);
        if (empty($permissions) || !in_array('view', $permissions)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Insufficient permissions']);
        }

        $result = $this->financialService->handleFinancialTransactionFormSubmission($data, $userRole, $staffId);

        if ($result['success']) {
            return $this->response->setJSON(['status' => 'success', 'message' => $result['message']]);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => $result['message']]);
        }
    }

    private function getPermissions($userRole)
    {
        $permissions = [
            'admin' => ['view', 'create_bill', 'process_payment', 'create_expense', 'view_all'],
            'accountant' => ['view', 'create_bill', 'process_payment', 'create_expense', 'view_all'],
            'doctor' => ['view', 'create_bill'],
            'receptionist' => ['view', 'create_bill', 'process_payment'],
            'it_staff' => ['view', 'create_bill', 'process_payment', 'create_expense', 'view_all']
        ];

        return $permissions[$userRole] ?? ['view'];
    }

    private function getPageTitle($userRole)
    {
        switch ($userRole) {
            case 'admin': return 'Financial Management';
            case 'accountant': return 'Accounting & Finance';
            case 'doctor': return 'My Financial Overview';
            case 'receptionist': return 'Billing & Payments';
            case 'it_staff': return 'Financial Management';
            default: return 'Financial Overview';
        }
    }
}