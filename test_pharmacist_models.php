<?php
// Test pharmacist models only
try {
    $prescriptionModel = new \App\Models\PrescriptionModel();
    $inventoryModel = new \App\Models\InventoryModel();

    echo "✅ Pharmacist Models loaded successfully\n";

    // Test if we can call methods (without database dependency for now)
    $statistics = $prescriptionModel->getStatistics();
    echo "✅ getStatistics() method works\n";

    $lowStock = $inventoryModel->getLowStockItems();
    echo "✅ getLowStockItems() method works\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
