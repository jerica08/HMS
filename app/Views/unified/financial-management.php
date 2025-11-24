<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> - HMS</title>
    <meta name="base-url" content="<?= base_url() ?>">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta name="csrf-hash" content="<?= csrf_hash() ?>">
    <meta name="user-role" content="<?= esc($userRole) ?>">
    
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/unified/financial-management.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="<?= esc($userRole) ?>">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <?= $this->include('unified/components/notification', [
        'id' => 'financialNotification',
        'dismissFn' => 'dismissFinancialNotification()'
    ]) ?>

    <div class="main-container">
        <?php include APPPATH . 'Views/unified/components/sidebar.php'; ?>

        <main class="content" role="main">
            <h1 class="page-title">
                <i class="fas fa-dollar-sign"></i>
                <?php
                $pageTitles = [
                    'admin' => 'Financial Management',
                    'doctor' => 'My Financial Records',
                    'accountant' => 'Financial Overview',
                    'receptionist' => 'Billing & Payments'
                ];
                echo esc($pageTitles[$userRole] ?? 'Financial Management');
                ?>
            </h1>
            <div class="page-actions">
                <?php if (in_array('create_bill', $permissions)): ?>
                    <button type="button" id="addFinancialRecordBtn" class="btn btn-primary" aria-label="Add Financial Record" onclick="openFinancialTransactionModal()">
                        <i class="fas fa-plus" aria-hidden="true"></i> Add Transaction
                    </button>
                <?php endif; ?>
                <?php if (in_array($userRole ?? '', ['admin', 'it_staff', 'accountant'])): ?>
                    <button type="button" class="btn btn-secondary" id="exportBtn" aria-label="Export Data">
                        <i class="fas fa-download" aria-hidden="true"></i> Export
                    </button>
                <?php endif; ?>
            </div>

            <?php $errors = session()->get('errors'); ?>
            <?php if (!empty($errors) && is_array($errors)): ?>
                <div role="alert" aria-live="polite" style="margin-top:0.75rem; padding:0.75rem 1rem; border-radius:8px; border:1px solid #fecaca; background:#fee2e2; color:#991b1b;">
                    <div style="font-weight:600; margin-bottom:0.25rem;"><i class="fas fa-exclamation-circle"></i> Please fix the following errors:</div>
                    <ul style="margin:0; padding-left:1.25rem;">
                        <?php foreach ($errors as $field => $msg): ?>
                            <li><?= esc(is_array($msg) ? implode(', ', $msg) : $msg) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <br />

            <!-- Statistics Overview -->
            <div class="dashboard-overview">
                <?php if ($userRole === 'admin' || $userRole === 'it_staff' || $userRole === 'accountant'): ?>
                    <!-- Total Income Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern green"><i class="fas fa-dollar-sign"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Total Income</h3>
                                <p class="card-subtitle">Revenue generated</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value green">₱<?= number_format($stats['total_income'] ?? 0, 2) ?></div>
                                <div class="metric-label">Total</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value blue">₱<?= number_format($stats['monthly_income'] ?? 0, 2) ?></div>
                                <div class="metric-label">This Month</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Total Expenses Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern red"><i class="fas fa-credit-card"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Total Expenses</h3>
                                <p class="card-subtitle">Money spent</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value red">₱<?= number_format($stats['total_expenses'] ?? 0, 2) ?></div>
                                <div class="metric-label">Total</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value orange">₱<?= number_format($stats['monthly_expenses'] ?? 0, 2) ?></div>
                                <div class="metric-label">This Month</div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($userRole === 'doctor'): ?>
                    <!-- My Income Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern green"><i class="fas fa-wallet"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">My Income</h3>
                                <p class="card-subtitle">Personal earnings</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value green">₱<?= number_format($stats['my_income'] ?? 0, 2) ?></div>
                                <div class="metric-label">Total</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value blue">₱<?= number_format($stats['monthly_income'] ?? 0, 2) ?></div>
                                <div class="metric-label">This Month</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Status Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-file-invoice"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Payment Status</h3>
                                <p class="card-subtitle">Billing overview</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $stats['total_bills'] ?? 0 ?></div>
                                <div class="metric-label">Total Bills</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value green"><?= $stats['paid_bills'] ?? 0 ?></div>
                                <div class="metric-label">Paid</div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($userRole === 'receptionist'): ?>
                    <!-- Billing Queue Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern orange"><i class="fas fa-file-invoice-dollar"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Billing Queue</h3>
                                <p class="card-subtitle">Pending payments</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value orange"><?= $stats['pending_bills'] ?? 0 ?></div>
                                <div class="metric-label">Pending</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value red"><?= $stats['overdue_bills'] ?? 0 ?></div>
                                <div class="metric-label">Overdue</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Today's Payments Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern green"><i class="fas fa-money-bill-wave"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Today's Payments</h3>
                                <p class="card-subtitle"><?= date('F j, Y') ?></p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value green">₱<?= number_format($stats['today_payments'] ?? 0, 2) ?></div>
                                <div class="metric-label">Collected</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value blue"><?= $stats['today_transactions'] ?? 0 ?></div>
                                <div class="metric-label">Transactions</div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- General Financial Overview -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-chart-line"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Financial Overview</h3>
                                <p class="card-subtitle">General statistics</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue">₱<?= number_format($stats['total_income'] ?? 0, 2) ?></div>
                                <div class="metric-label">Income</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value red">₱<?= number_format($stats['total_expenses'] ?? 0, 2) ?></div>
                                <div class="metric-label">Expenses</div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Net Balance Card (All Roles) -->
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue"><i class="fas fa-balance-scale"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Financial Balance</h3>
                            <p class="card-subtitle">Current status</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value blue">₱<?= number_format($stats['net_balance'] ?? 0, 2) ?></div>
                            <div class="metric-label">Net Balance</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value purple">₱<?= number_format($stats['profit_margin'] ?? 0, 2) ?></div>
                            <div class="metric-label">Profit</div>
                        </div>
                    </div>
                </div>

                <!-- Bills & Payments Card (All Roles) -->
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern orange"><i class="fas fa-receipt"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Bills & Payments</h3>
                            <p class="card-subtitle">Payment status</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value orange"><?= $stats['pending_bills'] ?? 0 ?></div>
                            <div class="metric-label">Pending</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value green"><?= $stats['paid_bills'] ?? 0 ?></div>
                            <div class="metric-label">Paid</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="controls-section">
                <div class="filters-section">
                    <div class="filter-group">
                        <label for="dateFilter">Date:</label>
                        <input type="date" id="dateFilter" class="form-input">
                    </div>
                    
                    <div class="filter-group">
                        <label for="categoryFilter">Category:</label>
                        <select id="categoryFilter" class="form-select">
                            <option value="">All Categories</option>
                            <option value="Income">Income</option>
                            <option value="Expense">Expense</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="searchFilter">Search:</label>
                        <input type="text" id="searchFilter" class="form-input" placeholder="Search transactions...">
                    </div>
                    
                    <button type="button" id="clearFilters" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>
            </div>

            <?php if (isset($accounts)): ?>
                <!-- DEBUG: accounts count = <?= count($accounts) ?> -->
            <?php else: ?>
                <!-- DEBUG: $accounts is NOT set -->
            <?php endif; ?>

            <!-- Billing Accounts Table -->
            <div class="financial-table-container">
                <table class="financial-table">
                    <thead>
                        <tr>
                            <th>Billing ID</th>
                            <th>Patient</th>
                            <th>Admission</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="financialTableBody">
                        <?php if (!empty($accounts) && is_array($accounts)): ?>
                            <?php foreach ($accounts as $account): ?>
                                <tr>
                                    <td><?= esc($account['billing_id'] ?? '') ?></td>
                                    <td>
                                        <strong><?= esc($account['patient_name'] ?? ('Patient #' . ($account['patient_id'] ?? ''))) ?></strong><br>
                                        <small>ID: <?= esc($account['patient_id'] ?? 'N/A') ?></small>
                                    </td>
                                    <td>
                                        <?php if (!empty($account['admission_id'])): ?>
                                            In-Patient (Admission #<?= esc($account['admission_id']) ?>)
                                        <?php else: ?>
                                            OPD / Out-Patient
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                            $status = strtolower($account['status'] ?? 'open');
                                            $label  = ucfirst($status);
                                        ?>
                                        <span class="status-badge <?= $status === 'paid' ? 'paid' : 'open' ?>">
                                            <?= esc($label) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary btn-small" onclick="openBillingAccountModal(<?= esc($account['billing_id'] ?? 0) ?>)">
                                            <i class="fas fa-eye"></i> View Details
                                        </button>
                                        <?php if (in_array($userRole, ['admin', 'accountant']) && ($status !== 'paid')): ?>
                                            <button class="btn btn-success btn-small" onclick="markBillingAccountPaid(<?= esc($account['billing_id'] ?? 0) ?>)">
                                                <i class="fas fa-check-circle"></i> Mark as Paid
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="loading-row">
                                    <i class="fas fa-file-invoice-dollar"></i> No billing accounts found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
    
    <!-- Financial Transaction Modal -->
    <?php include APPPATH . 'Views/unified/modals/financial-transaction-modal.php'; ?>

    <!-- Billing Account Details Modal -->
    <div id="billingAccountModal" class="hms-modal-overlay" aria-hidden="true" style="display: none;">
        <div class="hms-modal" role="dialog" aria-modal="true" aria-labelledby="billingAccountTitle">
            <div class="hms-modal-header">
                <div class="hms-modal-title" id="billingAccountTitle">
                    <i class="fas fa-file-invoice-dollar" style="color:#4f46e5"></i>
                    Billing Account Details
                </div>
                <button type="button" class="btn btn-secondary btn-small" onclick="closeBillingAccountModal()" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="hms-modal-body">
                <div id="billingAccountHeader" style="margin-bottom:1rem;"></div>
                <table class="financial-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody id="billingItemsBody">
                        <tr>
                            <td colspan="4" class="loading-row">
                                <i class="fas fa-spinner fa-spin"></i> Loading billing details...
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div style="text-align:right; margin-top:0.75rem; font-weight:600;">
                    Total Amount: <span id="billingAccountTotal">₱0.00</span>
                </div>
            </div>
        </div>
    </div>

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
        function showFinancialNotification(message, type) {
            const container = document.getElementById('financialNotification');
            const iconEl = document.getElementById('financialNotificationIcon');
            const textEl = document.getElementById('financialNotificationText');

            if (!container || !iconEl || !textEl) return;

            const isError = type === 'error';
            const isSuccess = type === 'success';

            container.style.border = isError ? '1px solid #fecaca' : '1px solid #bbf7d0';
            container.style.background = isError ? '#fee2e2' : '#ecfdf5';
            container.style.color = isError ? '#991b1b' : '#166534';

            const iconClass = isError ? 'fa-exclamation-triangle' : (isSuccess ? 'fa-check-circle' : 'fa-info-circle');
            iconEl.className = 'fas ' + iconClass;

            textEl.textContent = String(message || '');
            container.style.display = 'flex';
        }

        function dismissFinancialNotification() {
            const container = document.getElementById('financialNotification');
            if (container) {
                container.style.display = 'none';
            }
        }

        async function markBillingAccountPaid(billingId) {
            if (!billingId) return;
            if (!confirm('Mark this billing account as PAID?')) {
                return;
            }

            try {
                const meta = document.querySelector('meta[name="base-url"]');
                const baseUrl = meta ? meta.content : '';
                const url = baseUrl.replace(/\/$/, '') + '/financial/billing-accounts/' + billingId + '/paid';

                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await res.json();
                if (data.success) {
                    if (typeof showFinancialNotification === 'function') {
                        showFinancialNotification(data.message || 'Billing account marked as paid.', 'success');
                    }
                    setTimeout(() => window.location.reload(), 600);
                } else {
                    const msg = data.message || 'Failed to mark billing account as paid.';
                    if (typeof showFinancialNotification === 'function') {
                        showFinancialNotification(msg, 'error');
                    } else {
                        alert(msg);
                    }
                }
            } catch (e) {
                console.error('Error marking billing account as paid:', e);
                if (typeof showFinancialNotification === 'function') {
                    showFinancialNotification('Unexpected error while marking account as paid.', 'error');
                } else {
                    alert('Unexpected error while marking account as paid.');
                }
            }
        }

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
                    if (typeof showFinancialNotification === 'function') {
                        showFinancialNotification('Financial record saved successfully', 'success');
                    }

                    // Refresh the page or update stats via AJAX
                    location.reload();
                } catch (err) {
                    if (typeof showFinancialNotification === 'function') {
                        showFinancialNotification('Failed to save financial record', 'error');
                    } else {
                        alert('Failed to save financial record');
                    }
                }
            });
        }

        // Edit and Delete functions
        function editTransaction(id) {
            alert('Edit transaction functionality coming soon! Transaction ID: ' + id);
        }

        function deleteTransaction(id) {
            if (confirm('Are you sure you want to delete this transaction?')) {
                alert('Delete transaction functionality coming soon! Transaction ID: ' + id);
            }
        }

        // Simple direct approach - override any existing handlers
        function setupFinancialModalButton() {
            const btn = document.getElementById('addFinancialRecordBtn');
            if (btn) {
                // Remove all existing event listeners by cloning
                const newBtn = btn.cloneNode(true);
                btn.parentNode.replaceChild(newBtn, btn);
                
                // Add our click handler
                newBtn.onclick = function(e) {
                    e.preventDefault();
                    if (typeof openFinancialTransactionModal === 'function') {
                        openFinancialTransactionModal();
                    } else {
                        alert('Modal function not found');
                    }
                };
            }
        }

        // Try multiple times to ensure button is ready
        setTimeout(setupFinancialModalButton, 100);
        setTimeout(setupFinancialModalButton, 500);
        setTimeout(setupFinancialModalButton, 1000);

        // Set base URL for financial modal AJAX requests
        window.baseUrl = '<?= base_url() ?>';

        // Billing account details modal helpers
        function openBillingAccountModal(billingId) {
            const modal = document.getElementById('billingAccountModal');
            const header = document.getElementById('billingAccountHeader');
            const body   = document.getElementById('billingItemsBody');
            const totalEl = document.getElementById('billingAccountTotal');
            if (!modal || !header || !body || !totalEl) return;

            header.innerHTML = '';
            body.innerHTML = `
                <tr>
                    <td colspan="4" class="loading-row">
                        <i class="fas fa-spinner fa-spin"></i> Loading billing details...
                    </td>
                </tr>
            `;
            totalEl.textContent = '₱0.00';

            modal.style.display = 'block';
            modal.setAttribute('aria-hidden', 'false');

            const baseUrl = window.baseUrl || document.querySelector('meta[name="base-url"]')?.content || '';
            if (!baseUrl) return;

            fetch(`${baseUrl}/billing/accounts/${billingId}`)
                .then(r => r.json())
                .then(result => {
                    if (!result || result.success === false) {
                        body.innerHTML = `
                            <tr>
                                <td colspan="4" class="loading-row">
                                    ${result && result.message ? result.message : 'Failed to load billing account.'}
                                </td>
                            </tr>
                        `;
                        return;
                    }

                    const acc = result.data || {};
                    header.innerHTML = `
                        <div><strong>Billing ID:</strong> ${acc.billing_id || ''}</div>
                        <div><strong>Patient:</strong> ${acc.patient_name || ('Patient #' + (acc.patient_id || ''))}</div>
                    `;

                    const items = Array.isArray(acc.items) ? acc.items : [];
                    if (!items.length) {
                        body.innerHTML = `
                            <tr>
                                <td colspan="4" class="loading-row">
                                    <i class="fas fa-info-circle"></i> No billing items for this account.
                                </td>
                            </tr>
                        `;
                    } else {
                        body.innerHTML = '';
                        items.forEach(item => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td>${item.description || ''}</td>
                                <td>${item.quantity || 1}</td>
                                <td>₱${parseFloat(item.unit_price || 0).toFixed(2)}</td>
                                <td>₱${parseFloat(item.line_total || 0).toFixed(2)}</td>
                            `;
                            body.appendChild(tr);
                        });
                    }

                    totalEl.textContent = '₱' + parseFloat(acc.total_amount || 0).toFixed(2);
                })
                .catch(() => {
                    body.innerHTML = `
                        <tr>
                            <td colspan="4" class="loading-row">
                                Failed to load billing account. Please try again.
                            </td>
                        </tr>
                    `;
                });
        }

        function closeBillingAccountModal() {
            const modal = document.getElementById('billingAccountModal');
            if (!modal) return;
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
        }
    </script>

    <?php if (session()->getFlashdata('success') || session()->getFlashdata('error')): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                showFinancialNotification(
                    '<?= esc(session()->getFlashdata('success') ?: session()->getFlashdata('error'), 'js') ?>',
                    '<?= session()->getFlashdata('success') ? 'success' : 'error' ?>'
                );
            });
        </script>
    <?php endif; ?>
</body>
</html>