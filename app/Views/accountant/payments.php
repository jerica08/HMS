<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Processing - HMS</title>
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="accountant-theme">

    <?php include APPPATH . 'Views/template/header.php'; ?>
    <div class="main-container">
        <?php include APPPATH . 'Views/accountant/components/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="content">
            <h1 class="page-title">Payment Processing</h1>

            <div class="overview-card" style="margin-bottom:2rem;">
                <div class="card-header-modern">
                    <div class="card-icon-modern blue"><i class="fas fa-plus"></i></div>
                    <div class="card-info">
                        <h3 class="card-title-modern">Record Payment</h3>
                        <p class="card-subtitle">Collect payment for an invoice</p>
                    </div>
                </div>
                <form action="#" method="post" onsubmit="alert('Payment recorded (demo).'); return false;">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Invoice #</label>
                            <input class="form-input" type="text" name="invoice_no">
                        </div>
                        <div class="form-group">
                            <label>Amount (₱)</label>
                            <input class="form-input" type="number" step="0.01" name="amount">
                        </div>
                        <div class="form-group">
                            <label>Payment Method</label>
                            <select class="form-input" name="method">
                                <option>Cash</option>
                                <option>Credit Card</option>
                                <option>Check</option>
                                <option>Insurance</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Reference (optional)</label>
                            <input class="form-input" type="text" name="reference">
                        </div>
                        <div class="form-group full-width">
                            <button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> Record Payment</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-container">
                <h3 style="margin-bottom: 1.5rem;">Recent Payments</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Invoice #</th>
                            <th>Patient</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>09:15 AM</td>
                            <td>INV-2025-1237</td>
                            <td>Maria Garcia</td>
                            <td>₱125.00</td>
                            <td>Credit Card</td>
                            <td><span class="badge badge-success">Processed</span></td>
                            <td><a href="#" class="btn btn-secondary">Receipt</a></td>
                        </tr>
                        <tr>
                            <td>09:08 AM</td>
                            <td>INV-2025-1238</td>
                            <td>David Lee</td>
                            <td>₱89.50</td>
                            <td>Cash</td>
                            <td><span class="badge badge-success">Processed</span></td>
                            <td><a href="#" class="btn btn-secondary">Receipt</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <script>
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function() {
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            });
        });

        function handleLogout() {
            if (confirm('Are you sure you want to logout?')) {
                alert('Logged out');
            }
        }
    </script>
</body>
</html>
