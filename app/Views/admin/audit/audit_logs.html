<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs - HMS Admin</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard-common.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>     
        .audit-section {
            background: #fff;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }
        .section-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.25rem;
            padding-bottom: .75rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .section-icon {
            width: 42px; height: 42px;
            background: #3b82f6;
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            border-radius: 8px;
            font-size: 1.25rem;
        }
        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1f2937;
        }

        /* === Filters === */
        .filter-row {
            display: flex;
            gap: 1rem;
            align-items: end;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            min-width: 150px;
        }
        .form-input {
            padding: 0.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            font-size: 0.9rem;
        }

        /* === Log Entries === */
        .logs-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .table-header {
            background: #f9fafb;
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .log-entry {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            font-size: 0.9rem;
            cursor: pointer;
        }
        .log-entry:last-child {
            border-bottom: none;
        }
        .log-entry:hover {
            background: #f9fafb;
        }
        .log-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1rem;
        }
        .log-icon.create { background: #dcfce7; color: #166534; }
        .log-icon.update { background: #dbeafe; color: #1e40af; }
        .log-icon.delete { background: #fecaca; color: #991b1b; }
        .log-icon.login { background: #fef3c7; color: #92400e; }
        .log-icon.logout { background: #e5e7eb; color: #374151; }
        .log-icon.access { background: #e0e7ff; color: #3730a3; }
        .log-icon.error { background: #fed7cc; color: #c2410c; }
        .log-content {
            flex: 1;
        }
        .log-title {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }
        .log-details {
            color: #6b7280;
            font-size: 0.8rem;
        }
        .log-meta {
            text-align: right;
            color: #6b7280;
            font-size: 0.8rem;
            min-width: 120px;
        }
        .log-timestamp {
            font-weight: 500;
            margin-bottom: 0.25rem;
        }
        .log-user {
            font-size: 0.75rem;
        }
        .severity-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-left: 0.5rem;
        }
        .severity-low { background: #dcfce7; color: #166534; }
        .severity-medium { background: #fef3c7; color: #92400e; }
        .severity-high { background: #fed7cc; color: #c2410c; }
        .severity-critical { background: #fecaca; color: #991b1b; }

        /* === Buttons === */
        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            margin-top: 1rem;
        }
        .btn-small {
            padding: .5rem 1rem;
            font-size: .8rem;
        }

        /* === Pagination === */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }
        .page-btn {
            padding: 0.5rem 0.75rem;
            border: 1px solid #e2e8f0;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
        }
        .page-btn:hover {
            background: #f3f4f6;
        }
        .page-btn.active {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        /* === Modal === */
        .log-details-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 2rem;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6b7280;
        }
    </style>
</head>
<body class="admin-theme">
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <h1><i class="fas fa-hospital"></i>Administrator</h1>
            </div>
            <div class="user-info">
                <div class="user-avatar">AD</div>
                <div>
                    <div style="font-weight: 600;">
                        <?= \App\Helpers\UserHelper::getDisplayName($currentUser ?? null) ?>
                    </div>
                    <div style="font-size: 0.9rem; opacity: 0.8;">
                        <?= \App\Helpers\UserHelper::getDisplayRole($currentUser ?? null) ?>
                    </div>
                </div>
                <button class="logout-btn" onclick="handleLogout()">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </div>
        </div>
    </header>

    <div class="main-container">
        <!--sidebar-->
        <nav class="sidebar">
              
              <ul class="nav-menu">
                  <li class="nav-item">
                      <a href="<?= base_url('admin/dashboard') ?>" class="nav-link">
                          <i class="fas fa-tachometer-alt nav-icon"></i>
                          Dashboard
                      </a>
                  </li>
                  <li class="nav-item">
                      <a href="<?= base_url('admin/users') ?>" class="nav-link">
                          <i class="fas fa-users nav-icon"></i>
                          User Management
                      </a>
                  </li>
                  <li class="nav-item">
                      <a href="<?= base_url('admin/staff') ?>" class="nav-link">
                          <i class="fas fa-user-tie nav-icon"></i>
                          Staff Management
                      </a>
                  </li>
                  <li class="nav-item">
                        <a href="<?= base_url('admin/patient') ?>" class="nav-link">
                            <i class="fas fa-user-injured nav-icon"></i>
                            Patient Management
                        </a>
                    </li>
                  <li class="nav-item">
                      <a href="<?= base_url('admin/resource') ?>" class="nav-link">
                          <i class="fas fa-hospital nav-icon"></i>
                          Resource Management
                      </a>
                  </li>
                  <li class="nav-item">
                      <a href="<?= base_url('admin/financial') ?>" class="nav-link">
                          <i class="fas fa-dollar-sign nav-icon"></i>
                          Financial Management
                      </a>
                  </li>
                  <li class="nav-item">
                      <a href="<?= base_url('admin/communication') ?>" class="nav-link">
                          <i class="fas fa-comments nav-icon"></i>
                          Communication
                      </a>
                  </li>
                  <li class="nav-item">
                      <a href="<?= base_url('admin/analytics') ?>" class="nav-link">
                          <i class="fas fa-chart-bar nav-icon"></i>
                          Analytics & Reports
                      </a>
                  </li>
                  <li class="nav-item">
                      <a href="<?= base_url('admin/systemSettings') ?>" class="nav-link">
                          <i class="fas fa-cogs nav-icon"></i>
                          System Settings
                      </a>
                  </li>
                  <li class="nav-item">
                      <a href="<?= base_url('admin/securityAccess') ?>" class="nav-link">
                          <i class="fas fa-shield-alt nav-icon"></i>
                          Security & Access
                      </a>
                  </li>
                  <li class="nav-item">
                      <a href="<?= base_url('admin/auditLogs') ?>" class="nav-link active">
                          <i class="fas fa-clipboard-list nav-icon"></i>
                          Audit Logs
                      </a>
                  </li>
              </ul>          
        </nav>
    
        <!-- Main Content -->
        <main class="content">
            <h1 class="page-title">System Audit Logs</h1>

            <!-- Dashboard Overview -->
            <div class="dashboard-overview">
                    <!--Today's Events Card-->
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue"><i class="fas fa-calendar-day"></i></div>
                        <div>
                            <h3 class="card-title">Today's Events</h3>
                        </div>
                    </div>
                    <div class="stat-number" style="color:#3b82f6;">0</div>
                </div>
                <!--Warning Events Card-->
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue"><i class="fas fa-exclamation-triangle"></i></div>
                        <div>
                            <h3 class="card-title">Warning Events</h3>
                        </div>
                        </div>
                    <div class="stat-number" style="color:#3b82f6;">0</div>
                </div>
                <!--Critical Events Card-->
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue"><i class="fas fa-times-circle"></i></div>
                        <div>
                            <h3 class="card-title">Critical Events</h3>
                        </div>
                    </div>
                    <div class="stat-number" style="color:#3b82f6;">0</div>
                </div>
                <!--Active Users Card-->
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue"><i class="fas fa-users"></i></div>
                        <div>
                            <h3 class="card-title">Active Users</h3>
                        </div>
                    </div>
                    <div class="stat-number" style="color:#3b82f6;">0</div>
                </div>
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue"><i class="fas fa-server"></i></div>
                        <div>
                            <h3 class="card-title">System Uptime</h3>
                        </div>
                    </div>
                    <div class="stat-number" style="color:#3b82f6;">0</div>
                </div>                               
            </div>
        </main>
    </div>
    <script src="/js/logout.js"></script>
</body>
</html>