<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Doctor Dashboard</title>
        <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">      
    </head>
    <body class="doctor">
        
        <?php include APPPATH . 'Views/template/header.php'; ?>

        <div class="main-container">
            <!--sidebar-->
            <?php include APPPATH . 'Views/doctor/components/sidebar.php'; ?>
           
        <!--main content-->
        <main class="content">
            <h1 class="page-title">Dashboard</h1>

            <!--Dashboard overview cards-->
            <div class="dashboard-overview">
                <!-- Today's Appointments Card -->
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Today's Appointments</h3>
                            <p class="card-subtitle">Manage your daily schedule</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value blue"><?= $scheduledToday ?? 0 ?></div>
                            <div class="metric-label">Scheduled</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value green"><?= $completedToday ?? 0 ?></div>
                            <div class="metric-label">Completed</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value orange"><?= $pendingToday ?? 0 ?></div>
                            <div class="metric-label">Pending</div>
                        </div>
                    </div>
                    <div class="card-actions">
                        <a href="<?= base_url('doctor/appointments') ?>" class="action-btn primary">View Schedule</a>
                        <button class="action-btn secondary" onclick="alert('Add Appointment functionality not implemented yet')">Add Appointment</button>
                    </div>
                </div>

                <!-- Patient Management Card -->
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern purple">
                            <i class="fas fa-user-injured"></i>
                        </div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Patient Management</h3>
                            <p class="card-subtitle">Monitor patient care</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value purple">0</div>
                            <div class="metric-label">Total Patients</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value purple">0</div>
                            <div class="metric-label">New This Week</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value red">0</div>
                            <div class="metric-label">Critical</div>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="action-btn primary">View Patients</button>
                        <button class="action-btn secondary">Add Patient</button>
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
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '<?= base_url('auth/logout') ?>';
            }
        }
    </script>
    <script src="<?= base_url('js/logout.js') ?>"></script>
        
    </body>
</html>