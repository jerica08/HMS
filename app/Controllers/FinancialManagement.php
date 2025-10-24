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
        $session = session();
        $userRole = $session->get('role');
        $userId = $session->get('staff_id');

        if (!$userRole) {
            return redirect()->to('/login');
        }

        $permissions = $this->getPermissions($userRole);
        $stats = $this->financialService->getFinancialStats($userRole, $userId);

        $data = [
            'title' => $this->getPageTitle($userRole),
            'userRole' => $userRole,
            'permissions' => $permissions,
            'stats' => $stats
        ];

        return view('unified/financial-management', $data);
    }

    public function createBill()
    {
        $session = session();
        $userRole = $session->get('role');
        $userId = $session->get('staff_id');

        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        $result = $this->financialService->createBill($input, $userRole, $userId);
        
        return $this->response->setJSON($result);
    }

    public function processPayment()
    {
        $session = session();
        $userRole = $session->get('role');
        $userId = $session->get('staff_id');

        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        $result = $this->financialService->processPayment($input, $userRole, $userId);
        
        return $this->response->setJSON($result);
    }

    public function createExpense()
    {
        $session = session();
        $userRole = $session->get('role');
        $userId = $session->get('staff_id');

        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        $result = $this->financialService->createExpense($input, $userRole, $userId);
        
        return $this->response->setJSON($result);
    }

    private function getPermissions(string $userRole): array
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

    private function getPageTitle(string $userRole): string
    {
        switch ($userRole) {
            case 'admin': return 'Financial Management';
            case 'accountant': return 'Accounting & Finance';
            case 'doctor': return 'My Financial Overview';
            case 'receptionist': return 'Billing & Payments';
            default: return 'Financial Overview';
        }
    }
}