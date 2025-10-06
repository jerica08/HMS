<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insurance Claims - HMS</title>
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="accountant-theme">

    <?php include APPPATH . 'Views/template/header.php'; ?>
    <div class="main-container">
        <?php include APPPATH . 'Views/accountant/components/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="content">
            <h1 class="page-title">Insurance Claims</h1>

            <div class="overview-card" style="margin-bottom:2rem;">
                <div class="card-header-modern">
                    <div class="card-icon-modern purple"><i class="fas fa-paper-plane"></i></div>
                    <div class="card-info">
                        <h3 class="card-title-modern">Submit Insurance Claim</h3>
                        <p class="card-subtitle">Send claim to provider</p>
                    </div>
                </div>
                <form action="#" method="post" onsubmit="alert('Claim submitted (demo).'); return false;">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Patient Name</label>
                            <input class="form-input" type="text" name="patient_name">
                        </div>
                        <div class="form-group">
                            <label>Policy #</label>
                            <input class="form-input" type="text" name="policy_no">
                        </div>
                        <div class="form-group">
                            <label>Claim Amount (₱)</label>
                            <input class="form-input" type="number" step="0.01" name="claim_amount">
                        </div>
                        <div class="form-group">
                            <label>Diagnosis Code</label>
                            <input class="form-input" type="text" name="diagnosis_code">
                        </div>
                        <div class="form-group full-width">
                            <label>Notes</label>
                            <textarea class="form-input" name="notes" rows="3"></textarea>
                        </div>
                        <div class="form-group full-width">
                            <button class="btn btn-primary" type="submit"><i class="fas fa-paper-plane"></i> Submit Claim</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-container">
                <h3 style="margin-bottom: 1.5rem;">Recent Insurance Claims</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ref #</th>
                            <th>Patient</th>
                            <th>Policy #</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>CLM-20250930-0001</td>
                            <td>Lisa Anderson</td>
                            <td>PL-778899</td>
                            <td>₱456.78</td>
                            <td><span class="badge badge-warning">Pending</span></td>
                            <td><a href="#" class="btn btn-secondary">Details</a></td>
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
