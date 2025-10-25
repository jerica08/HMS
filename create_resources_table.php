<?php
/**
 * Simple Database Table Creation
 * Run this directly: http://localhost:8080/create_resources_table.php
 */

echo "<h1>🏥 Create Resources Table</h1>";
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

    if ($db->tableExists('resources')) {
        echo "<p>✅ Resources table: <span class='success'>ALREADY EXISTS</span></p>";
        echo "<p><a href='/admin/resource-management' style='background:#28a745;color:white;padding:10px 20px;text-decoration:none;'>Go to Resource Management →</a></p>";
    } else {
        echo "<p>❌ Resources table: <span class='error'>MISSING</span></p>";
        echo "<p>🔧 Creating table...</p>";

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

        if ($db->tableExists('resources')) {
            echo "<p>✅ <span class='success'>Table created!</span></p>";
            echo "<p><a href='/admin/resource-management' style='background:#28a745;color:white;padding:10px 20px;text-decoration:none;'>Now go to Resource Management →</a></p>";
        } else {
            echo "<p>❌ <span class='error'>Failed to create table</span></p>";
            echo "<p>Use phpMyAdmin manually.</p>";
        }
    }

} catch (Exception $e) {
    echo "<p>❌ Database Error: " . $e->getMessage() . "</p>";
    echo "<p><strong>Manual fix:</strong> Use phpMyAdmin</p>";
}

echo "<hr><p><a href='/login'>← Back to Login</a></p>";
?>
