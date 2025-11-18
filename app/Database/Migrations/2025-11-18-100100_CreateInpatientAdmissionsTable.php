<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInpatientAdmissionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'admission_id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'patient_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
            'admission_datetime' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'admission_type' => [
                'type'       => 'ENUM',
                'constraint' => ['ER', 'Scheduled', 'Transfer'],
                'null'       => true,
            ],
            'admitting_diagnosis' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'admitting_doctor' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'department' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'patient_classification' => [
                'type'       => 'ENUM',
                'constraint' => ['Medical', 'Surgical', 'Maternity', 'Pediatric', 'Geriatric', 'Infectious', 'Psychiatric', 'Rehabilitation'],
                'null'       => true,
            ],
            'consent_signed' => [
                'type'       => 'BOOLEAN',
                'null'       => false,
                'default'    => 0,
            ],
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'default' => null,
            ],
        ]);

        $this->forge->addKey('admission_id', true);
        $this->forge->addForeignKey('patient_id', 'patients', 'patient_id', 'CASCADE', 'CASCADE', 'fk_inpatient_admissions_patient');

        $this->forge->createTable('inpatient_admissions');

        $db = \Config\Database::connect();
        $db->query('ALTER TABLE inpatient_admissions ENGINE=InnoDB');
        $db->query('ALTER TABLE inpatient_admissions MODIFY created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');
    }

    public function down()
    {
        $this->forge->dropTable('inpatient_admissions');
    }
}
