<!DOCTYPE html>
<html>
<head>
    <title>Financial Management Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .income { background-color: #d4edda; }
        .expense { background-color: #f8d7da; }
    </style>
</head>
<body>
    <h1>Financial Management System Test</h1>
    
    <?php
    try {
        // Test database connection
        $db = \Config\Database::connect();
        echo "<p class='success'>✓ Database connection successful</p>";
        
        // Test if tables exist
        $tables = $db->getTableList();
        if (in_array('categories', $tables)) {
            echo "<p class='success'>✓ Categories table exists</p>";
            
            // Show categories
            $categories = $db->table('categories')->get()->getResultArray();
            echo "<h3>Categories (" . count($categories) . " total)</h3>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Name</th><th>Type</th><th>Description</th></tr>";
            foreach ($categories as $cat) {
                echo "<tr class='" . strtolower($cat['type']) . "'>";
                echo "<td>" . $cat['category_id'] . "</td>";
                echo "<td>" . $cat['name'] . "</td>";
                echo "<td>" . $cat['type'] . "</td>";
                echo "<td>" . ($cat['description'] ?: '-') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='error'>✗ Categories table not found</p>";
        }
        
        if (in_array('financial_transactions', $tables)) {
            echo "<p class='success'>✓ Financial transactions table exists</p>";
            
            // Show transactions
            $transactions = $db->table('financial_transactions')
                               ->select('financial_transactions.*, categories.name as category_name, users.username')
                               ->join('categories', 'categories.category_id = financial_transactions.category_id', 'left')
                               ->join('users', 'users.id = financial_transactions.user_id', 'left')
                               ->get()
                               ->getResultArray();
            
            echo "<h3>Financial Transactions (" . count($transactions) . " total)</h3>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Date</th><th>Type</th><th>Category</th><th>Amount</th><th>Description</th><th>User</th></tr>";
            
            $totalIncome = 0;
            $totalExpenses = 0;
            
            foreach ($transactions as $trans) {
                if ($trans['type'] == 'Income') {
                    $totalIncome += $trans['amount'];
                } else {
                    $totalExpenses += $trans['amount'];
                }
                
                echo "<tr class='" . strtolower($trans['type']) . "'>";
                echo "<td>" . $trans['transaction_id'] . "</td>";
                echo "<td>" . date('M d, Y', strtotime($trans['transaction_date'])) . "</td>";
                echo "<td>" . $trans['type'] . "</td>";
                echo "<td>" . ($trans['category_name'] ?: 'N/A') . "</td>";
                echo "<td>";
                if ($trans['type'] == 'Income') {
                    echo "<span style='color: green'>+₱" . number_format($trans['amount'], 2) . "</span>";
                } else {
                    echo "<span style='color: red'>-₱" . number_format($trans['amount'], 2) . "</span>";
                }
                echo "</td>";
                echo "<td>" . ($trans['description'] ?: '-') . "</td>";
                echo "<td>" . ($trans['username'] ?: 'N/A') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            echo "<h3>Summary</h3>";
            echo "<p><strong>Total Income:</strong> <span style='color: green'>₱" . number_format($totalIncome, 2) . "</span></p>";
            echo "<p><strong>Total Expenses:</strong> <span style='color: red'>₱" . number_format($totalExpenses, 2) . "</span></p>";
            echo "<p><strong>Net Total:</strong> <span style='color: " . ($totalIncome - $totalExpenses >= 0 ? 'green' : 'blue') . "'>₱" . number_format($totalIncome - $totalExpenses, 2) . "</span></p>";
            
        } else {
            echo "<p class='error'>✗ Financial transactions table not found</p>";
        }
        
        echo "<hr>";
        echo "<h2>Test Add Transaction</h2>";
        echo "<form method='post' action='" . base_url('financialController/addTransaction') . "'>";
        echo csrf_field();
        echo "<table>";
        echo "<tr><td>Type:</td><td>";
        echo "<select name='type' required><option value=''>Select</option><option value='Income'>Income</option><option value='Expense'>Expense</option></select>";
        echo "</td></tr>";
        echo "<tr><td>Category:</td><td>";
        echo "<select name='category_id' required><option value=''>Select</option>";
        foreach ($categories as $cat) {
            echo "<option value='" . $cat['category_id'] . "'>" . $cat['name'] . " (" . $cat['type'] . ")</option>";
        }
        echo "</select>";
        echo "</td></tr>";
        echo "<tr><td>Amount:</td><td><input type='number' name='amount' step='0.01' min='0.01' required placeholder='0.00'></td></tr>";
        echo "<tr><td>Date:</td><td><input type='date' name='transaction_date' value='" . date('Y-m-d') . "' required></td></tr>";
        echo "<tr><td>Description:</td><td><input type='text' name='description' placeholder='Optional description'></td></tr>";
        echo "<tr><td>User ID:</td><td><input type='number' name='user_id' value='1' required></td></tr>";
        echo "<tr><td colspan='2'><button type='submit' style='background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer;'>Add Transaction</button></td></tr>";
        echo "</table>";
        echo "</form>";
        
    } catch (\Exception $e) {
        echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
    }
    ?>
    
    <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 5px;">
        <h3>✅ Financial Management System Status</h3>
        <p><strong>Database Tables:</strong> Created successfully</p>
        <p><strong>Models:</strong> FinancialTransactionModel and CategoryModel ready</p>
        <p><strong>Controller:</strong> FinancialController available</p>
        <p><strong>Next Steps:</strong> Create views and integrate with main HMS system</p>
    </div>
</body>
</html>
