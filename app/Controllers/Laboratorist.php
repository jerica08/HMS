<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Laboratorist extends BaseController
{
    public function dashboard()
    {
        return view('laboratorists/dashboard');
    }

    public function sampleManagement()
    {
        return view('laboratorists/sample-management');
    }

    public function testRequest()
    {
        return view('laboratorists/test-request');
    }

    public function testResult()
    {
        return view('laboratorists/test-result');
    }
      public function logout()
    {
        $session = session();
        $session->destroy();
        return redirect()->to(base_url('/login'));
    }

}
