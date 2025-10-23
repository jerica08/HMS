<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= base_url() ?>">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta name="user-role" content="<?= esc($userRole ?? 'admin') ?>">
    <title><?= esc($title ?? 'Patient Management') ?> - HMS</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <?php
      // Initialize optional filter vars to avoid notices
      $search = $search ?? null;
      $statusFilter = $statusFilter ?? null;
      $typeFilter = $typeFilter ?? null;
    ?>
</head>
<?php include APPPATH . 'Views/template/header.php'; ?> 
<div class="main-container">
    <!-- Sidebar -->
    <?php if ($userRole === 'admin'): ?>
        <?= $this->include('admin/components/sidebar') ?>
    <?php elseif ($userRole === 'doctor'): ?>
        <?= $this->include('doctor/components/sidebar') ?>
    <?php elseif ($userRole === 'receptionist'): ?>
        <?= $this->include('receptionist/components/sidebar') ?>
    <?php elseif ($userRole === 'it_staff'): ?>
        <?= $this->include('IT-staff/components/sidebar') ?>
    <?php endif; ?>

    <main class="content" role="main">
        <h1 class="page-title"><?= esc($title ?? 'Patient Management') ?></h1>
        <div class="page-actions">
            <?php if (in_array('create', $permissions ?? [])): ?>
                <button type="button" class="btn btn-primary" id="addPatientBtn" aria-label="Add New Patient">
                    <i class="fas fa-plus" aria-hidden="true"></i> Add New Patient
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
            <?php if (in_array($userRole ?? '', ['admin', 'it_staff'])): ?>
                <!-- Total Patient Card -->
                <div class="overview-card" tabindex="0">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Total Patients</h3>
                            <p class="card-subtitle">All Registered Patients</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value blue"><?= $patientStats['total_patients'] ?? 0 ?></div>
                        </div>
                    </div>
                </div>

                <!-- Patient Type Card -->
                <div class="overview-card" tabindex="0">
                    <div class="card-header-modern">
                        <div class="card-icon-modern purple"><i class="fas fa-calendar-week"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Patient Types</h3>
                            <p class="card-subtitle">Active vs Emergency</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value blue"><?= $patientStats['active_patients'] ?? 0 ?></div>
                            <div class="metric-label">Active</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value green"><?= $patientStats['emergency_patients'] ?? 0 ?></div>
                            <div class="metric-label">Emergency</div>
                        </div>
                    </div>
                </div>
            <?php elseif ($userRole === 'doctor'): ?>
                <!-- My Patients Card -->
                <div class="overview-card" tabindex="0">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <div class="card-info">
                            <h3 class="card-title-modern">My Patients</h3>
                            <p class="card-subtitle">Assigned to Me</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value blue"><?= $patientStats['my_patients'] ?? 0 ?></div>
                        </div>
                    </div>
                </div>

                <!-- Patient Status Card -->
                <div class="overview-card" tabindex="0">
                    <div class="card-header-modern">
                        <div class="card-icon-modern purple"><i class="fas fa-heartbeat"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Patient Status</h3>
                            <p class="card-subtitle">Active vs Emergency</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value blue"><?= $patientStats['active_patients'] ?? 0 ?></div>
                            <div class="metric-label">Active</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value green"><?= $patientStats['emergency_patients'] ?? 0 ?></div>
                            <div class="metric-label">Emergency</div>
                        </div>
                    </div>
                </div>
            <?php elseif ($userRole === 'receptionist'): ?>
                <!-- Total Patients Card -->
                <div class="overview-card" tabindex="0">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Total Patients</h3>
                            <p class="card-subtitle">All Registered</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value blue"><?= $patientStats['total_patients'] ?? 0 ?></div>
                        </div>
                    </div>
                </div>

                <!-- New Registrations Card -->
                <div class="overview-card" tabindex="0">
                    <div class="card-header-modern">
                        <div class="card-icon-modern purple"><i class="fas fa-user-plus"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">New Registrations</h3>
                            <p class="card-subtitle">Today vs This Week</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value blue"><?= $patientStats['new_patients_today'] ?? 0 ?></div>
                            <div class="metric-label">Today</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value green"><?= $patientStats['new_patients_week'] ?? 0 ?></div>
                            <div class="metric-label">This Week</div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="patient-filter" role="search" aria-label="Patient Filters">
            <!-- Filters here as before -->
        </div>

        <div class="patient-table">
            <div class="table-header">
                <h3>Patients</h3>
            </div>
            <table class="table" id="patientsTable" aria-describedby="patientsTableCaption">
                <thead>
                    <tr>
                        <th scope="col">Patient</th>
                        <th scope="col">Type</th>
                        <?php if (in_array($userRole ?? '', ['admin', 'receptionist', 'it_staff'])): ?>
                            <th scope="col">Assigned Doctor</th>
                        <?php endif; ?>
                        <th scope="col">Status</th>
                        <th scope="col">Registered</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody id="patientsTableBody">
                    <tr>
                        <td colspan="<?= in_array($userRole ?? '', ['admin', 'receptionist', 'it_staff']) ? '6' : '5' ?>" style="text-align: center; padding: 2rem;">
                            <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #ccc; margin-bottom: 1rem;" aria-hidden="true"></i>
                            <p>Loading patients...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Modals -->
<?= $this->include('unified/modals/add-patient-modal') ?>
<?= $this->include('unified/modals/view-patient-modal') ?>
<?= $this->include('unified/modals/edit-patient-modal') ?>
<?php if (in_array($userRole ?? '', ['admin', 'receptionist', 'it_staff'])): ?>
    <?= $this->include('unified/modals/assign-doctor-modal') ?>
<?php endif; ?>

<!-- Patient Management Scripts -->
<script>
function dismissFlash() {
    const flashNotice = document.getElementById('flashNotice');
    if (flashNotice) {
        flashNotice.style.display = 'none';
    }
}
</script>
<script src="<?= base_url('assets/js/unified/patient-utils.js') ?>"></script>
<script src="<?= base_url('assets/js/unified/modals/add-patient-modal.js') ?>"></script>
<script src="<?= base_url('assets/js/unified/modals/view-patient-modal.js') ?>"></script>
<script src="<?= base_url('assets/js/unified/modals/edit-patient-modal.js') ?>"></script>
<?php if (in_array($userRole ?? '', ['admin', 'receptionist', 'it_staff'])): ?>
    <script src="<?= base_url('assets/js/unified/modals/assign-doctor-modal.js') ?>"></script>
<?php endif; ?>
<script src="<?= base_url('assets/js/unified/patient-management.js') ?>"></script>
</body>
</html>
