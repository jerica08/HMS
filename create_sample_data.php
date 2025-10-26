<?php
/**
 * Create Sample Data for HMS Testing
 * Run this directly: http://localhost:8080/create_sample_data.php
 */

echo "<h1>🏥 Create Sample Data for HMS Testing</h1>";
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

    $sampleDataCreated = 0;

    // 1. Create Resources Table if not exists and add sample data
    echo "<h2>📦 Resource Management Sample Data</h2>";

    if (!$db->tableExists('resources')) {
        echo "<p class='warning'>⚠️ Resources table doesn't exist. Creating it...</p>";

        $sql = "
        CREATE TABLE resources (
            id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            equipment_name VARCHAR(100) NOT NULL,
            category VARCHAR(50) NOT NULL,
            quantity INT(11) NOT NULL,
            status VARCHAR(30) NOT NULL,
            location VARCHAR(100) NULL,
            date_acquired DATE NULL,
            supplier VARCHAR(100) NULL,
            maintenance_schedule DATE NULL,
            remarks TEXT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ";

        $db->query($sql);
    }

    if ($db->tableExists('resources')) {
        echo "<p>✅ Resources table: <span class='success'>EXISTS</span></p>";

        $currentCount = $db->table('resources')->countAll();
        echo "<p>📊 Current records: <strong>" . $currentCount . "</strong></p>";

        if ($currentCount == 0) {
            echo "<p>💡 Adding sample resources...</p>";

            $sampleResources = [
                [
                    'equipment_name' => 'MRI Machine',
                    'category' => 'Equipment',
                    'quantity' => 1,
                    'status' => 'Available',
                    'location' => 'Radiology Department',
                    'date_acquired' => '2024-01-15',
                    'supplier' => 'Medical Equipment Corp',
                    'maintenance_schedule' => '2025-01-15',
                    'remarks' => 'High-end 3T MRI scanner'
                ],
                [
                    'equipment_name' => 'X-Ray Machine',
                    'category' => 'Equipment',
                    'quantity' => 2,
                    'status' => 'Available',
                    'location' => 'Emergency Room',
                    'date_acquired' => '2023-06-10',
                    'supplier' => 'Radiology Solutions Inc',
                    'maintenance_schedule' => '2024-12-10',
                    'remarks' => 'Digital X-ray system'
                ],
                [
                    'equipment_name' => 'Hospital Beds',
                    'category' => 'Facility',
                    'quantity' => 50,
                    'status' => 'Available',
                    'location' => 'General Ward',
                    'date_acquired' => '2023-03-01',
                    'supplier' => 'Hospital Furniture Ltd',
                    'maintenance_schedule' => null,
                    'remarks' => 'Standard hospital beds with side rails'
                ],
                [
                    'equipment_name' => 'Wheelchairs',
                    'category' => 'Equipment',
                    'quantity' => 15,
                    'status' => 'Available',
                    'location' => 'Equipment Storage',
                    'date_acquired' => '2023-08-20',
                    'supplier' => 'Mobility Solutions',
                    'maintenance_schedule' => null,
                    'remarks' => 'Standard wheelchairs for patient transport'
                ],
                [
                    'equipment_name' => 'Defibrillator',
                    'category' => 'Equipment',
                    'quantity' => 3,
                    'status' => 'Available',
                    'location' => 'Emergency Room',
                    'date_acquired' => '2024-02-28',
                    'supplier' => 'Cardiac Care Systems',
                    'maintenance_schedule' => '2024-08-28',
                    'remarks' => 'Automated external defibrillator units'
                ]
            ];

            foreach ($sampleResources as $resource) {
                $db->table('resources')->insert($resource);
                echo "<p>✅ <span class='success'>Added: " . $resource['equipment_name'] . "</span></p>";
                $sampleDataCreated++;
            }

            echo "<p><strong>🎉 " . count($sampleResources) . " sample resources added!</strong></p>";
        } else {
            echo "<p class='info'>ℹ️ Resources table already has data. Skipping sample data creation.</p>";
        }
    } else {
        echo "<p class='error'>❌ Failed to create resources table</p>";
    }

    // 2. Create Financial Tables and Sample Data
    echo "<h2>💰 Financial Management Sample Data</h2>";

    // Create payments table if not exists
    if (!$db->tableExists('payments')) {
        echo "<p class='warning'>⚠️ Payments table doesn't exist. Creating it...</p>";

        $sql = "
        CREATE TABLE payments (
            id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            bill_id INT(11) NULL,
            amount DECIMAL(10,2) NOT NULL,
            payment_method VARCHAR(50) NOT NULL,
            payment_date TIMESTAMP NOT NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'completed',
            processed_by INT(11) NULL,
            description TEXT NULL,
            PRIMARY KEY (id),
            INDEX idx_payment_date (payment_date),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ";

        $db->query($sql);
    }

    // Create expenses table if not exists
    if (!$db->tableExists('expenses')) {
        echo "<p class='warning'>⚠️ Expenses table doesn't exist. Creating it...</p>";

        $sql = "
        CREATE TABLE expenses (
            id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            expense_name VARCHAR(100) NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            category VARCHAR(50) NOT NULL,
            expense_date DATE NOT NULL,
            created_by INT(11) NULL,
            description TEXT NULL,
            PRIMARY KEY (id),
            INDEX idx_expense_date (expense_date),
            INDEX idx_category (category)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ";

        $db->query($sql);
    }

    // Add sample payments (Income)
    if ($db->tableExists('payments')) {
        echo "<p>✅ Payments table: <span class='success'>EXISTS</span></p>";

        $currentPayments = $db->table('payments')->countAll();
        echo "<p>📊 Current payment records: <strong>" . $currentPayments . "</strong></p>";

        if ($currentPayments == 0) {
            echo "<p>💡 Adding sample income records...</p>";

            $samplePayments = [
                [
                    'bill_id' => null,
                    'amount' => 15000.00,
                    'payment_method' => 'cash',
                    'payment_date' => '2024-10-25 10:30:00',
                    'status' => 'completed',
                    'processed_by' => 1,
                    'description' => 'Consultation fee payment'
                ],
                [
                    'bill_id' => null,
                    'amount' => 2500.00,
                    'payment_method' => 'card',
                    'payment_date' => '2024-10-25 14:15:00',
                    'status' => 'completed',
                    'processed_by' => 1,
                    'description' => 'Lab test payment'
                ],
                [
                    'bill_id' => null,
                    'amount' => 8500.00,
                    'payment_method' => 'bank_transfer',
                    'payment_date' => '2024-10-24 16:45:00',
                    'status' => 'completed',
                    'processed_by' => 1,
                    'description' => 'Surgery payment'
                ],
                [
                    'bill_id' => null,
                    'amount' => 3200.00,
                    'payment_method' => 'cash',
                    'payment_date' => '2024-10-24 11:20:00',
                    'status' => 'completed',
                    'processed_by' => 1,
                    'description' => 'Emergency room visit'
                ]
            ];

            foreach ($samplePayments as $payment) {
                $db->table('payments')->insert($payment);
                echo "<p>✅ <span class='success'>Added Income: ₱" . number_format($payment['amount'], 2) . " (" . $payment['description'] . ")</span></p>";
                $sampleDataCreated++;
            }

            echo "<p><strong>🎉 " . count($samplePayments) . " sample income records added!</strong></p>";
        } else {
            echo "<p class='info'>ℹ️ Payments table already has data. Skipping sample income data.</p>";
        }
    }

    // Add sample expenses
    if ($db->tableExists('expenses')) {
        echo "<p>✅ Expenses table: <span class='success'>EXISTS</span></p>";

        $currentExpenses = $db->table('expenses')->countAll();
        echo "<p>📊 Current expense records: <strong>" . $currentExpenses . "</strong></p>";

        if ($currentExpenses == 0) {
            echo "<p>💡 Adding sample expense records...</p>";

            $sampleExpenses = [
                [
                    'expense_name' => 'Medical Supplies Purchase',
                    'amount' => 25000.00,
                    'category' => 'supplies',
                    'expense_date' => '2024-10-25',
                    'created_by' => 1,
                    'description' => 'Monthly medical supplies order'
                ],
                [
                    'expense_name' => 'Electricity Bill',
                    'amount' => 8500.00,
                    'category' => 'utilities',
                    'expense_date' => '2024-10-24',
                    'created_by' => 1,
                    'description' => 'Hospital electricity October 2024'
                ],
                [
                    'expense_name' => 'Equipment Maintenance',
                    'amount' => 12000.00,
                    'category' => 'maintenance',
                    'expense_date' => '2024-10-23',
                    'created_by' => 1,
                    'description' => 'MRI machine maintenance service'
                ],
                [
                    'expense_name' => 'Staff Salaries',
                    'amount' => 150000.00,
                    'category' => 'salaries',
                    'expense_date' => '2024-10-20',
                    'created_by' => 1,
                    'description' => 'Monthly staff salaries'
                ],
                [
                    'expense_name' => 'Cleaning Supplies',
                    'amount' => 3200.00,
                    'category' => 'other',
                    'expense_date' => '2024-10-22',
                    'created_by' => 1,
                    'description' => 'Hospital cleaning and sanitation supplies'
                ]
            ];

            foreach ($sampleExpenses as $expense) {
                $db->table('expenses')->insert($expense);
                echo "<p>✅ <span class='success'>Added Expense: ₱" . number_format($expense['amount'], 2) . " (" . $expense['expense_name'] . ")</span></p>";
                $sampleDataCreated++;
            }

            echo "<p><strong>🎉 " . count($sampleExpenses) . " sample expense records added!</strong></p>";
        } else {
            echo "<p class='info'>ℹ️ Expenses table already has data. Skipping sample expense data.</p>";
        }
    }

    echo "<hr>";
    echo "<div class='info'>";
    echo "<h3>🎉 SUCCESS! Sample Data Created</h3>";
    echo "<p><strong>Total sample records created: " . $sampleDataCreated . "</strong></p>";
    echo "<p>✅ Resource Management: Ready for testing</p>";
    echo "<p>✅ Financial Management: Ready for testing</p>";
    echo "</div>";

    echo "<h3>🧪 Ready to Test!</h3>";
    echo "<div style='background: #d4edda; padding: 20px; border: 1px solid #c3e6cb; border-radius: 5px;'>";
    echo "<h4>📋 Test the Add Modals:</h4>";
    echo "<p><strong>1. Resource Management:</strong></p>";
    echo "<p>🔗 <a href='/admin/resource-management' style='color: #007bff;'>Go to Resource Management</a></p>";
    echo "<p>Click 'Add Resource' and try adding: 'Blood Pressure Monitor', Equipment, 5, Available, 'Emergency Room'</p>";
    echo "<br>";
    echo "<p><strong>2. Financial Management:</strong></p>";
    echo "<p>🔗 <a href='/admin/financial-management' style='color: #007bff;'>Go to Financial Management</a></p>";
    echo "<p>Click 'Add Financial Record' and try adding:</p>";
    echo "<p>💰 Income: 'Patient Consultation Fee', ₱1,500, today, cash</p>";
    echo "<p>💸 Expense: 'Office Supplies', ₱2,500, today, other</p>";
    echo "</div>";

    echo "<p><a href='/login' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>← Back to Login</a></p>";

} catch (Exception $e) {
    echo "<h3>❌ <span class='error'>Database Error: " . $e->getMessage() . "</span></h3>";
    echo "<p><strong>Manual fix:</strong> Use phpMyAdmin to create tables manually</p>";
}
?>
