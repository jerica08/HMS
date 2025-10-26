<?php
/**
 * Create Financial Tables and Sample Data
 * This ensures all financial tables exist with proper structure
 */

echo "<h1>💰 Setting Up Financial Tables</h1>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
.success { color: #28a745; font-weight: bold; }
.error { color: #dc3545; font-weight: bold; }
.info { background: #f3f4f6; padding: 15px; border-left: 4px solid #3b82f6; margin: 10px 0; }
</style>";

try {
    $db = \Config\Database::connect();
    $db->connect();

    echo "<h3>✅ Database Connection: <span class='success'>SUCCESS</span></h3>";
    echo "<p>📊 Database: <strong>" . $db->getDatabase() . "</strong></p>";

    // Create bills table if not exists
    if (!$db->tableExists('bills')) {
        echo "<p>🔧 Creating bills table...</p>";

        $sql = "
        CREATE TABLE bills (
            id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            bill_number VARCHAR(50) NOT NULL,
            patient_id INT(11) NULL,
            doctor_id INT(11) NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'pending',
            bill_date TIMESTAMP NOT NULL,
            created_by INT(11) NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_bill_number (bill_number),
            INDEX idx_status (status),
            INDEX idx_patient_id (patient_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ";

        $db->query($sql);
        echo "<p>✅ Bills table: <span class='success'>CREATED</span></p>";
    } else {
        echo "<p>✅ Bills table: <span class='success'>ALREADY EXISTS</span></p>";
    }

    // Add sample bills if table is empty
    $billCount = $db->table('bills')->countAll();
    if ($billCount == 0) {
        echo "<p>💡 Adding sample bills...</p>";

        $sampleBills = [
            [
                'bill_number' => 'BILL-20241025-0001',
                'patient_id' => 1,
                'doctor_id' => 1,
                'total_amount' => 15000.00,
                'status' => 'paid',
                'bill_date' => '2024-10-25 10:30:00',
                'created_by' => 1
            ],
            [
                'bill_number' => 'BILL-20241025-0002',
                'patient_id' => 2,
                'doctor_id' => 2,
                'total_amount' => 2500.00,
                'status' => 'paid',
                'bill_date' => '2024-10-25 14:15:00',
                'created_by' => 1
            ]
        ];

        foreach ($sampleBills as $bill) {
            $db->table('bills')->insert($bill);
            echo "<p>✅ Added bill: " . $bill['bill_number'] . " (₱" . number_format($bill['total_amount'], 2) . ")</p>";
        }
    }

    echo "<h3>🎉 Financial Tables Ready!</h3>";
    echo "<p>✅ All financial tables are now ready for testing</p>";
    echo "<p><a href='/create_sample_data.php'>← Back to Full Sample Data Setup</a></p>";

} catch (Exception $e) {
    echo "<h3>❌ Database Error: " . $e->getMessage() . "</h3>";
}
?>
