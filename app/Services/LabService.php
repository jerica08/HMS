<?php

namespace App\Services;

use CodeIgniter\Database\ConnectionInterface;

class LabService
{
    protected $db;

    public function __construct(ConnectionInterface $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    /**
     * Build base query for lab orders with role-based filtering
     */
    private function buildLabOrderQuery(string $userRole, ?int $staffId = null)
    {
        $builder = $this->db->table('lab_orders lo')
            ->select('lo.*, p.first_name, p.last_name')
            ->join('patients p', 'p.patient_id = lo.patient_id', 'left');

        if (in_array($userRole, ['admin', 'it_staff', 'accountant', 'laboratorist', 'nurse', 'receptionist'], true)) {
            // Full visibility for these roles
        } elseif ($userRole === 'doctor' && $staffId) {
            $builder->where('lo.doctor_id', $staffId);
        } else {
            $builder->where('1', '0');
        }

        return $builder;
    }

    /**
     * List lab orders based on role and optional filters.
     */
    public function getLabOrdersByRole(string $userRole, ?int $staffId = null, array $filters = []): array
    {
        try {
            if (!$this->db->tableExists('lab_orders')) {
                return [];
            }

            $builder = $this->buildLabOrderQuery($userRole, $staffId);

            if (!empty($filters['status'])) {
                $builder->where('lo.status', $filters['status']);
            }
            if (!empty($filters['priority'])) {
                $builder->where('lo.priority', $filters['priority']);
            }
            if (!empty($filters['date'])) {
                $builder->where('DATE(lo.ordered_at)', $filters['date']);
            }
            if (!empty($filters['patient_id'])) {
                $builder->where('lo.patient_id', (int) $filters['patient_id']);
            }
            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $builder->groupStart()
                    ->like('lo.test_code', $search)
                    ->orLike('lo.test_name', $search)
                    ->orLike('p.first_name', $search)
                    ->orLike('p.last_name', $search)
                    ->groupEnd();
            }

            $orders = $builder->orderBy('lo.ordered_at', 'DESC')->get()->getResultArray();

            foreach ($orders as &$o) {
                $o['patient_name'] = trim(($o['first_name'] ?? '') . ' ' . ($o['last_name'] ?? ''));
            }

            return $orders;
        } catch (\Throwable $e) {
            log_message('error', 'LabService::getLabOrdersByRole error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Single lab order with basic permission awareness handled at controller level.
     */
    public function getLabOrder(int $labOrderId): ?array
    {
        try {
            if (!$this->db->tableExists('lab_orders')) {
                return null;
            }

            $order = $this->db->table('lab_orders lo')
                ->select('lo.*, p.first_name, p.last_name, p.date_of_birth')
                ->join('patients p', 'p.patient_id = lo.patient_id', 'left')
                ->where('lo.lab_order_id', $labOrderId)
                ->get()
                ->getRowArray();

            if ($order) {
                $order['patient_name'] = trim(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? ''));
            }

            return $order ?: null;
        } catch (\Throwable $e) {
            log_message('error', 'LabService::getLabOrder error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Simple stats for dashboard cards.
     */
    public function getLabStats(string $userRole, ?int $staffId = null): array
    {
        try {
            if (!$this->db->tableExists('lab_orders')) {
                return [];
            }

            $today = date('Y-m-d');

            $base = $this->db->table('lab_orders');

            if ($userRole === 'doctor' && $staffId) {
                $base->where('doctor_id', $staffId);
            }

            return [
                'total_orders'   => (clone $base)->countAllResults(),
                'today_orders'   => (clone $base)->where('DATE(ordered_at)', $today)->countAllResults(),
                'in_progress'    => (clone $base)->where('status', 'in_progress')->countAllResults(),
                'completed'      => (clone $base)->where('status', 'completed')->countAllResults(),
            ];
        } catch (\Throwable $e) {
            log_message('error', 'LabService::getLabStats error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a new lab order.
     * For outpatients: automatically adds to billing account.
     * For inpatients: billing is handled later (upon discharge or completion).
     */
    public function createLabOrder(array $data, string $userRole, ?int $staffId = null): array
    {
        try {
            if (!in_array($userRole, ['admin', 'doctor', 'it_staff'], true)) {
                return ['success' => false, 'message' => 'Permission denied'];
            }

            if (!$this->db->tableExists('lab_orders')) {
                return ['success' => false, 'message' => 'Lab orders table is missing'];
            }

            $validation = \Config\Services::validation();
            if (!$validation->setRules([
                'patient_id' => 'required|integer',
                'test_code'  => 'required|max_length[100]',
                'test_name'  => 'permit_empty|max_length[191]',
                'priority'   => 'permit_empty|in_list[routine,urgent,stat]',
            ])->run($data)) {
                return ['success' => false, 'message' => 'Validation failed', 'errors' => $validation->getErrors()];
            }

            $doctorId = $data['doctor_id'] ?? $staffId;
            $patientId = (int) $data['patient_id'];

            $insert = [
                'patient_id'     => $patientId,
                'doctor_id'      => (int) $doctorId,
                'appointment_id' => !empty($data['appointment_id']) ? (int) $data['appointment_id'] : null,
                'test_code'      => $data['test_code'],
                'test_name'      => $data['test_name'] ?? null,
                'status'         => 'ordered',
                'priority'       => $data['priority'] ?? 'routine',
                'ordered_at'     => date('Y-m-d H:i:s'),
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ];

            $this->db->table('lab_orders')->insert($insert);
            $id = (int) $this->db->insertID();

            if ($id <= 0) {
                return ['success' => false, 'message' => 'Failed to create lab order'];
            }

            // For outpatients: automatically add to billing
            $patientType = $this->getPatientType($patientId);
            if (strtolower($patientType) === 'outpatient') {
                $this->addLabOrderToBillingForOutpatient($id, $patientId, $data['test_code'], $staffId);
            }

            return ['success' => true, 'message' => 'Lab order created successfully', 'lab_order_id' => $id];
        } catch (\Throwable $e) {
            log_message('error', 'LabService::createLabOrder error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create lab order'];
        }
    }

    /**
     * Automatically add lab order to billing for outpatients
     */
    private function addLabOrderToBillingForOutpatient(int $labOrderId, int $patientId, string $testCode, ?int $staffId): void
    {
        try {
            // Check if FinancialService is available
            if (!class_exists(\App\Services\FinancialService::class)) {
                log_message('warning', 'FinancialService not available for auto-billing lab order');
                return;
            }

            $financialService = new \App\Services\FinancialService();

            // Get test price from lab_tests table
            $unitPrice = $this->getTestPrice($testCode);
            if ($unitPrice <= 0) {
                log_message('warning', "Lab order {$labOrderId}: No valid price found for test code {$testCode}, using default 500.00");
                $unitPrice = 500.00; // Default price
            }

            // Get or create billing account for patient
            $account = $financialService->getOrCreateBillingAccountForPatient($patientId, null, $staffId);
            if (!$account || empty($account['billing_id'])) {
                log_message('error', "Lab order {$labOrderId}: Failed to get/create billing account for patient {$patientId}");
                return;
            }

            $billingId = (int) $account['billing_id'];

            // Add lab order to billing
            if (method_exists($financialService, 'addItemFromLabOrder')) {
                $result = $financialService->addItemFromLabOrder($billingId, $labOrderId, $unitPrice, $staffId);
                if (!($result['success'] ?? false)) {
                    log_message('error', "Lab order {$labOrderId}: Failed to add to billing - " . ($result['message'] ?? 'Unknown error'));
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'LabService::addLabOrderToBillingForOutpatient error: ' . $e->getMessage());
        }
    }

    /**
     * Get test price from lab_tests table
     */
    private function getTestPrice(string $testCode): float
    {
        try {
            if (!$this->db->tableExists('lab_tests')) {
                return 0.0;
            }

            $test = $this->db->table('lab_tests')
                ->where('test_code', $testCode)
                ->where('status', 'active')
                ->get()
                ->getRowArray();

            if ($test && isset($test['default_price'])) {
                return (float) $test['default_price'];
            }

            return 0.0;
        } catch (\Throwable $e) {
            log_message('error', 'LabService::getTestPrice error: ' . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Update basic lab order fields (not status).
     */
    public function updateLabOrder(int $labOrderId, array $data, string $userRole, ?int $staffId = null): array
    {
        try {
            if (!in_array($userRole, ['admin', 'doctor', 'it_staff'], true)) {
                return ['success' => false, 'message' => 'Permission denied'];
            }

            if (!$this->db->tableExists('lab_orders')) {
                return ['success' => false, 'message' => 'Lab orders table is missing'];
            }

            $order = $this->getLabOrder($labOrderId);
            if (!$order) {
                return ['success' => false, 'message' => 'Lab order not found'];
            }

            if ($userRole === 'doctor' && $staffId && (int) $order['doctor_id'] !== (int) $staffId) {
                return ['success' => false, 'message' => 'You can only edit your own lab orders'];
            }

            $allowedFields = ['test_code', 'test_name', 'priority'];
            $update = array_intersect_key($data, array_flip($allowedFields));

            if (empty($update)) {
                return ['success' => false, 'message' => 'Nothing to update'];
            }

            $update['updated_at'] = date('Y-m-d H:i:s');

            $this->db->table('lab_orders')
                ->where('lab_order_id', $labOrderId)
                ->update($update);

            return ['success' => true, 'message' => 'Lab order updated successfully'];
        } catch (\Throwable $e) {
            log_message('error', 'LabService::updateLabOrder error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update lab order'];
        }
    }

    /**
     * Update status only; billing handled by controller/FinancialService when completed.
     * For outpatients: payment must be verified before allowing status to change to 'in_progress'.
     * For inpatients: can proceed without payment verification.
     */
    public function updateStatus(int $labOrderId, string $status, string $userRole, ?int $staffId = null): array
    {
        try {
            if (!$this->db->tableExists('lab_orders')) {
                return ['success' => false, 'message' => 'Lab orders table is missing'];
            }

            $validStatuses = ['ordered', 'in_progress', 'completed', 'cancelled'];
            if (!in_array($status, $validStatuses, true)) {
                return ['success' => false, 'message' => 'Invalid status'];
            }

            $order = $this->getLabOrder($labOrderId);
            if (!$order) {
                return ['success' => false, 'message' => 'Lab order not found'];
            }

            // Permission: doctor can only update own orders; admin/it_staff/laboratorist have full access
            if ($userRole === 'doctor' && (!$staffId || (int) $order['doctor_id'] !== (int) $staffId)) {
                return ['success' => false, 'message' => 'You can only update your own lab orders'];
            }
            if (!in_array($userRole, ['admin', 'it_staff', 'laboratorist', 'doctor'], true)) {
                return ['success' => false, 'message' => 'Permission denied'];
            }

            // Check payment requirement for outpatients when changing to 'in_progress'
            if ($status === 'in_progress') {
                $patientId = (int) ($order['patient_id'] ?? 0);
                if ($patientId > 0) {
                    $patientType = $this->getPatientType($patientId);
                    
                    // For outpatients, verify payment before allowing lab surgery to start
                    if (strtolower($patientType) === 'outpatient') {
                        if (!$this->isLabOrderPaid($labOrderId)) {
                            return [
                                'success' => false,
                                'message' => 'Payment is required before starting lab surgery for outpatients. Please ensure the lab order has been added to billing and payment has been processed.'
                            ];
                        }
                    }
                    // Inpatients can proceed without payment verification
                }
            }

            $update = [
                'status'     => $status,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if ($status === 'completed' && empty($order['completed_at'])) {
                $update['completed_at'] = date('Y-m-d H:i:s');
            }

            $this->db->table('lab_orders')
                ->where('lab_order_id', $labOrderId)
                ->update($update);

            return ['success' => true, 'message' => 'Status updated successfully'];
        } catch (\Throwable $e) {
            log_message('error', 'LabService::updateStatus error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update status'];
        }
    }

    /**
     * Get patient type (inpatient/outpatient) from patient record
     */
    private function getPatientType(int $patientId): string
    {
        try {
            if (!$this->db->tableExists('patients')) {
                return 'outpatient'; // Default to outpatient if table doesn't exist
            }

            $patient = $this->db->table('patients')
                ->select('patient_type')
                ->where('patient_id', $patientId)
                ->get()
                ->getRowArray();

            if ($patient && !empty($patient['patient_type'])) {
                return $patient['patient_type'];
            }

            // If patient_type is not set, check if patient has active inpatient admission
            if ($this->db->tableExists('inpatient_admissions')) {
                $activeAdmission = $this->db->table('inpatient_admissions')
                    ->where('patient_id', $patientId)
                    ->groupStart()
                        ->where('discharge_date', null)
                        ->orWhere('discharge_date', '')
                    ->groupEnd()
                    ->get()
                    ->getRowArray();

                if ($activeAdmission) {
                    return 'inpatient';
                }
            }

            return 'outpatient'; // Default to outpatient
        } catch (\Throwable $e) {
            log_message('error', 'LabService::getPatientType error: ' . $e->getMessage());
            return 'outpatient'; // Default to outpatient on error
        }
    }

    /**
     * Check if lab order has been paid
     * Returns true if:
     * 1. Lab order is linked to a billing item, AND
     * 2. The billing account has status 'paid' OR has completed payments
     */
    private function isLabOrderPaid(int $labOrderId): bool
    {
        try {
            // Check if billing_items table exists and has lab_order_id field
            if (!$this->db->tableExists('billing_items')) {
                return false;
            }

            if (!$this->db->fieldExists('lab_order_id', 'billing_items')) {
                // If lab_order_id field doesn't exist, we can't link lab orders to billing
                // In this case, we'll check if there's any billing account for the patient
                $order = $this->getLabOrder($labOrderId);
                if (!$order || empty($order['patient_id'])) {
                    return false;
                }

                // For backward compatibility, if lab_order_id field doesn't exist,
                // we'll allow proceeding (assuming payment will be handled separately)
                return true;
            }

            // Find billing items linked to this lab order
            $billingItems = $this->db->table('billing_items')
                ->where('lab_order_id', $labOrderId)
                ->get()
                ->getResultArray();

            if (empty($billingItems)) {
                return false; // Lab order not added to billing yet
            }

            // Get unique billing IDs
            $billingIds = array_unique(array_column($billingItems, 'billing_id'));

            // Check if any billing account is marked as paid
            if ($this->db->tableExists('billing_accounts')) {
                foreach ($billingIds as $billingId) {
                    $account = $this->db->table('billing_accounts')
                        ->where('billing_id', $billingId)
                        ->get()
                        ->getRowArray();

                    if ($account) {
                        // Check if billing account status is 'paid'
                        if (isset($account['status']) && strtolower($account['status']) === 'paid') {
                            return true;
                        }

                        // Check if there are completed payments for this billing account
                        if ($this->db->tableExists('payments')) {
                            // Check if payments table has billing_id field
                            if ($this->db->fieldExists('billing_id', 'payments')) {
                                $payment = $this->db->table('payments')
                                    ->where('billing_id', $billingId)
                                    ->where('status', 'completed')
                                    ->get()
                                    ->getRowArray();

                                if ($payment) {
                                    return true;
                                }
                            } else {
                                // If payments table doesn't have billing_id, check via bill_id
                                // This is a fallback for older payment structures
                                if ($this->db->tableExists('bills') && isset($account['bill_id'])) {
                                    $payment = $this->db->table('payments')
                                        ->where('bill_id', $account['bill_id'])
                                        ->where('status', 'completed')
                                        ->get()
                                        ->getRowArray();

                                    if ($payment) {
                                        return true;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            return false;
        } catch (\Throwable $e) {
            log_message('error', 'LabService::isLabOrderPaid error: ' . $e->getMessage());
            return false; // On error, assume not paid to be safe
        }
    }

    public function deleteLabOrder(int $labOrderId, string $userRole, ?int $staffId = null): array
    {
        try {
            if (!in_array($userRole, ['admin', 'it_staff'], true)) {
                return ['success' => false, 'message' => 'Permission denied'];
            }

            if (!$this->db->tableExists('lab_orders')) {
                return ['success' => false, 'message' => 'Lab orders table is missing'];
            }

            $order = $this->getLabOrder($labOrderId);
            if (!$order) {
                return ['success' => false, 'message' => 'Lab order not found'];
            }

            $this->db->table('lab_orders')
                ->where('lab_order_id', $labOrderId)
                ->delete();

            return ['success' => true, 'message' => 'Lab order deleted successfully'];
        } catch (\Throwable $e) {
            log_message('error', 'LabService::deleteLabOrder error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to delete lab order'];
        }
    }
}

