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

    // Helpers
    private function sumIfTable(string $table, string $column, array $where = []): float
    {
        if (!$this->db->tableExists($table)) {
            return 0.0;
        }
        $builder = $this->db->table($table)->selectSum($column);
        foreach ($where as $k => $v) {
            $builder->where($k, $v);
        }
        $row = $builder->get()->getRow();
        return (isset($row) && isset($row->{$column})) ? (float)$row->{$column} : 0.0;
    }

    private function countIfTable(string $table, array $where = []): int
    {
        if (!$this->db->tableExists($table)) {
            return 0;
        }
        $builder = $this->db->table($table);
        foreach ($where as $k => $v) {
            $builder->where($k, $v);
        }
        return (int)$builder->countAllResults();
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
        $totalIncome = $this->sumIfTable('payments', 'amount', ['status' => 'completed']);
        $totalExpenses = $this->sumIfTable('expenses', 'amount');
        $pendingBills = $this->countIfTable('bills', ['status' => 'pending']);

        return [
            'total_income' => (float)$totalIncome,
            'total_expenses' => (float)$totalExpenses,
            'net_balance' => (float)$totalIncome - (float)$totalExpenses,
            'pending_bills' => $pendingBills,
            'paid_bills' => $this->countIfTable('bills', ['status' => 'paid'])
        ];
    }

    private function getDoctorStats(int $doctorId): array
    {
        $income = 0.0;
        if ($this->db->tableExists('payments') && $this->db->tableExists('bills')) {
            $row = $this->db->table('payments p')
                ->join('bills b', 'b.bill_id = p.bill_id')
                ->selectSum('p.amount')
                ->where('b.doctor_id', $doctorId)
                ->where('p.status', 'completed')
                ->get()->getRow();
            $income = isset($row) && isset($row->amount) ? (float)$row->amount : 0.0;
        }

        return [
            'total_income' => (float)$income,
            'total_expenses' => 0,
            'net_balance' => (float)$income,
            'pending_bills' => $this->countIfTable('bills', ['doctor_id' => $doctorId, 'status' => 'pending']),
            'paid_bills' => 0
        ];
    }

    private function getReceptionistStats(): array
    {
        $todayIncome = 0.0;
        if ($this->db->tableExists('payments')) {
            $row = $this->db->table('payments')
                ->selectSum('amount')
                ->where('DATE(payment_date)', date('Y-m-d'))
                ->where('status', 'completed')
                ->get()->getRow();
            $todayIncome = isset($row) && isset($row->amount) ? (float)$row->amount : 0.0;
        }

        return [
            'total_income' => (float)$todayIncome,
            'total_expenses' => 0,
            'net_balance' => (float)$todayIncome,
            'pending_bills' => $this->countIfTable('bills', ['status' => 'pending']),
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

            if (!$this->db->tableExists('bills')) {
                return ['success' => false, 'message' => 'Bills table is missing'];
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

            if (!$this->db->tableExists('payments')) {
                return ['success' => false, 'message' => 'Payments table is missing'];
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

            if (!$this->db->tableExists('expenses')) {
                return ['success' => false, 'message' => 'Expenses table is missing'];
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

    public function createFinancialRecord(array $data, string $userRole, int $userId): array
    {
        try {
            // Validate permissions based on category
            $category = $data['category'] ?? '';
            if ($category === 'Income') {
                if (!in_array($userRole, ['admin', 'accountant', 'receptionist', 'doctor', 'it_staff'])) {
                    return ['success' => false, 'message' => 'Insufficient permissions to create income records'];
                }
            } elseif ($category === 'Expense') {
                if (!in_array($userRole, ['admin', 'accountant', 'it_staff'])) {
                    return ['success' => false, 'message' => 'Insufficient permissions to create expense records'];
                }
            } else {
                return ['success' => false, 'message' => 'Invalid category'];
            }

            // Validate required fields
            $requiredFields = ['transaction_name', 'category', 'amount', 'date'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return ['success' => false, 'message' => "Field '{$field}' is required"];
                }
            }

            // Validate amount
            $amount = (float)($data['amount'] ?? 0);
            if ($amount <= 0) {
                return ['success' => false, 'message' => 'Amount must be greater than zero'];
            }

            // Validate date
            $date = $data['date'];
            if (!strtotime($date)) {
                return ['success' => false, 'message' => 'Invalid date format'];
            }

            if ($category === 'Income') {
                // Create income record (payment)
                if (!$this->db->tableExists('payments')) {
                    return ['success' => false, 'message' => 'Payments table is missing'];
                }

                $payment = [
                    'bill_id' => null, // General income, not tied to specific bill
                    'amount' => $amount,
                    'payment_method' => $data['payment_method'] ?? 'cash',
                    'payment_date' => $date . ' ' . date('H:i:s'),
                    'status' => 'completed',
                    'processed_by' => $userId,
                    'description' => $data['description'] ?? null
                ];

                $paymentId = $this->db->table('payments')->insert($payment);
                return ['success' => true, 'message' => 'Income record created successfully'];

            } elseif ($category === 'Expense') {
                // Create expense record
                if (!$this->db->tableExists('expenses')) {
                    return ['success' => false, 'message' => 'Expenses table is missing'];
                }

                $expense = [
                    'expense_name' => $data['transaction_name'],
                    'amount' => $amount,
                    'category' => $data['expense_category'] ?? 'other',
                    'expense_date' => $date,
                    'created_by' => $userId,
                    'description' => $data['description'] ?? null
                ];

                $expenseId = $this->db->table('expenses')->insert($expense);
                return ['success' => true, 'message' => 'Expense record created successfully'];
            }

            return ['success' => false, 'message' => 'Invalid category'];

        } catch (\Exception $e) {
            log_message('error', 'FinancialService::createFinancialRecord error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error creating financial record'];
        }
    }
    public function getAllTransactions(string $userRole, int $userId = null): array
    {
        try {
            $transactions = [];

            // Get income transactions (from payments table)
            if ($this->db->tableExists('payments')) {
                $payments = $this->db->table('payments')
                    ->select('id, amount, payment_date as date, \'Income\' as category, payment_method as transaction_name, description')
                    ->where('status', 'completed')
                    ->orderBy('payment_date', 'DESC')
                    ->limit(10)
                    ->get()
                    ->getResultArray();

                foreach ($payments as $payment) {
                    $transactions[] = array_merge($payment, [
                        'type' => 'income',
                        'transaction_name' => $payment['payment_method'] . ' Payment' . ($payment['description'] ? ' - ' . $payment['description'] : '')
                    ]);
                }
            }

            // Get expense transactions (from expenses table)
            if ($this->db->tableExists('expenses')) {
                $expenses = $this->db->table('expenses')
                    ->select('id, expense_name as transaction_name, amount, expense_date as date, category as expense_category, description, \'Expense\' as category')
                    ->orderBy('expense_date', 'DESC')
                    ->limit(10)
                    ->get()
                    ->getResultArray();

                foreach ($expenses as $expense) {
                    $transactions[] = array_merge($expense, ['type' => 'expense']);
                }
            }

            // Sort all transactions by date (newest first)
            usort($transactions, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });

            return array_slice($transactions, 0, 20); // Return latest 20 transactions

        } catch (\Exception $e) {
            log_message('error', 'FinancialService::getAllTransactions error: ' . $e->getMessage());
            return [];
        }
    }
}