<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class AdminAppointments extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Appointment Management',
            'todayStats' => [
                'total' => 0,
                'completed' => 0,
                'pending' => 0,
            ],
            'weekStats' => [
                'total' => 0,
                'cancelled' => 0,
                'no_shows' => 0,
            ],
            'scheduleStats' => [
                'next_appointment' => null,
                'hours_scheduled' => 0,
            ],
            'todayAppointments' => [],
        ];

        return view('admin/appointments', $data);
    }
}
