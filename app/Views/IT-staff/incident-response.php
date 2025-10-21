<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incident Response - IT Staff</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="it-theme">
    <?php include APPPATH . 'Views/template/header.php'; ?>
    <div class="main-container">
        <?php include APPPATH . 'Views/IT-staff/components/sidebar.php'; ?>
        <main class="content">
            <h1 class="page-title">Incident Response</h1>

            <div class="panel">
                <div class="panel-header">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Incidents</span>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Owner</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>—</td>
                            <td>—</td>
                            <td><span class="badge badge-success">None</span></td>
                            <td>—</td>
                            <td><button class="btn" disabled>View</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <script>
        function handleLogout(){
            if(confirm('Logout?')){ window.location.href = '<?= base_url('/logout') ?>'; }
        }
    </script>
</body>
</html>
