<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Departments extends BaseController
{
    public function create()
    {
        try {
            // Allow POST for create and OPTIONS for preflight
            $method = strtolower($this->request->getMethod());
            if ($method === 'options') {
                return $this->response->setStatusCode(200);
            }
            if ($method !== 'post') {
                return $this->response->setStatusCode(405)->setJSON(['status' => 'error', 'message' => 'Method not allowed']);
            }

            // Support both JSON and form-data submissions safely
            $input = [];
            $contentType = $this->request->getHeaderLine('Content-Type');
            if ($contentType && stripos($contentType, 'application/json') !== false) {
                // Only attempt JSON parsing when Content-Type is JSON
                $rawBody = (string) $this->request->getBody();
                if ($rawBody !== '') {
                    $decoded = json_decode($rawBody, true);
                    if (is_array($decoded)) {
                        $input = $decoded;
                    }
                }
            }
            if (empty($input)) {
                // Fallback to form fields (e.g., multipart/form-data)
                $input = $this->request->getPost();
            }
            $name = trim(preg_replace('/\s+/', ' ', (string)($input['name'] ?? '')));
            $description = $this->sanitizeString($input['description'] ?? null);
            $code = $this->sanitizeString($input['code'] ?? null, 20);
            $floor = $this->sanitizeString($input['floor'] ?? null, 50);
            $building = $this->sanitizeString($input['building'] ?? null, 100);
            $contactNumber = $this->sanitizeString($input['contact_number'] ?? null, 20);
            $departmentHeadId = $this->parseNullableInt($input['department_head'] ?? null);
            $status = $this->normalizeStatus($input['status'] ?? null);
            $departmentType = $this->normalizeDepartmentType($input['department_type'] ?? null);

            if ($name === '') {
                return $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'message' => 'Department name is required',
                    'errors' => ['name' => 'Department name is required']
                ]);
            }

            $db = \Config\Database::connect();

            // Resolve table name dynamically: prefer 'department', support common variants
            $table = null;
            if ($db->tableExists('department')) {
                $table = 'department';
            } elseif ($db->tableExists('deaprtment')) { // handle misspelling
                $table = 'deaprtment';
            } elseif ($db->tableExists('departments')) {
                $table = 'departments';
            } else {
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => "No department table found (looked for 'department', 'deaprtment', 'departments')."
                ]);
            }

            // Determine the correct name column in department table
            $nameColumn = null;
            if ($db->fieldExists('name', $table)) {
                $nameColumn = 'name';
            } elseif ($db->fieldExists('department_name', $table)) {
                $nameColumn = 'department_name';
            } elseif ($db->fieldExists('dept_name', $table)) {
                $nameColumn = 'dept_name';
            }
            if ($nameColumn === null) {
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => 'Department table is missing a name column (expected "name" or "department_name").',
                ]);
            }

            // Check exists (case-insensitive) with proper quoting of value
            $exists = $db->table($table)
                ->where('LOWER(' . $nameColumn . ') = ' . $db->escape(strtolower($name)), null, false)
                ->get()->getRowArray();
            if ($exists) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Department already exists',
                    'id' => $exists['department_id'] ?? null
                ]);
            }

            $builder = $db->table($table);

            // Try a series of inserts, progressively reducing columns
            $now = date('Y-m-d H:i:s');
            $payload = [$nameColumn => $name];

            $optionalColumns = [
                'code' => $code,
                'floor' => $floor,
                'building' => $building,
                'department_head_id' => $departmentHeadId,
                'contact_number' => $contactNumber,
                'description' => $description,
                'status' => $status,
                'type' => $departmentType,
            ];

            foreach ($optionalColumns as $column => $value) {
                if ($this->fieldExists($column, $table)) {
                    $payload[$column] = $value;
                }
            }

            if ($this->fieldExists('created_at', $table)) {
                $payload['created_at'] = $now;
            }
            if ($this->fieldExists('updated_at', $table)) {
                $payload['updated_at'] = $now;
            }

            $attempts = [
                $payload,
                [$nameColumn => $name],
            ];

            $ok = false;
            foreach ($attempts as $row) {
                $ok = $builder->insert($row);
                if ($ok) {
                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => 'Department created',
                        'id' => $db->insertID(),
                    ]);
                }
            }

            // Provide DB error for diagnostics
            $dbError = $db->error();
            log_message('error', 'Department insert failed: ' . ($dbError['message'] ?? 'unknown'));
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to create department',
                'db_error' => $dbError,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Departments::create error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Server error',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    private function sanitizeString(?string $value, ?int $maxLength = null): ?string
    {
        if ($value === null) {
            return null;
        }

        $clean = trim(preg_replace('/\s+/', ' ', strip_tags($value)));
        if ($clean === '') {
            return null;
        }

        if ($maxLength !== null) {
            $clean = mb_substr($clean, 0, $maxLength);
        }

        return $clean;
    }

    private function parseNullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    private function normalizeStatus(?string $value): ?string
    {
        $allowed = ['Active', 'Inactive'];
        if ($value === null) {
            return null;
        }

        $normalized = ucfirst(strtolower(trim($value)));
        return in_array($normalized, $allowed, true) ? $normalized : null;
    }

    private function normalizeDepartmentType(?string $value): ?string
    {
        $allowed = ['Clinical', 'Administrative', 'Emergency', 'Diagnostic', 'Support'];
        if ($value === null) {
            return null;
        }

        $normalized = ucfirst(strtolower(trim($value)));
        return in_array($normalized, $allowed, true) ? $normalized : null;
    }

    private function fieldExists(string $field, string $table): bool
    {
        try {
            return \Config\Database::connect()->fieldExists($field, $table);
        } catch (\Throwable $e) {
            log_message('warning', 'fieldExists check failed: ' . $e->getMessage());
            return false;
        }
    }
<<<<<<< HEAD
=======

    /**
     * Create Medical Department
     */
    public function createMedical()
    {
        return $this->createDepartment('medical_departments', 'medical_department_id', null);
    }

    /**
     * Create Non-Medical Department
     */
    public function createNonMedical()
    {
        return $this->createDepartment('non_medical_departments', 'non_medical_department_id', 'non_medical_function');
    }

    /**
     * Generic department creation method
     */
    private function createDepartment(string $table, string $idField, ?string $categoryField = null)
    {
        try {
            $method = strtolower($this->request->getMethod());
            if ($method === 'options') {
                return $this->response->setStatusCode(200);
            }
            if ($method !== 'post') {
                return $this->response->setStatusCode(405)->setJSON(['status' => 'error', 'message' => 'Method not allowed']);
            }

            // Get input data
            $input = [];
            $contentType = $this->request->getHeaderLine('Content-Type');
            if ($contentType && stripos($contentType, 'application/json') !== false) {
                $rawBody = (string) $this->request->getBody();
                if ($rawBody !== '') {
                    $decoded = json_decode($rawBody, true);
                    if (is_array($decoded)) {
                        $input = $decoded;
                    }
                }
            }
            if (empty($input)) {
                $input = $this->request->getPost();
            }

            // Validate required fields
            $name = trim(preg_replace('/\s+/', ' ', (string)($input['name'] ?? '')));
            $category = $input['department_category'] ?? '';
            
            // Debug logging
            log_message('info', 'Department creation request: ' . json_encode([
                'table' => $table,
                'input' => $input,
                'name' => $name,
                'category' => $category
            ]));
            
            // Get the category-specific value based on the category type
            $categoryValue = '';
            if ($category === 'non_medical') {
                $categoryValue = $input['non_medical_function'] ?? '';
            }

            if ($name === '') {
                return $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'message' => 'Department name is required'
                ]);
            }

            // Only validate category for non-medical departments (medical departments don't need category validation)
            if ($table === 'non_medical_departments' && $category === '') {
                return $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'message' => 'Department category is required'
                ]);
            }

            // Only validate category value for non-medical departments
            if ($table === 'non_medical_departments' && $categoryValue === '') {
                return $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'message' => 'Function is required for non-medical departments'
                ]);
            }

            // Prepare data
            $db = \Config\Database::connect();
            
            // Check if the table exists
            if (!$db->tableExists($table)) {
                log_message('error', 'Table does not exist: ' . $table);
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => "Database table '$table' does not exist. Please run database migrations first."
                ]);
            }
            
            // Log the data for debugging
            log_message('info', 'Department creation data: ' . json_encode([
                'table' => $table,
                'categoryField' => $categoryField,
                'category' => $category,
                'categoryValue' => $categoryValue,
                'input' => $input
            ]));
            
            $data = [
                'name' => $this->sanitizeString($name),
                'code' => $this->sanitizeString($input['code'] ?? null, 20),
                'floor' => $this->sanitizeString($input['floor'] ?? null, 50),
                'contact_number' => $this->sanitizeString($input['contact_number'] ?? null, 20),
                'description' => $this->sanitizeString($input['description'] ?? null),
                'department_head_id' => $this->parseNullableInt($input['department_head'] ?? null),
                'status' => $this->normalizeStatus($input['status'] ?? null) ?: 'Active',
            ];
            
            // Only add timestamps if they exist in the table
            if ($this->fieldExists('created_at', $table)) {
                $data['created_at'] = date('Y-m-d H:i:s');
            }
            if ($this->fieldExists('updated_at', $table)) {
                $data['updated_at'] = date('Y-m-d H:i:s');
            }
            
            // Add category-specific field only if categoryField is provided (for non-medical departments)
            if ($categoryField && $categoryValue && $this->fieldExists($categoryField, $table)) {
                $data[$categoryField] = $this->sanitizeString($categoryValue, 100);
            }
            
            log_message('info', 'Final data array: ' . json_encode($data));

            // Insert department
            try {
                if ($db->table($table)->insert($data)) {
                    $departmentId = $db->insertID();
                    log_message('info', 'Department created successfully with ID: ' . $departmentId);
                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => 'Department created successfully',
                        'department_id' => $departmentId
                    ]);
                } else {
                    $dbError = $db->error();
                    log_message('error', 'Database insert failed: ' . json_encode($dbError));
                    return $this->response->setStatusCode(500)->setJSON([
                        'status' => 'error',
                        'message' => 'Failed to create department',
                        'db_error' => $dbError,
                    ]);
                }
            } catch (\Exception $e) {
                log_message('error', 'Exception during insert: ' . $e->getMessage());
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => 'Database error: ' . $e->getMessage(),
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Create department error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Server error while creating department'
            ]);
        }
    }
>>>>>>> 03d4e70 (COMMITenter the commit message for your changes. Lines starting)
}
