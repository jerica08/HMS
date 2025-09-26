<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Communication & Notifications - HMS Admin</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard-common.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .comm-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .comm-section {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .section-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .section-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: #3b82f6;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }
        .message-item {
            display: flex;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .message-item:last-child {
            border-bottom: none;
        }
        .message-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #3b82f6;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 0.9rem;
        }
        .message-content {
            flex: 1;
        }
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        .message-sender {
            font-weight: 500;
            color: #1f2937;
        }
        .message-time {
            font-size: 0.8rem;
            color: #6b7280;
        }
        .message-text {
            color: #4b5563;
            font-size: 0.9rem;
        }
        .notification-item {
            background: #f8fafc;
            border-radius: 6px;
            padding: 1rem;
            margin: 0.5rem 0;
            border-left: 4px solid #e2e8f0;
        }
        .notification-item.notif-urgent {
            border-left-color: #ef4444;
            background: #fef2f2;
        }
        .notification-item.notif-warning {
            border-left-color: #f59e0b;
            background: #fffbeb;
        }
        .notification-item.notif-info {
            border-left-color: #3b82f6;
            background: #eff6ff;
        }
        .notification-item.notif-success {
            border-left-color: #22c55e;
            background: #f0fdf4;
        }
        .notif-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        .notif-title {
            font-weight: 500;
            color: #1f2937;
        }
        .notif-time {
            font-size: 0.8rem;
            color: #6b7280;
        }
        .notif-content {
            font-size: 0.9rem;
            color: #4b5563;
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }
        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
        }
        .comm-alert {
            background: #dbeafe;
            border: 1px solid #93c5fd;
            border-left: 4px solid #3b82f6;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .alert-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 0.5rem;
        }
        .alert-content {
            color: #1e3a8a;
            font-size: 0.9rem;
        }
        .quick-actions {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        .broadcast-form {
            background: #f8fafc;
            border-radius: 6px;
            padding: 1rem;
            margin: 1rem 0;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #374151;
        }
        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        .form-textarea {
            resize: vertical;
            min-height: 80px;
        }
        .priority-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .priority-high { background: #fecaca; color: #991b1b; }
        .priority-medium { background: #fef3c7; color: #92400e; }
        .priority-low { background: #dcfce7; color: #166534; }
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
                      <a href="<?= base_url('admin/staff') ?>" class="nav-link">
                          <i class="fas fa-user-tie nav-icon"></i>
                          Staff Management
                      </a>
                  </li>                 
                  <li class="nav-item">
                      <a href="<?= base_url('admin/users') ?>" class="nav-link">
                          <i class="fas fa-users nav-icon"></i>
                          User Management
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
                      <a href="<?= base_url('admin/communication') ?>" class="nav-link active">
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
                      <a href="<?= base_url('admin/auditLogs') ?>" class="nav-link">
                          <i class="fas fa-clipboard-list nav-icon"></i>
                          Audit Logs
                      </a>
                  </li>
              </ul>          
          </nav>
        <main class="content">
            <h1 class="page-title">Communication & Notifications</h1>

            <!-- Communication Overview Stats -->
            <div class="dashboard-overview" style="margin-bottom: 2rem;">
                <div class="overview-card">
                    <div class="card-header modern">
                        <div class="card-icon"><i class="fas fa-envelope"></i></div>
                        <div>
                            <h3 class="card-title">Unread Messages</h3>
                            <p class="card-content">Pending review</p>
                        </div>
                    </div>
                    <div class="stat-number" style="font-size: 2rem; font-weight: bold; color: #3b82f6; text-align: center; padding: 1rem;">0</div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon"><i class="fas fa-bell"></i></div>
                        <div>
                            <h3 class="card-title">Active Alerts</h3>
                            <p class="card-content">System notifications</p>
                        </div>
                    </div>
                    <div class="stat-number" style="font-size: 2rem; font-weight: bold; color: #ef4444; text-align: center; padding: 1rem;">0</div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon"><i class="fas fa-broadcast-tower"></i></div>
                        <div>
                            <h3 class="card-title">Broadcasts Sent</h3>
                            <p class="card-content">This week</p>
                        </div>
                    </div>
                    <div class="stat-number" style="font-size: 2rem; font-weight: bold; color: #22c55e; text-align: center; padding: 1rem;">0</div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon"><i class="fas fa-users"></i></div>
                        <div>
                            <h3 class="card-title">Online Staff</h3>
                            <p class="card-content">Currently active</p>
                        </div>
                    </div>
                    <div class="stat-number" style="font-size: 2rem; font-weight: bold; color: #f59e0b; text-align: center; padding: 1rem;">0</div>
                </div>
            </div>
        </main>
    </div>

    <script src="/js/logout.js"></script>
</body>
</html>
