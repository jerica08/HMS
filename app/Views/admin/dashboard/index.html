<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width", initial-scale="1.0">
        <title>Admin Dashboard</title>
        <link rel="stylesheet" href=<?= base_url('assets/css/dashboard-common.css') ?>>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>
    <body class="admin">
        <!--Header-->
        <header class="header">
            <div class="header-content">
                <div class="logo">
                    <h1><i class="fas fa-hospital"></i> Administrator</h1>                    
                </div>
                <div class="user-info">
                    <div href="" class="fas fa-avatar" href=""></div>
                    <div>
                        <div style="font-weight: 600;">
                            <?= \App\Helpers\UserHelper::getDisplayName($currentUser ?? null) ?>
                        </div>
                        <div style="font-size: 0.9rem;opacity:0.8">
                            <?= \App\Helpers\UserHelper::getDisplayRole($currentUser ?? null) ?>
                        </div>
                    </div>
                    <a class="btn btn-secondary" href="<?= base_url('profile') ?>" style="margin-left:.5rem;">
                        <i class="fas fa-user"></i> Profile
                    </a>
                    <button class="logout-btn" onclick="handleLogout()">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </button>
                </div>
            </div>
        </header>
        <div class="main-container">
            <!--sidebar-->
            <nav class="sidebar">
              
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="<?= base_url('admin/dashboard') ?>" class="nav-link active">
                            <i class="fas fa-tachometer-alt nav-icon"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('admin/staff') ?>" class="nav-link">
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
            <!--main content-->
            <main class="content">
                <h1 class="page-title"> Administrator Dashboard</h1>

                <!--Dashboard overview cards-->
                <div class="dashboard-overview">
                    <!-- User Management Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="card-info">
                                <h3 class="card-title-modern">User Management</h3>
                                <p class="card-subtitle">Manage all system users and roles</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue" id="totalUsers"><?= $userStats['total_users'] ?? 0 ?></div>
                                <div class="metric-label">Total Users</div>
                            </div>
                            <div class="metric">
                            <div class="metric-value blue" id="activeRoles"><?= $userStats['active_users'] ?? 0 ?></div>
                                <div class="metric-label">Active Users</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value blue" id="pendingUsers"><?= $userStats['inactive_users'] ?? 0 ?></div>
                                <div class="metric-label">Inactive Users</div>
                            </div>
                        </div>
                        <div class="card-actions">
                            <button class="action-btn primary" onclick="window.location.href='<?= base_url('admin/users') ?>'">Manage Users</button>
                            <button class="action-btn secondary" onclick="window.location.href='<?= base_url('admin/roles') ?>'">Manage Roles</button>
                        </div>
                    </div>

                    <!-- System Analytics Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern purple">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="card-info">
                                <h3 class="card-title-modern">System Analytics</h3>
                                <p class="card-subtitle">Comprehensive system reports</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value purple" id="total-patients"><?= $patientStats['total_patients'] ?? 0 ?></div>
                                <div class="metric-label">Patients</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value purple" id="todays-visits"><?= $patientStats['registrations_today'] ?? 0 ?></div>
                                <div class="metric-label">Today's Visits</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value purple">₱0</div>
                                <div class="metric-label">Revenue</div>
                            </div>
                        </div>
                        <div class="card-actions">
                            <button class="action-btn primary" onclick="window.location.href='<?= base_url('admin/analytics') ?>'">View Reports</button>
                            <button class="action-btn secondary">Export Data</button>
                        </div>
                    </div>
                </div>

                <!--Recent activity table-->
                <div class="table-container">
                    <h3 style="margin-bottom: 1.5rem;"> Recent System Activities</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Module</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>08:45 AM</td>
                                <td>Dr. GAGNI</td>
                                <td>Patient Record Updated</td>
                                <td>EHR System</td>
                                <td><span class="badge badge-success">Success</span></td>
                                <td><a href="#" class="btn btn-secondary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">View</a></td>
                            </tr>
                            <tr>
                                <td>08:42 AM</td>
                                <td>Nurse Xyril</td>
                                <td>Medication Administered</td>
                                <td>Patient Care</td>
                                <td><span class="badge badge-success">Success</span></td>
                                <td><a href="#" class="btn btn-secondary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">View</a></td>
                            </tr>
                            <tr>
                                <td>08:38 AM</td>
                                <td>Lab Tech</td>
                                <td>Test Results Uploaded</td>
                                <td>Laboratory</td>
                                <td><span class="badge badge-success">Success</span></td>
                                <td><a href="#" class="btn btn-secondary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">View</a></td>
                            </tr>
                            <tr>
                                <td>08:35 AM</td>
                                <td>System</td>
                                <td>Branch Sync Failed</td>
                                <td>Integration</td>
                                <td><span class="badge badge-danger">Failed</span></td>
                                <td><a href="#" class="btn btn-warning" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Retry</a></td>
                            </tr>
                            <tr>
                                <td>08:30 AM</td>
                                <td>Pharmacist</td>
                                <td>Inventory Updated</td>
                                <td>Pharmacy</td>
                                <td><span class="badge badge-success">Success</span></td>
                                <td><a href="#" class="btn btn-secondary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">View</a></td>
                            </tr>             
                        </tbody>
                    </table>
                </div>    
            </main>
    </div>
    <script src="/js/session-manager.js"></script>
    <script src="/js/utils.js"></script>
    <script src="/js/admin-dashboard.js"></script>
    <script src="/js/index.js"></script>
    <script src="/js/logout.js"></script>
</body>
</html>