<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= $title ?? 'Appointment Management' ?> - HMS <?= ucfirst($userRole ?? 'User') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('assets/css/doctor/appointment.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <meta name="base-url" content="<?= base_url() ?>">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta name="user-role" content="<?= $userRole ?? 'guest' ?>">
</head>
<body class="<?= $userRole ?? 'guest' ?>">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <?php include APPPATH . 'Views/unified/components/sidebar.php'; ?>

        <main class="content">
            <h1 class="page-title">
                <i class="fas fa-calendar-alt"></i>
                <?php 
                $pageTitles = [
                    'admin' => 'System Appointments',
                    'doctor' => 'My Appointments',
                    'nurse' => 'Department Appointments',
                    'receptionist' => 'Appointment Booking'
                ];
                echo $pageTitles[$userRole] ?? 'Appointments';
                ?>
            </h1>
            
            <?php if (in_array($userRole, ['admin', 'doctor', 'receptionist'])): ?>
            <div class="page-actions">
                <button class="btn btn-primary" id="scheduleAppointmentBtn">
                    <i class="fas fa-plus"></i> 
                    <?= $userRole === 'receptionist' ? 'Add Appointment' : 'Add Appointment' ?>
                </button>
            </div><br>
            <?php endif; ?>

            <!-- Statistics Overview -->
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
                            <div class="metric-value blue"><?= $appointmentStats['today_total'] ?? 0 ?></div>
                            <div class="metric-label">Total</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value green"><?= $appointmentStats['today_completed'] ?? 0 ?></div>
                            <div class="metric-label">Completed</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value orange"><?= $appointmentStats['today_pending'] ?? 0 ?></div>
                            <div class="metric-label">Pending</div>
                        </div>
                    </div>
                </div>

                <!-- This Week Card -->
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern purple"><i class="fas fa-calendar-alt"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">This Week</h3>
                            <p class="card-subtitle">Weekly overview</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value purple"><?= $appointmentStats['week_total'] ?? 0 ?></div>
                            <div class="metric-label">Scheduled</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value red"><?= $appointmentStats['week_cancelled'] ?? 0 ?></div>
                            <div class="metric-label">Cancelled</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value orange"><?= $appointmentStats['week_no_shows'] ?? 0 ?></div>
                            <div class="metric-label">No-shows</div>
                        </div>
                    </div>
                </div>

                <!-- Schedule Overview Card -->
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern green"><i class="fas fa-clock"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">
                                <?= $userRole === 'admin' ? 'System Status' : 'Today\'s Schedule' ?>
                            </h3>
                            <p class="card-subtitle">Current time: <?= date('h:i A') ?></p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value green"><?= $appointmentStats['next_appointment'] ?? 'None' ?></div>
                            <div class="metric-label">
                                <?= $userRole === 'admin' ? 'Active Doctors' : 'Next Appointment' ?>
                            </div>
                        </div>
                        <div class="metric">
                            <div class="metric-value blue"><?= $appointmentStats['hours_scheduled'] ?? 0 ?></div>
                            <div class="metric-label">
                                <?= $userRole === 'admin' ? 'Total Hours' : 'Hours Scheduled' ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Appointments Table -->
            <div class="patient-table">
                <div class="search-filters">
                    <h3 style="margin-bottom: 1rem;">Filter Options</h3>
                    <div class="filter-row">
                        <div>
                            <input type="date" class="filter-input" id="dateSelector" value="<?= date('Y-m-d') ?>">
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
                        <?php if ($userRole === 'admin'): ?>
                        <div>
                            <select class="filter-input" id="doctorFilter">
                                <option value="">All Doctors</option>
                                <?php if (!empty($doctors)): ?>
                                    <?php foreach ($doctors as $doctor): ?>
                                        <option value="<?= $doctor['staff_id'] ?>">
                                            Dr. <?= esc($doctor['first_name'] . ' ' . $doctor['last_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <?php endif; ?>
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
                            <?php if ($userRole === 'admin'): ?>
                                <th>Doctor</th>
                            <?php endif; ?>
                            <th>Type</th>
                            <th>Condition/Reason</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="appointmentsTableBody">
                        <?php if (!empty($appointments) && is_array($appointments)): ?>
                            <?php foreach ($appointments as $appointment): ?>
                                <tr>
                                    <td>
                                        <strong><?= date('g:i A', strtotime($appointment['appointment_time'] ?? '00:00:00')) ?></strong>
                                        <?php if (!empty($appointment['duration'])): ?>
                                            <br><small><?= esc($appointment['duration']) ?> min</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>
                                                <a href="<?= base_url($userRole . '/patient-management?patient_id=' . ($appointment['patient_id'] ?? '')) ?>" 
                                                   class="patient-link" style="color: #3b82f6; text-decoration: none;">
                                                    <?= esc(($appointment['patient_first_name'] ?? '') . ' ' . ($appointment['patient_last_name'] ?? '')) ?>
                                                </a>
                                            </strong><br>
                                            <small style="color: #6b7280;">
                                                ID: <?= esc($appointment['patient_id'] ?? 'N/A') ?> | 
                                                Age: <?= esc($appointment['patient_age'] ?? 'N/A') ?> |
                                                Phone: <?= esc($appointment['patient_phone'] ?? 'N/A') ?>
                                            </small>
                                        </div>
                                    </td>
                                    <?php if ($userRole === 'admin'): ?>
                                    <td>
                                        <div>
                                            <strong>Dr. <?= esc(($appointment['doctor_first_name'] ?? '') . ' ' . ($appointment['doctor_last_name'] ?? '')) ?></strong><br>
                                            <small><?= esc($appointment['doctor_department'] ?? 'N/A') ?></small>
                                        </div>
                                    </td>
                                    <?php endif; ?>
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
                                            <?php if (in_array($userRole, ['admin', 'doctor']) && strtolower($status) !== 'completed'): ?>
                                                <button class="btn btn-success" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" onclick="markCompleted(<?= esc($appointment['appointment_id'] ?? 0) ?>)">
                                                    <i class="fas fa-check"></i> Complete
                                                </button>
                                            <?php endif; ?>
                                            <?php if (in_array($userRole, ['admin', 'doctor', 'receptionist'])): ?>
                                                <button class="btn btn-warning" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" onclick="editAppointment(<?= esc($appointment['appointment_id'] ?? 0) ?>)">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($userRole === 'admin'): ?>
                                                <button class="btn btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" onclick="deleteAppointment(<?= esc($appointment['appointment_id'] ?? 0) ?>)">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= $userRole === 'admin' ? '8' : '7' ?>" style="text-align: center; padding: 2rem; color: #6b7280;">
                                    <i class="fas fa-calendar-times" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                                    <p>No appointments found for the selected criteria.</p>
                                    <?php if (in_array($userRole, ['admin', 'doctor', 'receptionist'])): ?>
                                        <button class="btn btn-primary" onclick="document.getElementById('scheduleAppointmentBtn').click()">
                                            <i class="fas fa-plus"></i> Schedule New Appointment
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

  
    <?php include APPPATH . 'Views/unified/modals/new-appointment-modal.php'; ?>
    <?php include APPPATH . 'Views/unified/modals/view-appointment-modal.php'; ?>

</body>
</html>
