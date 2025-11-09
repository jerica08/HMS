<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= base_url() ?>">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <meta name="user-role" content="<?= esc($userRole) ?>">
    <title><?= esc($title) ?> - HMS</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/unified/analytics-reports.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="<?= esc($userRole) ?>">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <?php include APPPATH . 'Views/unified/components/sidebar.php'; ?>

        <main class="content" role="main">
            <h1 class="page-title">
                <i class="fas fa-chart-line"></i>
                <?= esc($title) ?>
            </h1>
            <div class="page-actions">
                <?php if (in_array('generate_reports', $permissions)): ?>
                    <button class="btn btn-primary" onclick="AnalyticsManager.openReportModal()">
                        <i class="fas fa-file-pdf"></i> Generate Report
                    </button>
                <?php endif; ?>
                <?php if (in_array('export', $permissions)): ?>
                    <button class="btn btn-secondary" onclick="AnalyticsManager.exportData()">
                        <i class="fas fa-download"></i> Export
                    </button>
                <?php endif; ?>
                <button class="btn btn-secondary" onclick="AnalyticsManager.refreshData()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>

            <!-- Filters and Search -->
            <div class="controls-section">
                <div class="filters-section">
                    <div class="filter-group">
                        <label for="dateRange">Date Range:</label>
                        <select id="dateRange" class="form-select">
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month" selected>This Month</option>
                            <option value="quarter">This Quarter</option>
                            <option value="year">This Year</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                    
                    <div class="filter-group" id="customDateGroup" style="display: none;">
                        <label for="startDate">Start Date:</label>
                        <input type="date" id="startDate" class="form-input">
                    </div>
                    
                    <div class="filter-group" id="customDateGroup2" style="display: none;">
                        <label for="endDate">End Date:</label>
                        <input type="date" id="endDate" class="form-input">
                    </div>
                    
                    <button type="button" id="applyFilters" class="btn btn-primary" onclick="AnalyticsManager.applyFilters()">
                        <i class="fas fa-filter"></i> Apply
                    </button>
                </div>
            </div>

            <!-- Statistics Overview -->
            <div class="dashboard-overview">
                <?php if ($userRole === 'admin' || $userRole === 'accountant' || $userRole === 'it_staff'): ?>
                    <!-- Total Patients Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-users"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Total Patients</h3>
                                <p class="card-subtitle">Patient statistics</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $analytics['patient_analytics']['total_patients'] ?? 0 ?></div>
                                <div class="metric-label">Total</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value green">+<?= $analytics['patient_analytics']['new_patients'] ?? 0 ?></div>
                                <div class="metric-label">New</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Total Appointments Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern purple"><i class="fas fa-calendar-check"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Total Appointments</h3>
                                <p class="card-subtitle">This period</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value purple"><?= $analytics['appointment_analytics']['total_appointments'] ?? 0 ?></div>
                                <div class="metric-label">Total</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Total Revenue Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern green"><i class="fas fa-dollar-sign"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Total Revenue</h3>
                                <p class="card-subtitle">Financial performance</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value green">₱<?= number_format($analytics['financial_analytics']['total_revenue'] ?? 0, 0) ?></div>
                                <div class="metric-label">Revenue</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value blue">₱<?= number_format($analytics['financial_analytics']['net_profit'] ?? 0, 0) ?></div>
                                <div class="metric-label">Net Profit</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Active Staff Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern orange"><i class="fas fa-user-md"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Active Staff</h3>
                                <p class="card-subtitle">All departments</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value orange"><?= $analytics['staff_analytics']['total_staff'] ?? 0 ?></div>
                                <div class="metric-label">Staff Members</div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($userRole === 'doctor'): ?>
                    <!-- My Patients Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-user-injured"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">My Patients</h3>
                                <p class="card-subtitle">Assigned patients</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $analytics['my_patients']['total_patients'] ?? 0 ?></div>
                                <div class="metric-label">Total</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value green">+<?= $analytics['my_patients']['new_patients'] ?? 0 ?></div>
                                <div class="metric-label">New</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- My Appointments Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern purple"><i class="fas fa-calendar-alt"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">My Appointments</h3>
                                <p class="card-subtitle">Performance tracking</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value purple"><?= $analytics['my_appointments']['total_appointments'] ?? 0 ?></div>
                                <div class="metric-label">Total</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value green"><?= $analytics['my_appointments']['completion_rate'] ?? 0 ?>%</div>
                                <div class="metric-label">Completed</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- My Revenue Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern green"><i class="fas fa-wallet"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">My Revenue</h3>
                                <p class="card-subtitle">This period</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value green">₱<?= number_format($analytics['my_revenue']['total_revenue'] ?? 0, 0) ?></div>
                                <div class="metric-label">Earnings</div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($userRole === 'nurse'): ?>
                    <!-- Department Patients Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-hospital"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Department Patients</h3>
                                <p class="card-subtitle">Patient care</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $analytics['department_patients']['total'] ?? 0 ?></div>
                                <div class="metric-label">Total</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value green"><?= $analytics['department_patients']['active'] ?? 0 ?></div>
                                <div class="metric-label">Active</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Medications Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern green"><i class="fas fa-pills"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Medications</h3>
                                <p class="card-subtitle">Administration tracking</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value green"><?= $analytics['medication_tracking']['administered'] ?? 0 ?></div>
                                <div class="metric-label">Administered</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value orange"><?= $analytics['medication_tracking']['pending'] ?? 0 ?></div>
                                <div class="metric-label">Pending</div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($userRole === 'receptionist'): ?>
                    <!-- New Registrations Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-user-plus"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">New Registrations</h3>
                                <p class="card-subtitle">Patient registration</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $analytics['registration_stats']['new_registrations'] ?? 0 ?></div>
                                <div class="metric-label">Total</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value green"><?= $analytics['registration_stats']['total_today'] ?? 0 ?></div>
                                <div class="metric-label">Today</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Appointments Booked Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern purple"><i class="fas fa-calendar"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Appointments Booked</h3>
                                <p class="card-subtitle">Today's bookings</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value purple"><?= $analytics['appointment_booking_stats']['booked_today'] ?? 0 ?></div>
                                <div class="metric-label">Booked</div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

            <!-- Charts Section -->
                <?php if ($userRole === 'admin' || $userRole === 'accountant' || $userRole === 'it_staff'): ?>
                <div class="charts-section">
                    <!-- Full Width Chart -->
                    <div class="chart-container full-width">
                        <div class="chart-header">
                            <h3 class="chart-title">Appointment Trends</h3>
                            <span class="chart-period">Last 30 Days</span>
                        </div>
                        <canvas id="appointmentTrendsChart" height="80"></canvas>
                    </div>

                    <!-- Two Column Charts -->
                    <div class="charts-row">
                        <div class="chart-container">
                            <div class="chart-header">
                                <h3 class="chart-title">Patient Distribution</h3>
                                <span class="chart-period">By Type</span>
                            </div>
                            <canvas id="patientTypeChart"></canvas>
                        </div>
                        <div class="chart-container">
                            <div class="chart-header">
                                <h3 class="chart-title">Appointment Status</h3>
                                <span class="chart-period">Current Period</span>
                            </div>
                            <canvas id="appointmentStatusChart"></canvas>
                        </div>
                    </div>

                    <div class="charts-row">
                        <div class="chart-container">
                            <div class="chart-header">
                                <h3 class="chart-title">Revenue Trend</h3>
                                <span class="chart-period">Monthly</span>
                            </div>
                            <canvas id="revenueTrendChart"></canvas>
                        </div>
                        <div class="chart-container">
                            <div class="chart-header">
                                <h3 class="chart-title">Staff Distribution</h3>
                                <span class="chart-period">By Role</span>
                            </div>
                            <canvas id="staffDistributionChart"></canvas>
                        </div>
                    </div>
                </div>
                <?php elseif ($userRole === 'doctor'): ?>
                <div class="charts-section">
                    <div class="charts-row">
                        <div class="chart-container">
                            <div class="chart-header">
                                <h3 class="chart-title">My Appointments</h3>
                                <span class="chart-period">This Month</span>
                            </div>
                            <canvas id="doctorAppointmentsChart"></canvas>
                        </div>
                        <div class="chart-container">
                            <div class="chart-header">
                                <h3 class="chart-title">Patient Growth</h3>
                                <span class="chart-period">Last 6 Months</span>
                            </div>
                            <canvas id="patientGrowthChart"></canvas>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Data Tables -->
                <?php if (in_array('view_all', $permissions) || in_array('advanced_analytics', $permissions)): ?>
                <div class="data-table-container">
                    <div class="table-header">
                        <h3>Recent Activity</h3>
                        <div class="table-actions">
                            <?php if (in_array('export', $permissions)): ?>
                            <button class="btn btn-secondary btn-sm" onclick="AnalyticsManager.exportData()">
                                <i class="fas fa-download"></i> Export
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Activity Type</th>
                                    <th>Details</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="activityTableBody">
                                <tr>
                                    <td colspan="4" class="text-center">Loading data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
            </div>
            <?php endif; ?>

        </main>
    </div>

    <!-- Generate Report Modal -->
    <?php if (in_array('generate_reports', $permissions)): ?>
    <div id="reportModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Generate Report</h2>
                <button class="close-btn" onclick="AnalyticsManager.closeReportModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="reportForm">
                    <div class="form-group">
                        <label>Report Type</label>
                        <select id="reportType" class="form-control" required>
                            <option value="">Select Report Type</option>
                            <option value="patient_summary">Patient Summary</option>
                            <option value="appointment_summary">Appointment Summary</option>
                            <option value="financial_summary">Financial Summary</option>
                            <?php if ($userRole === 'admin' || $userRole === 'it_staff'): ?>
                            <option value="staff_performance">Staff Performance</option>
                            <?php endif; ?>
                            <?php if ($userRole === 'doctor'): ?>
                            <option value="doctor_performance">My Performance</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date Range</label>
                        <select id="reportDateRange" class="form-control" required>
                            <option value="week">Last 7 Days</option>
                            <option value="month" selected>Last 30 Days</option>
                            <option value="quarter">Last 90 Days</option>
                            <option value="year">Last Year</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Format</label>
                        <select id="reportFormat" class="form-control" required>
                            <option value="pdf">PDF</option>
                            <option value="excel">Excel</option>
                            <option value="csv">CSV</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="AnalyticsManager.closeReportModal()">Cancel</button>
                <button class="btn btn-primary" onclick="AnalyticsManager.generateReport()">
                    <i class="fas fa-file-pdf"></i> Generate
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // Pass analytics data to JavaScript
        window.analyticsData = <?= json_encode($analytics) ?>;
        window.userRole = '<?= esc($userRole) ?>';
    </script>
    <script src="<?= base_url('assets/js/unified/analytics-reports.js') ?>"></script>
</body>
</html>
