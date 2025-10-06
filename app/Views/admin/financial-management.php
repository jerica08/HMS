<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Financial Management - HMS Admin</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Scoped styles for this page's quick stats cards */
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .quick-stats .stat-card {
            background: #fff;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            border: 1px solid #f1f5f9;
        }
        .quick-stats .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        .quick-stats .stat-label {
            font-size: 0.9rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }
        .quick-stats .stat-change {
            font-size: 0.8rem;
            font-weight: 500;
        }
        .quick-stats .change-positive { color: #22c55e; }
        .quick-stats .change-negative { color: #ef4444; }

        /* Per-card accents and number colors */
        .quick-stats .stat-card.revenue { border-top: 4px solid #10b981; }
        .quick-stats .stat-card.revenue .stat-number { color: #10b981; }
        .quick-stats .stat-card.expenses { border-top: 4px solid #eab308; }
        .quick-stats .stat-card.expenses .stat-number { color: #eab308; }
        .quick-stats .stat-card.profit { border-top: 4px solid #4f46e5; }
        .quick-stats .stat-card.profit .stat-number { color: #4f46e5; }
        .quick-stats .stat-card.outstanding { border-top: 4px solid #f97316; }
        .quick-stats .stat-card.outstanding .stat-number { color: #f97316; }
    </style>
</head>
<body class="admin">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <?php include APPPATH . 'Views/admin/components/sidebar.php'; ?>

        <main class="content">
            <h1 class="page-title">Financial Management</h1>

            <!-- Financial Stats -->
            <div class="quick-stats">
                <div class="stat-card revenue">
                    <div class="stat-number">₱0</div>
                    <div class="stat-label">Monthly Revenue</div>   
                    <div class="stat-change change-positive">
                        <i class="fas fa-arrow-up"></i> +12% from last month
                    </div>
                </div>
                <div class="stat-card expenses">
                    <div class="stat-number">₱0</div>
                    <div class="stat-label">Monthly Expenses</div>
                    <div class="stat-change change-positive">
                        <i class="fas fa-arrow-down"></i> -5% from last month
                    </div>
                </div>
                <div class="stat-card profit">
                    <div class="stat-number">₱0</div>
                    <div class="stat-label">Net Profit</div>
                    <div class="stat-change change-positive">
                        <i class="fas fa-arrow-up"></i> +25% from last month
                    </div>
                </div>
                <div class="stat-card outstanding">
                    <div class="stat-number">₱0</div>
                    <div class="stat-label">Outstanding Bills</div>
                    <div class="stat-change change-negative">
                        <i class="fas fa-arrow-up"></i> +8% from last month
                    </div>
                </div>
            </div>

        </main>
    </div>

    <script src="<?= base_url('js/logout.js') ?>"></script>
</body>
</html>
