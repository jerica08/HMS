<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= base_url() ?>">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta name="user-role" content="<?= esc($userRole ?? 'admin') ?>">
    <title><?= esc($title ?? 'Patient Records') ?> - HMS</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/unified/patient-management.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<?php include APPPATH . 'Views/template/header.php'; ?>
<div class="main-container">
    <?php include APPPATH . 'Views/unified/components/sidebar.php'; ?>
    <main class="content" role="main">
        <header class="page-header">
            <div>
                <h1 class="page-title">
                    <i class="fas fa-file-medical-alt"></i>
                    <?= esc($title ?? 'Patient Records') ?>
                </h1>
                <p class="page-subtitle">Browse patient dossiers and quickly access medical summaries.</p>
            </div>
            <div class="page-actions">
                <button type="button" class="btn btn-secondary" aria-label="Search Records">
                    <i class="fas fa-search"></i> Search
                </button>
                <button type="button" class="btn btn-primary" aria-label="Add Patient Record">
                    <i class="fas fa-plus"></i> Add Record
                </button>
            </div>
        </header>

        <section class="dashboard-overview" role="region" aria-label="dashboard overview cards">
            <article class="overview-card" tabindex="0">
                <div class="card-header-modern">
                    <div class="card-icon-modern blue"><i class="fas fa-user-injured"></i></div>
                    <div class="card-info">
                        <h3 class="card-title-modern">Total Files</h3>
                        <p class="card-subtitle">All patient dossiers</p>
                    </div>
                </div>
                <div class="card-metrics">
                    <div class="metric">
                        <div class="metric-value blue"><?= $patientStats['total_patients'] ?? '---' ?></div>
                    </div>
                </div>
            </article>
            <article class="overview-card" tabindex="0">
                <div class="card-header-modern">
                    <div class="card-icon-modern purple"><i class="fas fa-heartbeat"></i></div>
                    <div class="card-info">
                        <h3 class="card-title-modern">Active Cases</h3>
                        <p class="card-subtitle">Currently admitted or under treatment</p>
                    </div>
                </div>
                <div class="card-metrics">
                    <div class="metric">
                        <div class="metric-value purple"><?= $patientStats['active_patients'] ?? '---' ?></div>
                    </div>
                </div>
            </article>
            <article class="overview-card" tabindex="0">
                <div class="card-header-modern">
                    <div class="card-icon-modern green"><i class="fas fa-hand-holding-medical"></i></div>
                    <div class="card-info">
                        <h3 class="card-title-modern">Insurance-Covered</h3>
                        <p class="card-subtitle">With valid providers listed</p>
                    </div>
                </div>
                <div class="card-metrics">
                    <div class="metric">
                        <div class="metric-value green"><?= $patientStats['patients_with_insurance'] ?? '---' ?></div>
                    </div>
                </div>
            </article>
        </section>

        <section class="record-panel">
            <div class="panel-header">
                <h2>Patient Records</h2>
                <div class="panel-controls">
                    <label class="form-label" for="filterStatus">Status</label>
                    <select id="filterStatus" class="form-select">
                        <option value="">All statuses</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                        <option value="Discharged">Discharged</option>
                    </select>
                    <label class="form-label" for="filterType">Type</label>
                    <select id="filterType" class="form-select">
                        <option value="">All types</option>
                        <option value="Outpatient">Outpatient</option>
                        <option value="Inpatient">Inpatient</option>
                        <option value="Emergency">Emergency</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table" aria-label="Patient records table">
                    <caption id="patientRecordsCaption">Latest patient records generated for the facility.</caption>
                    <thead>
                        <tr>
                            <th scope="col">Patient</th>
                            <th scope="col">Age / Sex</th>
                            <th scope="col">Type</th>
                            <th scope="col">Status</th>
                            <th scope="col">Insurance</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6" class="empty-state">
                                <i class="fas fa-file-medical" aria-hidden="true"></i>
                                <p>No patient records yet. Use the button above to create one.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
<script src="<?= base_url('assets/js/unified/patient-management.js') ?>"></script>
</body>
</html>
