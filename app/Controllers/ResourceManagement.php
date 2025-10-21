<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class ResourceManagement extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();

        $session = session();
        if (!$session->get('isLoggedIn') || $session->get('role') !== 'admin') {
            redirect()->to(base_url('/login'))->send();
            exit();
        }
    }

    public function index()
    {
        $resources = [];
        try {
            $rows = $this->db->table('resources')->get()->getResultArray();
            foreach ($rows as $r) {
                $r['name'] = $r['equipment_name'] ?? null;
                $r['notes'] = $r['remarks'] ?? null;
                $resources[] = $r;
            }
        } catch (\Throwable $e) {
            $resources = [];
        }
        $data = [
            'title' => 'Resource Management',
            'resources' => $resources,
        ];
        return view('admin/resource-management', $data);
    }

    public function getResourcesAPI()
    {
        try {
            $rows = $this->db->table('resources')->get()->getResultArray();
            $data = [];
            foreach ($rows as $r) {
                $r['name'] = $r['equipment_name'] ?? null;
                $r['notes'] = $r['remarks'] ?? null;
                $data[] = $r;
            }
            return $this->response->setJSON(['status' => 'success', 'data' => $data]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error']);
        }
    }

    public function getResource($id = null)
    {
        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error']);
        }
        try {
            $r = $this->db->table('resources')->where('id', (int)$id)->get()->getRowArray();
            if (!$r) {
                return $this->response->setStatusCode(404)->setJSON(['status' => 'error']);
            }
            $r['name'] = $r['equipment_name'] ?? null;
            $r['notes'] = $r['remarks'] ?? null;
            return $this->response->setJSON(['status' => 'success', 'data' => $r]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error']);
        }
    }

    public function create()
    {
        $input = $this->request->getPost();
        $ct = strtolower($this->request->getHeaderLine('Content-Type'));
        if (strpos($ct, 'application/json') !== false) {
            try { 
                $json = $this->request->getJSON(true); 
                if (is_array($json)) { $input = $json; } 
            } catch (\Throwable $e) { /* ignore */ }
        }
        if (!is_array($input)) { $input = []; }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'name' => 'required|min_length[1]|max_length[100]',
            'category' => 'required|max_length[50]',
            'quantity' => 'required|integer|greater_than_equal_to[0]',
            'status' => 'required|in_list[available,in_use,maintenance,retired]',
            'location' => 'permit_empty|max_length[100]',
            'supplier' => 'permit_empty|max_length[100]',
            'date_acquired' => 'permit_empty|valid_date',
            'maintenance_schedule' => 'permit_empty|valid_date',
        ]);
        
        if (!$validation->run($input)) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error', 
                'errors' => $validation->getErrors()
            ]);
        }

        $data = [
            'equipment_name' => trim((string)$input['name']),
            'category' => trim((string)$input['category']),
            'quantity' => (int)$input['quantity'],
            'status' => trim((string)$input['status']),
            'location' => $input['location'] ?? '',
            'date_acquired' => !empty($input['date_acquired']) ? $input['date_acquired'] : null,
            'supplier' => $input['supplier'] ?? '',
            'maintenance_schedule' => !empty($input['maintenance_schedule']) ? $input['maintenance_schedule'] : null,
            'remarks' => $input['notes'] ?? null,
        ];
        
        try {
            if ($this->db->table('resources')->insert($data)) {
                return $this->response->setJSON(['status' => 'success', 'id' => $this->db->insertID()]);
            }
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Failed to create resource']);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function update()
    {
        $input = $this->request->getPost();
        $ct = strtolower($this->request->getHeaderLine('Content-Type'));
        if (strpos($ct, 'application/json') !== false) {
            try { 
                $json = $this->request->getJSON(true); 
                if (is_array($json)) { $input = $json; } 
            } catch (\Throwable $e) { /* ignore */ }
        }
        
        if (!is_array($input) || empty($input['id'])) {
            return $this->response->setStatusCode(422)->setJSON(['status' => 'error', 'message' => 'id is required']);
        }
        $id = (int)$input['id'];

        $data = [
            'equipment_name' => $input['name'] ?? null,
            'category' => $input['category'] ?? null,
            'quantity' => isset($input['quantity']) ? (int)$input['quantity'] : null,
            'status' => $input['status'] ?? null,
            'location' => $input['location'] ?? null,
            'supplier' => $input['supplier'] ?? null,
            'remarks' => $input['notes'] ?? null,
        ];
        
        $data = array_filter($data, function($v){ return $v !== null; });
        if (empty($data)) {
            return $this->response->setStatusCode(422)->setJSON(['status' => 'error', 'message' => 'No fields to update']);
        }
        
        try {
            if ($this->db->table('resources')->where('id', $id)->update($data)) {
                return $this->response->setJSON(['status' => 'success']);
            }
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error']);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error']);
        }
    }

    public function delete()
    {
        $input = $this->request->getPost();
        $ct = strtolower($this->request->getHeaderLine('Content-Type'));
        if (strpos($ct, 'application/json') !== false) {
            try { 
                $json = $this->request->getJSON(true); 
                if (is_array($json)) { $input = $json; } 
            } catch (\Throwable $e) { /* ignore */ }
        }
        
        if (!is_array($input) || empty($input['id'])) {
            return $this->response->setStatusCode(422)->setJSON(['status' => 'error', 'message' => 'id is required']);
        }
        $id = (int)$input['id'];
        
        try {
            if ($this->db->table('resources')->where('id', $id)->delete()) {
                return $this->response->setJSON(['status' => 'success']);
            }
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error']);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error']);
        }
    }
}