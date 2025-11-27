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

    public function handleFinancialTransactionFormSubmission(array $data, string $userRole, int $userId): array
    {
        try {
            // Validate permissions based on type
            $type = $data['type'] ?? '';
            if ($type === 'Income') {
                if (!in_array($userRole, ['admin', 'accountant', 'receptionist', 'doctor', 'it_staff'])) {
                    return ['success' => false, 'message' => 'Insufficient permissions to create income records'];
                }
            } elseif ($type === 'Expense') {
                if (!in_array($userRole, ['admin', 'accountant', 'it_staff'])) {
                    return ['success' => false, 'message' => 'Insufficient permissions to create expense records'];
                }
            } else {
                return ['success' => false, 'message' => 'Invalid transaction type'];
            }

            // Validate required fields
            $requiredFields = ['type', 'category', 'amount', 'transaction_date'];
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
            $date = $data['transaction_date'];
            if (!strtotime($date)) {
                return ['success' => false, 'message' => 'Invalid date format'];
            }

            // Check if financial_transaction table exists
            if (!$this->db->tableExists('financial_transaction')) {
                return ['success' => false, 'message' => 'Financial transaction table is missing'];
            }

            // Insert into financial_transaction table
            $transaction = [
                'user_id' => $userId,
                'type' => $type,
                'category' => $data['category'],
                'amount' => $amount,
                'description' => $data['description'] ?? null,
                'transaction_date' => $date,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $transactionId = $this->db->table('financial_transaction')->insert($transaction);
            
            if ($transactionId) {
                return ['success' => true, 'message' => 'Financial transaction created successfully', 'transaction_id' => $transactionId];
            } else {
                return ['success' => false, 'message' => 'Failed to create financial transaction'];
            }

        } catch (\Exception $e) {
            log_message('error', 'FinancialService::handleFinancialTransactionFormSubmission error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error creating financial record'];
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

    /**
     * Billing accounts & items (integration with patients, appointments, prescriptions)
     */

    public function getOrCreateBillingAccountForPatient(int $patientId, ?int $admissionId = null, int $createdByStaffId = null): ?array
    {
        try {
            if (!$this->db->tableExists('billing_accounts')) {
                return null;
            }

            $builder = $this->db->table('billing_accounts');
            $builder->where('patient_id', $patientId);
            if ($admissionId !== null) {
                $builder->where('admission_id', $admissionId);
            }

            // Optionally filter by status if such a column exists
            if ($this->db->fieldExists('status', 'billing_accounts')) {
                $builder->where('status', 'open');
            }

            $account = $builder->get()->getRowArray();
            if ($account) {
                return $account;
            }

            // Create a new billing account
            $insertData = [
                'patient_id' => $patientId,
            ];

            if ($admissionId !== null) {
                $insertData['admission_id'] = $admissionId;
            }

            if ($this->db->fieldExists('status', 'billing_accounts')) {
                $insertData['status'] = 'open';
            }

            if ($this->db->fieldExists('created_by', 'billing_accounts') && $createdByStaffId !== null) {
                $insertData['created_by'] = $createdByStaffId;
            }

            $this->db->table('billing_accounts')->insert($insertData);

            if ($this->db->affectedRows() <= 0) {
                log_message('error', 'FinancialService::getOrCreateBillingAccountForPatient insert failed for patient_id ' . $patientId);
                return null;
            }

            // Re-query the account using the same criteria instead of relying on insertID
            $builder = $this->db->table('billing_accounts');
            $builder->where('patient_id', $patientId);
            if ($admissionId !== null) {
                $builder->where('admission_id', $admissionId);
            }
            if ($this->db->fieldExists('status', 'billing_accounts')) {
                $builder->where('status', 'open');
            }

            return $builder->orderBy('billing_id', 'DESC')->get()->getRowArray();

        } catch (\Exception $e) {
            log_message('error', 'FinancialService::getOrCreateBillingAccountForPatient error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update the status of a billing account (if the status column exists).
     */
    public function updateBillingAccountStatus(int $billingId, string $status): array
    {
        try {
            if (!$this->db->tableExists('billing_accounts')) {
                return ['success' => false, 'message' => 'Billing accounts table is missing'];
            }

            if (!$this->db->fieldExists('status', 'billing_accounts')) {
                // Be defensive: do not throw, just report that the field is not present
                return ['success' => false, 'message' => 'Status field does not exist on billing_accounts'];
            }

            $this->db->table('billing_accounts')
                ->where('billing_id', $billingId)
                ->update(['status' => $status]);

            if ($this->db->affectedRows() <= 0) {
                return ['success' => false, 'message' => 'Billing account not found or status unchanged'];
            }

            return ['success' => true, 'message' => 'Billing account status updated'];
        } catch (\Exception $e) {
            log_message('error', 'FinancialService::updateBillingAccountStatus error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error updating billing account status'];
        }
    }

    /**
     * Convenience wrapper to mark an account as paid.
     */
    public function markBillingAccountPaid(int $billingId): array
    {
        return $this->updateBillingAccountStatus($billingId, 'paid');
    }

    public function addItemFromAppointment(int $billingId, int $appointmentId, float $unitPrice, int $quantity = 1, ?int $createdByStaffId = null): array
    {
        try {
            if (!$this->db->tableExists('billing_items') || !$this->db->tableExists('appointments')) {
                return ['success' => false, 'message' => 'Billing or appointments table is missing'];
            }

            $appointment = $this->db->table('appointments')
                ->where('appointment_id', $appointmentId)
                ->get()
                ->getRowArray();

            if (!$appointment) {
                return ['success' => false, 'message' => 'Appointment not found'];
            }

            $patientId = (int)($appointment['patient_id'] ?? 0);
            if ($patientId <= 0) {
                return ['success' => false, 'message' => 'Appointment has no patient linked'];
            }

            $descriptionParts = [];
            if (!empty($appointment['appointment_type'])) {
                $descriptionParts[] = $appointment['appointment_type'];
            } else {
                $descriptionParts[] = 'Consultation';
            }

            if (!empty($appointment['appointment_date'])) {
                $descriptionParts[] = date('Y-m-d', strtotime($appointment['appointment_date']));
            }

            $description = implode(' - ', $descriptionParts);

            $quantity = max(1, (int)$quantity);
            $unitPrice = max(0, (float)$unitPrice);
            $lineTotal = $quantity * $unitPrice;

            $itemData = [
                'billing_id'      => $billingId,
                'patient_id'      => $patientId,
                'appointment_id'  => $appointmentId,
                'prescription_id' => null,
                'description'     => $description,
                'quantity'        => $quantity,
                'unit_price'      => $unitPrice,
                'line_total'      => $lineTotal,
            ];

            if ($this->db->fieldExists('created_by_staff_id', 'billing_items') && $createdByStaffId !== null) {
                $itemData['created_by_staff_id'] = $createdByStaffId;
            }

            $this->db->table('billing_items')->insert($itemData);

            return ['success' => true, 'message' => 'Appointment item added to billing'];
        } catch (\Exception $e) {
            log_message('error', 'FinancialService::addItemFromAppointment error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error adding appointment to billing'];
        }
    }

    public function addItemFromPrescription(int $billingId, int $prescriptionId, float $unitPrice, ?int $quantity = null, ?int $createdByStaffId = null): array
    {
        try {
            if (!$this->db->tableExists('billing_items') || !$this->db->tableExists('prescriptions')) {
                return ['success' => false, 'message' => 'Billing or prescriptions table is missing'];
            }

            $prescription = $this->db->table('prescriptions')
                ->where('id', $prescriptionId)
                ->get()
                ->getRowArray();

            if (!$prescription) {
                return ['success' => false, 'message' => 'Prescription not found'];
            }

            $patientId = (int)($prescription['patient_id'] ?? 0);
            if ($patientId <= 0) {
                return ['success' => false, 'message' => 'Prescription has no patient linked'];
            }

            // Avoid duplicate billing entries for the same prescription and billing account
            $existing = $this->db->table('billing_items')
                ->where('billing_id', $billingId)
                ->where('prescription_id', $prescriptionId)
                ->countAllResults();

            if ($existing > 0) {
                return ['success' => true, 'message' => 'Prescription is already added to this billing account.'];
            }

            $descriptionParts = [];
            if (!empty($prescription['medication'])) {
                $descriptionParts[] = $prescription['medication'];
            }
            if (!empty($prescription['dosage'])) {
                $descriptionParts[] = $prescription['dosage'];
            }

            $description = !empty($descriptionParts) ? implode(' - ', $descriptionParts) : 'Medication';

            // Determine quantity: prefer dispensed_quantity, then quantity, then 1
            if ($quantity === null) {
                if (!empty($prescription['dispensed_quantity'])) {
                    $quantity = (int)$prescription['dispensed_quantity'];
                } elseif (!empty($prescription['quantity'])) {
                    $quantity = (int)$prescription['quantity'];
                } else {
                    $quantity = 1;
                }
            }

            $quantity = max(1, (int)$quantity);
            $unitPrice = max(0, (float)$unitPrice);
            $lineTotal = $quantity * $unitPrice;

            $itemData = [
                'billing_id'      => $billingId,
                'patient_id'      => $patientId,
                'appointment_id'  => null,
                'prescription_id' => $prescriptionId,
                'description'     => $description,
                'quantity'        => $quantity,
                'unit_price'      => $unitPrice,
                'line_total'      => $lineTotal,
            ];

            if ($this->db->fieldExists('created_by_staff_id', 'billing_items') && $createdByStaffId !== null) {
                $itemData['created_by_staff_id'] = $createdByStaffId;
            }

            $this->db->table('billing_items')->insert($itemData);

            return ['success' => true, 'message' => 'Prescription item added to billing'];
        } catch (\Exception $e) {
            log_message('error', 'FinancialService::addItemFromPrescription error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error adding prescription to billing'];
        }
    }

    public function addItemFromLabOrder(int $billingId, int $labOrderId, float $unitPrice, ?int $createdByStaffId = null): array
    {
        try {
            if (!$this->db->tableExists('billing_items') || !$this->db->tableExists('lab_orders')) {
                return ['success' => false, 'message' => 'Billing or lab_orders table is missing'];
            }

            $order = $this->db->table('lab_orders')
                ->where('lab_order_id', $labOrderId)
                ->get()
                ->getRowArray();

            if (!$order) {
                return ['success' => false, 'message' => 'Lab order not found'];
            }

            $patientId = (int)($order['patient_id'] ?? 0);
            if ($patientId <= 0) {
                return ['success' => false, 'message' => 'Lab order has no patient linked'];
            }

            // Avoid duplicate billing entries for the same lab order and billing account when we have a lab_order_id column
            if ($this->db->fieldExists('lab_order_id', 'billing_items')) {
                $existing = $this->db->table('billing_items')
                    ->where('billing_id', $billingId)
                    ->where('lab_order_id', $labOrderId)
                    ->countAllResults();

                if ($existing > 0) {
                    return ['success' => true, 'message' => 'Lab order is already added to this billing account.'];
                }
            }

            $descriptionParts = [];
            if (!empty($order['test_name'])) {
                $descriptionParts[] = $order['test_name'];
            }
            if (!empty($order['test_code'])) {
                $descriptionParts[] = $order['test_code'];
            }

            $description = !empty($descriptionParts)
                ? implode(' - ', $descriptionParts)
                : 'Laboratory Test';

            $quantity  = 1;
            $unitPrice = max(0, (float)$unitPrice);
            $lineTotal = $quantity * $unitPrice;

            $itemData = [
                'billing_id'      => $billingId,
                'patient_id'      => $patientId,
                'appointment_id'  => !empty($order['appointment_id']) ? (int)$order['appointment_id'] : null,
                'prescription_id' => null,
                'description'     => $description,
                'quantity'        => $quantity,
                'unit_price'      => $unitPrice,
                'line_total'      => $lineTotal,
            ];

            if ($this->db->fieldExists('lab_order_id', 'billing_items')) {
                $itemData['lab_order_id'] = $labOrderId;
            }

            if ($this->db->fieldExists('created_by_staff_id', 'billing_items') && $createdByStaffId !== null) {
                $itemData['created_by_staff_id'] = $createdByStaffId;
            }

            $this->db->table('billing_items')->insert($itemData);

            return ['success' => true, 'message' => 'Lab order item added to billing'];
        } catch (\Exception $e) {
            log_message('error', 'FinancialService::addItemFromLabOrder error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error adding lab order to billing'];
        }
    }

    public function addItemFromRoomAssignment(int $billingId, int $assignmentId, ?float $unitPricePerDay = null, ?int $createdByStaffId = null): array
    {
        try {
            if (!$this->db->tableExists('billing_items') || !$this->db->tableExists('room_assignment')) {
                return ['success' => false, 'message' => 'Billing or room_assignment table is missing'];
            }

            $assignment = $this->db->table('room_assignment')
                ->where('assignment_id', $assignmentId)
                ->get()
                ->getRowArray();

            if (!$assignment) {
                return ['success' => false, 'message' => 'Room assignment not found'];
            }

            $patientId = (int)($assignment['patient_id'] ?? 0);
            if ($patientId <= 0) {
                return ['success' => false, 'message' => 'Room assignment has no patient linked'];
            }

            if ($this->db->fieldExists('room_assignment_id', 'billing_items')) {
                $existing = $this->db->table('billing_items')
                    ->where('billing_id', $billingId)
                    ->where('room_assignment_id', $assignmentId)
                    ->countAllResults();

                if ($existing > 0) {
                    return ['success' => true, 'message' => 'Room assignment is already added to this billing account.'];
                }
            }

            $totalDays  = (int)($assignment['total_days'] ?? 0);
            $totalHours = (int)($assignment['total_hours'] ?? 0);

            if ($totalDays <= 0 && $totalHours > 0) {
                $totalDays = 1;
            } elseif ($totalDays <= 0) {
                $totalDays = 1;
            }

            $dailyRate = $unitPricePerDay !== null
                ? (float)$unitPricePerDay
                : (float)($assignment['room_rate_at_time'] ?? 0);

            if ($dailyRate <= 0 && isset($assignment['bed_rate_at_time'])) {
                $dailyRate = (float)$assignment['bed_rate_at_time'];
            }

            if ($dailyRate <= 0) {
                return ['success' => false, 'message' => 'No valid room rate available for this assignment'];
            }

            $quantity  = max(1, $totalDays);
            $unitPrice = max(0, $dailyRate);
            $lineTotal = $quantity * $unitPrice;

            $descriptionParts = ['Room charge'];
            if (!empty($assignment['admission_id'])) {
                $descriptionParts[] = 'Admission #' . $assignment['admission_id'];
            }
            if (!empty($assignment['date_in'])) {
                $descriptionParts[] = 'from ' . date('Y-m-d', strtotime($assignment['date_in']));
            }
            if (!empty($assignment['date_out'])) {
                $descriptionParts[] = 'to ' . date('Y-m-d', strtotime($assignment['date_out']));
            }
            $description = implode(' - ', $descriptionParts);

            $itemData = [
                'billing_id'      => $billingId,
                'patient_id'      => $patientId,
                'appointment_id'  => null,
                'prescription_id' => null,
                'description'     => $description,
                'quantity'        => $quantity,
                'unit_price'      => $unitPrice,
                'line_total'      => $lineTotal,
            ];

            if ($this->db->fieldExists('room_assignment_id', 'billing_items')) {
                $itemData['room_assignment_id'] = $assignmentId;
            }

            if ($this->db->fieldExists('created_by_staff_id', 'billing_items') && $createdByStaffId !== null) {
                $itemData['created_by_staff_id'] = $createdByStaffId;
            }

            $this->db->table('billing_items')->insert($itemData);

            return ['success' => true, 'message' => 'Room assignment item added to billing'];
        } catch (\Exception $e) {
            log_message('error', 'FinancialService::addItemFromRoomAssignment error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error adding room assignment to billing'];
        }
    }

    public function getBillingAccount(int $billingId, string $userRole, ?int $staffId = null): ?array
    {
        try {
            if (!$this->db->tableExists('billing_accounts')) {
                return null;
            }

            $account = $this->db->table('billing_accounts')
                ->where('billing_id', $billingId)
                ->get()
                ->getRowArray();

            if (!$account) {
                return null;
            }

            // Attach patient name details if patients table exists
            if (!empty($account['patient_id']) && $this->db->tableExists('patients')) {
                $patient = $this->db->table('patients')
                    ->where('patient_id', (int)$account['patient_id'])
                    ->get()
                    ->getRowArray();

                if ($patient) {
                    $firstName = $patient['first_name'] ?? '';
                    $lastName  = $patient['last_name'] ?? '';
                    $fullName  = trim($firstName . ' ' . $lastName);

                    $account['first_name']        = $firstName;
                    $account['last_name']         = $lastName;
                    $account['patient_full_name'] = $fullName !== '' ? $fullName : ($patient['full_name'] ?? '');

                    if (empty($account['patient_name'])) {
                        $account['patient_name'] = $account['patient_full_name'] ?: ('Patient #' . $account['patient_id']);
                    }
                }
            }

            if ($this->db->tableExists('billing_items')) {
                $items = $this->db->table('billing_items')
                    ->where('billing_id', $billingId)
                    ->orderBy('item_id', 'ASC')
                    ->get()
                    ->getResultArray();

                $totalAmount = 0.0;
                foreach ($items as $item) {
                    $totalAmount += (float)($item['line_total'] ?? 0);
                }

                $account['items'] = $items;
                $account['total_amount'] = $totalAmount;
            } else {
                $account['items'] = [];
                $account['total_amount'] = 0.0;
            }

            return $account;
        } catch (\Exception $e) {
            log_message('error', 'FinancialService::getBillingAccount error: ' . $e->getMessage());
            return null;
        }
    }

    public function getBillingAccounts(array $filters, string $userRole, ?int $staffId = null): array
    {
        try {
            if (!$this->db->tableExists('billing_accounts')) {
                return [];
            }

            // Base query for billing accounts
            $builder = $this->db->table('billing_accounts ba');

            // Join patients table when available so we can show patient names
            if ($this->db->tableExists('patients')) {
                $builder = $builder
                    ->join('patients p', 'p.patient_id = ba.patient_id', 'left')
                    ->select('ba.*, p.first_name, p.last_name');
            } else {
                $builder = $builder->select('ba.*');
            }

            if (!empty($filters['patient_id'])) {
                $builder->where('ba.patient_id', (int)$filters['patient_id']);
            }

            if (!empty($filters['status']) && $this->db->fieldExists('status', 'billing_accounts')) {
                $builder->where('ba.status', $filters['status']);
            }

            if (!empty($filters['from_date']) && $this->db->fieldExists('created_at', 'billing_accounts')) {
                $builder->where('ba.created_at >=', $filters['from_date']);
            }

            if (!empty($filters['to_date']) && $this->db->fieldExists('created_at', 'billing_accounts')) {
                $builder->where('ba.created_at <=', $filters['to_date']);
            }

            $builder->orderBy('ba.billing_id', 'DESC');

            $accounts = $builder->get()->getResultArray();

            // Attach patient name details if patients table exists
            if ($this->db->tableExists('patients')) {
                foreach ($accounts as &$account) {
                    if (!empty($account['patient_id'])) {
                        $firstName = $account['first_name'] ?? '';
                        $lastName  = $account['last_name'] ?? '';
                        $fullName  = trim($firstName . ' ' . $lastName);

                        $account['patient_full_name'] = $fullName !== '' ? $fullName : ($account['full_name'] ?? '');

                        if (empty($account['patient_name'])) {
                            $account['patient_name'] = $account['patient_full_name'] ?: ('Patient #' . $account['patient_id']);
                        }
                    }
                }
            }

            return $accounts;
        } catch (\Exception $e) {
            log_message('error', 'FinancialService::getBillingAccounts error: ' . $e->getMessage());
            return [];
        }
    }
}