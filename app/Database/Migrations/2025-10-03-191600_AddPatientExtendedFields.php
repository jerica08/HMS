<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPatientExtendedFields extends Migration
{
    public function up()
    {
        // Add new columns to align patient table with the form fields
        $fields = [
            'middle_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'first_name',
            ],
            'civil_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'gender',
            ],
            'province' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'address',
            ],
            'city' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'province',
            ],
            'barangay' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'city',
            ],
            'zip_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'barangay',
            ],
            'insurance_provider' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'after'      => 'zip_code',
            ],
            'insurance_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'insurance_provider',
            ],
            'patient_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50, // outpatient | inpatient | emergency
                'null'       => true,
                'after'      => 'insurance_number',
            ],
            'medical_notes' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'patient_type',
            ],
        ];

        $this->forge->addColumn('patient', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('patient', [
            'middle_name',
            'civil_status',
            'province',
            'city',
            'barangay',
            'zip_code',
            'insurance_provider',
            'insurance_number',
            'patient_type',
            'medical_notes',
        ]);
    }
}
