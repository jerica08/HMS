<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nurse Dashboard</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Modern card styling for nurse dashboard */
        .statistics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stats-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .stats-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .stats-icon.patient-care { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stats-icon.vitals { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stats-icon.medication { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .stats-icon.shift { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }

        .stats-value {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.25rem;
        }

        .stats-label {
            margin: 0;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .quick-actions {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .action-btn {
            background: #667eea;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            color: white;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            transition: background 0.2s;
        }

        .action-btn:hover {
            background: #5a67d8;
            color: white;
        }

        .action-btn.secondary {
            background: #6b7280;
        }

        .action-btn.secondary:hover {
            background: #4b5563;
        }

        .error-notice {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            text-align: center;
        }

        .activity-list {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-weight: 500;
            margin: 0 0 0.25rem 0;
        }

        .activity-time {
            font-size: 0.8rem;
            color: #6b7280;
            margin: 0;
        }
    </style>
</head>
<body class="nurse">
    <?php include APPPATH . 'Views/template/header.php'; ?>
    <div class="main-container">
        <?= $this->include('nurse/components/sidebar') ?>
        <main class="content">
            <h1 class="page-title">Nurse Dashboard</h1>
            <p class="text-muted">Welcome to the Nursing Management System</p>

            <!-- Error Notice -->
            <?php if (isset($error)): ?>
                <div class="error-notice">
                    <i class="fas fa-exclamation-triangle" style="margin-right: 0.5rem;"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <button onclick="recordVitals()" class="action-btn">
                    <i class="fas fa-heartbeat"></i> Record Vitals
                </button>
                <button onclick="administerMedication()" class="action-btn">
                    <i class="fas fa-pills"></i> Administer Medication
                </button>
                <button onclick="createShiftReport()" class="action-btn secondary">
                    <i class="fas fa-clipboard-list"></i> Shift Report
                </button>
                <button onclick="viewPatients()" class="action-btn secondary">
                    <i class="fas fa-users"></i> My Patients
                </button>
            </div>

            <!-- Statistics Cards -->
            <div class="statistics-grid">
                <div class="stats-card">
                    <div class="stats-header">
                        <div class="stats-icon patient-care">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <div>
                            <div class="stats-value" style="color: #667eea;"><?php echo $statistics['assigned_patients'] ?? 0; ?></div>
                            <p class="stats-label">Assigned Patients</p>
                        </div>
                    </div>
                </div>

                <div class="stats-card">
                    <div class="stats-header">
                        <div class="stats-icon vitals">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <div>
                            <div class="stats-value" style="color: #f093fb;"><?php echo $statistics['pending_vitals'] ?? 0; ?></div>
                            <p class="stats-label">Pending Vitals</p>
                        </div>
                    </div>
                </div>

                <div class="stats-card">
                    <div class="stats-header">
                        <div class="stats-icon medication">
                            <i class="fas fa-pills"></i>
                        </div>
                        <div>
                            <div class="stats-value" style="color: #4facfe;"><?php echo $statistics['medication_due'] ?? 0; ?></div>
                            <p class="stats-label">Medication Due</p>
                        </div>
                    </div>
                </div>

                <div class="stats-card">
                    <div class="stats-header">
                        <div class="stats-icon shift">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div>
                            <div class="stats-value" style="color: #43e97b;"><?php echo $statistics['completed_tasks'] ?? 0; ?></div>
                            <p class="stats-label">Completed Tasks</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="activity-list">
                <h3 style="margin-top: 0; color: #374151;">Recent Activities</h3>
                <?php if (!empty($recent_activities)): ?>
                    <?php foreach ($recent_activities as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon" style="background: #10b981;">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="activity-content">
                                <p class="activity-title"><?php echo $activity['description']; ?></p>
                                <p class="activity-time"><?php echo date('M j, Y g:i A', strtotime($activity['timestamp'])); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="activity-item">
                        <div class="activity-icon" style="background: #6b7280;">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="activity-content">
                            <p class="activity-title">No recent activities</p>
                            <p class="activity-time">Activities will appear here once you start using the system</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // Navigation functions for quick actions
        function recordVitals() {
            window.location.href = '<?= base_url('nurse/vitals') ?>';
        }

        function administerMedication() {
            window.location.href = '<?= base_url('nurse/medication') ?>';
        }

        function createShiftReport() {
            window.location.href = '<?= base_url('nurse/shift-report') ?>';
        }

        function viewPatients() {
            window.location.href = '<?= base_url('nurse/patient') ?>';
        }

        // Logout functionality
        function handleLogout() {
            if(confirm('Are you sure you want to logout?')) {
                window.location.href = '<?= base_url('auth/logout') ?>';
            }
        }

        // Auto-refresh dashboard data every 30 seconds
        setInterval(function() {
            // Refresh statistics from server if needed
            console.log('Dashboard refreshed');
        }, 30000);
    </script>
    <script src="<?= base_url('js/logout.js') ?>"></script>
</body>
</html>
