<?php
// Script to mark problematic migrations as completed

// Bootstrap CodeIgniter
require_once 'system/bootstrap.php';

$db = \Config\Database::connect();

try {
    // Mark CreateFinancialTransactionsTable as completed since we manually altered the table
    $db->table('migrations')->insert([
        'version' => '2025-11-11-033932',
        'class' => 'CreateFinancialTransactionsTable'
    ]);
    
    echo "✓ Marked CreateFinancialTransactionsTable as completed\n";
    
    // Mark CreateTransactionsTable as completed 
    $db->table('migrations')->insert([
        'version' => '2025-11-11-033334',
        'class' => 'CreateTransactionsTable'
    ]);
    
    echo "✓ Marked CreateTransactionsTable as completed\n";
    
    echo "\n🎉 Migration records updated successfully!\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
