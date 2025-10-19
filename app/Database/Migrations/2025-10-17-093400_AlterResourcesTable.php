<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterResourcesTable extends Migration
{
    public function up()
    {
        // Make 'location' and 'supplier' nullable to prevent insert failures when omitted
        $fields = [
            'location' => [
                'name'       => 'location',
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'supplier' => [
                'name'       => 'supplier',
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
        ];

        $this->forge->modifyColumn('resources', $fields);
    }

    public function down()
    {
        // Revert 'location' and 'supplier' back to NOT NULL
        $fields = [
            'location' => [
                'name'       => 'location',
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'supplier' => [
                'name'       => 'supplier',
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
        ];

        $this->forge->modifyColumn('resources', $fields);
    }
}
