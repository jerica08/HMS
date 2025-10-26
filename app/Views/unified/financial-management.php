
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= esc($title) ?> - HMS</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('assets/css/unified/financial-management.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        /* Dashboard Overview Cards styling (same as Resource Management) */
        .dashboard-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .overview-card {
            background: white;
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid #e5e7eb;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .overview-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .card-header-modern {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .card-icon-modern {
            width: 3rem;
            height: 3rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
        }

        .card-icon-modern.blue { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
        .card-icon-modern.green { background: linear-gradient(135deg, #10b981, #059669); }
        .card-icon-modern.red { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .card-icon-modern.orange { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .card-icon-modern.purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }

        .card-info {
            flex: 1;
        }

        .card-title-modern {
            font-size: 1.125rem;
            font-weight: 600;
            color: #111827;
            margin: 0 0 0.25rem 0;
        }

        .card-subtitle {
            font-size: 0.875rem;
            color: #6b7280;
            margin: 0;
        }

        .card-metrics {
            display: flex;
            gap: 1rem;
        }

        .metric {
            flex: 1;
            text-align: center;
        }

        .metric-value {
            font-size: 1.875rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            display: block;
        }

        .metric-value.blue { color: #3b82f6; }
        .metric-value.green { color: #10b981; }
        .metric-value.red { color: #ef4444; }
        .metric-value.orange { color: #f59e0b; }
        .metric-value.purple { color: #8b5cf6; }

        .metric-label {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 500;
        }

        /* Table styling to match Resource Management */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 0.75rem;
            vertical-align: top;
            border-top: 1px solid #dee2e6;
        }

        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #dee2e6;
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        /* Empty state styling */
        .empty-state-cell {
            text-align: center;
            vertical-align: middle;
            padding: 3rem 1rem !important;
        }

        /* Button styling */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2563eb;
        }

        .btn-secondary {
            background-color: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #4b5563;
        }

        .btn-danger {
            background-color: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background-color: #dc2626;
        }

        .btn-small {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        /* Inline actions styling */
        .inline-actions {
            display: flex;
            gap: 0.5rem;
        }

        /* Text alignment */
        .text-right {
            text-align: right !important;
        }
        .text-capitalize {
            text-transform: capitalize !important;
        }

        /* Page actions styling */
        .page-actions {
            margin-bottom: 1.5rem;
        }

        /* HMS Card styling */
        .hms-card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }

        .hms-card-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            background-color: #f9fafb;
        }

        .hms-card-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #374151;
            margin: 0;
        }

        .hms-card-body {
            padding: 1.5rem;
        }

        /* Margin top classes */
        .mt-6 {
            margin-top: 1.5rem;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <meta name="base-url" content="<?= base_url() ?>">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta name="user-role" content="<?= esc($userRole) ?>">
<body class="<?= esc($userRole) ?>">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <?php include APPPATH . 'Views/unified/components/sidebar.php'; ?>

        <main class="content">
            <h1 class="page-title"><?= esc($title) ?></h1>
            
            <!-- Role-based Action Buttons -->
            <?php if (in_array('create_bill', $permissions)): ?>
            <div class="page-actions">
                <button type="button" class="btn btn-primary" onclick="openAddFinancialRecordModal()">
                    <i class="fas fa-plus"></i> Add Financial Record
                </button>
            </div>
            <?php endif; ?>

            <!-- Financial Statistics Cards (Dashboard Style) -->
            <div class="dashboard-overview">
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern green">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                        <div class="card-info">
                            <h3 class="card-title-modern">
                                <?= $userRole === 'doctor' ? 'My Income' : 'Total Income' ?>
                            </h3>
                            <p class="card-subtitle">Revenue generated</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value green">₱<?= number_format($stats['total_income'], 2) ?></div>
                        </div>
                    </div>
                </div>

                <?php if (in_array('view_all', $permissions)): ?>
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern red">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Total Expenses</h3>
                            <p class="card-subtitle">Money spent</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value red">₱<?= number_format($stats['total_expenses'], 2) ?></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue">
                            <i class="fas fa-balance-scale"></i>
                        </div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Net Balance</h3>
                            <p class="card-subtitle">Current balance</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value blue">₱<?= number_format($stats['net_balance'], 2) ?></div>
                        </div>
                    </div>
                </div>

                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern orange">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Pending Bills</h3>
                            <p class="card-subtitle">Awaiting payment</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value orange"><?= $stats['pending_bills'] ?? 0 ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial Transactions Table -->
            <div class="hms-card mt-6">
                <div class="hms-card-header">
                    <h2 class="hms-card-title">Financial Transactions</h2>
                </div>
                <div class="hms-card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Transaction Name</th>
                                    <th>Category</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $transactions = $transactions ?? [];
                                if (!empty($transactions) && is_array($transactions)): ?>
                                    <?php foreach ($transactions as $t): ?>
                                        <tr>
                                            <td><?= esc($t['transaction_name'] ?? $t['expense_name'] ?? '-') ?></td>
                                            <td>
                                                <?php if (isset($t['category'])): ?>
                                                    <span class="badge badge-<?= $t['category'] === 'Income' ? 'success' : 'warning' ?>">
                                                        <?= esc($t['category']) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-info">
                                                        <?= esc($t['expense_category'] ?? 'Other') ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-right">
                                                <strong>₱<?= number_format($t['amount'] ?? 0, 2) ?></strong>
                                            </td>
                                            <td><?= esc(date('M d, Y', strtotime($t['date'] ?? $t['expense_date'] ?? $t['payment_date'] ?? ''))) ?></td>
                                            <td>
                                                <?php if (isset($t['category'])): ?>
                                                    <?= $t['category'] === 'Income' ? '💰 Income' : '💸 Expense' ?>
                                                <?php else: ?>
                                                    💸 Expense
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="inline-actions">
                                                    <button class="btn btn-secondary btn-small" onclick="editTransaction(<?= esc($t['id'] ?? 0) ?>)">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <button class="btn btn-danger btn-small" onclick="deleteTransaction(<?= esc($t['id'] ?? 0) ?>)">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="empty-state-cell">
                                            <div class="empty-state">
                                                <i class="fas fa-inbox fa-3x text-muted"></i>
                                                <p>No financial transactions found.</p>
                                                <small class="text-muted">Add your first financial record to get started.</small>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Add Financial Record Modal -->
            <?php if (in_array('create_bill', $permissions)): ?>
            <div id="addFinancialRecordModal" class="hms-modal-overlay" aria-hidden="true">
                <div class="hms-modal" role="dialog" aria-modal="true" aria-labelledby="addFinancialRecordTitle">
                    <div class="hms-modal-header">
                        <div class="hms-modal-title" id="addFinancialRecordTitle">
                            <i class="fas fa-plus-circle" style="color:#4f46e5"></i>
                            Add Financial Record
                        </div>
                        <button type="button" class="btn btn-secondary btn-small" onclick="closeAddFinancialRecordModal()" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form id="addFinancialRecordForm">
                        <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                        <div class="hms-modal-body">
                            <div class="form-grid">
                                <div>
                                    <label class="form-label" for="fr_transaction_name">Transaction Name*</label>
                                    <input id="fr_transaction_name" name="transaction_name" type="text" class="form-input" required autocomplete="off" placeholder="Enter transaction name">
                                    <small id="err_fr_transaction_name" style="color:#dc2626"></small>
                                </div>
                                <div>
                                    <label class="form-label" for="fr_category">Category*</label>
                                    <select id="fr_category" name="category" class="form-select" required>
                                        <option value="">Select category</option>
                                        <option value="Income">Income</option>
                                        <option value="Expense">Expense</option>
                                    </select>
                                    <small id="err_fr_category" style="color:#dc2626"></small>
                                </div>
                                <div>
                                    <label class="form-label" for="fr_amount">Amount* (₱)</label>
                                    <input id="fr_amount" name="amount" type="number" class="form-input" min="0.01" step="0.01" required autocomplete="off" placeholder="0.00">
                                    <small id="err_fr_amount" style="color:#dc2626"></small>
                                </div>
                                <div>
                                    <label class="form-label" for="fr_date">Date*</label>
                                    <input id="fr_date" name="date" type="date" class="form-input" required autocomplete="off">
                                    <small id="err_fr_date" style="color:#dc2626"></small>
                                </div>
                                <div id="payment_method_div" style="display: none;">
                                    <label class="form-label" for="fr_payment_method">Payment Method</label>
                                    <select id="fr_payment_method" name="payment_method" class="form-select">
                                        <option value="cash">Cash</option>
                                        <option value="card">Card</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="check">Check</option>
                                    </select>
                                    <small id="err_fr_payment_method" style="color:#dc2626"></small>
                                </div>
                                <div id="expense_category_div" style="display: none;">
                                    <label class="form-label" for="fr_expense_category">Expense Category</label>
                                    <select id="fr_expense_category" name="expense_category" class="form-select">
                                        <option value="supplies">Medical Supplies</option>
                                        <option value="equipment">Equipment</option>
                                        <option value="utilities">Utilities</option>
                                        <option value="salaries">Salaries</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <small id="err_fr_expense_category" style="color:#dc2626"></small>
                                </div>
                                <div class="full">
                                    <label class="form-label" for="fr_description">Description</label>
                                    <textarea id="fr_description" name="description" rows="3" class="form-textarea" autocomplete="off" placeholder="Optional description..."></textarea>
                                    <small id="err_fr_description" style="color:#dc2626"></small>
                                </div>
                            </div>
                        </div>
                        <div class="hms-modal-actions">
                            <button type="button" class="btn btn-secondary" onclick="closeAddFinancialRecordModal()">Cancel</button>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Record</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

    <!-- Include Modal Components -->
    <?php if (in_array('create_bill', $permissions)): ?>
        <?php include APPPATH . 'Views/unified/modals/billing-modal.php'; ?>
    <?php endif; ?>
    
    <?php if (in_array('process_payment', $permissions)): ?>
        <?php include APPPATH . 'Views/unified/modals/payment-modal.php'; ?>
    <?php endif; ?>
    
    <?php if (in_array('create_expense', $permissions)): ?>
        <?php include APPPATH . 'Views/unified/modals/expense-modal.php'; ?>
    <?php endif; ?>

    <!-- JavaScript Files -->
    <script src="<?= base_url('assets/js/unified/financial-utils.js') ?>"></script>
    
    <?php if (in_array('create_bill', $permissions)): ?>
        <script src="<?= base_url('assets/js/unified/billing-modal.js') ?>"></script>
    <?php endif; ?>
    
    <?php if (in_array('process_payment', $permissions)): ?>
        <script src="<?= base_url('assets/js/unified/payment-modal.js') ?>"></script>
    <?php endif; ?>
    
    <?php if (in_array('create_expense', $permissions)): ?>
        <script src="<?= base_url('assets/js/unified/expense-modal.js') ?>"></script>
    <?php endif; ?>
    
    <script src="<?= base_url('assets/js/unified/financial-management.js') ?>"></script>
    <script>
        // Add Financial Record modal functions
        function openAddFinancialRecordModal() {
            const modal = document.getElementById('addFinancialRecordModal');
            if (modal) {
                modal.setAttribute('aria-hidden', 'false');
                modal.style.display = 'block';
                // Focus first input
                setTimeout(() => {
                    document.getElementById('fr_transaction_name')?.focus();
                }, 100);
            }
        }

        function closeAddFinancialRecordModal() {
            const modal = document.getElementById('addFinancialRecordModal');
            const form = document.getElementById('addFinancialRecordForm');
            if (modal) {
                modal.setAttribute('aria-hidden', 'true');
                modal.style.display = 'none';
            }
            if (form) form.reset();
            // Clear errors
            const errors = form?.querySelectorAll('[id^="err_fr_"]');
            errors?.forEach(e => e.textContent = '');
            // Hide conditional fields
            document.getElementById('payment_method_div')?.style.setProperty('display', 'none');
            document.getElementById('expense_category_div')?.style.setProperty('display', 'none');
        }

        // Category change handler
        document.getElementById('fr_category')?.addEventListener('change', function() {
            const category = this.value;
            const paymentMethodDiv = document.getElementById('payment_method_div');
            const expenseCategoryDiv = document.getElementById('expense_category_div');

            if (category === 'Income') {
                paymentMethodDiv?.style.setProperty('display', 'block');
                expenseCategoryDiv?.style.setProperty('display', 'none');
            } else if (category === 'Expense') {
                paymentMethodDiv?.style.setProperty('display', 'none');
                expenseCategoryDiv?.style.setProperty('display', 'block');
            } else {
                paymentMethodDiv?.style.setProperty('display', 'none');
                expenseCategoryDiv?.style.setProperty('display', 'none');
            }
        });

        // Add Financial Record form submission
        const addFinancialRecordForm = document.getElementById('addFinancialRecordForm');
        if (addFinancialRecordForm) {
            addFinancialRecordForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                // Clear previous errors
                const errors = addFinancialRecordForm.querySelectorAll('[id^="err_fr_"]');
                errors.forEach(error => error.textContent = '');

                // Get form values
                const transactionName = document.getElementById('fr_transaction_name')?.value?.trim();
                const category = document.getElementById('fr_category')?.value;
                const amount = document.getElementById('fr_amount')?.value;
                const date = document.getElementById('fr_date')?.value;

                // Validate
                let hasErrors = false;
                if (!transactionName) {
                    document.getElementById('err_fr_transaction_name').textContent = 'Transaction name is required.';
                    hasErrors = true;
                }
                if (!category) {
                    document.getElementById('err_fr_category').textContent = 'Please select a category.';
                    hasErrors = true;
                }
                if (!amount || parseFloat(amount) <= 0) {
                    document.getElementById('err_fr_amount').textContent = 'Amount must be greater than zero.';
                    hasErrors = true;
                }
                if (!date) {
                    document.getElementById('err_fr_date').textContent = 'Date is required.';
                    hasErrors = true;
                }

                if (hasErrors) return;

                try {
                    const formData = new FormData(addFinancialRecordForm);
                    const res = await fetch(window.location.origin + '/financial/record/create', {
                        method: 'POST',
                        body: formData
                    });

                    let data = null;
                    try {
                        const raw = await res.text();
                        data = raw ? JSON.parse(raw) : null;
                    } catch (e) {}

                    if (!res.ok || (data && data.success === false)) {
                        const detail = data?.message || 'Failed to save financial record';
                        alert('Error: ' + detail);
                        return;
                    }

                    closeAddFinancialRecordModal();
                    alert('Financial record saved successfully');

                    // Refresh the page or update stats via AJAX
                    location.reload();
                } catch (err) {
                    alert('Failed to save financial record');
        // Edit and Delete functions
        function editTransaction(id) {
            alert('Edit transaction functionality coming soon! Transaction ID: ' + id);
        }

        function deleteTransaction(id) {
            if (confirm('Are you sure you want to delete this transaction?')) {
                alert('Delete transaction functionality coming soon! Transaction ID: ' + id);
            }
        }