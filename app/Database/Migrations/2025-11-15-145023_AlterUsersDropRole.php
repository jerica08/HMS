<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterUsersDropRole extends Migration
{
    public function up()
    {
        // Make sure role_id exists before dropping role
        $db     = \Config\Database::connect();
        $fields = $db->getFieldNames('users');

        if (!in_array('role_id', $fields)) {
            throw new \RuntimeException('role_id column must exist before dropping role column.');
        }

        // Drop the old ENUM role column
        $this->forge->dropColumn('users', 'role');
    }

    public function down()
    {
        // Recreate role ENUM if you ever rollback.
        // Adjust the ENUM values if needed.
        $this->forge->addColumn('users', [
            'role' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'admin',
                    'doctor',
                    'nurse',
                    'receptionist',
                    'pharmacist',
                    'accountant',
                    'it_staff',
                    'laboratorist',
                ],
                'default'    => 'admin',
                'after'      => 'password',
            ],
        ]);
    }
}