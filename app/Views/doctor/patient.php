<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="base-url" content="<?= base_url() ?>" />
    <title>Patient Management - HMS Doctor</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('assets/css/doctor/patient.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="doctor">
     <!--header-->
    <?php include APPPATH . 'Views/template/header.php'; ?>
    <div class="main-container">
        <!--sidebar-->
        <?php include APPPATH . 'Views/doctor/components/sidebar.php'; ?>

        <main class="content">
            <h1 class="page-title">My Patient</h1>
            <div class="page-actions">
                <button class="btn btn-success" id="addPatientBtn">
                    <i class="fas fa-plus"></i> Add New Patient
                </button>
            </div><br>

            <div class="dashboard-overview">
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue"><i class="fas fa-users"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Total Patients</h3>
                            <p class="card-subtitle">Under your care</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value blue"><?= $totalPatients ?? 0 ?></div>
                        </div>
                    </div>
                </div>

                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue"><i class="fas fa-calendar-week"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Patient Type</h3>
                            <p class="card-subtitle">Under your care</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value blue"><?= $inPatients ?? 0 ?></div>
                            <div class="metric-label">In-Patient</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value green"><?= $outPatients ?? 0 ?></div>
                            <div class="metric-label">Out-Patient</div>
                        </div>
                    </div>
                </div>
            </div>
             <div class="patient-view">         
                    <!-- Patient List Table -->
                    <div class="patient-table">
                        <div class="table-header">
                            <h3>Patients</h3>
                        </div>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>ID</th>
                                    <th>Age</th>
                                    <th>Patient Type</th>
                                    <th>Assigned Doctor</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="doctorPatientsBody">
                                <tr><td colspan="7" style="text-align:center; color:#6b7280; padding:1rem;">Loading patients...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
        </main>
    </div> 

 <?php include APPPATH . 'Views/doctor/components/add-patient-modal.php'; ?>
 <?php include APPPATH . 'Views/doctor/components/view-patient-modal.php'; ?>
 <?php include APPPATH . 'Views/doctor/components/edit-patient-modal.php'; ?>
        
    <!-- Patient Management Scripts -->
    <script src="<?= base_url('js/doctor/patient-utils.js') ?>"></script>
    <script src="<?= base_url('js/doctor/add-patient-modal.js') ?>"></script>
    <script src="<?= base_url('js/doctor/assign-doctor-modal.js') ?>"></script>
    <script src="<?= base_url('js/doctor/view-patient-modal.js') ?>"></script>
    <script src="<?= base_url('js/doctor/edit-patient-modal.js') ?>"></script>
    <script src="<?= base_url('js/doctor/patient-management.js') ?>"></script>
            
           
</body>
</html>
