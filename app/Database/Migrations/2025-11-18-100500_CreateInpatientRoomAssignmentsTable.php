<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInpatientRoomAssignmentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'room_assignment_id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'admission_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
            'room_type' => [
                'type'       => 'ENUM',
                'constraint' => ['Ward', 'Semi-Private', 'Private', 'Isolation', 'ICU'],
                'null'       => true,
            ],
            'floor_number' => [
                'type' => 'INT',
                'null' => true,
            ],
            'room_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'bed_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            'daily_rate' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
            ],
            'assigned_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'default' => null,
            ],
        ]);

        $this->forge->addKey('room_assignment_id', true);
        $this->forge->addForeignKey('admission_id', 'inpatient_admissions', 'admission_id', 'CASCADE', 'CASCADE', 'fk_inpatient_room_assign_admission');

        $this->forge->createTable('inpatient_room_assignments');

        $db = \Config\Database::connect();
        $db->query('ALTER TABLE inpatient_room_assignments ENGINE=InnoDB');
        $db->query('ALTER TABLE inpatient_room_assignments MODIFY assigned_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');
    }

    public function down()
    {
        $this->forge->dropTable('inpatient_room_assignments');
    }
}
