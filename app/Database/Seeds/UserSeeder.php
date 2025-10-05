<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            'staff_id' => 1,
            'username' => 'admin',
            'email'    => 'admin@gagni.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role'     => 'admin',
        ];

        $this->db->table('users')->insert($data);
    }
}
