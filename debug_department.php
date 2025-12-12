<?php
// Debug script to test department creation endpoint
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize CodeIgniter
require_once 'vendor/autoload.php';

$app = new \CodeIgniter\CodeIgniter();
$app->initialize();

// Test data for medical department
$testData = [
    'name' => 'Test Medical Department',
    'code' => 'TEST-MED',
    'department_category' => 'medical',
    'status' => 'Active'
];

echo "=== Testing Department Creation Endpoint ===\n";

// Simulate POST request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

// Create a mock request
$request = new \CodeIgniter\HTTP\IncomingRequest(
    new \CodeIgniter\HTTP\URI(),
    null,
    new \CodeIgniter\HTTP\UserAgent()
);

// Set JSON input
$request->setBody(json_encode($testData));

// Create the controller and test
try {
    $controller = new \App\Controllers\Departments();
    
    // Test the createMedical method
    echo "Testing createMedical method...\n";
    $response = $controller->createMedical();
    
    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "Response body: " . $response->getBody() . "\n";
    
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Database Connection Test ===\n";
try {
    $db = \Config\Database::connect();
    echo "Database connection: " . ($db ? "SUCCESS" : "FAILED") . "\n";
    
    // Check if table exists
    $medicalTableExists = $db->tableExists('medical_departments');
    echo "Medical departments table exists: " . ($medicalTableExists ? "YES" : "NO") . "\n";
    
    if ($medicalTableExists) {
        // Test simple insert
        $testInsert = [
            'name' => 'Debug Test Department',
            'status' => 'Active'
        ];
        
        try {
            $result = $db->table('medical_departments')->insert($testInsert);
            echo "Simple insert test: " . ($result ? "SUCCESS" : "FAILED") . "\n";
            
            if ($result) {
                $id = $db->insertID();
                echo "Inserted ID: $id\n";
                
                // Clean up
                $db->table('medical_departments')->where('medical_department_id', $id)->delete();
                echo "Test record cleaned up\n";
            }
        } catch (Exception $e) {
            echo "Insert test exception: " . $e->getMessage() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Database connection error: " . $e->getMessage() . "\n";
}

echo "\n=== Complete ===\n";
?>
