<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInpatientMedicalHistoryTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'history_id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'admission_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
            'allergies' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'past_medical_history' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'past_surgical_history' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'family_history' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'current_medications' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('history_id', true);
        $this->forge->addForeignKey('admission_id', 'inpatient_admissions', 'admission_id', 'CASCADE', 'CASCADE', 'fk_inpatient_med_hist_admission');

        $this->forge->createTable('inpatient_medical_history');

        $db = \Config\Database::connect();
        $db->query('ALTER TABLE inpatient_medical_history ENGINE=InnoDB');
    }

    public function down()
    {
        $this->forge->dropTable('inpatient_medical_history');
    }
}
