<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Prescription Management - HMS Doctor</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('assets/css/doctor/prescriptions.css')?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <meta name="base-url" content="<?= base_url() ?>">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
</head>
<body class="doctor">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <?php include APPPATH . 'Views/doctor/components/sidebar.php'; ?>

        <main class="content">
            <h1 class="page-title">Prescriptions</h1>
            <div class="page-actions">
                <button class="btn btn-success" id="addPrescriptionBtn">
                    <i class="fas fa-plus"></i> New Prescription
                </button>
            </div><br>

            <div class="dashboard-overview">
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue"><i class="fas fa-prescription-bottle"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Today's Prescriptions</h3>
                            <p class="card-subtitle">Issues today</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric"><div class="metric-value blue"><?= $totalToday ?? 0 ?></div><div class="metric-label">Total</div></div>
                        <div class="metric"><div class="metric-value green"><?= $pending ?? 0 ?></div><div class="metric-label">Pending</div></div>
                        <div class="metric"><div class="metric-value orange"><?= $sent ?? 0 ?></div><div class="metric-label">Sent</div></div>
                    </div>
                </div>
            </div>

            <div class="patient-table">
                <div class="search-filter">
                    <h3 style="margin-bottom: 1rem;">Search Prescriptions</h3>
                    <div class="filter-row">
                        <div class="filter-group">
                            <label>Search Patient</label>
                            <input type="text" class="filter-input" placeholder="Search by patient name, medication, or prescription ID..." id="prescriptionSearch" value="">
                        </div>
                        <div class="filter-group" id="statusFilter">
                            <label>Status</label>
                            <select class="filter-input" id="conditionsFilter">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="filter-group" id="dateFilter">
                            <label>Date</label>
                            <select class="filter-input" id="roleFilter">
                                <option value="">All Dates</option>
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>&nbsp;</label>
                            <button class="btn btn-primary" onclick="applyFilters()"><i class="fas fa-search"></i> Search</button>
                        </div>
                    </div>
                </div>
                <div class="table-header">
                    <h3>Recent Prescription</h3>
                    <div style="display: flex; gap: 0.5rem;">
                        <button class="btn btn-primary btn-small" id="printBtn"><i class="fas fa-download"></i> Export</button>
                        <button class="btn btn-secondary btn-small" id="exportBtn"><i class="fas fa-sync"></i> Refresh</button>
                    </div>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Prescription ID</th>
                            <th>Patient</th>
                            <th>Medication</th>
                            <th>Dosage</th>
                            <th>Duration</th>
                            <th>Date Issued</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($prescriptions) && !empty($prescriptions)): ?>
                            <?php foreach ($prescriptions as $rx): ?>
                                <tr>
                                    <td><?= $rx['prescription_id'] ?></td>
                                    <td>
                                        <div>
                                            <strong><?= $rx['first_name'] . ' ' . $rx['last_name'] ?></strong><br>
                                            <small><?= $rx['pat_id'] ?> | Age: <?php
                                                if (!empty($rx['date_of_birth'])) {
                                                    $dob = new DateTime($rx['date_of_birth']);
                                                    $now = new DateTime();
                                                    $age = $now->diff($dob)->y;
                                                    echo $age;
                                                } else {
                                                    echo 'N/A';
                                                }
                                            ?> years</small>
                                        </div>
                                    </td>
                                    <td><?= $rx['medication'] ?></td>
                                    <td><?= $rx['dosage'] ?></td>
                                    <td><?= $rx['duration'] ?></td>
                                    <td><?= date('F j, Y', strtotime($rx['date_issued'])) ?></td>
                                    <td><span class="badge badge-<?= $rx['status'] == 'active' ? 'success' : ($rx['status'] == 'completed' ? 'info' : 'danger') ?>"><?= ucfirst($rx['status']) ?></span></td>
                                    <td>
                                        <button class="btn btn-primary view-rx-btn" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">View</button>
                                        <button class="btn btn-secondary edit-rx-btn" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Edit</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center;">No prescriptions found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <?php include APPPATH . 'Views/doctor/components/create-prescriptions-modal.php'; ?>

<script src="<?= base_url('js/doctor/prescriptions-utils.js') ?>"></script>
<script src="<?= base_url('js/doctor/new-prescription-modal.js') ?>"></script>
<script src="<?= base_url('js/doctor/view-prescriptions-modal.js') ?>"></script>
<script src="<?= base_url('js/doctor/prescriptions-management.js') ?>"></script>

</body>
</html>
