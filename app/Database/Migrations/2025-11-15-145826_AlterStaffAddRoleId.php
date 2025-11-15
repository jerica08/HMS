<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterStaffAddRoleId extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // Check if column already exists
        $fields = $db->getFieldNames('staff');

        if (!in_array('role_id', $fields)) {
            $this->forge->addColumn('staff', [
                'role_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'role', // after existing ENUM
                ],
            ]);
        }

        // Backfill staff.role_id from staff.role using roles.slug
        $sql = "
            UPDATE staff s
            JOIN roles r ON r.slug = s.role
            SET s.role_id = r.role_id
        ";
        $db->query($sql);
    }

    public function down()
    {
        $this->forge->dropColumn('staff', 'role_id');
    }
}