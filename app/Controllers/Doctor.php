<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Doctor extends BaseController
{
    public function dashboard()
    {
        return view ('doctor/dashboard');
    }

    public function patients()
    {
        return view('doctor/patient');
    }

    public function appointments()
    {
        return view('doctor/appointments');
    }

    public function prescriptions()
    {
        return view('doctor/prescriptions');
    }

    public function labResults()
    {
        return view('doctor/lab-results');
    }

    public function ehr()
    {
        return view('doctor/ehr');
    }

    public function schedule()
    {
        return view('doctor/schedule');
    }
}
