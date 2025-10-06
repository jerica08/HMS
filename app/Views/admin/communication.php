<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Communication & Notifications - HMS Admin</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="admin">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <?php include APPPATH . 'Views/admin/components/sidebar.php'; ?>

        <main class="content">
            <h1 class="page-title">Communication & Notifications</h1>

            <div class="dashboard-overview" style="margin-bottom: 2rem;">
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue"><i class="fas fa-envelope"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Unread Messages</h3>
                            <p class="card-subtitle">Pending review</p>
                        </div>
                    </div>
                    <div class="metric">
                        <div class="metric-value blue">0</div>
                    </div>
                </div>

                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern purple"><i class="fas fa-bell"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Active Alerts</h3>
                            <p class="card-subtitle">System notifications</p>
                        </div>
                    </div>
                    <div class="metric">
                        <div class="metric-value purple">0</div>
                    </div>
                </div>

                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue"><i class="fas fa-broadcast-tower"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Broadcasts Sent</h3>
                            <p class="card-subtitle">This week</p>
                        </div>
                    </div>
                    <div class="metric">
                        <div class="metric-value green">0</div>
                    </div>
                </div>

                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern purple"><i class="fas fa-users"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Online Staff</h3>
                            <p class="card-subtitle">Currently active</p>
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
