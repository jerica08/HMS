<!DOCTYPE html>
<html lang="en">
    <head>
         <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratory Dashboard - HMS</title>
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>
    <body class="lab-theme">
        <!--Header-->
        <?php include APPPATH . 'Views/template/header.php'; ?>

        <div class="main-container">
            <!--Sidebar-->
            <?php include APPPATH . '/laboratorists/components/sidebar.php'; ?>

            <!--Main Content-->
            <main class="content">
                <h1 class="page-title">Laboratory Dashboard</h1>

                <!--Dashboard Overview Cards-->
                 <div class="dashboard-overview">              
                <!--Test Request-->
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Test Requests</h3>
                            <p class="card-subtitle">Pending lab tests</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value blue">23</div>
                            <div class="metric-label">Pending</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value green">8</div>
                            <div class="metric-label">Urgent</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value orange">15</div>
                            <div class="metric-label">Routine</div>
                        </div>
                    </div>
                    <div class="card-actions">
                        <a href="test-request.html#urgent" class="action-btn danger">Process Urgent</a>
                        <a href="test-request.html" class="action-btn secondary">View all</a>
                    </div>
                </div>
                <!--Sample Management-->
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue">
                            <i class="fas fa-vial"></i>
                        </div>
                        <div class="card-info">
                            <h3 class="card-title">Sample Management</h3>
                            <p class="card-subtitle">Sample Tracking</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value purple">45</div>
                            <div class="metric-label">Received</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value green">32</div>
                            <div class="metric-label">Processing</div> 
                        </div>
                        <div class="metric">
                            <div class="metric-value red">13</div>
                            <div class="metric-label">Completed</div>
                        </div>
                    </div>
                    <div class="card-actions">
                        <a href="sample-management.html#log" class="action-btn primary">Log Sample</a>
                        <a href="sample-management.html#queue" class="action-btn secondary">Track Status</a>
                    </div>
                </div>
                <!--Result Ready-->
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div>
                            <h3 class="card-title-modern">Result Ready</h3>
                            <p class="card-subtitle">Completed tests</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value purple">18</div>
                            <div class="metric-label">Ready</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value green">5</div>
                            <div class="metric-label">Critical</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value">13</div>
                            <div class="metric-label">Normal</div>
                        </div>
                    </div>
                    <div class="card-actions">
                        <a href="test-result.html#critical" class="action-btn danger">Review Critical</a>
                        <a href="test-result.html" class="action-btn secondary">Send Results</a>
                    </div>
                </div>
                <!--Equipment Status-->
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue">
                            <i class="fas fa-microscope"></i>
                        </div>
                        <div>
                            <h3 class="card-title-modern">Equipment Status</h3>
                            <p class="card-subtitle">Lab equipment monitoring</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value purple">12</div>
                            <div class="metric-label">Online</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value green">2</div>
                            <div class="metric-label">Maintenance</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value green">1</div>
                            <div class="metric-label">Offline</div>
                        </div>
                    </div>
                    <div class="card-actions">
                        <a href="sample-management.html#equipment" class="action-btn primary">Check Status</a>
                        <a href="sample-management.html#maintenance" class="action-btn secondary">Scheduling Maintenance</a>
                    </div>
                </div>
            </div>

            <!--Urgent Test Requests-->
            <div class="table-container">
                <h3 style="margin-bottom: 1.5rem; color: #f56565;">
                    <i class="fas fa-exclamation-triangle"></i> Urgent Test Requests
                </h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Sample ID</th>
                            <th>Patient</th>
                            <th>Test Type</th>
                            <th>Ordered By</th>
                            <th>Priority</th>
                            <th>Received</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background: #fed7d7;">
                            <td><strong>LAB-25-001</strong></td>
                            <td>John Martinez</td>
                            <td>Cardiac Enzymes</td>
                            <td>Dr. Johnson</td>
                            <td><span class="badge badge-danger">STAT</span></td>
                            <td>08:45 AM</td>
                            <td><a href="#" class="btn btn-danger" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Process Now</a></td>
                        </tr>
                       <tr style="background: #feebc8;">
                            <td><strong>LAB-25-002</strong></td>
                            <td>Sarah Wilson</td>
                            <td>Blood Gas Analysis</td>
                            <td>Dr. Smith</td>
                            <td><span class="badge badge-danger">Urgent</span></td>
                            <td>08:50 AM</td>
                            <td><a href="#" class="btn btn-danger" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Process</a></td>
                        </tr>
                        <tr style="background: #fed7d7;">
                            <td><strong>LAB-25-003</strong></td>
                            <td>Allison Wang</td>
                            <td>Blood Culture</td>
                            <td>Dr. Brown</td>
                            <td><span class="badge badge-danger">STAT</span></td>
                            <td>09:00 AM</td>
                            <td><a href="#" class="btn btn-danger" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Process Now</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!--Sample Processing Queue-->
             <div class="table-container" style="margin-top: 2rem;">
                <h3 style="margin-bottom: 1.5rem;">Sample Processing Queue</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Sample ID</th>
                            <th>Patient</th>
                            <th>Test Type</th>
                            <th>Sample Type</th>
                            <th>Status</th>
                            <th>Estimated Completion</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>LAB-2025-004</td>
                            <td>Maria Garcia</td>
                            <td>Complete Blood Count</td>
                            <td>Blood</td>
                            <td><span class="badge badge-warning">Processing</span></td>
                            <td>10:30 AM</td>
                            <td><a href="#" class="btn btn-primary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Update</a></td>
                        </tr>
                        <tr>
                            <td>LAB-2025-005</td>
                            <td>David Lee</td>
                            <td>Lipid Panel</td>
                            <td>Serum</td>
                            <td><span class="badge badge-info">Queued</span></td>
                            <td>11:00 AM</td>
                            <td><a href="#" class="btn btn-success" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Start</a></td>
                        </tr>
                        <tr>
                            <td>LAB-2025-006</td>
                            <td>Lisa Anderson</td>
                            <td>Urinalysis</td>
                            <td>Urine</td>
                            <td><span class="badge badge-success">Completed</span></td>
                            <td>Completed</td>
                            <td><a href="#" class="btn btn-secondary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Review</a></td>
                        </tr>
                        <tr>
                            <td>LAB-2025-007</td>
                            <td>James Brown</td>
                            <td>Liver Function Tests</td>
                            <td>Serum</td>
                            <td><span class="badge badge-warning">Processing</span></td>
                            <td>11:15 AM</td>
                            <td><a href="#" class="btn btn-primary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Update</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>

           <!--Equipment Status and Quality Control-->
           <div class="dashboard-grid" style="margin-top: 2rem;">

            </div>

            <!--Critical Result Alert-->
            <div class="table-container" style="margin-top: 2rem;">
                <h3 style="margin-bottom: 1.5rem; color: #f56565;">
                    <i class="fas fa-exclamation-circle"></i> Critical Results Requiring Immediate Notification
                </h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Sample ID</th>
                            <th>Patient</th>
                            <th>Test</th>
                            <th>Result</th>
                            <th>Reference Range</th>
                            <th>Ordering Physician</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background: #fed7d7;">
                            <td><strong>LAB-2025-008</strong></td>
                            <td>Patricia Davis</td>
                            <td>Troponin I</td>
                            <td>15.2 ng/mL</td>
                            <td>0.04 ng/mL</td>
                            <td>Dr. Johnson</td>
                            <td><a href="#" class="btn btn-danger" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Call Now</a></td>
                        </tr>
                        <tr style="background: #fed7d7;">
                            <td><strong>LAB-2025-009</strong></td>
                            <td>Michael Wilson</td>
                            <td>Potassium</td>
                            <td>6.8 mEq/L</td>
                            <td>3.5-5.0 mEq/L</td>
                            <td>Dr. Smith</td>
                            <td><a href="#" class="btn btn-danger" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Call Now</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            </main>
        </div>
    <script>
        // Navigation links go to separate static pages; no JS needed here.

        // Logout functionality
        function handleLogout() {
            if(confirm('Are you sure you want to logout?')) {
                alert('Logged out (demo)');
            }
        }
    </script>