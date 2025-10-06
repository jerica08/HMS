<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Audit Logs - HMS Admin</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="admin">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <?php include APPPATH . 'Views/admin/components/sidebar.php'; ?>

        <main class="content">
            <h1 class="page-title">System Audit Logs</h1>

            <div class="dashboard-overview">
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue"><i class="fas fa-calendar-day"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Today's Events</h3>
                        </div>
                    </div>
                    <div class="metric">
                        <div class="metric-value blue">0</div>
                    </div>
                </div>

                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue"><i class="fas fa-exclamation-triangle"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Warning Events</h3>
                        </div>
                    </div>
                    <div class="metric">
                        <div class="metric-value blue">0</div>
                    </div>
                </div>

                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue"><i class="fas fa-times-circle"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Critical Events</h3>
                        </div>
                    </div>
                    <div class="metric">
                        <div class="metric-value blue">0</div>
                    </div>
                </div>

                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue"><i class="fas fa-users"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Active Users</h3>
                        </div>
                    </div>
                    <div class="metric">
                        <div class="metric-value blue">0</div>
                    </div>
                </div>

                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue"><i class="fas fa-server"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">System Uptime</h3>
                        </div>
                    </div>
                    <div class="metric">
                        <div class="metric-value blue">0</div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="<?= base_url('js/logout.js') ?>"></script>
</body>
</html>
