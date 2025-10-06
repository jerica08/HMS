<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Receptionist extends BaseController
{
    public function dashboard()
    {
        return view('receptionist/dashboard');
    }

    public function appointmentBooking()
    {
        return view('receptionist/appointment-booking');
    }

    public function patientRegistration()
    {
        return view('receptionist/patient-registration');
    }
    public function logout()
    {
        $session = session();
        $session->destroy();
        return redirect()->to(base_url('/login'));
    }

}
