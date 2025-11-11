<?php

namespace App\Services;

use CodeIgniter\Database\ConnectionInterface;
use App\Libraries\PermissionManager;

class ResourceService
{
    protected $db;
    protected $permissionManager;

    public function __construct(ConnectionInterface $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
        $this->permissionManager = new PermissionManager();
    }

    public function getResources($role, $staffId = null, $filters = [])
    {
        try {
            $builder = $this->db->table('resources r');
            
            switch ($role) {
                case 'admin':
                case 'it_staff':
                    break;
                case 'doctor':
                case 'nurse':
                    $builder->whereIn('r.category', ['Medical Equipment', 'Medical Supplies', 'Diagnostic Equipment']);
                    break;
                case 'pharmacist':
                    $builder->whereIn('r.category', ['Medical Supplies', 'Pharmacy Equipment', 'Medications']);
                    break;
                case 'laboratorist':
                    $builder->whereIn('r.category', ['Lab Equipment', 'Diagnostic Equipment', 'Medical Supplies']);
                    break;
                case 'receptionist':
                    $builder->whereIn('r.category', ['Office Equipment', 'IT Equipment']);
                    break;
                default:
                    $builder->where('1', '0');
                    break;
            }

            if (!empty($filters['category'])) {
                $builder->where('r.category', $filters['category']);
            }
            if (!empty($filters['status'])) {
                $builder->where('r.status', $filters['status']);
            }
            if (!empty($filters['location'])) {
                $builder->like('r.location', $filters['location']);
            }
            if (!empty($filters['search'])) {
                $builder->groupStart()
                    ->like('r.equipment_name', $filters['search'])
                    ->orLike('r.remarks', $filters['search'])
                    ->groupEnd();
            }

            $builder->select('r.*')
                ->orderBy('r.id', 'DESC');

            return $builder->get()->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'ResourceService::getResources - ' . $e->getMessage());
            return [];
        }
    }

    public function getResourceStats($role, $staffId = null)
    {
        try {
            $stats = [
                'total_resources' => 0,
                'available' => 0,
                'in_use' => 0,
                'maintenance' => 0,
                'out_of_order' => 0,
                'categories' => 0
            ];

            $builder = $this->db->table('resources');

            switch ($role) {
                case 'admin':
                case 'it_staff':
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
                    return $stats;
            }

            $stats['total_resources'] = $builder->countAllResults(false);
            $stats['available'] = $builder->where('status', 'Available')->countAllResults(false);
            $stats['in_use'] = $builder->where('status', 'In Use')->countAllResults(false);
            $stats['maintenance'] = $builder->where('status', 'Maintenance')->countAllResults(false);
            $stats['out_of_order'] = $builder->where('status', 'Out of Order')->countAllResults(false);

            $categoryBuilder = $this->db->table('resources');
            switch ($role) {
                case 'doctor':
                case 'nurse':
                    $categoryBuilder->whereIn('category', ['Medical Equipment', 'Medical Supplies', 'Diagnostic Equipment']);
                    break;
                case 'pharmacist':
                    $categoryBuilder->whereIn('category', ['Medical Supplies', 'Pharmacy Equipment', 'Medications']);
                    break;
                case 'laboratorist':
                    $categoryBuilder->whereIn('category', ['Lab Equipment', 'Diagnostic Equipment', 'Medical Supplies']);
                    break;
                case 'receptionist':
                    $categoryBuilder->whereIn('category', ['Office Equipment', 'IT Equipment']);
                    break;
            }
            
            $categories = $categoryBuilder->select('category')->distinct()->get()->getResultArray();
            $stats['categories'] = count($categories);

            return $stats;

        } catch (\Exception $e) {
            log_message('error', 'ResourceService::getResourceStats - ' . $e->getMessage());
            return $stats;
        }
    }

    public function createResource($data, $role, $staffId)
    {
        try {
            if (!$this->permissionManager->hasPermission($role, 'resources', 'create')) {
                return ['success' => false, 'message' => 'Insufficient permissions'];
            }

            $requiredFields = ['equipment_name', 'category', 'status', 'location'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return ['success' => false, 'message' => "Field '{$field}' is required"];
                }
            }

            $resourceData = [
                'equipment_name' => trim($data['equipment_name']),
                'category' => $data['category'],
                'quantity' => $data['quantity'] ?? 1,
                'status' => $data['status'] ?? 'Available',
                'location' => $data['location'] ?? '',
                'date_acquired' => $data['date_acquired'] ?? null,
                'supplier' => $data['supplier'] ?? '',
                'maintenance_schedule' => $data['maintenance_schedule'] ?? null,
                'remarks' => $data['remarks'] ?? ''
            ];

            $result = $this->db->table('resources')->insert($resourceData);

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Resource created successfully',
                    'resource_id' => $this->db->insertID()
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to create resource'];
            }

        } catch (\Exception $e) {
            log_message('error', 'ResourceService::createResource - ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred'];
        }
    }

    public function updateResource($resourceId, $data, $role, $staffId)
    {
        try {
            if (!$this->permissionManager->hasPermission($role, 'resources', 'edit')) {
                return ['success' => false, 'message' => 'Insufficient permissions'];
            }

            $resource = $this->db->table('resources')->where('id', $resourceId)->get()->getRow();
            if (!$resource) {
                return ['success' => false, 'message' => 'Resource not found'];
            }

            $updateData = [];
            $allowedFields = ['equipment_name', 'category', 'quantity', 'status', 'location',
                            'date_acquired', 'supplier', 'maintenance_schedule', 'remarks'];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }

            $result = $this->db->table('resources')->where('id', $resourceId)->update($updateData);

            return $result ? 
                ['success' => true, 'message' => 'Resource updated successfully'] :
                ['success' => false, 'message' => 'No changes made'];

        } catch (\Exception $e) {
            log_message('error', 'ResourceService::updateResource - ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred'];
        }
    }

    public function deleteResource($resourceId, $role, $staffId)
    {
        try {
            if (!$this->permissionManager->hasPermission($role, 'resources', 'delete')) {
                return ['success' => false, 'message' => 'Insufficient permissions'];
            }

            $resource = $this->db->table('resources')->where('id', $resourceId)->get()->getRow();
            if (!$resource) {
                return ['success' => false, 'message' => 'Resource not found'];
            }

            if ($resource->status === 'In Use') {
                return ['success' => false, 'message' => 'Cannot delete resource that is currently in use'];
            }

            $result = $this->db->table('resources')->where('id', $resourceId)->delete();

            return $result ? 
                ['success' => true, 'message' => 'Resource deleted successfully'] :
                ['success' => false, 'message' => 'Failed to delete resource'];

        } catch (\Exception $e) {
            log_message('error', 'ResourceService::deleteResource - ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred'];
        }
    }

    public function getResource($resourceId, $role, $staffId)
    {
        try {
            $builder = $this->db->table('resources r');
            
            switch ($role) {
                case 'admin':
                case 'it_staff':
                    break;
                case 'doctor':
                case 'nurse':
                    $builder->whereIn('r.category', ['Medical Equipment', 'Medical Supplies', 'Diagnostic Equipment']);
                    break;
                case 'pharmacist':
                    $builder->whereIn('r.category', ['Medical Supplies', 'Pharmacy Equipment', 'Medications']);
                    break;
                case 'laboratorist':
                    $builder->whereIn('r.category', ['Lab Equipment', 'Diagnostic Equipment', 'Medical Supplies']);
                    break;
                case 'receptionist':
                    $builder->whereIn('r.category', ['Office Equipment', 'IT Equipment']);
                    break;
                default:
                    return null;
            }

            return $builder->select('r.*')
                ->where('r.id', $resourceId)
                ->get()->getRow();

        } catch (\Exception $e) {
            log_message('error', 'ResourceService::getResource - ' . $e->getMessage());
            return null;
        }
    }

    public function getCategories($role)
    {
        $allCategories = [
            'Medical Equipment',
            'Medical Supplies', 
            'Diagnostic Equipment',
            'Lab Equipment',
            'Pharmacy Equipment',
            'Medications',
            'Office Equipment',
            'IT Equipment',
            'Furniture',
            'Vehicles',
            'Other'
        ];

        switch ($role) {
            case 'admin':
            case 'it_staff':
                return $allCategories;
            case 'doctor':
            case 'nurse':
                return ['Medical Equipment', 'Medical Supplies', 'Diagnostic Equipment'];
            case 'pharmacist':
                return ['Medical Supplies', 'Pharmacy Equipment', 'Medications'];
            case 'laboratorist':
                return ['Lab Equipment', 'Diagnostic Equipment', 'Medical Supplies'];
            case 'receptionist':
                return ['Office Equipment', 'IT Equipment'];
            default:
                return [];
        }
    }

    public function getStaffForAssignment()
    {
        try {
            return $this->db->table('staff')
                ->select('staff_id, first_name, last_name, role')
                ->where('status', 'Active')
                ->orderBy('first_name')
                ->get()->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'ResourceService::getStaffForAssignment - ' . $e->getMessage());
            return [];
        }
    }
}