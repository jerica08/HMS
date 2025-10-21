<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Shifts extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $totalStaff = 0;
        try {
            $totalStaff = $this->db->table('staff')->countAllResults();
        } catch (\Throwable $e) {
            log_message('error', 'Failed to count staff: ' . $e->getMessage());
        }

        $data = [
            'title' => 'Shifts Management',
            'total_staff' => $totalStaff,
        ];

        return view('admin/shifts', $data);
    }
}
