<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Accountant extends BaseController
{
    public function dashboard()
    {
        return view('accountant/dashboard');
    }

    public function billing()
    {
        return view('accountant/billing');
    }

    public function payments()
    {
        return view('accountant/payments');
    }

    public function insurance()
    {
        return view('accountant/insurance');
    }
    
    public function logout()
    {
        $session = session();
        $session->destroy();
        return redirect()->to(base_url('/login'));
    }

}
