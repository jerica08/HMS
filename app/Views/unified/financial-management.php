
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= esc($title) ?> - HMS</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('assets/css/unified/financial-management.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <meta name="base-url" content="<?= base_url() ?>">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta name="user-role" content="<?= esc($userRole) ?>">
</head>
<body class="<?= esc($userRole) ?>">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <?php include APPPATH . 'Views/unified/components/sidebar.php'; ?>

        <main class="content">
            <h1 class="page-title"><?= esc($title) ?></h1>
            
            <!-- Role-based Action Buttons -->
            <?php if (in_array('create_bill', $permissions)): ?>
            <div class="page-actions">
                <button type="button" class="btn btn-primary" onclick="openBillingModal()">
                    <i class="fas fa-plus"></i> Create Bill
                </button>
                <?php if (in_array('process_payment', $permissions)): ?>
                <button type="button" class="btn btn-success" onclick="openPaymentModal()">
                    <i class="fas fa-credit-card"></i> Process Payment
                </button>
                <?php endif; ?>
                <?php if (in_array('create_expense', $permissions)): ?>
                <button type="button" class="btn btn-warning" onclick="openExpenseModal()">
                    <i class="fas fa-receipt"></i> Add Expense
                </button>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Financial Statistics -->
            <div class="financial-stats">
                <div class="stat-card income">
                    <div class="stat-icon">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" id="totalIncome">₱<?= number_format($stats['total_income'], 2) ?></div>
                        <div class="stat-label">
                            <?= $userRole === 'doctor' ? 'My Income' : 'Total Income' ?>
                        </div>
                    </div>
                </div>
                
                <?php if (in_array('view_all', $permissions)): ?>
                <div class="stat-card expenses">
                    <div class="stat-icon">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" id="totalExpenses">₱<?= number_format($stats['total_expenses'], 2) ?></div>
                        <div class="stat-label">Total Expenses</div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="stat-card balance">
                    <div class="stat-icon">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" id="netBalance">₱<?= number_format($stats['net_balance'], 2) ?></div>
                        <div class="stat-label">Net Balance</div>
                    </div>
                </div>