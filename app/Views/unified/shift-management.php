<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= base_url() ?>">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta name="csrf-hash" content="<?= csrf_hash() ?>">
    <meta name="user-role" content="<?= esc($userRole) ?>">
    <title><?= esc($title ?? 'Shift Management') ?> - HMS</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
     <link rel="stylesheet" href="<?= base_url('assets/css/unified/shift-management.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>

<?php include APPPATH . 'Views/template/header.php'; ?> 
<div class="main-container">
    <!-- Unified Sidebar -->
     <?php include APPPATH . 'Views/unified/components/sidebar.php'; ?>

    <main class="content" role="main">
        <h1 class="page-title">
            <i class="fas fa-calendar-alt"></i>
            <?= esc($title ?? 'Shift Management') ?>
        </h1>
        <div class="page-actions">
            <?php if ($permissions['canCreate'] ?? false): ?>
                <button type="button" class="btn btn-primary" id="createShiftBtn" aria-label="Create New Shift">
                    <i class="fas fa-plus" aria-hidden="true"></i> Add Shift
                </button>
            <?php endif; ?>
            <?php if (in_array($userRole ?? '', ['admin', 'it_staff'])): ?>
                <button type="button" class="btn btn-secondary" id="exportBtn" aria-label="Export Data">
                    <i class="fas fa-download" aria-hidden="true"></i> Export
                </button>
            <?php endif; ?>
        </div>

        <?php if (session()->getFlashdata('success') || session()->getFlashdata('error')): ?>
            <div id="flashNotice" role="alert" aria-live="polite" style="
                margin-top: 1rem; padding: 0.75rem 1rem; border-radius: 8px;
                border: 1px solid <?= session()->getFlashdata('success') ? '#86efac' : '#fecaca' ?>;
                background: <?= session()->getFlashdata('success') ? '#dcfce7' : '#fee2e2' ?>;
                color: <?= session()->getFlashdata('success') ? '#166534' : '#991b1b' ?>; display:flex; align-items:center; gap:0.5rem;">
                <i class="fas <?= session()->getFlashdata('success') ? 'fa-check-circle' : 'fa-exclamation-triangle' ?>" aria-hidden="true"></i>
                <span>
                    <?= esc(session()->getFlashdata('success') ?: session()->getFlashdata('error')) ?>
                </span>
                <button type="button" onclick="dismissFlash()" aria-label="Dismiss notification" style="margin-left:auto; background:transparent; border:none; cursor:pointer; color:inherit;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        <?php endif; ?>

        <?php $errors = session()->get('errors'); ?>
        <?php if (!empty($errors) && is_array($errors)): ?>
            <div role="alert" aria-live="polite" style="margin-top:0.75rem; padding:0.75rem 1rem; border-radius:8px; border:1px solid #fecaca; background:#fee2e2; color:#991b1b;">
                <div style="font-weight:600; margin-bottom:0.25rem;"><i class="fas fa-exclamation-circle"></i> Please fix the following errors:</div>
                <ul style="margin:0; padding-left:1.25rem;">
                    <?php foreach ($errors as $field => $msg): ?>
                        <li><?= esc(is_array($msg) ? implode(', ', $msg) : $msg) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <br />

        <div class="dashboard-overview" role="region" aria-label="Dashboard Overview Cards">
                <?php if ($userRole === 'admin' || $userRole === 'it_staff'): ?>
                    <!-- Total Shifts Card -->
                    <div class="overview-card" tabindex="0">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-calendar-alt"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Total Shifts</h3>
                                <p class="card-subtitle">All scheduled shifts</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $stats['total_shifts'] ?? 0 ?></div>
                                <div class="metric-label">Total</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value green"><?= $stats['scheduled_shifts'] ?? 0 ?></div>
                                <div class="metric-label">Scheduled</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Today's Shifts Card -->
                    <div class="overview-card" tabindex="0">
                        <div class="card-header-modern">
                            <div class="card-icon-modern orange"><i class="fas fa-calendar-day"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Today's Shifts</h3>
                                <p class="card-subtitle"><?= date('F j, Y') ?></p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value orange"><?= $stats['today_shifts'] ?? 0 ?></div>
                                <div class="metric-label">Today</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value purple"><?= $stats['active_doctors'] ?? 0 ?></div>
                                <div class="metric-label">Active Doctors</div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($userRole === 'doctor'): ?>
                    <!-- My Shifts Card -->
                    <div class="overview-card" tabindex="0">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-user-clock"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">My Shifts</h3>
                                <p class="card-subtitle">Personal schedule</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $stats['my_shifts'] ?? 0 ?></div>
                                <div class="metric-label">Total</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value orange"><?= $stats['today_shifts'] ?? 0 ?></div>
                                <div class="metric-label">Today</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Weekly Overview Card -->
                    <div class="overview-card" tabindex="0">
                        <div class="card-header-modern">
                            <div class="card-icon-modern green"><i class="fas fa-calendar-week"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Weekly Overview</h3>
                                <p class="card-subtitle">This week's schedule</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value green"><?= $stats['week_shifts'] ?? 0 ?></div>
                                <div class="metric-label">This Week</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value purple"><?= $stats['upcoming_shifts'] ?? 0 ?></div>
                                <div class="metric-label">Upcoming</div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($userRole === 'nurse'): ?>
                    <!-- Department Shifts Card -->
                    <div class="overview-card" tabindex="0">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-hospital"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Department Shifts</h3>
                                <p class="card-subtitle"><?= esc($stats['department'] ?? 'Your department') ?></p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $stats['department_shifts'] ?? 0 ?></div>
                                <div class="metric-label">Total</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value orange"><?= $stats['today_shifts'] ?? 0 ?></div>
                                <div class="metric-label">Today</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Schedule Status Card -->
                    <div class="overview-card" tabindex="0">
                        <div class="card-header-modern">
                            <div class="card-icon-modern green"><i class="fas fa-chart-line"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Schedule Status</h3>
                                <p class="card-subtitle">Current status</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value green"><?= $stats['scheduled_shifts'] ?? 0 ?></div>
                                <div class="metric-label">Scheduled</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value purple"><?= $stats['department'] ? 1 : 0 ?></div>
                                <div class="metric-label">Department</div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- General Shifts Overview -->
                    <div class="overview-card" tabindex="0">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-clock"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Shifts Overview</h3>
                                <p class="card-subtitle">General statistics</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $stats['total_shifts'] ?? 0 ?></div>
                                <div class="metric-label">Total</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value orange"><?= $stats['today_shifts'] ?? 0 ?></div>
                                <div class="metric-label">Today</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Schedule Status -->
                    <div class="overview-card" tabindex="0">
                        <div class="card-header-modern">
                            <div class="card-icon-modern green"><i class="fas fa-tasks"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Schedule Status</h3>
                                <p class="card-subtitle">Current status</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value green"><?= $stats['scheduled_shifts'] ?? 0 ?></div>
                                <div class="metric-label">Scheduled</div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
        </div>

        <div class="shift-table-container">
                <div class="table-header">
                    <h3>Shifts</h3>
                </div>
                <div class="table-responsive">
                    <table class="table" id="shiftsTable" aria-describedby="shiftsTableCaption">
                        <thead>
                            <tr>
                                <th scope="col">Doctor</th>
                                <th scope="col">Date</th>
                                <th scope="col">Time</th>
                                <th scope="col">Department</th>
                                <th scope="col">Type</th>
                                <th scope="col">Status</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="shiftsTableBody">
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2rem;">
                                    <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #ccc; margin-bottom: 1rem;" aria-hidden="true"></i>
                                    <p>Loading shifts...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

    </main>
</div>

<!-- Modals (hidden by default) -->
<div style="display: none;">
    <?= $this->include('unified/modals/add-shift-modal', [
        'availableStaff' => $availableStaff ?? [],
        'departments' => $departments ?? [],
        'shiftTypes' => $shiftTypes ?? [],
        'roomsWards' => $roomsWards ?? []
    ]) ?>
    <?= $this->include('unified/modals/view-shift-modal') ?>
    <?= $this->include('unified/modals/edit-shift-modal', [
        'availableStaff' => $availableStaff ?? [],
        'departments' => $departments ?? [],
        'shiftTypes' => $shiftTypes ?? [],
        'roomsWards' => $roomsWards ?? []
    ]) ?>
</div>

<!-- Shift Management Scripts -->
<script>
// Pass initial data to JavaScript
window.initialShifts = <?= json_encode($shifts ?? []) ?>;
window.userRole = <?= json_encode($userRole ?? 'admin') ?>;

function dismissFlash() {
    const flashNotice = document.getElementById('flashNotice');
    if (flashNotice) {
        flashNotice.style.display = 'none';
    }
}
</script>
<script src="<?= base_url('assets/js/unified/shift-management.js') ?>"></script>
</body>
</html>
