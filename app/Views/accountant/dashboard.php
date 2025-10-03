<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accountant Dashboard - HMS</title>
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
</head>
<body class="accountant-theme">
   
    <?php include APPPATH . 'Views/template/header.php'; ?>
    <div class="main-container">
        <?php include APPPATH . 'Views/accountant/components/sidebar.php'; ?>
        <!-- Main Content -->
        <main class="content">
            <h1 class="page-title">Accounting Dashboard</h1>
            <!-- Dashboard Overview Cards -->
            <div class="dashboard-overview">              
                <!--Daily Revenue Card-->
                
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Daily Revenue</h3>
                            <p class="card-subtitle">Today's financial summary</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value blue">P0</div>
                            <div class="metric-label">Total</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value green">P0</div>
                            <div class="metric-label">Collected</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value orange">P0</div>
                            <div class="metric-label">Pending</div>
                        </div>
                    </div>
                    <div class="card-actions">
                        <a href="payments.html" class="action-btn primary">View Details</a>
                        <a href="#" class="action-btn secondary">Generate Report</a>
                    </div>
                </div>
                <!--Billing Status-->
                
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Billing Status</h3>
                            <p class="card-subtitle">Invoice processing</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value purple">0</div>
                            <div class="metric-label">Generated</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value green">0</div>
                            <div class="metric-label">Pending</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value red">0</div>
                            <div class="metric-label">Disputed</div>
                        </div>
                    </div>
                    <div class="card-actions">
                        <a href="billing.html" class="action-btn warning">Process Pending</a>
                        <a href="#" class="action-btn danger">Resolve Disputes</a>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="table-container" style="margin-top: 2rem;">
                <h3 style="margin-bottom: 1.5rem;">Recent Payment Transactions</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Patient</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Invoice #</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>09:15 AM</td>
                            <td>Maria Garcia</td>
                            <td>P125.00</td>
                            <td>Credit Card</td>
                            <td>INV-2025-1237</td>
                            <td><span class="badge badge-success">Processed</span></td>
                            <td><a href="payments.html" class="btn btn-secondary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Receipt</a></td>
                        </tr>
                        <tr>
                            <td>09:08 AM</td>
                            <td>David Lee</td>
                            <td>P89.50</td>
                            <td>Cash</td>
                            <td>INV-2025-1238</td>
                            <td><span class="badge badge-success">Processed</span></td>
                            <td><a href="payments.html" class="btn btn-secondary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Receipt</a></td>
                        </tr>
                        <tr>
                            <td>08:52 AM</td>
                            <td>Lisa Anderson</td>
                            <td>P456.78</td>
                            <td>Insurance</td>
                            <td>INV-2025-1239</td>
                            <td><span class="badge badge-warning">Pending</span></td>
                            <td><a href="insurance.html" class="btn btn-warning" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Verify</a></td>
                        </tr>
                        <tr>
                            <td>08:45 AM</td>
                            <td>James Brown</td>
                            <td>P234.56</td>
                            <td>Check</td>
                            <td>INV-2025-1240</td>
                            <td><span class="badge badge-info">Deposited</span></td>
                            <td><a href="#" class="btn btn-secondary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Receipt</a></td>
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
                alert('Logged out');
            }
        }
    </script>
</body>
</html>