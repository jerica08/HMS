<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        // 1) Ensure an Admin staff exists
        $staff = $db->table('staff')->where('email', 'admin@hospital.com')->get()->getRowArray();

        if (!$staff) {
            $db->table('staff')->insert([
                'employee_id' => 'EMP001',
                'first_name'  => 'Admin',
                'last_name'   => 'User',
                'gender'      => 'male',
                'dob'         => '1980-01-01',
                'contact_no'  => '1234567890',
                'email'       => 'admin@hospital.com',
                'address'     => 'Hospital Address',
                'department'  => 'Administration',
                'designation' => 'Administrator',
                'role'        => 'admin', // matches ENUM in staff table
                'date_joined' => date('Y-m-d'),
            ]);

            $staffId = (int) $db->insertID();
        } else {
            $staffId = (int) $staff['staff_id'];
        }

        // 2) Upsert the admin user
        $existing = $db->table('users')->where('username', 'admin')->get()->getRowArray();

        $userData = [
            'staff_id'   => $staffId,
            'email'      => 'admin@hospital.com',
            'first_name' => 'Admin',
            'last_name'  => 'User',
            'username'   => 'admin',
            'password'   => password_hash('admin123', PASSWORD_DEFAULT),
            'role'       => 'admin', // matches ENUM in users table
            'status'     => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $db->table('users')->where('username', 'admin')->update($userData);
        } else {
            $db->table('users')->insert($userData);
        }
    }
}
