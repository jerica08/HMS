<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\DepartmentService;

class DepartmentManagement extends BaseController
{
    protected $departmentService;
    protected $userRole;

    public function __construct()
    {
        $this->departmentService = new DepartmentService();
        $this->userRole = (string) (session()->get('role') ?? 'guest');
    }

    public function index()
    {
        return view('unified/department-management', [
            'title' => 'Department Management',
            'userRole' => $this->userRole,
            'departmentStats' => $this->departmentService->getDepartmentStats(),
            'departmentHeads' => $this->departmentService->getPotentialDepartmentHeads(),
            'specialties' => $this->departmentService->getAvailableSpecialties(),
            'departments' => $this->departmentService->getDepartments(),
        ]);
    }
}
