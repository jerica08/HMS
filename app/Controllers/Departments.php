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
            $description = $input['description'] ?? null;

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
            $attempts = [
                [ $nameColumn => $name, 'description' => $description, 'created_at' => $now, 'updated_at' => $now ],
                [ $nameColumn => $name, 'description' => $description ],
                [ $nameColumn => $name ],
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
}
