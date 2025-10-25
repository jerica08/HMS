<?php
/**
 * Create Department Table
 * Run this directly: http://localhost:8080/create_department_table.php
 */

echo "<h1>🏥 Create Department Table</h1>";
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

    // Check if any department table exists
    $tables = ['department', 'departments', 'deaprtment'];
    $found = false;
    $existingTable = '';

    foreach ($tables as $table) {
        if ($db->tableExists($table)) {
            $found = true;
            $existingTable = $table;
            break;
        }
    }

    if ($found) {
        echo "<h3>✅ Department table: <span class='success'>EXISTS</span></h3>";
        echo "<p>📊 Table name: <strong>" . $existingTable . "</strong></p>";
        $count = $db->table($existingTable)->countAll();
        echo "<p>📋 Records: <strong>" . $count . "</strong></p>";

        if ($count == 0) {
            echo "<p>💡 Adding sample department...</p>";

            $sampleData = [
                'name' => 'Emergency Department',
                'description' => 'Emergency medical services'
            ];

            $db->table($existingTable)->insert($sampleData);
            echo "<p>✅ <span class='success'>Sample department added!</span></p>";
        }

        echo "<div class='info'>";
        echo "<h4>🎉 Department Management is Ready!</h4>";
        echo "<p><strong>Now you can use the Add Department button!</strong></p>";
        echo "<p><a href='/admin/resource-management' style='background:#28a745;color:white;padding:10px 20px;text-decoration:none;'>Go to Resource Management →</a></p>";
        echo "</div>";

    } else {
        echo "<h3>❌ Department table: <span class='error'>MISSING</span></h3>";
        echo "<p>🔧 Creating departments table...</p>";

        $sql = "
        CREATE TABLE departments (
            id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            description TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ";

        $db->query($sql);

        if ($db->tableExists('departments')) {
            echo "<h3>✅ <span class='success'>Departments table created!</span></h3>";

            // Add sample data
            $sampleData = [
                'name' => 'Emergency Department',
                'description' => 'Emergency medical services'
            ];

            $db->table('departments')->insert($sampleData);
            echo "<p>✅ <span class='success'>Sample department added!</span></p>";

            echo "<div class='info'>";
            echo "<h4>🎉 SUCCESS! Department Management Ready!</h4>";
            echo "<p><strong>Click the 'Add Department' button in Resource Management!</strong></p>";
            echo "<p><a href='/admin/resource-management' style='background:#28a745;color:white;padding:10px 20px;text-decoration:none;'>Test Add Department →</a></p>";
            echo "</div>";

        } else {
            echo "<h3>❌ <span class='error'>Failed to create table</span></h3>";
            echo "<p>Use phpMyAdmin to create manually.</p>";
        }
    }

} catch (Exception $e) {
    echo "<h3>❌ <span class='error'>Database Error: " . $e->getMessage() . "</span></h3>";
    echo "<p><strong>Manual fix:</strong> Use phpMyAdmin</p>";
}

echo "<hr><p><a href='/login'>← Back to Login</a></p>";
?>
