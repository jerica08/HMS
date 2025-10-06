<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Billing - HMS</title>
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="accountant-theme">

    <?php include APPPATH . 'Views/template/header.php'; ?>
    <div class="main-container">
        <?php include APPPATH . 'Views/accountant/components/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="content">
            <h1 class="page-title">Patient Billing</h1>

            <div class="overview-card" style="margin-bottom:2rem;">
                <div class="card-header-modern">
                    <div class="card-icon-modern purple"><i class="fas fa-plus"></i></div>
                    <div class="card-info">
                        <h3 class="card-title-modern">Create Invoice</h3>
                        <p class="card-subtitle">Generate a new patient invoice</p>
                    </div>
                </div>
                <form action="#" method="post" onsubmit="alert('Invoice created (demo).'); return false;">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Patient Name</label>
                            <input class="form-input" type="text" name="patient_name">
                        </div>
                        <div class="form-group">
                            <label>Service/Description</label>
                            <input class="form-input" type="text" name="service">
                        </div>
                        <div class="form-group">
                            <label>Amount (₱)</label>
                            <input class="form-input" type="number" step="0.01" name="amount">
                        </div>
                        <div class="form-group">
                            <label>Due Date</label>
                            <input class="form-input" type="date" name="due_date">
                        </div>
                        <div class="form-group full-width">
                            <button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> Create Invoice</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-container">
                <h3 style="margin-bottom: 1.5rem;">Recent Invoices</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Patient</th>
                            <th>Service</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>INV-2025-1237</td>
                            <td>Maria Garcia</td>
                            <td>Consultation</td>
                            <td>₱125.00</td>
                            <td>2025-10-01</td>
                            <td><span class="badge badge-warning">Pending</span></td>
                            <td><a href="<?= base_url('accountant/payments') ?>" class="btn btn-success">Collect</a></td>
                        </tr>
                        <tr>
                            <td>INV-2025-1238</td>
                            <td>David Lee</td>
                            <td>Lab Tests</td>
                            <td>₱89.50</td>
                            <td>2025-10-02</td>
                            <td><span class="badge badge-success">Paid</span></td>
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
