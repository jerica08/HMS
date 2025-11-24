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
                                        <span class="status-badge <?= $status === 'paid' ? 'paid' : 'open' ?>">
                                            <?= esc($label) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="financial-actions">
                                            <button
                                                class="btn btn-primary btn-small"
                                                data-patient-name="<?= esc($account['patient_name'] ?? ('Patient #' . ($account['patient_id'] ?? ''))) ?>"
                                                onclick="openBillingAccountModal(<?= esc($account['billing_id'] ?? 0) ?>, this.dataset.patientName)">
                                                <i class="fas fa-eye"></i> View Details
                                            </button>

                                            <?php if (in_array($userRole, ['admin', 'accountant']) && ($status !== 'paid')): ?>
                                                <button class="btn btn-success btn-small" onclick="markBillingAccountPaid(<?= esc($account['billing_id'] ?? 0) ?>)">
                                                    <i class="fas fa-check-circle"></i> Mark as Paid
                                                </button>
                                            <?php endif; ?>
<!-- ... -->
        setTimeout(setupFinancialModalButton, 1000);

        // Set base URL for financial modal AJAX requests
        window.baseUrl = '<?= base_url() ?>';

        // Billing account details modal helpers
        function openBillingAccountModal(billingId, patientNameFromRow) {

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
<!-- ... -->
                            </tr>
                        `;
                        return;
                    }

                    const acc = result.data || {};

                    // Prefer name from table row, then fall back to API fields
                    const patientName = (patientNameFromRow
                        || acc.patient_name
                        || acc.patient_full_name
                        || ((acc.first_name || acc.last_name) ? `${acc.first_name || ''} ${acc.last_name || ''}`.trim() : '')
                        || (acc.patient_id ? `Patient #${acc.patient_id}` : 'Unknown patient'));

                    header.innerHTML = `
                        <div><strong>Billing ID:</strong> ${acc.billing_id || ''}</div>
                        <div><strong>Patient:</strong> ${patientName}</div>
                    `;

                    const items = Array.isArray(acc.items) ? acc.items : [];
                    if (!items.length) {
                        body.innerHTML = `
                            <tr>
<!-- ... -->
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