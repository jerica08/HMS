<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Departments extends BaseController
{
    public function ping()
    {
        return $this->response->setJSON(['status' => 'ok']);
    }

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

            // Support both JSON and form-data submissions
            $input = $this->request->getJSON(true);
            if (!is_array($input) || empty($input)) {
                $input = $this->request->getPost();
            }
            $name = trim(preg_replace('/\s+/', ' ', (string)($input['name'] ?? '')));
            $description = $input['description'] ?? null;

            if ($name === '') {
                return $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'message' => 'Department name is required',
                    'errors' => ['name' => 'Department name is required']
                ]);
            }

            $db = \Config\Database::connect();

            // Resolve table name dynamically: prefer 'department', else 'departments'
            $table = null;
            if ($db->tableExists('department')) {
                $table = 'department';
            } elseif ($db->tableExists('departments')) {
                $table = 'departments';
            } else {
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => "Neither 'department' nor 'departments' table exists."
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

            // Check exists (case-insensitive)
            $exists = $db->table($table)
                ->where('LOWER(' . $nameColumn . ')', strtolower($name))
                ->get()->getRowArray();
            if ($exists) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Department already exists',
                    'id' => $exists['department_id'] ?? null
                ]);
            }

            $builder = $db->table($table);

            // Try full insert first (name, description, timestamps)
            $now = date('Y-m-d H:i:s');
            $rowFull = [
                $nameColumn => $name,
                'description' => $description,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $ok = false;
            try {
                $ok = $builder->insert($rowFull);
            } catch (\Throwable $e) {
                // Fall back to fewer columns if schema lacks them
                try {
                    $ok = $builder->insert([$nameColumn => $name, 'description' => $description]);
                } catch (\Throwable $e2) {
                    try {
                        $ok = $builder->insert([$nameColumn => $name]);
                    } catch (\Throwable $e3) {
                        $ok = false;
                    }
                }
            }

            if ($ok) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Department created',
                    'id' => $db->insertID(),
                ]);
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
}
