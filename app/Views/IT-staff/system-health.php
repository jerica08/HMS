<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Health - IT Staff</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="it-theme">
    <?php include APPPATH . 'Views/template/header.php'; ?>
    <div class="main-container">
        <?php include APPPATH . 'Views/IT-staff/components/sidebar.php'; ?>
        <main class="content">
            <h1 class="page-title">System Health</h1>

            <div class="panel">
                <div class="panel-header">
                    <i class="fas fa-heartbeat"></i>
                    <span>Overall Status</span>
                </div>
                <div class="panel-body grid-3">
                    <div class="stat">
                        <div class="stat-label">Uptime (24h)</div>
                        <div class="stat-value blue">0%</div>
                    </div>
                    <div class="stat">
                        <div class="stat-label">Servers Online</div>
                        <div class="stat-value green">0</div>
                    </div>
                    <div class="stat">
                        <div class="stat-label">Active Alerts</div>
                        <div class="stat-value orange">0</div>
                    </div>
                </div>
            </div>

            <div class="panel" style="margin-top:1.5rem;">
                <div class="panel-header">
                    <i class="fas fa-server"></i>
                    <span>Services</span>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Latency</th>
                            <th>Last Check</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Web Server</td>
                            <td><span class="badge badge-success">Online</span></td>
                            <td>0 ms</td>
                            <td>now</td>
                        </tr>
                        <tr>
                            <td>Database</td>
                            <td><span class="badge badge-success">Online</span></td>
                            <td>0 ms</td>
                            <td>now</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <script>
        function handleLogout(){
            if(confirm('Logout?')){ window.location.href = '<?= base_url('/logout') ?>'; }
        }
    </script>
</body>
</html>
