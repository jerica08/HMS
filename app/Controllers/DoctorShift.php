<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class DoctorShift extends Controller
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        try {
            $builder = $this->db->table('doctor_shift ds')
                ->select([
                    'ds.shift_id as id',
                    'ds.doctor_id',
                    'ds.shift_date as date',
                    'ds.shift_start as start',
                    'ds.shift_end as end',
                    'ds.department',
                    'ds.shift_type',
                    'ds.duration_hours',
                    'ds.room_ward',
                    'ds.status',
                    "CONCAT(COALESCE(s.first_name,''),' ',COALESCE(s.last_name,'')) as doctor_name",
                ])
                ->join('doctor d', 'd.doctor_id = ds.doctor_id', 'left')
                ->join('staff s', 's.staff_id = d.staff_id', 'left')
                ->orderBy('ds.shift_date', 'DESC')
                ->orderBy('ds.shift_start', 'DESC');
            $rows = $builder->get()->getResultArray();
            return $this->response->setJSON(['status' => 'success', 'data' => $rows]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error']);
        }
    }

    public function create()
    {
        $input = $this->request->getPost();
        $ct = strtolower($this->request->getHeaderLine('Content-Type'));
        if (strpos($ct, 'application/json') !== false) {
            try { $json = $this->request->getJSON(true); if (is_array($json)) { $input = $json; } } catch (\Throwable $e) {}
        }
        if (!is_array($input)) { $input = []; }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'doctor_id' => 'required|integer',
            'shift_date' => 'required|valid_date',
            'shift_start' => 'required',
            'shift_end' => 'required',
            'department' => 'permit_empty|max_length[100]',
            'shift_type' => 'permit_empty|max_length[50]',
            'notes' => 'permit_empty',
            'status' => 'permit_empty|in_list[Scheduled,Completed,Cancelled]',
        ]);
        if (!$validation->run($input)) {
            return $this->response->setStatusCode(422)->setJSON(['status' => 'error', 'errors' => $validation->getErrors()]);
        }

        $duration = null;
        try {
            $start = new \DateTime($input['shift_start']);
            $end = new \DateTime($input['shift_end']);
            $diff = $end->getTimestamp() - $start->getTimestamp();
            if ($diff < 0) { $diff += 24*3600; }
            $duration = round($diff / 3600, 2);
        } catch (\Throwable $e) {}

        $data = [
            'doctor_id' => (int)$input['doctor_id'],
            'department' => $input['department'] ?? null,
            'shift_date' => $input['shift_date'],
            'shift_start' => $input['shift_start'],
            'shift_end' => $input['shift_end'],
            'shift_type' => $input['shift_type'] ?? null,
            'duration_hours' => $duration,
            'room_ward' => $input['room_ward'] ?? null,
            'notes' => $input['notes'] ?? null,
            'status' => $input['status'] ?? 'Scheduled',
        ];

        try {
            $ok = $this->db->table('doctor_shift')->insert($data);
            if ($ok) {
                return $this->response->setJSON(['status' => 'success', 'id' => $this->db->insertID()]);
            }
            $err = $this->db->error();
            $lastQuery = null; try { $lastQuery = (string)$this->db->getLastQuery(); } catch (\Throwable $__) {}
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'db' => $err, 'lastQuery' => $lastQuery]);
        } catch (\Throwable $e) {
            $lastQuery = null; try { $lastQuery = (string)$this->db->getLastQuery(); } catch (\Throwable $__) {}
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => $e->getMessage(), 'lastQuery' => $lastQuery]);
        }
    }

    public function delete($id = null)
    {
        $id = $id !== null ? (int)$id : (int)($this->request->getPost('id') ?? 0);
        if ($id <= 0) {
            return $this->response->setStatusCode(422)->setJSON(['status' => 'error', 'message' => 'id is required']);
        }
        try {
            $ok = $this->db->table('doctor_shift')->where('shift_id', $id)->delete();
            if ($ok) { return $this->response->setJSON(['status' => 'success']); }
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error']);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error']);
        }
    }
}
