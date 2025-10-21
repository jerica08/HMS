<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class PatientManagement extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Patient Management',
        ];
        return view('admin/patient-management', $data);
    }
}
