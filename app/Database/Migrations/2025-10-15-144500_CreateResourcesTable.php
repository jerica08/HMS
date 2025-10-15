<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateResourcesTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if (! $db->tableExists('resources')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'equipment_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                ],
                'category' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                ],
                'quantity' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                ],
                'status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                ],
                'location' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                ],
                'date_acquired' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'supplier' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                ],
                'maintenance_schedule' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'remarks' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->createTable('resources', true);
        }
    }

    public function down()
    {
        $this->forge->dropTable('resources');
    }
}
