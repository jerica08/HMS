<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDepartmentTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'department_id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => false,
                'unique'     => true,
            ],
            'description' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            // Create timestamps as NULL first; set DEFAULT/ON UPDATE after create to satisfy strict mode
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'default' => null,
            ],
            'updated_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'default' => null,
            ],
        ]);

        $this->forge->addKey('department_id', true);
        $this->forge->createTable('department');

        // Ensure InnoDB for FK support and set timestamp defaults
        $db = \Config\Database::connect();
        $db->query('ALTER TABLE department ENGINE=InnoDB');
        $db->query('ALTER TABLE department MODIFY created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');
        $db->query('ALTER TABLE department MODIFY updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

        // Seed default departments
        $defaults = [
            'Administration',
            'Emergency',
            'Cardiology',
            'Intensive Care Unit',
            'Outpatient',
            'Pharmacy',
            'Laboratory',
            'Radiology',
            'Pediatrics',
            'Surgery',
        ];

        // Insert only those not present
        $existing = $db->table('department')->select('name')->get()->getResultArray();
        $existingNames = array_map(fn($r) => $r['name'], $existing);

        $toInsert = [];
        foreach ($defaults as $name) {
            if (!in_array($name, $existingNames, true)) {
                $toInsert[] = [
                    'name' => $name,
                    'description' => null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
        }
        if (!empty($toInsert)) {
            $db->table('department')->insertBatch($toInsert);
        }
    }

    public function down()
    {
        $this->forge->dropTable('department');
    }
}
