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
}
