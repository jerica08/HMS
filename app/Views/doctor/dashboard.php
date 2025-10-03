<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Doctor Dashboard</title>
        <link rel="stylesheet" href="assets/css/common.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">      
    </head>
    <body class="doctor">
        <!--Header-->
        <header class="header">
            <div class="header-content">
                <div class="logo">
                    <h1><i class="fas fa-user-md"></i> Doctor</h1>                    
                </div>
                <div class="user-info">
                    <div class="user-avatar">
                        <?= strtoupper(substr(\App\Helpers\UserHelper::getDisplayName($currentUser ?? null), 0, 2)) ?>
                    </div>
                    <div>
                        <div style="font-weight: 600;">
                            <?= \App\Helpers\UserHelper::getDisplayName($currentUser ?? null) ?>
                        </div>
                        <div style="font-size: 0.9rem;opacity:0.8">
                            <?= \App\Helpers\UserHelper::getDisplayRole($currentUser ?? null) ?>
                        </div>
                    </div>
                    <a class="btn btn-secondary" href="<?= base_url('profile') ?>" style="margin-left:.5rem;">
                        <i class="fas fa-user"></i> Profile
                    </a>
                    <button class="logout-btn" onclick="handleLogout()">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </button>
                </div>
            </div>
        </header>
        <div class="main-container">

            <!--sidebar-->
            <nav class="sidebar">
                <ul class="nav-menu">
                    <li class="nav-item">
                         <a href="<?= base_url('doctor/dashboard') ?>" class="nav-link active">
                            <i class="fas fa-tachometer-alt nav-icon"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                         <a href="<?= base_url('doctor/patients') ?>" class="nav-link">
                            <i class="fas fa-users nav-icon"></i>
                            My Patients
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('doctor/appointments') ?>" class="nav-link">
                            <i class="fas fa-calendar-alt nav-icon"></i>
                            Appointments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('doctor/prescriptions') ?>" class="nav-link">
                            <i class="fas fa-prescription-bottle nav-icon"></i>
                            Prescriptions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('doctor/lab-results') ?>" class="nav-link">
                            <i class="fas fa-flask nav-icon"></i>
                            Lab Results
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('doctor/ehr') ?>" class="nav-link">
                            <i class="fas fa-file-medical nav-icon"></i>
                            Electronic Health Record
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('doctor/my-schedule') ?>" class="nav-link">
                            <i class="fas fa-clock nav-icon"></i>
                            My Schedule
                        </a>
                    </li>
                </ul>      
            </nav>
        <!--main content-->
        <main class="content">
            <h1 class="page-title">Dashboard</h1>

            <!--Dashboard overview cards-->
            <div class="dashboard-overview">
                <!-- Today's Appointments Card -->
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Today's Appointments</h3>
                            <p class="card-subtitle">Manage your daily schedule</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value blue">0</div>
                            <div class="metric-label">Scheduled</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value green">0</div>
                            <div class="metric-label">Completed</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value orange">0</div>
                            <div class="metric-label">Pending</div>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="action-btn primary">View Schedule</button>
                        <button class="action-btn secondary">Add Appointment</button>
                    </div>
                </div>

                <!-- Patient Management Card -->
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern purple">
                            <i class="fas fa-user-injured"></i>
                        </div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Patient Management</h3>
                            <p class="card-subtitle">Monitor patient care</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value purple">0</div>
                            <div class="metric-label">Total Patients</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value purple">0</div>
                            <div class="metric-label">New This Week</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value red">0</div>
                            <div class="metric-label">Critical</div>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="action-btn primary">View Patients</button>
                        <button class="action-btn secondary">Add Patient</button>
                    </div>
                </div>
            </div>

            <!--Recent activity table-->
            <div class="table-container">
                <h3 style="margin-bottom: 1.5rem;">Recent Patient Activities</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Patient</th>
                            <th>Action</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>09:15 AM</td>
                            <td>Maria Santos</td>
                            <td>Consultation Completed</td>
                            <td>Appointment</td>
                            <td><span class="badge badge-success">Completed</span></td>
                            <td><a href="#" class="btn btn-secondary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">View</a></td>
                        </tr>
                        <tr>
                            <td>09:00 AM</td>
                            <td>John Doe</td>
                            <td>Prescription Updated</td>
                            <td>Medication</td>
                            <td><span class="badge badge-success">Updated</span></td>
                            <td><a href="#" class="btn btn-secondary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">View</a></td>
                        </tr>
                        <tr>
                            <td>08:45 AM</td>
                            <td>Sarah Johnson</td>
                            <td>Lab Results Reviewed</td>
                            <td>Laboratory</td>
                            <td><span class="badge badge-success">Reviewed</span></td>
                            <td><a href="#" class="btn btn-secondary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">View</a></td>
                        </tr>
                        <tr>
                            <td>08:30 AM</td>
                            <td>Michael Brown</td>
                            <td>Appointment Scheduled</td>
                            <td>Appointment</td>
                            <td><span class="badge badge-warning">Scheduled</span></td>
                            <td><a href="#" class="btn btn-secondary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">View</a></td>
                        </tr>
                        <tr>
                            <td>08:15 AM</td>
                            <td>Emma Wilson</td>
                            <td>Medical Record Updated</td>
                            <td>Record</td>
                            <td><span class="badge badge-success">Updated</span></td>
                            <td><a href="#" class="btn btn-secondary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">View</a></td>
                        </tr>             
                    </tbody>
                </table>
            </div>
     
        </main>
    </div>
     <script>
        // Simple navigation functionality - removed preventDefault to allow page navigation
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                // Allow navigation to proceed - don't prevent default
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Logout functionality
        function handleLogout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '<?= base_url('auth/logout') ?>';
            }
        }
    </script>
    <script src="<?= base_url('js/logout.js') ?>"></script>
        
    </body>
</html>