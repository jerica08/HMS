<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Pharmacist extends BaseController
{
    public function dashboard()
    {
        $data = [
            'title' => 'Pharmacist Dashboard',
            'page' => 'dashboard'
        ];

        return view('pharmacists/dashboard', $data);
    }

    public function prescription()
    {
        $data = [
            'title' => 'Prescription Management',
            'page' => 'prescription'
        ];

        return view('pharmacists/prescription', $data);
    }

    public function inventory()
    {
        $data = [
            'title' => 'Inventory Management',
            'page' => 'inventory'
        ];

        return view('pharmacists/inventory', $data);
    }
}
