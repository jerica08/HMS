<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropOriginalDepartmentTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        
        // Check if the original department table exists
        if ($db->tableExists('department')) {
            // Drop foreign key constraints first
            try {
                $db->query('ALTER TABLE ' . $db->escapeString('department') . ' DROP FOREIGN KEY fk_department_head_staff');
            } catch (\Throwable $e) {
                // Ignore if the constraint does not exist
            }
            
            // Drop the original department table
            $this->forge->dropTable('department', true);
        }
    }

    public function down()
    {
        // This migration is not easily reversible
        // To restore, you would need to:
        // 1. Run the original CreateDepartmentManagementTable migration
        // 2. Run the MigrateDepartmentsToMedicalNonMedical migration in reverse
        // 3. Restore data from backups
    }
}
