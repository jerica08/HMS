<?php

namespace App\Models;

use CodeIgniter\Model;

class ResourceModel extends Model
{
    protected $table = 'resources';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'equipment_name',
        'category',
        'quantity',
        'status',
        'location',
        'batch_number',
        'expiry_date',
        'serial_number',
        'remarks',
        'assigned_to_staff_id',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'equipment_name' => 'required|min_length[2]|max_length[255]',
        'category' => 'required|in_list[Medical Equipment,Medical Supplies,Diagnostic Equipment,Lab Equipment,Pharmacy Equipment,Medications,Office Equipment,IT Equipment,Furniture,Vehicles,Other]',
        'quantity' => 'required|integer|greater_than[0]',
        'status' => 'required|in_list[Stock In,Stock Out]',
        'location' => 'permit_empty|max_length[255]',
        'batch_number' => 'permit_empty|max_length[100]',
        'expiry_date' => 'permit_empty|valid_date[Y-m-d]',
        'serial_number' => 'permit_empty|max_length[100]',
        'remarks' => 'permit_empty|max_length[1000]'
    ];

    protected $validationMessages = [
        'equipment_name' => [
            'required' => 'Resource name is required.',
            'min_length' => 'Resource name must be at least 2 characters.'
        ],
        'category' => [
            'required' => 'Category is required.',
            'in_list' => 'Please select a valid category.'
        ],
        'quantity' => [
            'required' => 'Quantity is required.',
            'greater_than' => 'Quantity must be greater than 0.'
        ]
    ];

    /**
     * Get resources with filtering
     */
    public function getResources($filters = [], $role = null, $staffId = null)
    {
        $builder = $this->builder();

        // Role-based filtering
        if ($role) {
            $this->applyRoleFilter($builder, $role);
        }

        // Search filter
        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('equipment_name', $filters['search'])
                ->orLike('location', $filters['search'])
                ->orLike('remarks', $filters['search'])
                ->groupEnd();
        }

        // Category filter
        if (!empty($filters['category'])) {
            $builder->where('category', $filters['category']);
        }

        // Status filter
        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        // Location filter
        if (!empty($filters['location'])) {
            $builder->like('location', $filters['location']);
        }

        return $builder->orderBy('id', 'DESC')->get()->getResultArray();
    }

    /**
     * Apply role-based filtering to query builder
     */
    private function applyRoleFilter($builder, $role)
    {
        switch ($role) {
            case 'admin':
            case 'it_staff':
                // No filtering - can see all resources
                break;
            case 'doctor':
            case 'nurse':
                $builder->whereIn('category', ['Medical Equipment', 'Medical Supplies', 'Diagnostic Equipment']);
                break;
            case 'pharmacist':
                $builder->whereIn('category', ['Medical Supplies', 'Pharmacy Equipment', 'Medications']);
                break;
            case 'laboratorist':
                $builder->whereIn('category', ['Lab Equipment', 'Diagnostic Equipment', 'Medical Supplies']);
                break;
            case 'receptionist':
                $builder->whereIn('category', ['Office Equipment', 'IT Equipment']);
                break;
            default:
                $builder->where('1', '0'); // No access
                break;
        }
    }

    /**
     * Get resource statistics
     */
    public function getStats($role = null, $staffId = null)
    {
        $builder = $this->builder();

        // Apply role-based filtering
        if ($role) {
            $this->applyRoleFilter($builder, $role);
        }

        $stats = [
            'total_resources' => 0,
            'stock_in' => 0,
            'stock_out' => 0,
            'categories' => 0,
            'low_quantity' => 0 // Resources with quantity <= 3
        ];

        // Get all resources in a single query for efficient counting
        $allResources = $builder->select('status, category, quantity')
            ->get()
            ->getResultArray();
        
        $stats['total_resources'] = count($allResources);

        $categories = [];
        foreach ($allResources as $resource) {
            // Count by status
            switch ($resource['status']) {
                case 'Stock In':
                    $stats['stock_in']++;
                    break;
                case 'Stock Out':
                    $stats['stock_out']++;
                    break;
            }

            // Track categories
            if (!empty($resource['category'])) {
                $categories[$resource['category']] = true;
            }

            // Count low quantity resources
            if (isset($resource['quantity']) && $resource['quantity'] <= 3) {
                $stats['low_quantity']++;
            }
        }

        $stats['categories'] = count($categories);

        return $stats;
    }

    /**
     * Get medications with expired or expiring soon
     */
    public function getExpiringMedications($daysAhead = 30)
    {
        $futureDate = date('Y-m-d', strtotime("+{$daysAhead} days"));
        
        return $this->where('category', 'Medications')
            ->where('expiry_date <=', $futureDate)
            ->where('expiry_date >=', date('Y-m-d'))
            ->where('status', 'Stock In')
            ->orderBy('expiry_date', 'ASC')
            ->findAll();
    }

    /**
     * Get expired medications
     */
    public function getExpiredMedications()
    {
        return $this->where('category', 'Medications')
            ->where('expiry_date <', date('Y-m-d'))
            ->where('expiry_date !=', null)
            ->orderBy('expiry_date', 'ASC')
            ->findAll();
    }

    /**
     * Get low quantity resources
     */
    public function getLowQuantity($threshold = 5)
    {
        return $this->where('quantity <=', $threshold)
            ->where('status', 'Stock In')
            ->orderBy('quantity', 'ASC')
            ->findAll();
    }

    /**
     * Assign resource to staff
     */
    public function assignToStaff($resourceId, $staffId)
    {
        return $this->update($resourceId, [
            'assigned_to_staff_id' => $staffId,
            'status' => 'Stock Out',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Release resource assignment
     */
    public function releaseAssignment($resourceId)
    {
        return $this->update($resourceId, [
            'assigned_to_staff_id' => null,
            'status' => 'Stock In',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Update resource quantity safely (prevent negative)
     */
    public function updateQuantity($resourceId, $quantityChange, $operation = 'add')
    {
        $resource = $this->find($resourceId);
        if (!$resource) {
            return false;
        }

        $currentQuantity = (int)($resource['quantity'] ?? 0);
        
        if ($operation === 'add') {
            $newQuantity = $currentQuantity + $quantityChange;
        } else {
            $newQuantity = max(0, $currentQuantity - $quantityChange);
        }

        return $this->update($resourceId, [
            'quantity' => $newQuantity,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}

