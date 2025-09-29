<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRoleTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'role_id' => [
                'type'           => 'INT',
                'auto_increment' => true,
                'unsigned'       => true,
            ],
            'role_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'unique'     => true,
                'null'       => false,
            ],
        ]);
        $this->forge->addKey('role_id', true);
        $this->forge->createTable('role');    

        $roles = [
            ['role_name' => 'Hospital Administrator'],
            ['role_name' => 'Doctor'],
            ['role_name' => 'Nurse'],
            ['role_name' => 'Receptionist'],
            ['role_name' => 'Laboratory Staff'],
            ['role_name' => 'Pharmacist'],
            ['role_name' => 'Accountant'],
            ['role_name' => 'IT Staff'],
        ];
        $db = \Config\Database::connect();
        $db->table('role')->insertBatch($roles);
    }

    public function down()
    {
        $this->forge->dropTable('role');
    }
}