<?php
// Initialize CodeIgniter
require_once 'system/bootstrap.php';

// Initialize the database
$db = \Config\Database::connect();

echo "Testing doctor table...\n";
try {
    $result = $db->table('doctor')->select('COUNT(*) as count')->get()->getRow();
    echo "Doctor table count: " . $result->count . "\n";
} catch (Exception $e) {
    echo "Error with doctor table: " . $e->getMessage() . "\n";
}

echo "\nTesting staff table...\n";
try {
    $result = $db->table('staff')->select('COUNT(*) as count')->get()->getRow();
    echo "Staff table count: " . $result->count . "\n";
} catch (Exception $e) {
    echo "Error with staff table: " . $e->getMessage() . "\n";
}

echo "\nTesting doctor-staff join...\n";
try {
    $result = $db->table('doctor d')
        ->select('s.staff_id, s.first_name, s.last_name, s.department')
        ->join('staff s', 's.staff_id = d.staff_id', 'left')
        ->limit(5)
        ->get()
        ->getResultArray();
    echo "Sample doctors:\n";
    foreach ($result as $doctor) {
        echo "- ID: {$doctor['staff_id']}, Name: {$doctor['first_name']} {$doctor['last_name']}, Dept: {$doctor['department']}\n";
    }
} catch (Exception $e) {
    echo "Error with join: " . $e->getMessage() . "\n";
}

echo "\nTesting PatientService::getAvailableDoctors()...\n";
try {
    $patientService = new \App\Services\PatientService();
    $doctors = $patientService->getAvailableDoctors();
    echo "Found " . count($doctors) . " doctors\n";
    foreach ($doctors as $doctor) {
        echo "- ID: {$doctor['staff_id']}, Name: {$doctor['first_name']} {$doctor['last_name']}, Dept: {$doctor['department']}\n";
    }
} catch (Exception $e) {
    echo "Error with PatientService: " . $e->getMessage() . "\n";
}
?>
