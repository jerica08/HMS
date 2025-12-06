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

        return view('unified/financial-management', [
            'title' => $this->getPageTitle($userRole),
            'userRole' => $userRole,
            'permissions' => $this->getPermissions($userRole),
            'stats' => $this->financialService->getFinancialStats($userRole, $staffId),
            'transactions' => $this->financialService->getAllTransactions($userRole, $staffId)
        ]);
    }

    public function createBill()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->jsonResponse(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $result = $this->financialService->createBill(
            $this->getRequestData(),
            session()->get('role'),
            session()->get('staff_id')
        );

        return $this->jsonResponse($result);
    }

    public function processPayment()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->jsonResponse(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $result = $this->financialService->processPayment(
            $this->getRequestData(),
            session()->get('role'),
            session()->get('staff_id')
        );

        return $this->jsonResponse($result);
    }

    public function createExpense()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->jsonResponse(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $result = $this->financialService->createExpense(
            $this->getRequestData(),
            session()->get('role'),
            session()->get('staff_id')
        );

        return $this->jsonResponse($result);
    }

    public function createFinancialRecord()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->jsonResponse(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $userRole = session()->get('role');
        $permissions = $this->getPermissions($userRole);
        if (empty($permissions) || !in_array('view', $permissions)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Insufficient permissions'], 403);
        }

        $result = $this->financialService->createFinancialRecord(
            $this->getRequestData(),
            $userRole,
            session()->get('staff_id')
        );

        return $this->jsonResponse($result);
    }

    public function addTransaction()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Not authenticated'], 401);
        }

        $userRole = session()->get('role');
        $permissions = $this->getPermissions($userRole);
        if (empty($permissions) || !in_array('view', $permissions)) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Insufficient permissions'], 403);
        }

        $result = $this->financialService->handleFinancialTransactionFormSubmission(
            $this->request->getPost(),
            $userRole,
            session()->get('staff_id')
        );

        return $this->jsonResponse([
            'status' => $result['success'] ? 'success' : 'error',
            'message' => $result['message']
        ]);
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

    private function getRequestData(): array
    {
        $input = $this->request->getJSON(true);
        return is_array($input) ? $input : $this->request->getPost();
    }

    private function jsonResponse(array $data, int $statusCode = 200)
    {
        return $this->response->setStatusCode($statusCode)->setJSON($data);
    }

    private function getPageTitle($userRole)
    {
        return match ($userRole) {
            'admin' => 'Financial Management',
            'accountant' => 'Accounting & Finance',
            'doctor' => 'My Financial Overview',
            'receptionist' => 'Billing & Payments',
            'it_staff' => 'Financial Management',
            default => 'Financial Overview',
        };
    }
}