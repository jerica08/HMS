<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterStaffDropRole extends Migration
{
    public function up()
    {
        // Make sure role_id exists before dropping role
        $db     = \Config\Database::connect();
        $fields = $db->getFieldNames('staff');

        if (!in_array('role_id', $fields)) {
            $this->forge->addColumn('staff', [
                'role_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'role',
                ],
            ]);

            if ($db->tableExists('roles')) {
                $db->query('
                    UPDATE staff s
                    JOIN roles r ON r.slug = s.role
                    SET s.role_id = r.role_id
                ');
            }
        }

        // Drop the old ENUM role column from staff
        $this->forge->dropColumn('staff', 'role');
    }

    public function down()
    {
        // Recreate role ENUM if you ever rollback.
        // Adjust the ENUM values if needed.
        $this->forge->addColumn('staff', [
            'role' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'admin',
                    'doctor',
                    'nurse',
                    'pharmacist',
                    'receptionist',
                    'laboratorist',
                    'it_staff',
                    'accountant',
                ],
                'null'    => true,
                'after'   => 'department_id',
            ],
        ]);
    }
}