<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveMedicalSpecialtyFromMedicalDepartments extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        
        // Check if the medical_departments table exists
        if ($db->tableExists('medical_departments')) {
            // Check if the specialty column exists before dropping
            if ($db->fieldExists('specialty', 'medical_departments')) {
                $this->forge->dropColumn('medical_departments', 'specialty');
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        
        // Add back the specialty column if needed
        if ($db->tableExists('medical_departments') && !$db->fieldExists('specialty', 'medical_departments')) {
            $this->forge->addColumn('medical_departments', [
                'specialty' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
            ]);
        }
    }
}
