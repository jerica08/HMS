<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nurse Dashboard</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="nurse">
    <?php include APPPATH . 'Views/template/header.php'; ?>
    <div class="main-container">
        <?= $this->include('Views/nurse/components/sidebar') ?>
        <main class="content">
            <h1 class="page-title">Nurse Dashboard</h1>

            <div class="dashboard-overview">
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue"><i class="fas fa-bed"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Patient Care</h3>
                            <p class="card-subtitle">Monitor assigned patients</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value blue">18</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="<?= base_url('js/logout.js') ?>"></script>
</body>
</html>
