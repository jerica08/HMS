<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= base_url() ?>">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta name="csrf-hash" content="<?= csrf_hash() ?>">
    <meta name="user-role" content="<?= esc($userRole ?? 'admin') ?>">
    <title><?= esc($title ?? 'Room Management') ?> - HMS</title>

    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
    <!-- Reuse the same unified styling as patient management for uniformity -->
    <link rel="stylesheet" href="<?= base_url('assets/css/unified/patient-management.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />

    <?php
      // Avoid notices if not passed
      $roomStats = $roomStats ?? [];
    ?>
</head>

<?php include APPPATH . 'Views/template/header.php'; ?>

<div class="main-container">
    <!-- Unified Sidebar -->
    <?php include APPPATH . 'Views/unified/components/sidebar.php'; ?>

    <main class="content" role="main">
        <h1 class="page-title">
            <i class="fas fa-hotel"></i>
            <?= esc($title ?? 'Room Management') ?>
        </h1>

        <div class="page-actions">
            <button type="button" class="btn btn-primary" id="addRoomBtn" aria-label="Add New Room">
                <i class="fas fa-plus" aria-hidden="true"></i> Add New Room
            </button>
        </div>

        <?php if (session()->getFlashdata('success') || session()->getFlashdata('error')): ?>
            <div id="flashNotice" role="alert" aria-live="polite" style="
                margin-top: 1rem; padding: 0.75rem 1rem; border-radius: 8px;
                border: 1px solid <?= session()->getFlashdata('success') ? '#86efac' : '#fecaca' ?>;
                background: <?= session()->getFlashdata('success') ? '#dcfce7' : '#fee2e2' ?>;
                color: <?= session()->getFlashdata('success') ? '#166534' : '#991b1b' ?>;
                display:flex; align-items:center; gap:0.5rem;">
                <i class="fas <?= session()->getFlashdata('success') ? 'fa-check-circle' : 'fa-exclamation-triangle' ?>" aria-hidden="true"></i>
                <span><?= esc(session()->getFlashdata('success') ?: session()->getFlashdata('error')) ?></span>
                <button type="button" onclick="dismissFlash()" aria-label="Dismiss notification"
                        style="margin-left:auto; background:transparent; border:none; cursor:pointer; color:inherit;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        <?php endif; ?>

        <br />

        <!-- Overview cards, same style as other modules -->
        <div class="dashboard-overview" role="region" aria-label="Rooms Overview Cards">
            <div class="overview-card" tabindex="0">
                <div class="card-header-modern">
                    <div class="card-icon-modern blue">
                        <i class="fas fa-hotel"></i>
                    </div>
                    <div class="card-info">
                        <h3 class="card-title-modern">Total Rooms</h3>
                        <p class="card-subtitle">All configured rooms</p>
                    </div>
                </div>
                <div class="card-metrics">
                    <div class="metric">
                        <div class="metric-value blue">
                            <?= $roomStats['total_rooms'] ?? 0 ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overview-card" tabindex="0">
                <div class="card-header-modern">
                    <div class="card-icon-modern purple">
                        <i class="fas fa-bed"></i>
                    </div>
                    <div class="card-info">
                        <h3 class="card-title-modern">Occupancy</h3>
                        <p class="card-subtitle">Occupied vs Available</p>
                    </div>
                </div>
                <div class="card-metrics">
                    <div class="metric">
                        <div class="metric-value blue">
                            <?= $roomStats['occupied_rooms'] ?? 0 ?>
                        </div>
                        <div class="metric-label">Occupied</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value green">
                            <?= $roomStats['available_rooms'] ?? 0 ?>
                        </div>
                        <div class="metric-label">Available</div>
                    </div>
                </div>
            </div>

            <div class="overview-card" tabindex="0">
                <div class="card-header-modern">
                    <div class="card-icon-modern orange">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="card-info">
                        <h3 class="card-title-modern">Maintenance</h3>
                        <p class="card-subtitle">Rooms not usable</p>
                    </div>
                </div>
                <div class="card-metrics">
                    <div class="metric">
                        <div class="metric-value orange">
                            <?= $roomStats['maintenance_rooms'] ?? 0 ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rooms table (same visual style as patient table) -->
        <div class="patient-table-container">
            <div class="table-header">
                <h3>Rooms</h3>
            </div>
            <div class="table-responsive">
                <table class="table" id="roomsTable" aria-describedby="roomsTableCaption">
                    <thead>
                        <tr>
                            <th scope="col">Room</th>
                            <th scope="col">Type</th>
                            <th scope="col">Department</th>
                            <th scope="col">Capacity</th>
                            <th scope="col">Status</th>
                            <th scope="col">Rate / Day</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="roomsTableBody">
                        <!-- Placeholder row while you haven’t wired backend/JS yet -->
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem;">
                                <i class="fas fa-spinner fa-spin"
                                   style="font-size: 2rem; color: #ccc; margin-bottom: 1rem;"
                                   aria-hidden="true"></i>
                                <p>Loading rooms...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?= $this->include('unified/modals/add-room-modal', ['roomTypes' => $roomTypes ?? [], 'departments' => $departments ?? []]) ?>


<script>
window.roomTypeMetadata = <?= json_encode($roomTypeMetadata ?? [], JSON_HEX_TAG) ?>;
</script>

<script>
function dismissFlash() {
    const flashNotice = document.getElementById('flashNotice');
    if (flashNotice) {
        flashNotice.style.display = 'none';
    }
}
</script>

<!-- Reuse existing utility styles/behaviour if needed -->
<script src="<?= base_url('assets/js/unified/patient-utils.js') ?>"></script>
<!-- Create this file later for JS logic (open modal, AJAX, etc.) -->
<script src="<?= base_url('assets/js/unified/room-management.js') ?>"></script>

</body>
</html>