<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Nurse extends BaseController
{
    public function dashboard()
    {
        return view('nurse/dashboard');
    }

    public function patient()
    {
        return view('nurse/patient');
    }

    public function medication()
    {
        return view('nurse/medication');
    }

    public function vitals()
    {
        return view('nurse/vitals');
    }

    public function shiftReport()
    {
        return view('nurse/shift-report');
    }
}
