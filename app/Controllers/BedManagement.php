<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class BedManagement extends BaseController
{
    protected $db;
    protected $userRole;
    protected $staffId;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $session = session();
        $this->userRole = $session->get('role');
        $this->staffId = $session->get('staff_id');
    }

    public function index()
    {
        // Simple listing of beds with room info (if available)
        $builder = $this->db->table('bed b')
            ->select('b.*, r.room_number, r.room_type')
            ->join('room r', 'r.room_id = b.room_id', 'left')
            ->orderBy('r.room_number', 'ASC')
            ->orderBy('b.bed_number', 'ASC');

        $beds = $builder->get()->getResultArray();

        // Rooms for dropdown when adding a bed
        $rooms = $this->db->table('room')
            ->select('room_id, room_number, room_type')
            ->orderBy('room_number', 'ASC')
            ->get()->getResultArray();

        $data = [
            'title' => 'Bed Management',
            'userRole' => $this->userRole,
            'beds' => $beds,
            'rooms' => $rooms,
        ];

        return view('unified/bed-management', $data);
    }

    public function create()
    {
        if (!in_array($this->userRole, ['admin', 'it_staff', 'nurse'])) {
            return redirect()->back()->with('error', 'Insufficient permissions to add beds.');
        }

        if ($this->request->getMethod() !== 'post') {
            return redirect()->back();
        }

        $input = $this->request->getPost();

        $roomId = (int)($input['room_id'] ?? 0);
        $bedNumber = trim($input['bed_number'] ?? '');
        $status = $input['status'] ?? 'available';

        if ($roomId <= 0 || $bedNumber === '') {
            return redirect()->back()->with('error', 'Room and bed number are required.');
        }

        $data = [
            'room_id' => $roomId,
            'bed_number' => $bedNumber,
            'status' => $status,
            'bed_daily_rate' => $input['bed_daily_rate'] !== '' ? $input['bed_daily_rate'] : null,
            'bed_hourly_rate' => $input['bed_hourly_rate'] !== '' ? $input['bed_hourly_rate'] : null,
            'last_cleaned_at' => $input['last_cleaned_at'] !== '' ? $input['last_cleaned_at'] : null,
        ];

        try {
            $this->db->table('bed')->insert($data);
            return redirect()->to(base_url('admin/bed-management'))
                ->with('success', 'Bed added successfully.');
        } catch (\Throwable $e) {
            log_message('error', 'Failed to create bed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to add bed.');
        }
    }
}
