<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - HMS</title>
    <link rel="stylesheet" href="/assets/css/common.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   
</head>

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <?php include APPPATH . 'Views/admin/components/sidebar.php'; ?>
        <main class="content">
            <h1 class="page-title">Dashboard</h1>
            <p class="text-muted">Welcome to the Hospital Management System</p>

            <!-- Statistics Cards -->
            <div class="dashboard-overview">
                <div class="overview-card">
                    <div class="card-icon-modern blue">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value blue"><?= esc($total_patients ?? 0) ?></div>
                            <p class="metric-label">Total Patients</p>
                        </div>
                    </div>
                </div>

                <div class="overview-card">
                    <div class="card-icon-modern green">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value green"><?= esc($total_doctors ?? 0) ?></div>
                            <p class="metric-label">Doctors</p>
                        </div>
                    </div>
                </div>

                <div class="overview-card">
                    <div class="card-icon-modern purple">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value purple">45</div>
                            <p class="metric-label">Staff Members</p>
                        </div>
                    </div>
                </div>

                <div class="overview-card">
                    <div class="card-icon-modern red">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value red">12</div>
                            <p class="metric-label">Appointments Today</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="recent-activity">
                <h2>Recent Activity</h2>
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">Login Successful</div>
                        <div class="activity-details">You have successfully logged into the admin dashboard</div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">User ID: <?= $user['user_id'] ?? 'N/A' ?></div>
                        <div class="activity-details">Staff ID: <?= $user['staff_id'] ?? 'N/A' ?> | Email: <?= $user['email'] ?? 'N/A' ?> | Role: <?= ucfirst($user['role'] ?? 'N/A') ?></div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">Login Time</div>
                        <div class="activity-details"><?= date('Y-m-d H:i:s') ?></div>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>
</html>