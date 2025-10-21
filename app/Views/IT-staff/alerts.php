<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerts - IT Staff</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="it-theme">
    <?php include APPPATH . 'Views/template/header.php'; ?>
    <div class="main-container">
        <?php include APPPATH . 'Views/IT-staff/components/sidebar.php'; ?>
        <main class="content">
            <h1 class="page-title">System Alerts</h1>

            <div class="panel">
                <div class="panel-header">
                    <i class="fas fa-bell"></i>
                    <span>Active Alerts</span>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Severity</th>
                            <th>Source</th>
                            <th>Message</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>now</td>
                            <td><span class="badge badge-info">Info</span></td>
                            <td>System</td>
                            <td>No alerts</td>
                            <td><button class="btn btn-secondary" disabled>View</button></td>
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
