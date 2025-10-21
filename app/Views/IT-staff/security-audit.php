<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Audit - IT Staff</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="it-theme">
    <?php include APPPATH . 'Views/template/header.php'; ?>
    <div class="main-container">
        <?php include APPPATH . 'Views/IT-staff/components/sidebar.php'; ?>
        <main class="content">
            <h1 class="page-title">Security Audit</h1>

            <div class="panel">
                <div class="panel-header">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Audit Checklist</span>
                </div>
                <div class="panel-body">
                    <ul class="checklist">
                        <li><input type="checkbox" disabled> Review access logs</li>
                        <li><input type="checkbox" disabled> Verify admin accounts</li>
                        <li><input type="checkbox" disabled> Patch updates applied</li>
                        <li><input type="checkbox" disabled> Backup integrity check</li>
                    </ul>
                </div>
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
