<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Appointment Management - HMS Doctor</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
     <link rel="stylesheet" href="<?= base_url('assets/css/doctor/appointment.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <meta name="base-url" content="<?= base_url() ?>">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
</head>
<body class="doctor">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <?php include APPPATH . 'Views/doctor/components/sidebar.php'; ?>

        <main class="content">
            <h1 class="page-title">Appointments</h1>
            <div class="page-actions">
                <button class="btn btn-success" id="scheduleAppointmentBtn">
                    <i class="fas fa-plus"></i> Schedule Appointments
                </button>
            </div><br>

            <div class="dashboard-overview">
                <!-- Today's Appointments Card -->
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
                            <div class="metric-value blue"><?= $todayStats['total'] ?? 0 ?></div>
                            <div class="metric-label">Total</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value green"><?= $todayStats['completed'] ?? 0 ?></div>
                            <div class="metric-label">Completed</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value orange"><?= $todayStats['pending'] ?? 0 ?></div>
                            <div class="metric-label">Pending</div>
                        </div>
                    </div>
                </div>

                <!-- This Week Card -->
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern purple"><i class="fas fa-calendar-week"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">This Week</h3>
                            <p class="card-subtitle">Weekly overview</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value purple"><?= $weekStats['total'] ?? 0 ?></div>
                            <div class="metric-label">Scheduled</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value red"><?= $weekStats['cancelled'] ?? 0 ?></div>
                            <div class="metric-label">Cancelled</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value orange"><?= $weekStats['no_shows'] ?? 0 ?></div>
                            <div class="metric-label">No-shows</div>
                        </div>
                    </div>
                </div>

                <!-- Today's Schedule Card -->
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern green"><i class="fas fa-clock"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Today's Schedule</h3>
                            <p class="card-subtitle">Current time: <?= date('h:i A') ?></p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value green"><?= $scheduleStats['next_appointment'] ?? 'None' ?></div>
                            <div class="metric-label">Next Appointment</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value blue"><?= $scheduleStats['hours_scheduled'] ?? 0 ?></div>
                            <div class="metric-label">Hours Scheduled</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="patient-table">
                <div class="search-filters">
                    <h3 style="margin-bottom: 1rem;">View Options</h3>
                    <div class="filter-row">
                        <div class="btn-group">
                            <button class="btn btn-primary active" id="todayView">Today</button>
                            <button class="btn btn-secondary" id="weekView">Week</button>
                            <button class="btn btn-secondary" id="monthView">Month</button>
                        </div>
                        <div>
                            <input type="date" class="filter-input" id="dateSelector" value="2025-08-20">
                        </div>
                        <div>
                            <select class="filter-input" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="scheduled">Scheduled</option>
                                <option value="in-progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="no-show">No Show</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="table-header">
                    <h3 id="scheduleTitle">Today's Schedule - <?= date('F j, Y') ?></h3>
                    <div style="display: flex; gap: 0.5rem;">
                        <button class="btn btn-primary btn-small" id="printBtn">
                            <i class="fas fa-print"></i> Print Schedule
                        </button>
                        <button class="btn btn-secondary btn-small" id="refreshBtn">
                            <i class="fas fa-sync"></i> Refresh
                        </button>
                    </div>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Patient</th>
                            <th>Type</th>
                            <th>Condition/Reason</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="appointmentsTableBody">
                        <?php if (!empty($todayAppointments) && is_array($todayAppointments)): ?>
                            <?php foreach ($todayAppointments as $appointment): ?>
                                <tr>
                                    <td>
                                        <strong><?= date('g:i A', strtotime($appointment['appointment_time'] ?? '00:00:00')) ?></strong>
                                        <?php if (!empty($appointment['duration'])): ?>
                                            <br><small><?= esc($appointment['duration']) ?> min</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?= esc(($appointment['patient_first_name'] ?? '') . ' ' . ($appointment['patient_last_name'] ?? '')) ?></strong><br>
                                            <small><?= esc($appointment['patient_id'] ?? 'N/A') ?> | Age: <?= esc($appointment['patient_age'] ?? 'N/A') ?></small>
                                        </div>
                                    </td>
                                    <td><?= esc($appointment['appointment_type'] ?? 'N/A') ?></td>
                                    <td><?= esc($appointment['reason'] ?? 'General consultation') ?></td>
                                    <td><?= esc($appointment['duration'] ?? '30') ?> min</td>
                                    <td>
                                        <?php 
                                            $status = $appointment['status'] ?? 'scheduled';
                                            $badgeClass = '';
                                            switch(strtolower($status)) {
                                                case 'completed': $badgeClass = 'badge-success'; break;
                                                case 'in-progress': $badgeClass = 'badge-info'; break;
                                                case 'cancelled': $badgeClass = 'badge-danger'; break;
                                                case 'no-show': $badgeClass = 'badge-warning'; break;
                                                default: $badgeClass = 'badge-info';
                                            }
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= esc(ucfirst($status)) ?></span>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem; flex-wrap: wrap;">
                                            <button class="btn btn-primary" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" onclick="viewAppointment(<?= esc($appointment['appointment_id'] ?? 0) ?>)">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <?php if (strtolower($status) !== 'completed'): ?>
                                                <button class="btn btn-success" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" onclick="markCompleted(<?= esc($appointment['appointment_id'] ?? 0) ?>)">
                                                    <i class="fas fa-check"></i> Complete
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" onclick="deleteAppointment(<?= esc($appointment['appointment_id'] ?? 0) ?>)">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2rem; color: #6b7280;">
                                    <i class="fas fa-calendar-times" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                                    <p>No appointments scheduled for today.</p>
                                    <button class="btn btn-primary" onclick="document.getElementById('scheduleAppointmentBtn').click()">
                                        <i class="fas fa-plus"></i> Schedule New Appointment
                                    </button>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <?php include APPPATH . 'Views/doctor/components/new-appointment-modal.php'; ?>
    <?php include APPPATH . 'Views/doctor/components/view-appointment-modal.php'; ?>

    
<script src="<?= base_url('js/doctor/appointmernt-utils.js') ?>"></script>
<script src="<?= base_url('js/doctor/new-appointment-modal.js') ?>"></script>
<script src="<?= base_url('js/doctor/view-appointment-modal.js') ?>"></script>
<script src="<?= base_url('js/doctor/apppointment-management.js') ?>"></script>