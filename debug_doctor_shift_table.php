<?php
// Debug script to check doctor_shift table structure
require_once 'system/bootstrap.php';

$db = \Config\Database::connect();

try {
    // Check if table exists
    if ($db->tableExists('doctor_shift')) {
        echo "✓ doctor_shift table exists\n";
        
        // Get table structure
        $fields = $db->getFieldData('doctor_shift');
        echo "\nTable structure:\n";
        foreach ($fields as $field) {
            echo "- {$field->name}: {$field->type} (nullable: " . ($field->nullable ? 'yes' : 'no') . ")\n";
        }
        
        // Check if department column still exists
        $hasDepartment = false;
        foreach ($fields as $field) {
            if ($field->name === 'department') {
                $hasDepartment = true;
                break;
            }
        }
        
        if ($hasDepartment) {
            echo "\n❌ Department column still exists - this needs to be removed\n";
            echo "Run this SQL: ALTER TABLE doctor_shift DROP COLUMN department;\n";
        } else {
            echo "\n✓ Department column has been removed\n";
        }
        
    } else {
        echo "❌ doctor_shift table does not exist\n";
        echo "You need to run the migration to create it\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
