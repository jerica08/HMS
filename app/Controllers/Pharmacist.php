<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\InventoryModel;
use App\Models\PrescriptionModel;

class Pharmacist extends BaseController
{
    protected $inventoryModel;
    protected $prescriptionModel;

    public function __construct()
    {
        $this->inventoryModel = new InventoryModel();
        $this->prescriptionModel = new PrescriptionModel();
    }

    public function dashboard()
    {
        $data = [
            'title' => 'Pharmacist Dashboard',
            'page' => 'dashboard'
        ];

        try {
            // Try to load models and get data
            $prescriptionModel = new PrescriptionModel();
            $inventoryModel = new InventoryModel();

            $data['statistics'] = $prescriptionModel->getStatistics();
            $data['low_stock_items'] = $inventoryModel->getLowStockItems();
            $data['recent_prescriptions'] = $prescriptionModel->getPrescriptions(['limit' => 5]);
            $data['expired_items'] = $inventoryModel->getExpiredItems();
            $data['inventory_items'] = $inventoryModel->getInventory();

        } catch (Exception $e) {
            // Fallback data when database/models fail
            $data['statistics'] = [
                'total_queued' => 0,
                'total_verifying' => 0,
                'total_ready' => 0,
                'total_dispensed' => 0,
                'stat_prescriptions' => 0,
                'today_dispensed' => 0
            ];
            $data['low_stock_items'] = [];
            $data['recent_prescriptions'] = [];
            $data['expired_items'] = [];
            $data['inventory_items'] = [];
            $data['error'] = 'Database not configured. Please contact administrator.';
        }

        return view('pharmacists/dashboard', $data);
    }

    public function prescription()
    {
        try {
            $filters = $this->request->getGet();

            $data = [
                'title' => 'Prescription Management',
                'page' => 'prescription',
                'prescriptions' => $this->prescriptionModel->getPrescriptions($filters),
                'queue' => $this->prescriptionModel->getQueue(),
                'priority_queue' => $this->prescriptionModel->getQueue('stat'),
                'interactions' => [], // Will be populated when checking interactions
                'statistics' => $this->prescriptionModel->getStatistics()
            ];

            return view('pharmacists/prescription', $data);

        } catch (Exception $e) {
            return view('pharmacists/prescription', [
                'title' => 'Prescription Management',
                'page' => 'prescription',
                'prescriptions' => [],
                'queue' => [],
                'priority_queue' => [],
                'interactions' => [],
                'statistics' => [
                    'total_queued' => 0,
                    'total_verifying' => 0,
                    'total_ready' => 0,
                    'total_dispensed' => 0,
                    'stat_prescriptions' => 0,
                    'today_dispensed' => 0
                ],
                'error' => 'Database not configured. Please contact administrator.'
            ]);
        }
    }

    public function inventory()
    {
        try {
            $filters = $this->request->getGet();

            $data = [
                'title' => 'Inventory Management',
                'page' => 'inventory',
                'inventory_items' => $this->inventoryModel->getInventory($filters),
                'low_stock_items' => $this->inventoryModel->getLowStockItems(),
                'expired_items' => $this->inventoryModel->getExpiredItems(),
                'transaction_history' => $this->inventoryModel->getTransactionHistory(null, 10)
            ];

            return view('pharmacists/inventory', $data);

        } catch (Exception $e) {
            return view('pharmacists/inventory', [
                'title' => 'Inventory Management',
                'page' => 'inventory',
                'inventory_items' => [],
                'low_stock_items' => [],
                'expired_items' => [],
                'transaction_history' => [],
                'error' => 'Database not configured. Please contact administrator.'
            ]);
        }
    }

    /**
     * Create new prescription
     */
    public function createPrescription()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'patient_id' => 'required',
            'patient_name' => 'required|min_length[3]',
            'medication' => 'required|min_length[3]',
            'quantity' => 'required|integer|greater_than[0]',
            'prescriber' => 'required|min_length[3]',
            'priority' => 'required|in_list[routine,priority,stat]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors()
            ]);
        }

        $prescriptionData = [
            'patient_id' => $this->request->getPost('patient_id'),
            'patient_name' => $this->request->getPost('patient_name'),
            'medication' => $this->request->getPost('medication'),
            'dosage' => $this->request->getPost('dosage'),
            'frequency' => $this->request->getPost('frequency'),
            'days_supply' => $this->request->getPost('days_supply'),
            'quantity' => $this->request->getPost('quantity'),
            'prescriber' => $this->request->getPost('prescriber'),
            'priority' => $this->request->getPost('priority'),
            'notes' => $this->request->getPost('notes')
        ];

        $result = $this->prescriptionModel->createPrescription($prescriptionData);

        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Prescription created successfully',
                'rx_number' => $this->prescriptionModel->getByRxNumber($prescriptionData['rx_number'])['rx_number']
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create prescription'
            ]);
        }
    }

    /**
     * Receive stock
     */
    public function receiveStock()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'item_code' => 'required',
            'quantity' => 'required|integer|greater_than[0]',
            'unit_price' => 'required|decimal|greater_than[0]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors()
            ]);
        }

        $stockData = [
            'item_code' => $this->request->getPost('item_code'),
            'name' => $this->request->getPost('name'),
            'category' => $this->request->getPost('category'),
            'quantity' => $this->request->getPost('quantity'),
            'unit' => $this->request->getPost('unit'),
            'batch_number' => $this->request->getPost('batch_number'),
            'expiry_date' => $this->request->getPost('expiry_date'),
            'unit_price' => $this->request->getPost('unit_price'),
            'reason' => 'Stock received'
        ];

        $result = $this->inventoryModel->receiveStock($stockData);

        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Stock received successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to receive stock'
            ]);
        }
    }

    /**
     * Adjust inventory
     */
    public function adjustInventory()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'item_code' => 'required',
            'adjustment' => 'required|integer',
            'reason' => 'required'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors()
            ]);
        }

        $adjustData = [
            'item_code' => $this->request->getPost('item_code'),
            'adjustment' => $this->request->getPost('adjustment'),
            'reason' => $this->request->getPost('reason')
        ];

        $result = $this->inventoryModel->adjustInventory($adjustData);

        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Inventory adjusted successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to adjust inventory or item not found'
            ]);
        }
    }

    /**
     * Dispense medication
     */
    public function dispenseMedication()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'rx_number' => 'required',
            'quantity' => 'required|integer|greater_than[0]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors()
            ]);
        }

        $rxNumber = $this->request->getPost('rx_number');
        $quantity = $this->request->getPost('quantity');

        $prescription = $this->prescriptionModel->getByRxNumber($rxNumber);

        if (!$prescription) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Prescription not found'
            ]);
        }

        if ($prescription['status'] !== 'ready') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Prescription is not ready for dispensing'
            ]);
        }

        // Check if we have enough stock
        $inventoryItem = $this->inventoryModel->where('name', $prescription['medication'])->first();
        if (!$inventoryItem || $inventoryItem['stock_quantity'] < $quantity) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Insufficient stock for this medication'
            ]);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Update prescription status
        $this->prescriptionModel->updateStatus($prescription['id'], 'dispensed', ['quantity' => $quantity]);

        // Reduce inventory
        $adjustData = [
            'item_code' => $inventoryItem['item_code'],
            'adjustment' => -$quantity,
            'reason' => 'Medication dispensed for RX: ' . $rxNumber
        ];

        $this->inventoryModel->adjustInventory($adjustData);

        $db->transComplete();

        if ($db->transStatus()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Medication dispensed successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to dispense medication'
            ]);
        }
    }

    /**
     * Check drug interactions
     */
    public function checkInteractions()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $medications = $this->request->getPost('medications');

        if (empty($medications)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No medications provided'
            ]);
        }

        $interactions = $this->prescriptionModel->checkInteractions($medications);

        return $this->response->setJSON([
            'success' => true,
            'interactions' => $interactions
        ]);
    }

    /**
     * Get inventory item details
     */
    public function getInventoryItem($itemCode)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $item = $this->inventoryModel->where('item_code', $itemCode)->first();

        if (!$item) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Item not found'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'item' => $item
        ]);
    }

    /**
     * Remove expired item
     */
    public function removeExpiredItem()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $itemCode = $this->request->getPost('item_code');

        if (!$itemCode) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Item code is required'
            ]);
        }

        $item = $this->inventoryModel->where('item_code', $itemCode)->first();

        if (!$item) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Item not found'
            ]);
        }

        // Log the removal
        $adjustData = [
            'item_code' => $itemCode,
            'adjustment' => -$item['stock_quantity'],
            'reason' => 'Expired item removed'
        ];

        $result = $this->inventoryModel->adjustInventory($adjustData);

        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Expired item removed successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to remove expired item'
            ]);
        }
    }
}
