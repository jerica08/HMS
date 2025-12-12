<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveMBLFromInsuranceDetails extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        
        // Check if table exists
        if (!$db->tableExists('insurance_details')) {
            return;
        }

        // Get existing columns
        $fields = $db->getFieldData('insurance_details');
        $existingColumns = array_map(fn($field) => $field->name, $fields);

        // Remove mbl column if it exists
        if (in_array('mbl', $existingColumns)) {
            $this->forge->dropColumn('insurance_details', 'mbl');
        }
    }

    public function down()
    {
        // Add back the mbl column for rollback
        $this->forge->addColumn('insurance_details', [
            'mbl' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
                'after'      => 'coverage_type_new'
            ]
        ]);
    }
}
