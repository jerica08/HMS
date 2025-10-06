<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class ITStaff extends BaseController
{
    public function dashboard()
    {
        return view('IT-staff/dashboard');
    }

    public function maintenance()
    {
        return view('IT-staff/maintenance');
    }

    public function security()
    {
        return view('IT-staff/security');
    }

  

}
