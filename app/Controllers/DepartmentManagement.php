<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Database\ConnectionInterface;

class DepartmentManagement extends BaseController
{
    protected ConnectionInterface $db;
    protected $session;
    protected string $userRole;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        $this->userRole = (string) ($this->session->get('role') ?? 'guest');
    }

    public function index()
    {
        $data = [
            'title' => 'Department Management',
            'userRole' => $this->userRole,
            'departmentStats' => $this->getDepartmentStats(),
            'departmentHeads' => $this->getPotentialDepartmentHeads(),
            'specialties' => $this->getAvailableSpecialties(),
            'departments' => $this->fetchDepartments(),
        ];

        return view('unified/department-management', $data);
    }

    private function fetchDepartments(): array
    {
        if (! $this->db->tableExists('department')) {
            return [];
        }

        return $this->db->table('department')
            ->select('department_id, name, description, created_at, updated_at')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function getDepartmentStats(): array
    {
        if (! $this->db->tableExists('department')) {
            return [
                'total_departments' => 0,
                'with_heads' => 0,
                'without_heads' => 0,
                'with_specialties' => 0,
            ];
        }

        $totalDepartments = (int) $this->db->table('department')->countAllResults();
        $withHeads = $this->countDepartmentsWithAssignedStaff();
        $withSpecialties = $this->countDepartmentsWithSpecialties();

        return [
            'total_departments' => $totalDepartments,
            'with_heads' => $withHeads,
            'without_heads' => max(0, $totalDepartments - $withHeads),
            'with_specialties' => $withSpecialties,
        ];
    }

    private function countDepartmentsWithAssignedStaff(): int
    {
        if (! $this->db->tableExists('staff') || ! $this->fieldExists('department_id', 'staff')) {
            return 0;
        }

        $rows = $this->db->table('staff')
            ->select('department_id')
            ->where('department_id IS NOT NULL', null, false)
            ->groupBy('department_id')
            ->get()
            ->getResultArray();

        return count($rows);
    }

    private function countDepartmentsWithSpecialties(): int
    {
        $table = $this->resolveSpecialtyTable();
        if (! $table || ! $this->fieldExists('department_id', $table)) {
            return 0;
        }

        $rows = $this->db->table($table)
            ->select('department_id')
            ->where('department_id IS NOT NULL', null, false)
            ->groupBy('department_id')
            ->get()
            ->getResultArray();

        return count($rows);
    }

    private function getPotentialDepartmentHeads(): array
    {
        if (! $this->db->tableExists('staff')) {
            return [];
        }

        $doctorTable = $this->db->tableExists('doctor') ? 'doctor' : null;

        $builder = $this->db->table('staff s')
            ->select($this->getStaffSelectColumns());

        if ($doctorTable) {
            $builder->join('doctor d', 'd.staff_id = s.staff_id', 'inner')
                ->where('d.status', 'Active')
                ->select($this->fieldExists('specialization', 'doctor') ? 'd.specialization' : null);
        } else {
            $builder->where('s.role', 'doctor');
        }

        $builder->orderBy('s.first_name', 'ASC');

        try {
            $rows = $builder->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('warning', 'Failed to fetch department heads: ' . $e->getMessage());
            if (! $this->db->tableExists('staff')) {
                return [];
            }
            $rows = $this->db->table('staff')
                ->select('staff_id, first_name, last_name, position, role')
                ->where('role', 'doctor')
                ->orderBy('first_name', 'ASC')
                ->get()
                ->getResultArray();
        }

        return array_map(static function (array $row) {
            $fullName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
            return [
                'staff_id' => $row['staff_id'],
                'full_name' => $fullName !== '' ? $fullName : 'Staff #' . $row['staff_id'],
                'position' => $row['position'] ?? ($row['role'] ?? null),
                'specialization' => $row['specialization'] ?? null,
            ];
        }, $rows);
    }

    private function getStaffSelectColumns(): array
    {
        $columns = ['s.staff_id', 's.first_name', 's.last_name'];
        if ($this->fieldExists('position', 'staff')) {
            $columns[] = 's.position';
        }
        if ($this->fieldExists('role', 'staff')) {
            $columns[] = 's.role';
        }

        return $columns;
    }

    private function getAvailableSpecialties(): array
    {
        $table = null;
        $nameColumn = null;

        if ($this->db->tableExists('specialty')) {
            $table = 'specialty';
            $nameColumn = $this->fieldExists('name', $table) ? 'name' : ($this->fieldExists('specialty_name', $table) ? 'specialty_name' : null);
        } elseif ($this->db->tableExists('specialties')) {
            $table = 'specialties';
            $nameColumn = $this->fieldExists('name', $table) ? 'name' : ($this->fieldExists('specialty_name', $table) ? 'specialty_name' : null);
        } elseif ($this->db->tableExists('department_specialties')) {
            $table = 'department_specialties';
            $nameColumn = $this->fieldExists('name', $table) ? 'name' : ($this->fieldExists('specialty_name', $table) ? 'specialty_name' : null);
        }

        if (! $table || ! $nameColumn) {
            return ['Emergency Medicine', 'Cardiology', 'Pediatrics', 'General Surgery'];
        }

        $results = $this->db->table($table)
            ->select($nameColumn . ' as name')
            ->groupBy($nameColumn)
            ->orderBy($nameColumn, 'ASC')
            ->get()
            ->getResultArray();

        if (empty($results)) {
            return ['Emergency Medicine', 'Cardiology', 'Pediatrics', 'General Surgery'];
        }

        return array_map(static fn ($row) => $row['name'], $results);
    }

    private function resolveSpecialtyTable(): ?string
    {
        foreach (['department_specialty', 'department_specialties', 'specialty_department'] as $table) {
            if ($this->db->tableExists($table)) {
                return $table;
            }
        }

        return null;
    }

    private function fieldExists(string $field, string $table): bool
    {
        try {
            return $this->db->fieldExists($field, $table);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
