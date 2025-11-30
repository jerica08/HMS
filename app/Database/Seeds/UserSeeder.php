<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
<<<<<<< HEAD
        $data = [
            'staff_id' => 1,
            'username' => 'admin',
            'email'    => 'admin@gagni.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role'     => 'admin',
        ];

        $this->db->table('users')->insert($data);
=======
        $roleId  = $this->ensureAdminRole();
        $staffId = $this->ensureAdminStaff($roleId);

        $userTable = $this->db->table('users');
        $existing = $userTable->where('username', 'admin')->get()->getRow();
        if ($existing) {
            return;
        }

        $data = [
            'staff_id'  => $staffId,
            'username'  => 'admin',
            'email'     => 'admin@gagni.com',
            'password'  => password_hash('password123', PASSWORD_DEFAULT),
            'role_id'   => $roleId,
            'first_name'=> 'System',
            'last_name' => 'Administrator',
            'status'    => 'active',
        ];

        $userTable->insert($data);
    }

    private function ensureAdminStaff(int $roleId): int
    {
        $staffTable = $this->db->table('staff');
        $staff = $staffTable->where('staff_id', 1)->get()->getRow();
        if ($staff) {
            return $staff->staff_id;
        }

        $staffTable->insert([
            'employee_id' => 'SR-0001',
            'first_name'  => 'System',
            'last_name'   => 'Administrator',
            'gender'      => 'other',
            'role_id'     => $roleId,
            'date_joined' => date('Y-m-d'),
        ]);

        return $this->db->insertID();
    }

    private function ensureAdminRole(): int
    {
        $rolesTable = $this->db->table('roles');
        $role = $rolesTable->where('slug', 'admin')->get()->getRow();
        if ($role) {
            return $role->role_id;
        }

        $rolesTable->insert([
            'name'        => 'Administrator',
            'slug'        => 'admin',
            'description' => 'System administrator',
            'status'      => 'active',
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        return $this->db->insertID();
>>>>>>> 3b7d5d2 (Commit)
    }
}
