<?php

namespace App\Services;

use CodeIgniter\Database\ConnectionInterface;

class FinancialService
{
    protected $db;

    public function __construct(ConnectionInterface $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    public function getFinancialStats(string $userRole, int $userId = null): array
    {
        try {
            switch ($userRole) {
                case 'admin':
                case 'accountant':
                case 'it_staff':
                    return $this->getSystemWideStats();
                case 'doctor':
                    return $this->getDoctorStats($userId);
                case 'receptionist':
                    return $this->getReceptionistStats();
                default:
                    return $this->getBasicStats();
            }
        } catch (\Exception $e) {
            log_message('error', 'FinancialService error: ' . $e->getMessage());
            return $this->getBasicStats();
        }
    }

    private function getSystemWideStats(): array
    {
        $totalIncome = $this->db->table('payments')->selectSum('amount')->where('status', 'completed')->get()->getRow()->amount ?? 0;
        $totalExpenses = $this->db->table('expenses')->selectSum('amount')->get()->getRow()->amount ?? 0;
        $pendingBills = $this->db->table('bills')->where('status', 'pending')->countAllResults();
        
        return [
            'total_income' => (float)$totalIncome,
            'total_expenses' => (float)$totalExpenses,
            'net_balance' => (float)$totalIncome - (float)$totalExpenses,
            'pending_bills' => $pendingBills,
            'paid_bills' => $this->db->table('bills')->where('status', 'paid')->countAllResults()
        ];
    }

    private function getDoctorStats(int $doctorId): array
    {
        $income = $this->db->table('payments p')
            ->join('bills b', 'b.bill_id = p.bill_id')
            ->selectSum('p.amount')
            ->where('b.doctor_id', $doctorId)
            ->where('p.status', 'completed')
            ->get()->getRow()->amount ?? 0;

        return [
            'total_income' => (float)$income,
            'total_expenses' => 0,
            'net_balance' => (float)$income,
            'pending_bills' => $this->db->table('bills')->where('doctor_id', $doctorId)->where('status', 'pending')->countAllResults(),
            'paid_bills' => 0
        ];
    }

    private function getReceptionistStats(): array
    {
        $todayIncome = $this->db->table('payments')
            ->selectSum('amount')
            ->where('DATE(payment_date)', date('Y-m-d'))
            ->where('status', 'completed')
            ->get()->getRow()->amount ?? 0;

        return [
            'total_income' => (float)$todayIncome,
            'total_expenses' => 0,
            'net_balance' => (float)$todayIncome,
            'pending_bills' => $this->db->table('bills')->where('status', 'pending')->countAllResults(),
            'paid_bills' => 0
        ];
    }

    private function getBasicStats(): array
    {
        return [
            'total_income' => 0,
            'total_expenses' => 0,
            'net_balance' => 0,
            'pending_bills' => 0,
            'paid_bills' => 0
        ];
    }

    public function createBill(array $billData, string $userRole, int $userId): array
    {
        try {
            if (!in_array($userRole, ['admin', 'accountant', 'receptionist', 'doctor', 'it_staff'])) {
                return ['success' => false, 'message' => 'Insufficient permissions'];
            }

            $bill = [
                'bill_number' => 'BILL-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'patient_id' => $billData['patient_id'] ?? null,
                'doctor_id' => $billData['doctor_id'] ?? $userId,
                'total_amount' => $billData['total_amount'] ?? 0,
                'status' => $billData['status'] ?? 'pending',
                'bill_date' => date('Y-m-d H:i:s'),
                'created_by' => $userId
            ];

            $billId = $this->db->table('bills')->insert($bill);
            return ['success' => true, 'message' => 'Bill created successfully', 'bill_id' => $billId];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error creating bill'];
        }
    }

    public function processPayment(array $paymentData, string $userRole, int $userId): array
    {
        try {
            if (!in_array($userRole, ['admin', 'accountant', 'receptionist', 'it_staff'])) {
                return ['success' => false, 'message' => 'Insufficient permissions'];
            }

            $payment = [
                'bill_id' => $paymentData['bill_id'] ?? null,
                'amount' => $paymentData['amount'] ?? 0,
                'payment_method' => $paymentData['payment_method'] ?? 'cash',
                'payment_date' => date('Y-m-d H:i:s'),
                'status' => 'completed',
                'processed_by' => $userId
            ];

            $paymentId = $this->db->table('payments')->insert($payment);
            return ['success' => true, 'message' => 'Payment processed successfully'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error processing payment'];
        }
    }

    public function createExpense(array $expenseData, string $userRole, int $userId): array
    {
        try {
            if (!in_array($userRole, ['admin', 'accountant', 'it_staff'])) {
                return ['success' => false, 'message' => 'Insufficient permissions'];
            }

            $expense = [
                'expense_name' => $expenseData['name'] ?? '',
                'amount' => $expenseData['amount'] ?? 0,
                'category' => $expenseData['category'] ?? 'other',
                'expense_date' => $expenseData['date'] ?? date('Y-m-d'),
                'created_by' => $userId
            ];

            $expenseId = $this->db->table('expenses')->insert($expense);
            return ['success' => true, 'message' => 'Expense created successfully'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error creating expense'];
        }
    }
}