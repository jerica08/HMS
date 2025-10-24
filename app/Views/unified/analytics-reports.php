<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= esc($title) ?> - HMS</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        .analytics-container {
            padding: 2rem;
        }
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .metric-card {
            background: #fff;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #3b82f6;
        }
        .metric-number {
            font-size: 2rem;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        .metric-label {
            color: #6b7280;
            font-size: 0.875rem;
        }
        .debug-info {
            background: #f3f4f6;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body class="<?= esc($userRole) ?>">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <?php include APPPATH . 'Views/unified/components/sidebar.php'; ?>

        <main class="content">
            <div class="analytics-container">
                <h1><?= esc($title) ?></h1>
                
                <!-- Debug Information -->
                <div class="debug-info">
                    <strong>Debug Info:</strong><br>
                    User Role: <?= esc($userRole) ?><br>
                    Permissions: <?= implode(', ', $permissions) ?><br>
                    Analytics Data: <?= json_encode($analytics, JSON_PRETTY_PRINT) ?>
                </div>

                <!-- Analytics Metrics -->
                <div class="metrics-grid">
                    
                    <?php if ($userRole === 'admin' || $userRole === 'accountant' || $userRole === 'it_staff'): ?>
                        <!-- System-wide metrics -->
                        <?php if (isset($analytics['patient_analytics'])): ?>
                        <div class="metric-card">
                            <div class="metric-number"><?= $analytics['patient_analytics']['total_patients'] ?? 0 ?></div>
                            <div class="metric-label">Total Patients</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-number"><?= $analytics['patient_analytics']['new_patients'] ?? 0 ?></div>
                            <div class="metric-label">New Patients</div>
                        </div>
                        <?php endif; ?>

                        <?php if (isset($analytics['appointment_analytics'])): ?>
                        <div class="metric-card">
                            <div class="metric-number"><?= $analytics['appointment_analytics']['total_appointments'] ?? 0 ?></div>
                            <div class="metric-label">Total Appointments</div>
                        </div>
                        <?php endif; ?>

                        <?php if (isset($analytics['financial_analytics'])): ?>
                        <div class="metric-card">
                            <div class="metric-number">₱<?= number_format($analytics['financial_analytics']['total_revenue'] ?? 0, 2) ?></div>
                            <div class="metric-label">Total Revenue</div>
                        </div>
                        <?php endif; ?>

                        <?php if (isset($analytics['staff_analytics'])): ?>
                        <div class="metric-card">
                            <div class="metric-number"><?= $analytics['staff_analytics']['total_staff'] ?? 0 ?></div>
                            <div class="metric-label">Total Staff</div>
                        </div>
                        <?php endif; ?>

                    <?php elseif ($userRole === 'doctor'): ?>
                        <!-- Doctor-specific metrics -->
                        <?php if (isset($analytics['my_patients'])): ?>
                        <div class="metric-card">
                            <div class="metric-number"><?= $analytics['my_patients']['total_patients'] ?? 0 ?></div>
                            <div class="metric-label">My Patients</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-number"><?= $analytics['my_patients']['new_patients'] ?? 0 ?></div>
                            <div class="metric-label">New Patients</div>
                        </div>
                        <?php endif; ?>

                        <?php if (isset($analytics['my_appointments'])): ?>
                        <div class="metric-card">
                            <div class="metric-number"><?= $analytics['my_appointments']['total_appointments'] ?? 0 ?></div>
                            <div class="metric-label">My Appointments</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-number"><?= $analytics['my_appointments']['completion_rate'] ?? 0 ?>%</div>
                            <div class="metric-label">Completion Rate</div>
                        </div>
                        <?php endif; ?>

                        <?php if (isset($analytics['my_revenue'])): ?>
                        <div class="metric-card">
                            <div class="metric-number">₱<?= number_format($analytics['my_revenue']['total_revenue'] ?? 0, 2) ?></div>
                            <div class="metric-label">My Revenue</div>
                        </div>
                        <?php endif; ?>

                    <?php elseif ($userRole === 'nurse'): ?>
                        <!-- Nurse-specific metrics -->
                        <?php if (isset($analytics['department_patients'])): ?>
                        <div class="metric-card">
                            <div class="metric-number"><?= $analytics['department_patients']['total'] ?? 0 ?></div>
                            <div class="metric-label">Department Patients</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-number"><?= $analytics['department_patients']['active'] ?? 0 ?></div>
                            <div class="metric-label">Active Patients</div>
                        </div>
                        <?php endif; ?>

                        <?php if (isset($analytics['medication_tracking'])): ?>
                        <div class="metric-card">
                            <div class="metric-number"><?= $analytics['medication_tracking']['administered'] ?? 0 ?></div>
                            <div class="metric-label">Medications Administered</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-number"><?= $analytics['medication_tracking']['pending'] ?? 0 ?></div>
                            <div class="metric-label">Pending Medications</div>
                        </div>
                        <?php endif; ?>

                    <?php elseif ($userRole === 'receptionist'): ?>
                        <!-- Receptionist-specific metrics -->
                        <?php if (isset($analytics['registration_stats'])): ?>
                        <div class="metric-card">
                            <div class="metric-number"><?= $analytics['registration_stats']['new_registrations'] ?? 0 ?></div>
                            <div class="metric-label">New Registrations</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-number"><?= $analytics['registration_stats']['total_today'] ?? 0 ?></div>
                            <div class="metric-label">Registrations Today</div>
                        </div>
                        <?php endif; ?>

                        <?php if (isset($analytics['appointment_booking_stats'])): ?>
                        <div class="metric-card">
                            <div class="metric-number"><?= $analytics['appointment_booking_stats']['booked_today'] ?? 0 ?></div>
                            <div class="metric-label">Appointments Booked Today</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-number"><?= $analytics['appointment_booking_stats']['cancelled_today'] ?? 0 ?></div>
                            <div class="metric-label">Cancelled Today</div>
                        </div>
                        <?php endif; ?>

                    <?php endif; ?>

                </div>

                <!-- Generate Report Button -->
                <?php if (in_array('generate_reports', $permissions)): ?>
                <div style="margin-top: 2rem;">
                    <button class="btn btn-primary" onclick="generateReport()">
                        <i class="fas fa-file-alt"></i> Generate Report
                    </button>
                </div>
                <?php endif; ?>

            </div>
        </main>
    </div>

    <script>
        function generateReport() {
            alert('Report generation feature coming soon!');
        }
    </script>
</body>
</html>
