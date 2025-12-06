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

            $insert = [
                'patient_id'     => (int) $data['patient_id'],
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

            return $id > 0
                ? ['success' => true, 'message' => 'Lab order created successfully', 'lab_order_id' => $id]
                : ['success' => false, 'message' => 'Failed to create lab order'];
        } catch (\Throwable $e) {
            log_message('error', 'LabService::createLabOrder error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create lab order'];
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

