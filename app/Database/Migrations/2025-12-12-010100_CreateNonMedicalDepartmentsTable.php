<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNonMedicalDepartmentsTable extends Migration
{
    protected string $table = 'non_medical_departments';

    public function up()
    {
        $this->forge->addField([
            'non_medical_department_id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'code' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'unique'     => true,
            ],
            'function' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'floor' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'department_head_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'contact_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type' => "ENUM('Active','Inactive')",
                'null' => false,
                'default' => 'Active',
            ],
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

        $this->forge->addKey('non_medical_department_id', true);
        $this->forge->addKey('name');
        $this->forge->addKey('function');
        $this->forge->addKey('status');

        $this->forge->createTable($this->table, true, ['ENGINE' => 'InnoDB']);

        // Add foreign key for department head
        $db = \Config\Database::connect();
        if ($db->tableExists('staff')) {
            $this->db->query(
                'ALTER TABLE ' . $this->db->escapeString($this->table) .
                ' ADD CONSTRAINT fk_non_medical_dept_head_staff
                  FOREIGN KEY (department_head_id) REFERENCES staff(staff_id)
                  ON UPDATE CASCADE ON DELETE SET NULL'
            );
        }

        // Set timestamp defaults
        $this->db->query('ALTER TABLE ' . $this->db->escapeString($this->table) . ' MODIFY created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');
        $this->db->query('ALTER TABLE ' . $this->db->escapeString($this->table) . ' MODIFY updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    }

    public function down()
    {
        // Drop FK if it exists
        try {
            $this->db->query('ALTER TABLE ' . $this->db->escapeString($this->table) . ' DROP FOREIGN KEY fk_non_medical_dept_head_staff');
        } catch (\Throwable $e) {
            // Ignore if the constraint does not exist
        }

        $this->forge->dropTable($this->table, true);
    }
}
