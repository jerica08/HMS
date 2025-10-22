<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= $title ?> - HMS <?= ucfirst($userRole) ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('assets/css/unified/appointments.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <meta name="base-url" content="<?= base_url() ?>">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta name="user-role" content="<?= $userRole ?>">
</head>
<body class="<?= $userRole ?>">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <?php 
        // Role-based sidebar inclusion
        switch ($userRole) {
            case 'admin':
                include APPPATH . 'Views/admin/components/sidebar.php';
                break;
            case 'doctor':
                include APPPATH . 'Views/doctor/components/sidebar.php';
                break;
            case 'nurse':
                include APPPATH . 'Views/nurse/components/sidebar.php';
                break;
            case 'receptionist':
                include APPPATH . 'Views/receptionist/components/sidebar.php';
                break;
        }
        ?>

        <main class="content">
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-calendar-alt"></i>
                    <?php 
                    switch ($userRole) {
                        case 'admin':
                            echo 'Appointment Management';
                            break;
                        case 'doctor':
                            echo 'My Appointments';
                            break;
                        case 'nurse':
                            echo 'Department Appointments';
                            break;
                        case 'receptionist':
                            echo 'Appointment Booking';
                            break;
                        default:
                            echo 'Appointments';
                    }
                    ?>
                </h1>
                
                <?php if ($permissions['canCreate']): ?>
                <div class="page-actions">
                    <button class="btn btn-success" id="scheduleAppointmentBtn">
                        <i class="fas fa-plus"></i> 
                        <?= $userRole === 'receptionist' ? 'Book Appointment' : 'Schedule Appointment' ?>
                    </button>
                </div>
                <?php endif; ?>
            </div>

            <!-- Statistics Overview -->
            <div class="dashboard-overview">
                <!-- Today's Appointments Card -->
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue"><i class="fas fa-calendar-day"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">
                                <?= $userRole === 'admin' ? "Today's All Appointments" : "Today's Appointments" ?>
                            </h3>
                            <p class="card-subtitle"><?= date('F j, Y') ?></p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value blue"><?= $appointmentStats['today_appointments'] ?? 0 ?></div>
                            <div class="metric-label">Total</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value green"><?= $appointmentStats['completed_appointments'] ?? 0 ?></div>
                            <div class="metric-label">Completed</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value orange"><?= $appointmentStats['scheduled_appointments'] ?? 0 ?></div>
                            <div class="metric-label">Scheduled</div>
                        </div>
                    </div>
                </div>

                <!-- Total Appointments Card -->
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern purple"><i class="fas fa-chart-bar"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">
                                <?= $userRole === 'admin' ? 'System Overview' : 'My Statistics' ?>
                            </h3>
                            <p class="card-subtitle">Overall statistics</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value purple"><?= $appointmentStats['total_appointments'] ?? 0 ?></div>
                            <div class="metric-label">Total</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value red"><?= $appointmentStats['cancelled_appointments'] ?? 0 ?></div>
                            <div class="metric-label">Cancelled</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value blue"><?= count($appointments ?? []) ?></div>
                            <div class="metric-label">Active</div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Card -->
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern green"><i class="fas fa-clock"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Quick Actions</h3>
                            <p class="card-subtitle">Current time: <?= date('h:i A') ?></p>
                        </div>
                    </div>
                    <div class="card-actions">
                        <?php if ($permissions['canCreate']): ?>
                            <button class="btn btn-primary btn-sm" onclick="document.getElementById('scheduleAppointmentBtn').click()">
                                <i class="fas fa-plus"></i> New
                            </button>
                        <?php endif; ?>
                        <button class="btn btn-secondary btn-sm" id="refreshBtn">
                            <i class="fas fa-sync"></i> Refresh
                        </button>
                        <?php if ($userRole === 'admin'): ?>
                            <button class="btn btn-info btn-sm" id="exportBtn">
                                <i class="fas fa-download"></i> Export
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Appointment Management Table -->
            <div class="appointment-table-container">
                <div class="table-controls">
                    <div class="search-filters">
                        <h3>Filter Appointments</h3>
                        <div class="filter-row">
                            <div class="filter-group">
                                <label>Date:</label>
                                <input type="date" class="filter-input" id="dateSelector" value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="filter-group">
                                <label>Status:</label>
                                <select class="filter-input" id="statusFilter">
                                    <option value="">All Status</option>
                                    <option value="scheduled">Scheduled</option>
                                    <option value="in-progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="no-show">No Show</option>
                                </select>
                            </div>
                            <?php if ($userRole === 'admin' || $userRole === 'receptionist'): ?>
                            <div class="filter-group">
                                <label>Doctor:</label>
                                <select class="filter-input" id="doctorFilter">
                                    <option value="">All Doctors</option>
                                    <!-- Populated via JavaScript -->
                                </select>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="view-controls">
                        <div class="btn-group" role="group">
                            <button class="btn btn-outline-primary active" data-view="today">Today</button>
                            <button class="btn btn-outline-primary" data-view="week">Week</button>
                            <button class="btn btn-outline-primary" data-view="month">Month</button>
                        </div>
                    </div>
                </div>

                <div class="table-header">
                    <h3 id="scheduleTitle">Today's Schedule - <?= date('F j, Y') ?></h3>
                    <div class="table-actions">
                        <?php if ($userRole === 'admin'): ?>
                            <button class="btn btn-info btn-sm" id="printBtn">
                                <i class="fas fa-print"></i> Print
                            </button>
                        <?php endif; ?>
                        <button class="btn btn-secondary btn-sm" id="refreshTableBtn">
                            <i class="fas fa-sync"></i> Refresh
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Patient</th>
                                <?php if ($userRole === 'admin' || $userRole === 'receptionist'): ?>
                                    <th>Doctor</th>
                                <?php endif; ?>
                                <th>Type</th>
                                <th>Reason</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="appointmentsTableBody">
                            <!-- Populated via JavaScript -->
                            <tr>
                                <td colspan="<?= ($userRole === 'admin' || $userRole === 'receptionist') ? '8' : '7' ?>" class="text-center">
                                    <div class="loading-state">
                                        <i class="fas fa-spinner fa-spin"></i>
                                        Loading appointments...
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modals -->
    <?php // TODO: Create unified modal components ?>
    <?php include APPPATH . 'Views/unified/components/new-appointment-modal.php'; ?>
    <?php include APPPATH . 'Views/unified/components/view-appointment-modal.php'; ?>

    <!-- Scripts -->
    <script src="<?= base_url('assets/js/unified/appointment-utils.js') ?>"></script>
    <script src="<?= base_url('assets/js/unified/appointment-management.js') ?>"></script>
</body>
</html>