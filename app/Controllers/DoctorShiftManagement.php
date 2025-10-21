<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class DoctorShiftManagement extends BaseController
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
        $data = ['title' => 'Doctor Shift Management'];
        return view('admin/doctor-shift-management', $data);
    }

    public function getDoctorShiftsAPI()
    {
        try {
            $builder = $this->db->table('doctor_shift ds')
                ->select('ds.shift_id as id, ds.shift_date as date, ds.shift_start as start, ds.shift_end as end, ds.department, ds.status, ds.shift_type, ds.duration_hours, ds.room_ward, ds.notes, d.doctor_id, s.first_name, s.last_name')
                ->join('doctor d', 'd.doctor_id = ds.doctor_id', 'left')
                ->join('staff s', 's.staff_id = d.staff_id', 'left')
                ->orderBy('ds.shift_date', 'DESC')
                ->orderBy('ds.shift_start', 'DESC');

            $rows = $builder->get()->getResultArray();
            $data = array_map(function ($r) {
                $r['doctor_name'] = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
                return $r;
            }, $rows);

            return $this->response->setJSON(['status' => 'success', 'data' => $data]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Failed to load doctor shifts']);
        }
    }

    public function getDoctorShift($id = null)
    {
        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Missing shift id']);
        }
        
        try {
            $r = $this->db->table('doctor_shift ds')
                ->select('ds.shift_id as id, ds.shift_date as date, ds.shift_start as start, ds.shift_end as end, ds.department, ds.status, ds.shift_type, ds.duration_hours, ds.room_ward, ds.notes, ds.doctor_id, s.first_name, s.last_name')
                ->join('doctor d', 'd.doctor_id = ds.doctor_id', 'left')
                ->join('staff s', 's.staff_id = d.staff_id', 'left')
                ->where('ds.shift_id', (int)$id)
                ->get()->getRowArray();
                
            if (!$r) {
                return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Shift not found']);
            }
            
            $r['doctor_name'] = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
            return $this->response->setJSON(['status' => 'success', 'data' => $r]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Failed to load shift']);
        }
    }

    public function create()
    {
        $input = $this->request->getJSON(true) ?? $this->request->getPost();

        $validation = \Config\Services::validation();
        $validation->setRules([
            'doctor_id'    => 'required|integer',
            'shift_date'   => 'required|valid_date',
            'shift_start'  => 'required',
            'shift_end'    => 'required',
            'department'   => 'permit_empty|max_length[100]',
            'status'       => 'permit_empty|in_list[Scheduled,Completed,Cancelled]',
            'shift_type'   => 'permit_empty|max_length[50]',
            'room_ward'    => 'permit_empty|max_length[100]',
            'notes'        => 'permit_empty',
        ]);
        
        if (!$validation->run($input)) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validation->getErrors(),
                'csrf' => [ 'name' => csrf_token(), 'value' => csrf_hash() ],
            ]);
        }

        $duration = $this->calculateDuration($input['shift_date'], $input['shift_start'], $input['shift_end']);

        try {
            $doctorId = (int)$input['doctor_id'];
            $exists = $this->db->table('doctor')->where('doctor_id', $doctorId)->countAllResults();
            if ($exists === 0) {
                return $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'message' => 'Selected doctor does not exist.',
                    'csrf' => [ 'name' => csrf_token(), 'value' => csrf_hash() ],
                ]);
            }

            $dept = $input['department'] ?? $this->getDoctorDepartment($doctorId);

            $data = [
                'doctor_id'      => $doctorId,
                'shift_date'     => $input['shift_date'],
                'shift_start'    => $input['shift_start'],
                'shift_end'      => $input['shift_end'],
                'department'     => $dept,
                'status'         => $input['status'] ?? 'Scheduled',
                'shift_type'     => $input['shift_type'] ?? null,
                'room_ward'      => $input['room_ward'] ?? null,
                'notes'          => $input['notes'] ?? null,
                'duration_hours' => $duration,
            ];
            
            if ($this->db->table('doctor_shift')->insert($data)) {
                return $this->response->setJSON([
                    'status'  => 'success',
                    'message' => 'Shift created',
                    'id'      => $this->db->insertID(),
                    'csrf'    => [ 'name' => csrf_token(), 'value' => csrf_hash() ],
                ]);
            }
            
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to create shift',
                'csrf' => [ 'name' => csrf_token(), 'value' => csrf_hash() ],
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to create shift',
                'csrf' => [ 'name' => csrf_token(), 'value' => csrf_hash() ],
            ]);
        }
    }

    public function update()
    {
        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        if (!is_array($input) || empty($input['id'])) {
            return $this->response->setStatusCode(422)->setJSON(['status' => 'error', 'message' => 'id is required']);
        }
        $id = (int)$input['id'];

        $exists = $this->db->table('doctor_shift')->where('shift_id', $id)->countAllResults();
        if ($exists === 0) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Shift not found',
                'csrf' => [ 'name' => csrf_token(), 'value' => csrf_hash() ],
            ]);
        }

        $data = array_filter([
            'shift_date'     => $input['shift_date'] ?? null,
            'shift_start'    => $input['shift_start'] ?? null,
            'shift_end'      => $input['shift_end'] ?? null,
            'department'     => $input['department'] ?? null,
            'status'         => $input['status'] ?? null,
            'shift_type'     => $input['shift_type'] ?? null,
            'room_ward'      => $input['room_ward'] ?? null,
            'notes'          => $input['notes'] ?? null,
        ], function($v) { return $v !== null; });

        if (!empty($input['shift_date']) && !empty($input['shift_start']) && !empty($input['shift_end'])) {
            $data['duration_hours'] = $this->calculateDuration($input['shift_date'], $input['shift_start'], $input['shift_end']);
        }

        if (empty($data)) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => 'No fields to update',
                'csrf' => [ 'name' => csrf_token(), 'value' => csrf_hash() ],
            ]);
        }

        try {
            if ($this->db->table('doctor_shift')->where('shift_id', $id)->update($data)) {
                return $this->response->setJSON([
                    'status'   => 'success',
                    'message'  => 'Shift updated',
                    'csrf'     => [ 'name' => csrf_token(), 'value' => csrf_hash() ],
                ]);
            }
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to update shift',
                'csrf' => [ 'name' => csrf_token(), 'value' => csrf_hash() ],
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to update shift',
                'csrf' => [ 'name' => csrf_token(), 'value' => csrf_hash() ],
            ]);
        }
    }

    public function delete()
    {
        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        $id = (int)($input['id'] ?? 0);
        if ($id <= 0) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => 'id is required',
                'csrf' => [ 'name' => csrf_token(), 'value' => csrf_hash() ],
            ]);
        }

        $exists = $this->db->table('doctor_shift')->where('shift_id', $id)->countAllResults();
        if ($exists === 0) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Shift not found',
                'csrf' => [ 'name' => csrf_token(), 'value' => csrf_hash() ],
            ]);
        }

        try {
            if ($this->db->table('doctor_shift')->where('shift_id', $id)->delete()) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Shift deleted',
                    'csrf' => [ 'name' => csrf_token(), 'value' => csrf_hash() ],
                ]);
            }
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to delete shift',
                'csrf' => [ 'name' => csrf_token(), 'value' => csrf_hash() ],
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to delete shift',
                'csrf' => [ 'name' => csrf_token(), 'value' => csrf_hash() ],
            ]);
        }
    }

    private function calculateDuration($date, $start, $end)
    {
        try {
            $startTime = new \DateTime($date.' '.$start);
            $endTime   = new \DateTime($date.' '.$end);
            if ($endTime < $startTime) { $endTime->modify('+1 day'); }
            $diff = $endTime->getTimestamp() - $startTime->getTimestamp();
            return round($diff / 3600, 2);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function getDoctorDepartment($doctorId)
    {
        try {
            $result = $this->db->table('doctor d')
                ->select('s.department')
                ->join('staff s', 's.staff_id = d.staff_id', 'left')
                ->where('d.doctor_id', $doctorId)
                ->get()->getRowArray();
            return $result['department'] ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}