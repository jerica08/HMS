<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Security Access - HMS Admin</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="admin">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <?php include APPPATH . 'Views/admin/components/sidebar.php'; ?>

        <main class="content">
            <h1 class="page-title">Security Access</h1>

            <div class="dashboard-overview">
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue"><i class="fas fa-users"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Security Score</h3>
                            <p class="card-subtitle">Overall system security</p>
                        </div>
                    </div>
                    <div class="metric">
                        <div class="metric-value green">0%</div>
                    </div>
                </div>

                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern purple"><i class="fas fa-user-check"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Active Sessions</h3>
                            <p class="card-subtitle">Currently user sessions</p>
                        </div>
                    </div>
                    <div class="metric">
                        <div class="metric-value blue">0</div>
                    </div>
                </div>

                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern purple"><i class="fas fa-user-times"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Blocked IPs</h3>
                            <p class="card-subtitle">Suspicious IPs</p>
                        </div>
                    </div>
                    <div class="metric">
                        <div class="metric-value purple">0</div>
                    </div>
                </div>

                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern purple"><i class="fas fa-user-shield"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">2FA Enabled</h3>
                            <p class="card-subtitle">Users with 2FA</p>
                        </div>
                    </div>
                    <div class="metric">
                        <div class="metric-value purple">0</div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="<?= base_url('js/logout.js') ?>"></script>
</body>
</html>
