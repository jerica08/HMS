<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= base_url() ?>">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta name="user-role" content="<?= esc($userRole ?? 'admin') ?>">
    <title><?= esc($title ?? 'User Management') ?> - HMS</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <?php
      // Initialize optional filter vars to avoid notices
      $search = $search ?? null;
      $roleFilter = $roleFilter ?? null;
      $statusFilter = $statusFilter ?? null;
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
        <h1 class="page-title"><?= esc($title ?? 'User Management') ?></h1>
        <div class="page-actions">
            <?php if (($permissions['canCreate'] ?? false)): ?>
                <button type="button" class="btn btn-primary" id="addUserBtn" aria-label="Add New User">
                    <i class="fas fa-plus" aria-hidden="true"></i> Add New User
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
                <!-- Total Users Card -->
                <div class="overview-card" tabindex="0">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue" aria-hidden="true">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Total Users</h3>
                            <p class="card-subtitle">All Registered Users</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value blue"><?= esc($userStats['total_users'] ?? 0) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Admin Users Card -->
                <div class="overview-card" tabindex="0">
                    <div class="card-header-modern">
                        <div class="card-icon-modern purple" aria-hidden="true">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Admin Users</h3>
                            <p class="card-subtitle">System Administrators</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value purple"><?= esc($userStats['admin_users'] ?? 0) ?></div>
                        </div>
                    </div>
                </div>
            <?php elseif ($userRole === 'doctor'): ?>
                <!-- Department Users Card -->
                <div class="overview-card" tabindex="0">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue" aria-hidden="true">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Department Users</h3>
                            <p class="card-subtitle">My Department</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value blue"><?= esc($userStats['department_users'] ?? 0) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Active Users Card -->
                <div class="overview-card" tabindex="0">
                    <div class="card-header-modern">
                        <div class="card-icon-modern purple" aria-hidden="true">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Active Users</h3>
                            <p class="card-subtitle">Currently Active</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value purple"><?= esc($userStats['department_active'] ?? 0) ?></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="user-filter" role="search" aria-label="User Filters">
            <!-- Filters here as before -->
        </div>

        <div class="user-table">
            <div class="table-header">
                <h3>Users</h3>
            </div>
            <table class="table" id="usersTable" aria-describedby="usersTableCaption">
                <thead>
                    <tr>
                        <th scope="col">User</th>
                        <th scope="col">Role</th>
                        <th scope="col">Department</th>
                        <th scope="col">Status</th>
                        <th scope="col">Last Login</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem;">
                            <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #ccc; margin-bottom: 1rem;" aria-hidden="true"></i>
                            <p>Loading users...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Modals -->
<?= $this->include('unified/modals/add-user-modal') ?>
<?= $this->include('unified/modals/view-user-modal') ?>
<?= $this->include('unified/modals/edit-user-modal') ?>

<!-- User Management Scripts -->
<script>
function dismissFlash() {
    const flashNotice = document.getElementById('flashNotice');
    if (flashNotice) {
        flashNotice.style.display = 'none';
    }
}
</script>
<script src="<?= base_url('assets/js/unified/user-utils.js') ?>"></script>
<script src="<?= base_url('assets/js/unified/modals/add-user-modal.js') ?>"></script>
<script src="<?= base_url('assets/js/unified/modals/view-user-modal.js') ?>"></script>
<script src="<?= base_url('assets/js/unified/modals/edit-user-modal.js') ?>"></script>
<script src="<?= base_url('assets/js/unified/user-management.js') ?>"></script>
</body>
</html>
