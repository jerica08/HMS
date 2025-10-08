<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>IT Staff Dashboard - HMS</title>
        <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>
    <body class="it-theme">
        <!--Header-->
        <?php include APPPATH . 'Views/template/header.php'; ?>
        <div class="main-container">
            <!--Sidebar-->
            <?php include APPPATH . 'Views/IT-staff/components/sidebar.php'; ?>
        <!--Main Content-->
        <main class="content">
            <h1 class="page-title">It Staff Dashboard</h1>
        
        <div class="dashboard-overview">              
            <!--System Status-->
            
            <div class="overview-card">
                <div class="card-header-modern">
                    <div class="card-icon-modern blue">
                        <i class="fas fa-server "></i>
                    </div>
                    <div class="card-info">
                        <h3 class="card-title-modern">System Status</h3>
                        <p class="card-subtitle">Overall System Health</p>
                    </div>
                </div>
                <div class="card-metrics">
                    <div class="metric">
                        <div class="metric-value blue">0%</div>
                        <div class="metric-label">Uptime</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value green">0</div>
                        <div class="metric-label">Servers Online</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value orange">0</div>
                        <div class="metric-label">Alert</div>
                    </div>
                </div>
                <div class="card-actions">
                    <a href="#" class="action-btn primary">System Health</a>
                    <a href="#" class="action-btn secondary">View Alerts</a>
                </div>
            </div>
            <!--Security Status-->
            
            <div class="overview-card">
                <div class="card-header-modern">
                    <div class="card-icon-modern blue">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="card-info">
                        <h3 class="card-title">Security Status</h3>
                        <p class="card-subtitle">System security monitoring</p>
                    </div>
                </div>
                <div class="card-metrics">
                    <div class="metric">
                        <div class="metric-value purple">0</div>
                        <div class="metric-label">Active Users</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value green">0</div>
                        <div class="metric-label">Failed Logins</div> 
                    </div>
                    <div class="metric">
                        <div class="metric-value red">0</div>
                        <div class="metric-label">Breaches</div>
                    </div>
                </div>
                <div class="card-actions">
                    <a href="#" class="action-btn warning">Security Audit</a>
                    <a href="#" class="action-btn danger">Incident Response</a>
                </div>
            </div>

        </div>
        </main>
        </div>
        <script>
        // Simple navigation functionality - removed preventDefault to allow page navigation
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                // Allow navigation to proceed - don't prevent default
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Logout functionality
        function handleLogout() {
            if(confirm('Are you sure you want to logout?')) {
                alert('Logged out (demo)');
            }
        }
    </script>
    </body>
</html>