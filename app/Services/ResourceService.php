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
                    ->like('r.name', $filters['search'])
                    ->orLike('r.description', $filters['search'])
                    ->orLike('r.serial_number', $filters['search'])
                    ->groupEnd();
            }

            $builder->select('r.*, s.first_name, s.last_name')
                ->join('staff s', 's.staff_id = r.assigned_to', 'left')
                ->orderBy('r.created_at', 'DESC');

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

            $requiredFields = ['name', 'category', 'status', 'location'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return ['success' => false, 'message' => "Field '{$field}' is required"];
                }
            }

            $resourceData = [
                'name' => trim($data['name']),
                'category' => $data['category'],
                'description' => $data['description'] ?? '',
                'serial_number' => $data['serial_number'] ?? '',
                'model' => $data['model'] ?? '',
                'manufacturer' => $data['manufacturer'] ?? '',
                'purchase_date' => $data['purchase_date'] ?? null,
                'warranty_expiry' => $data['warranty_expiry'] ?? null,
                'cost' => $data['cost'] ?? 0,
                'status' => $data['status'],
                'location' => $data['location'],
                'assigned_to' => $data['assigned_to'] ?? null,
                'notes' => $data['notes'] ?? '',
                'created_by' => $staffId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if (!empty($resourceData['serial_number'])) {
                $existing = $this->db->table('resources')
                    ->where('serial_number', $resourceData['serial_number'])
                    ->get()->getRow();
                
                if ($existing) {
                    return ['success' => false, 'message' => 'Serial number already exists'];
                }
            }

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

            $resource = $this->db->table('resources')->where('resource_id', $resourceId)->get()->getRow();
            if (!$resource) {
                return ['success' => false, 'message' => 'Resource not found'];
            }

            $updateData = ['updated_at' => date('Y-m-d H:i:s')];
            $allowedFields = ['name', 'category', 'description', 'serial_number', 'model', 
                            'manufacturer', 'purchase_date', 'warranty_expiry', 'cost', 
                            'status', 'location', 'assigned_to', 'notes'];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }

            $result = $this->db->table('resources')->where('resource_id', $resourceId)->update($updateData);

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

            $resource = $this->db->table('resources')->where('resource_id', $resourceId)->get()->getRow();
            if (!$resource) {
                return ['success' => false, 'message' => 'Resource not found'];
            }

            if ($resource->status === 'In Use' && !empty($resource->assigned_to)) {
                return ['success' => false, 'message' => 'Cannot delete resource that is currently in use'];
            }

            $result = $this->db->table('resources')->where('resource_id', $resourceId)->delete();

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

            return $builder->select('r.*, s.first_name, s.last_name')
                ->join('staff s', 's.staff_id = r.assigned_to', 'left')
                ->where('r.resource_id', $resourceId)
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