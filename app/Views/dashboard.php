<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Dashboard' ?> - HMS <?= ucfirst($userRole ?? 'User') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/unified/dashboard.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="base-url" content="<?= base_url() ?>">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta name="user-role" content="<?= $userRole ?? 'guest' ?>">
</head>
<body class="<?= $userRole ?? 'guest' ?>">

    <?= $this->include('template/header') ?>

    <div class="main-container">
        <?= $this->include('unified/components/sidebar') ?>

        <main class="content">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-tachometer-alt"></i>
                    <?= $title ?? 'Dashboard' ?>
                </h1>
                <div class="page-actions">
                    <div class="weather-widget" id="weatherWidget"></div>
                </div>
            </div>

            <!-- Statistics Overview -->
            <div class="dashboard-overview">
                <?= $this->include('unified/components/dashboard-stats', [
                    'userRole' => $userRole ?? 'guest',
                    'dashboardStats' => $dashboardStats ?? []
                ]) ?>
            </div>

            <!-- Main Dashboard Content -->
            <div class="dashboard-content">
                <div class="dashboard-grid">
                    <!-- Additional dashboard widgets can be added here -->
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="<?= base_url('assets/js/unified/dashboard.js') ?>"></script>
</body>
</html>

