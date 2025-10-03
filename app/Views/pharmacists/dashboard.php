<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacist Dashboard - HMS</title>
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="pharmacy-theme">
    <!-- Header -->
    <?php include APPPATH . 'Views/template/header.php'; ?>
    <div class="main-container">
        <!-- Sidebar -->
        <?php include APPPATH . 'Views/pharmacists/components/sidebar.html'; ?>
        <!--Main Content-->
        <main class="content">
            <h1 class="page-title">Pharmacy Dashboard</h1>

            <!-- Dashboard Overview Cards -->
            <div class="dashboard-overview">
                <!--Prescription Queue-->
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue">
                            <i class="fas fa-prescription"></i>
                        </div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Prescription Queue</h3>
                            <p class="card-subtitle">Pending prescriptions</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value blue">34</div>
                            <div class="metric-label">Pending</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value green">12</div>
                            <div class="metric-label">Priority</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value orange">22</div>
                            <div class="metric-label">Routine</div>
                        </div>
                    </div>
                    <div class="card-actions">
                        <a href="prescription.html#priority" class="action-btn primary">Process Priority</a>
                        <a href="prescription.html#queue" class="action-btn secondary">View Queue</a>
                    </div>
                </div>
                <!--Inventory Status-->
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern purple">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Inventory Status</h3>
                            <p class="card-subtitle">Stock levels and alerts</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value purple">847</div>
                            <div class="metric-label">Items</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value green">23</div>
                            <div class="metric-label">Low Stock</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value">8</div>
                            <div class="metric-label">Expired</div>
                        </div>
                    </div>
                    <div class="card-actions">
                        <a href="inventory.html#low-stock" class="action-btn warning">Check Low Stock</a>
                        <a href="inventory.html#expired" class="action-btn danger">Remove Expired</a>
                    </div>
                </div>
                <!--Dispensed Today-->
                 <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern purple">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Dispense Today</h3>
                            <p class="card-subtitle">Medications dispensed</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value purple">156</div>
                            <div class="metric-label">Total</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value green">142</div>
                            <div class="metric-label">Outpatient</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value">14</div>
                            <div class="metric-label">Inpatient</div>
                        </div>
                    </div>
                    <div class="card-actions">
                        <a href="prescription.html#dispense" class="action-btn warning">Dispense Medication</a>
                        <a href="prescription.html#history" class="action-btn danger">View History</a>
                    </div>
                </div>
                <!--Drug Interaction-->
               <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern purple">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Drug Interaction</h3>
                            <p class="card-subtitle">Safety Alerts</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value purple">7</div>
                            <div class="metric-label">Alerts</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value green">3</div>
                            <div class="metric-label">Critical</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value">4</div>
                            <div class="metric-label">Moderate</div>
                        </div>
                    </div>
                    <div class="card-actions">
                        <a href="prescription.html#interactions" class="action-btn warning">Review Critical</a>
                        <a href="prescription.html#interactions" class="action-btn danger">Check All</a>
                    </div>
                </div>
            </div>
            <!-- Prescription Processing Queue -->
            <div class="table-container" style="margin-top: 2rem;">
                <h3 style="margin-bottom: 1.5rem;">Prescription Processing Queue</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Rx Number</th>
                            <th>Patient</th>
                            <th>Medication</th>
                            <th>Quantity</th>
                            <th>Prescriber</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>RX-2025-001</strong></td>
                            <td>Maria Garcia</td>
                            <td>Lisinopril 10mg</td>
                            <td>30 tablets</td>
                            <td>Dr. Wilson</td>
                            <td><span class="badge badge-danger">STAT</span></td>
                            <td><span class="badge badge-warning">Verifying</span></td>
                            <td><a href="#" class="btn btn-primary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Process</a></td>
                        </tr>
                        <tr>
                            <td><strong>RX-2025-002</strong></td>
                            <td>David Lee</td>
                            <td>Metformin 500mg</td>
                            <td>60 tablets</td>
                            <td>Dr. Martinez</td>
                            <td><span class="badge badge-info">Routine</span></td>
                            <td><span class="badge badge-info">Queued</span></td>
                            <td><a href="#" class="btn btn-success" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Start</a></td>
                        </tr>
                        <tr>
                            <td><strong>RX-2025-003</strong></td>
                            <td>Lisa Anderson</td>
                            <td>Atorvastatin 20mg</td>
                            <td>30 tablets</td>
                            <td>Dr. Lee</td>
                            <td><span class="badge badge-warning">Priority</span></td>
                            <td><span class="badge badge-success">Ready</span></td>
                            <td><a href="#" class="btn btn-success" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Dispense</a></td>
                        </tr>
                        <tr>
                            <td><strong>RX-2025-004</strong></td>
                            <td>James Brown</td>
                            <td>Insulin Glargine</td>
                            <td>1 vial</td>
                            <td>Dr. Davis</td>
                            <td><span class="badge badge-danger">STAT</span></td>
                            <td><span class="badge badge-warning">Processing</span></td>
                            <td><a href="#" class="btn btn-primary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Continue</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <script>
        // Logout (demo)
        function handleLogout() {
            if (confirm('Are you sure you want to logout?')) {
                alert('Logged out (demo)');
            }
        }
    </script>
 </body>
</html>
