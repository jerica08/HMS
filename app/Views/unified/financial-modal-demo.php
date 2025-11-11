<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Transaction Modal Demo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f3f4f6;
        }
        
        .demo-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .demo-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .demo-header h1 {
            margin: 0 0 10px 0;
            font-size: 2rem;
        }
        
        .demo-header p {
            margin: 0;
            opacity: 0.9;
        }
        
        .demo-content {
            padding: 30px;
        }
        
        .demo-section {
            margin-bottom: 40px;
        }
        
        .demo-section h2 {
            color: #1f2937;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .demo-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .btn-demo {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        
        .btn-success {
            background: #10b981;
            color: white;
        }
        
        .btn-success:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .feature-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
        }
        
        .feature-card h3 {
            margin: 0 0 10px 0;
            color: #374151;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .feature-card p {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
        }
        
        .field-list {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin-top: 15px;
        }
        
        .field-list h3 {
            margin: 0 0 15px 0;
            color: #1f2937;
        }
        
        .field-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .field-item:last-child {
            border-bottom: none;
        }
        
        .field-name {
            font-weight: 500;
            color: #374151;
        }
        
        .field-type {
            color: #6b7280;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
        
        .required {
            color: #ef4444;
        }
        
        .optional {
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="demo-container">
        <div class="demo-header">
            <h1><i class="fas fa-money-bill-wave"></i> Financial Transaction Modal</h1>
            <p>Complete modal form for HMS financial management system</p>
        </div>
        
        <div class="demo-content">
            <div class="demo-section">
                <h2><i class="fas fa-play-circle"></i> Try the Modal</h2>
                <div class="demo-buttons">
                    <button class="btn-demo btn-primary" onclick="openFinancialTransactionModal()">
                        <i class="fas fa-plus"></i> Add Financial Transaction
                    </button>
                    <button class="btn-demo btn-success" onclick="openFinancialTransactionModal()">
                        <i class="fas fa-chart-line"></i> Record Income
                    </button>
                </div>
            </div>
            
            <div class="demo-section">
                <h2><i class="fas fa-database"></i> Database Fields</h2>
                <div class="field-list">
                    <h3>Financial Transactions Table Structure</h3>
                    <div class="field-item">
                        <span class="field-name">transaction_id</span>
                        <span class="field-type">INT (PK, Auto) <span class="required">*</span></span>
                    </div>
                    <div class="field-item">
                        <span class="field-name">user_id</span>
                        <span class="field-type">INT (FK) <span class="required">*</span></span>
                    </div>
                    <div class="field-item">
                        <span class="field-name">type</span>
                        <span class="field-type">ENUM('Income','Expense') <span class="required">*</span></span>
                    </div>
                    <div class="field-item">
                        <span class="field-name">category_id</span>
                        <span class="field-type">INT (FK) <span class="required">*</span></span>
                    </div>
                    <div class="field-item">
                        <span class="field-name">amount</span>
                        <span class="field-type">DECIMAL(10,2) <span class="required">*</span></span>
                    </div>
                    <div class="field-item">
                        <span class="field-name">description</span>
                        <span class="field-type">TEXT <span class="optional">optional</span></span>
                    </div>
                    <div class="field-item">
                        <span class="field-name">transaction_date</span>
                        <span class="field-type">DATE <span class="required">*</span></span>
                    </div>
                    <div class="field-item">
                        <span class="field-name">created_at</span>
                        <span class="field-type">DATETIME <span class="required">*</span></span>
                    </div>
                </div>
            </div>
            
            <div class="demo-section">
                <h2><i class="fas fa-star"></i> Features</h2>
                <div class="feature-grid">
                    <div class="feature-card">
                        <h3><i class="fas fa-exchange-alt"></i> Dynamic Categories</h3>
                        <p>Categories update based on transaction type (Income/Expense)</p>
                    </div>
                    <div class="feature-card">
                        <h3><i class="fas fa-eye"></i> Live Preview</h3>
                        <p>See transaction details in real-time as you fill the form</p>
                    </div>
                    <div class="feature-card">
                        <h3><i class="fas fa-peso-sign"></i> Currency Format</h3>
                        <p>Philippine Peso formatting with proper validation</p>
                    </div>
                    <div class="feature-card">
                        <h3><i class="fas fa-user"></i> User Selection</h3>
                        <p>Assign transactions to specific users in the system</p>
                    </div>
                    <div class="feature-card">
                        <h3><i class="fas fa-calendar"></i> Date Picker</h3>
                        <p>Easy date selection with today's date as default</p>
                    </div>
                    <div class="feature-card">
                        <h3><i class="fas fa-bell"></i> Notifications</h3>
                        <p>Success/error messages with smooth animations</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include the Financial Transaction Modal -->
    <?php include_once APPPATH . 'Views/unified/modals/financial-transaction-modal.php'; ?>

    <script>
        // Base URL for AJAX requests
        const baseUrl = '<?= base_url() ?>';
        
        // Mock data for demo (since we're not connected to the actual database)
        const mockCategories = {
            'Income': [
                {category_id: 1, name: 'Patient Consultations'},
                {category_id: 2, name: 'Laboratory Services'},
                {category_id: 3, name: 'Pharmacy Sales'},
                {category_id: 4, name: 'Insurance Payments'},
                {category_id: 5, name: 'Emergency Services'},
                {category_id: 6, name: 'Surgical Procedures'},
                {category_id: 7, name: 'Room Rentals'},
                {category_id: 8, name: 'Other Income'}
            ],
            'Expense': [
                {category_id: 9, name: 'Salaries & Wages'},
                {category_id: 10, name: 'Medical Supplies'},
                {category_id: 11, name: 'Medicines'},
                {category_id: 12, name: 'Utilities'},
                {category_id: 13, name: 'Rent & Maintenance'},
                {category_id: 14, name: 'Insurance Premiums'},
                {category_id: 15, name: 'Marketing'},
                {category_id: 16, name: 'Equipment Purchase'},
                {category_id: 17, name: 'Training & Development'},
                {category_id: 18, name: 'Other Expenses'}
            ]
        };
        
        const mockUsers = [
            {id: 1, username: 'admin'},
            {id: 2, username: 'accountant'},
            {id: 3, username: 'doctor1'},
            {id: 4, username: 'nurse1'}
        ];
        
        // Override the load functions for demo
        function loadFinancialCategories() {
            financialCategories = mockCategories;
            updateFinancialCategories();
        }
        
        function loadFinancialUsers() {
            const userSelect = document.getElementById('transactionUser');
            userSelect.innerHTML = '<option value="">Select User</option>';
            
            mockUsers.forEach(user => {
                const option = document.createElement('option');
                option.value = user.id;
                option.textContent = user.username;
                userSelect.appendChild(option);
            });
        }
        
        // Override form submission for demo
        document.getElementById('financialTransactionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('saveFinancialBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            
            // Simulate API call
            setTimeout(() => {
                closeFinancialTransactionModal();
                showNotification('Financial transaction added successfully! (Demo Mode)', 'success');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Save Transaction';
            }, 1500);
        });
    </script>
</body>
</html>
