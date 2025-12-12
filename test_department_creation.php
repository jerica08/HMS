<?php
// Simple test to check department creation
require_once 'vendor/autoload.php';

// Initialize CodeIgniter
$app = new \CodeIgniter\CodeIgniter();
$app->initialize();

$db = \Config\Database::connect();

echo "=== Department Creation Test ===\n";

// Check if tables exist
$medicalTableExists = $db->tableExists('medical_departments');
$nonMedicalTableExists = $db->tableExists('non_medical_departments');

echo "Medical departments table exists: " . ($medicalTableExists ? "YES" : "NO") . "\n";
echo "Non-medical departments table exists: " . ($nonMedicalTableExists ? "YES" : "NO") . "\n";

if (!$medicalTableExists || !$nonMedicalTableExists) {
    echo "\n=== SOLUTION ===\n";
    echo "The department tables don't exist. You need to run the database migrations:\n\n";
    echo "From your HMS root directory, run:\n";
    echo "php spark migrate\n\n";
    echo "This will create the medical_departments and non_medical_departments tables.\n";
    exit(1);
}

// Test inserting a medical department
echo "\n=== Testing Medical Department Insert ===\n";
$medicalData = [
    'name' => 'Test Medical Department',
    'code' => 'TEST-MED',
    'status' => 'Active',
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
];

try {
    if ($db->table('medical_departments')->insert($medicalData)) {
        $id = $db->insertID();
        echo "✓ Medical department created successfully with ID: $id\n";
    } else {
        $error = $db->error();
        echo "✗ Failed to create medical department: " . json_encode($error) . "\n";
    }
} catch (Exception $e) {
    echo "✗ Exception creating medical department: " . $e->getMessage() . "\n";
}

// Test inserting a non-medical department
echo "\n=== Testing Non-Medical Department Insert ===\n";
$nonMedicalData = [
    'name' => 'Test Non-Medical Department',
    'code' => 'TEST-NONMED',
    'function' => 'Administrative',
    'status' => 'Active',
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
];

try {
    if ($db->table('non_medical_departments')->insert($nonMedicalData)) {
        $id = $db->insertID();
        echo "✓ Non-medical department created successfully with ID: $id\n";
    } else {
        $error = $db->error();
        echo "✗ Failed to create non-medical department: " . json_encode($error) . "\n";
    }
} catch (Exception $e) {
    echo "✗ Exception creating non-medical department: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
echo "If both departments were created successfully, the issue is fixed!\n";
?>
