<?php
/**
 * Add Sample Doctors for Testing
 * Run this directly: http://localhost/hms/add_sample_doctors.php
 */

echo "<h1>🏥 Add Sample Doctors</h1>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
.success { color: #28a745; font-weight: bold; }
.error { color: #dc3545; font-weight: bold; }
.info { background: #f3f4f6; padding: 15px; border-left: 4px solid #3b82f6; margin: 10px 0; }
.warning { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 10px 0; }
</style>";

try {
    $db = \Config\Database::connect();
    $db->connect();

    echo "<h3>✅ Database Connection: <span class='success'>SUCCESS</span></h3>";
    echo "<p>📊 Database: <strong>" . $db->getDatabase() . "</strong></p>";

    // Check if tables exist
    echo "<h2>🔍 Checking Tables</h2>";
    
    if (!$db->tableExists('staff')) {
        echo "<p class='error'>❌ Staff table doesn't exist</p>";
        exit;
    } else {
        echo "<p class='success'>✅ Staff table exists</p>";
    }

    if (!$db->tableExists('doctor')) {
        echo "<p class='error'>❌ Doctor table doesn't exist</p>";
        exit;
    } else {
        echo "<p class='success'>✅ Doctor table exists</p>";
    }

    // Sample staff data (doctors)
    echo "<h2>👨‍⚕️ Adding Sample Doctors</h2>";

    $sampleDoctors = [
        [
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'john.smith@hospital.com',
            'phone' => '555-0101',
            'role' => 'doctor',
            'designation' => 'MD',
            'department' => 'Cardiology',
            'specialization' => 'Cardiology',
            'license_no' => 'MD12345',
            'consultation_fee' => 150.00
        ],
        [
            'first_name' => 'Sarah',
            'last_name' => 'Johnson',
            'email' => 'sarah.johnson@hospital.com',
            'phone' => '555-0102',
            'role' => 'doctor',
            'designation' => 'MD',
            'department' => 'Pediatrics',
            'specialization' => 'Pediatrics',
            'license_no' => 'MD12346',
            'consultation_fee' => 120.00
        ],
        [
            'first_name' => 'Michael',
            'last_name' => 'Brown',
            'email' => 'michael.brown@hospital.com',
            'phone' => '555-0103',
            'role' => 'doctor',
            'designation' => 'MD',
            'department' => 'Emergency',
            'specialization' => 'Emergency Medicine',
            'license_no' => 'MD12347',
            'consultation_fee' => 200.00
        ],
        [
            'first_name' => 'Emily',
            'last_name' => 'Davis',
            'email' => 'emily.davis@hospital.com',
            'phone' => '555-0104',
            'role' => 'doctor',
            'designation' => 'MD',
            'department' => 'Orthopedics',
            'specialization' => 'Orthopedics',
            'license_no' => 'MD12348',
            'consultation_fee' => 180.00
        ],
        [
            'first_name' => 'Robert',
            'last_name' => 'Wilson',
            'email' => 'robert.wilson@hospital.com',
            'phone' => '555-0105',
            'role' => 'doctor',
            'designation' => 'MD',
            'department' => 'Neurology',
            'specialization' => 'Neurology',
            'license_no' => 'MD12349',
            'consultation_fee' => 220.00
        ]
    ];

    $addedDoctors = 0;

    foreach ($sampleDoctors as $doctorData) {
        try {
            // Insert into staff table first
            $staffData = [
                'first_name' => $doctorData['first_name'],
                'last_name' => $doctorData['last_name'],
                'email' => $doctorData['email'],
                'phone' => $doctorData['phone'],
                'role' => $doctorData['role'],
                'designation' => $doctorData['designation'],
                'department' => $doctorData['department'],
                'status' => 'Active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $db->table('staff')->insert($staffData);
            $staffId = $db->insertID();

            // Insert into doctor table
            $doctorRecord = [
                'staff_id' => $staffId,
                'specialization' => $doctorData['specialization'],
                'license_no' => $doctorData['license_no'],
                'consultation_fee' => $doctorData['consultation_fee'],
                'status' => 'Active'
            ];

            $db->table('doctor')->insert($doctorRecord);
            $doctorId = $db->insertID();

            echo "<p class='success'>✅ Added Dr. {$doctorData['first_name']} {$doctorData['last_name']} (Staff ID: $staffId, Doctor ID: $doctorId)</p>";
            $addedDoctors++;

        } catch (Exception $e) {
            echo "<p class='error'>❌ Error adding Dr. {$doctorData['first_name']} {$doctorData['last_name']}: " . $e->getMessage() . "</p>";
        }
    }

    echo "<h2>📊 Summary</h2>";
    echo "<p class='info'><strong>Added $addedDoctors doctors to the database</strong></p>";

    // Test the PatientService method
    echo "<h2>🧪 Testing PatientService::getAvailableDoctors()</h2>";
    try {
        $patientService = new \App\Services\PatientService();
        $doctors = $patientService->getAvailableDoctors();
        echo "<p class='success'>✅ Found " . count($doctors) . " doctors in the system</p>";
        
        if (!empty($doctors)) {
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin-top: 10px;'>";
            echo "<tr><th>Staff ID</th><th>Name</th><th>Department</th><th>Specialization</th></tr>";
            foreach ($doctors as $doctor) {
                echo "<tr>";
                echo "<td>{$doctor['staff_id']}</td>";
                echo "<td>{$doctor['first_name']} {$doctor['last_name']}</td>";
                echo "<td>{$doctor['department']}</td>";
                echo "<td>{$doctor['specialization']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error testing PatientService: " . $e->getMessage() . "</p>";
    }

} catch (Exception $e) {
    echo "<p class='error'>❌ Database connection failed: " . $e->getMessage() . "</p>";
}
?>
