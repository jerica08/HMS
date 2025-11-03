<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Dashboard' ?> - HMS <?= ucfirst($userRole ?? 'User') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/unified/dashboard.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="base-url" content="<?= base_url() ?>">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta name="user-role" content="<?= $userRole ?? 'guest' ?>">
</head>
<body class="<?= $userRole ?? 'guest' ?>">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
         <?php include APPPATH . 'Views/unified/components/sidebar.php'; ?>

        <main class="content">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-tachometer-alt"></i>
                    <?php 
                    $dashboardTitles = [
                        'admin' => 'System Dashboard',
                        'doctor' => 'Doctor Dashboard',
                        'nurse' => 'Nursing Dashboard',
                        'receptionist' => 'Reception Dashboard',
                        'accountant' => 'Financial Dashboard',
                        'it_staff' => 'IT Dashboard',
                        'laboratorist' => 'Laboratory Dashboard',
                        'pharmacist' => 'Pharmacy Dashboard'
                    ];
                    echo $dashboardTitles[$userRole] ?? 'Dashboard';
                    ?>
                </h1>
                <div class="page-actions">
                    <div class="weather-widget" id="weatherWidget">
                    </div>
                </div>
            </div>

            <!-- Statistics Overview -->
            <div class="dashboard-overview">
                <?php if ($userRole === 'admin'): ?>
                    <!-- Admin Statistics -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-users"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Total Patients</h3>
                                <p class="card-subtitle">System-wide</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $dashboardStats['total_patients'] ?? 0 ?></div>
                                <div class="metric-label">Registered</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value green"><?= $dashboardStats['active_patients'] ?? 0 ?></div>
                                <div class="metric-label">Active</div>
                            </div>
                        </div>
                        <div class="card-actions">
                            <a href="<?= base_url('admin/patient-management') ?>" class="action-btn">Manage</a>
                        </div>
                    </div>

                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern green"><i class="fas fa-user-md"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Medical Staff</h3>
                                <p class="card-subtitle">Healthcare providers</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value green"><?= $dashboardStats['total_doctors'] ?? 0 ?></div>
                                <div class="metric-label">Doctors</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value purple"><?= $dashboardStats['total_staff'] ?? 0 ?></div>
                                <div class="metric-label">Total Staff</div>
                            </div>
                        </div>
                        <div class="card-actions">
                            <a href="<?= base_url('admin/staff-management') ?>" class="action-btn">Manage</a>
                        </div>
                    </div>

                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern orange"><i class="fas fa-calendar-alt"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Appointments</h3>
                                <p class="card-subtitle">Today's schedule</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value orange"><?= $dashboardStats['today_appointments'] ?? 0 ?></div>
                                <div class="metric-label">Today</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value blue"><?= $dashboardStats['pending_appointments'] ?? 0 ?></div>
                                <div class="metric-label">Pending</div>
                            </div>
                        </div>
                        <div class="card-actions">
                            <a href="<?= base_url('admin/appointments') ?>" class="action-btn">View</a>
                        </div>
                    </div>

                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern red"><i class="fas fa-chart-line"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">System Health</h3>
                                <p class="card-subtitle">Performance metrics</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value red"><?= $dashboardStats['total_users'] ?? 0 ?></div>
                                <div class="metric-label">Users</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value green"><?= $dashboardStats['completed_appointments'] ?? 0 ?></div>
                                <div class="metric-label">Completed</div>
                            </div>
                        </div>
                        <div class="card-actions">
                            <a href="<?= base_url('admin/analytics') ?>" class="action-btn">Analytics</a>
                        </div>
                    </div>

                <?php elseif ($userRole === 'doctor'): ?>
                    <!-- Doctor Statistics -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-calendar-day"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Today's Appointments</h3>
                                <p class="card-subtitle"><?= date('F j, Y') ?></p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $dashboardStats['today_appointments'] ?? 0 ?></div>
                                <div class="metric-label">Total</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value green"><?= $dashboardStats['completed_today'] ?? 0 ?></div>
                                <div class="metric-label">Completed</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value orange"><?= $dashboardStats['pending_today'] ?? 0 ?></div>
                                <div class="metric-label">Pending</div>
                            </div>
                        </div>
                        <div class="card-actions">
                            <a href="<?= base_url('doctor/appointments') ?>" class="action-btn">View Schedule</a>
                        </div>
                    </div>

                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern green"><i class="fas fa-users"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">My Patients</h3>
                                <p class="card-subtitle">Under your care</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value green"><?= $dashboardStats['my_patients'] ?? 0 ?></div>
                                <div class="metric-label">Total</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value blue"><?= $dashboardStats['new_patients_week'] ?? 0 ?></div>
                                <div class="metric-label">New This Week</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value red"><?= $dashboardStats['critical_patients'] ?? 0 ?></div>
                                <div class="metric-label">Critical</div>
                            </div>
                        </div>
                        <div class="card-actions">
                            <a href="<?= base_url('doctor/patients') ?>" class="action-btn">View Patients</a>
                        </div>
                    </div>

                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern purple"><i class="fas fa-prescription-bottle-alt"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Prescriptions</h3>
                                <p class="card-subtitle">Medication management</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value purple"><?= $dashboardStats['prescriptions_pending'] ?? 0 ?></div>
                                <div class="metric-label">Pending</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value green"><?= $dashboardStats['prescriptions_today'] ?? 0 ?></div>
                                <div class="metric-label">Today</div>
                            </div>
                        </div>
                        <div class="card-actions">
                            <a href="<?= base_url('doctor/prescriptions') ?>" class="action-btn">Manage</a>
                        </div>
                    </div>

                <?php elseif ($userRole === 'nurse'): ?>
                    <!-- Nurse Statistics -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-user-nurse"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Department Patients</h3>
                                <p class="card-subtitle">Your department</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $dashboardStats['department_patients'] ?? 0 ?></div>
                                <div class="metric-label">Total</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value red"><?= $dashboardStats['critical_patients'] ?? 0 ?></div>
                                <div class="metric-label">Critical</div>
                            </div>
                        </div>
                        <div class="card-actions">
                            <a href="<?= base_url('nurse/patients') ?>" class="action-btn">View Patients</a>
                        </div>
                    </div>

                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern green"><i class="fas fa-pills"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Medications</h3>
                                <p class="card-subtitle">Due today</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value green"><?= $dashboardStats['medications_due'] ?? 0 ?></div>
                                <div class="metric-label">Due</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value orange"><?= $dashboardStats['medications_overdue'] ?? 0 ?></div>
                                <div class="metric-label">Overdue</div>
                            </div>
                        </div>
                        <div class="card-actions">
                            <a href="<?= base_url('nurse/prescriptions') ?>" class="action-btn">Manage</a>
                        </div>
                    </div>

                <?php elseif ($userRole === 'receptionist'): ?>
                    <!-- Receptionist Statistics -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-calendar-check"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Today's Appointments</h3>
                                <p class="card-subtitle"><?= date('F j, Y') ?></p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $dashboardStats['total_appointments'] ?? 0 ?></div>
                                <div class="metric-label">Total</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value green"><?= $dashboardStats['scheduled_today'] ?? 0 ?></div>
                                <div class="metric-label">Scheduled</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value red"><?= $dashboardStats['cancelled_today'] ?? 0 ?></div>
                                <div class="metric-label">Cancelled</div>
                            </div>
                        </div>
                        <div class="card-actions">
                            <a href="<?= base_url('receptionist/appointments') ?>" class="action-btn">Manage</a>
                        </div>
                    </div>

                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern green"><i class="fas fa-user-plus"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Patient Registration</h3>
                                <p class="card-subtitle">New registrations</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value green"><?= $dashboardStats['new_patients_today'] ?? 0 ?></div>
                                <div class="metric-label">Today</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value blue"><?= $dashboardStats['total_patients'] ?? 0 ?></div>
                                <div class="metric-label">Total</div>
                            </div>
                        </div>
                        <div class="card-actions">
                            <a href="<?= base_url('receptionist/patients') ?>" class="action-btn">Register</a>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Default Statistics for other roles -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-chart-bar"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">System Overview</h3>
                                <p class="card-subtitle">General statistics</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $dashboardStats['total_patients'] ?? 0 ?></div>
                                <div class="metric-label">Patients</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value green"><?= $dashboardStats['total_appointments'] ?? 0 ?></div>
                                <div class="metric-label">Appointments</div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Main Dashboard Content -->
            <div class="dashboard-content">
                <div class="dashboard-grid">
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="<?= base_url('assets/js/unified/dashboard.js') ?>"></script>
</body>
</html>
