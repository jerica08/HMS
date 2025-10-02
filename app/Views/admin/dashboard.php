<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - HMS</title>
    <link rel="stylesheet" href="/assets/css/common.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dashboard-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .overview-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .card-icon-modern {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        .card-icon-modern.blue { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card-icon-modern.green { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .card-icon-modern.purple { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .card-icon-modern.red { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
        .card-metrics {
            flex: 1;
        }
        .metric {
            text-align: right;
        }
        .metric-value {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.25rem;
        }
        .metric-value.blue { color: #667eea; }
        .metric-value.green { color: #f093fb; }
        .metric-value.purple { color: #4facfe; }
        .metric-value.red { color: #43e97b; }
        .metric-label {
            font-size: 0.9rem;
            color: #6b7280;
            margin: 0;
        }
        .recent-activity {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .activity-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .activity-item:last-child {
            border-bottom: none;
        }
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: #e0f2fe;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0284c7;
        }
        .activity-content {
            flex: 1;
        }
        .activity-title {
            font-weight: 500;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }
        .activity-details {
            font-size: 0.8rem;
            color: #6b7280;
        }
    </style>
</head>
<body class="admin">

    <header class="header">
        <div class="header-content">
            <div class="logo">
                <h1><i class="fas fa-hospital"></i> Administrator</h1>
            </div>
            <div class="user-info">
                <div class="fas fa-user-circle"></div>
                <div>
                    <div style="font-weight: 600;">
                        <?= \App\Helpers\UserHelper::getDisplayName($user ?? null) ?>
                    </div>
                    <div style="font-size: 0.9rem;opacity:0.8">
                        <?= \App\Helpers\UserHelper::getDisplayRole($user ?? null) ?>
                    </div>
                </div>
                <button class="logout-btn" onclick="handleLogout()">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </button>
            </div>
        </div>
    </header>

    <div class="main-container">
        <nav class="sidebar">
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="<?= base_url('admin/dashboard') ?>" class="nav-link active">
                        <i class="fas fa-tachometer-alt nav-icon"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('admin/staff-management') ?>" class="nav-link">
                        <i class="fas fa-user-tie nav-icon"></i>
                        Staff Management
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('admin/users') ?>" class="nav-link">
                        <i class="fas fa-users nav-icon"></i>
                        User Management
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('admin/patient') ?>" class="nav-link">
                        <i class="fas fa-user-injured nav-icon"></i>
                        Patient Management
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('admin/resource') ?>" class="nav-link">
                        <i class="fas fa-hospital nav-icon"></i>
                        Resource Management
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('admin/financial') ?>" class="nav-link">
                        <i class="fas fa-dollar-sign nav-icon"></i>
                        Financial Management
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('admin/communication') ?>" class="nav-link">
                        <i class="fas fa-comments nav-icon"></i>
                        Communication
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('admin/analytics') ?>" class="nav-link">
                        <i class="fas fa-chart-bar nav-icon"></i>
                        Analytics & Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('admin/systemSettings') ?>" class="nav-link">
                        <i class="fas fa-cogs nav-icon"></i>
                        System Settings
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('admin/securityAccess') ?>" class="nav-link">
                        <i class="fas fa-shield-alt nav-icon"></i>
                        Security & Access
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('admin/auditLogs') ?>" class="nav-link">
                        <i class="fas fa-clipboard-list nav-icon"></i>
                        Audit Logs
                    </a>
                </li>
            </ul>
        </nav>

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
                            <div class="metric-value blue">150</div>
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
                            <div class="metric-value green">25</div>
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

    <script>
        function handleLogout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '<?= base_url('/admin/logout') ?>';
            }
        }
    </script>
</body>
</html>
