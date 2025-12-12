<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInsuranceFormFields extends Migration
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

        // Add member_type if it doesn't exist
        if (!in_array('member_type', $existingColumns)) {
            $this->forge->addColumn('insurance_details', [
                'member_type' => [
                    'type'       => 'ENUM',
                    'constraint' => ['Principal', 'Dependent'],
                    'null'       => true,
                ]
            ]);
        }

        // Add relationship if it doesn't exist
        if (!in_array('relationship', $existingColumns)) {
            $this->forge->addColumn('insurance_details', [
                'relationship' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                ]
            ]);
        }

        // Add plan_name if it doesn't exist
        if (!in_array('plan_name', $existingColumns)) {
            $this->forge->addColumn('insurance_details', [
                'plan_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                ]
            ]);
        }

        // Add coverage_type_new if it doesn't exist
        if (!in_array('coverage_type_new', $existingColumns)) {
            $this->forge->addColumn('insurance_details', [
                'coverage_type_new' => [
                    'type'       => 'SET',
                    'constraint' => ['outpatient', 'inpatient', 'er', 'dental', 'optical', 'maternity'],
                    'null'       => true,
                ]
            ]);
        }

        // Add mbl if it doesn't exist
        // REMOVED: MBL field has been removed from the system
        // if (!in_array('mbl', $existingColumns)) {
        //     $this->forge->addColumn('insurance_details', [
        //         'mbl' => [
        //             'type'       => 'DECIMAL',
        //             'constraint' => '10,2',
        //             'null'       => true,
        //         ]
        //     ]);
        // }

        // Add pre_existing_coverage if it doesn't exist
        if (!in_array('pre_existing_coverage', $existingColumns)) {
            $this->forge->addColumn('insurance_details', [
                'pre_existing_coverage' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ]
            ]);
        }

        // Add card_status if it doesn't exist
        if (!in_array('card_status', $existingColumns)) {
            $this->forge->addColumn('insurance_details', [
                'card_status' => [
                    'type'       => 'ENUM',
                    'constraint' => ['Active', 'Inactive', 'Expired', 'Suspended'],
                    'null'       => true,
                    'default'    => 'Active',
                ]
            ]);
        }

        // Update existing coverage_type to be more specific if needed
        try {
            $this->db->query("ALTER TABLE insurance_details MODIFY COLUMN coverage_type ENUM('inpatient', 'outpatient', 'er', 'dental', 'optical', 'maternity') NULL DEFAULT 'outpatient'");
        } catch (\Exception $e) {
            // Column modification might fail if it already has the new structure
            log_message('info', 'Coverage type column modification skipped: ' . $e->getMessage());
        }
    }

    public function down()
    {
        $this->forge->dropColumn('insurance_details', [
            'member_type',
            'relationship', 
            'plan_name',
            'coverage_type_new',
            'pre_existing_coverage',
            'card_status'
        ]);

        // Revert coverage_type to original
        try {
            $this->db->query("ALTER TABLE insurance_details MODIFY COLUMN coverage_type ENUM('inpatient', 'outpatient') NULL DEFAULT 'inpatient'");
        } catch (\Exception $e) {
            log_message('info', 'Coverage type column reversion skipped: ' . $e->getMessage());
        }
    }
}
