<!DOCTYPE html>
<html lang="en">
<head>
     <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - HMS Admin</title>
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">  
</head>
<body class="admin">

    <header class="header">
        <div class="header-content">
            <div class="logo">
                <h1><i class="fas fa-hospital"></i> Administrator</h1>                    
            </div>
            <div class="user-info">
                <div href="" class="fas fa-avatar" href=""></div>
                <div>
                    <div style="font-weight: 600;">
                        <?= \App\Helpers\UserHelper::getDisplayName($currentUser ?? null) ?>
                    </div>
                    <div style="font-size: 0.9rem;opacity:0.8">
                        <?= \App\Helpers\UserHelper::getDisplayRole($currentUser ?? null) ?>
                    </div>
                </div>
                <button class="logout-btn" onclick="handleLogout()">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </button>
            </div>
        </div>
    </header>
        <!--Main Content-->
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
                      <a href="<?= base_url('admin/users') ?>" class="nav-link active">
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
                      <a href="<?= base_url('admin/auditLogs') ?>" class="nav-link">
                          <i class="fas fa-clipboard-list nav-icon"></i>
                          Audit Logs
                      </a>
                  </li>
              </ul>          
            </nav>
       
           
            <main class="content">
                <h1 class="page-title"> User Management</h1>
                <div class="page-actions">
                        <button type="button" class="btn btn-primary" onclick="openAddUserModal()">
                            <i class="fas fa-plus"></i> Add New User
                        </button>
                </div><br>

                <!--Dashboard overview cards-->
                <div class="dashboard-overview">
                    <!-- Total User Cards -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Total Users</h3>
                                <p class="card-subtitle">All Registered Users</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $stats['total_users'] ?? 0 ?></div>
                            </div>
                        </div>
                    </div>

                    <!--Admin Users Card-->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern purple">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Admin Users</h3>
                                <p class="card-subtitle">System Administrators</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value purple"><?= $stats['admin_users'] ?? 0 ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--Filter and Actions-->    
                <div class="user-filter">
                    <div class="filter-group">
                        <label> Search Users</label>
                        <input type="text" class="filter-input" placeholder="Search by name, email, or ID..." 
                            id="searchInput" value="">
                    </div>
                    <div class="filter-group">
                        <label> Role Filter</label>
                        <select class="filter-input" id="roleFilter">
                            <option value="">All Roles</option>
                            <option value="admin" <?= ($roleFilter ?? '') === 'admin' ? 'selected' : '' ?>>Administrator</option>
                            <option value="doctor" <?= ($roleFilter ?? '') === 'doctor' ? 'selected' : '' ?>>Doctor</option>
                            <option value="nurse" <?= ($roleFilter ?? '') === 'nurse' ? 'selected' : '' ?>>Nurse</option>
                            <option value="receptionist" <?= ($roleFilter ?? '') === 'receptionist' ? 'selected' : '' ?>>Receptionist</option>
                            <option value="laboratorist" <?= ($roleFilter ?? '') === 'laboratorist' ? 'selected' : '' ?>>Laboratory Staff</option>
                            <option value="pharmacist" <?= ($roleFilter ?? '') === 'pharmacist' ? 'selected' : '' ?>>Pharmacist</option>
                            <option value="accountant" <?= ($roleFilter ?? '') === 'accountant' ? 'selected' : '' ?>>Accountant</option>
                            <option value="it_staff" <?= ($roleFilter ?? '') === 'it_staff' ? 'selected' : '' ?>>IT Staff</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Status Filter</label>
                        <select class="filter-input" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="active" <?= ($statusFilter ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($statusFilter ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>&nbsp;</label>
                        <button type="button" class="filter-input btn btn-primary" onclick="applyFilters()" style="background: #007bff; color: white; border: none;">
                            <i class="fas fa-search"></i> Apply Filters
                        </button>
                    </div>
                </div>
                <!--users Table-->
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr class="user-row">
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 1rem;">
                                                <div class="user-avatar">
                                                    <?= strtoupper(substr($user['first_name'] ?? 'U', 0, 1) . substr($user['last_name'] ?? 'U', 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <div style="font-weight: 600;">
                                                        <?= esc(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?>
                                                    </div>
                                                    <div style="font-size: 0.8rem; color: #6b7280;">
                                                        <?= esc($user['email'] ?? '') ?>
                                                    </div>
                                                    <div style="font-size: 0.8rem; color: #6b7280;">
                                                        ID: <?= esc($user['employee_id'] ?? $user['username'] ?? 'N/A') ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="role-badge role-<?= str_replace('_', '-', $user['role'] ?? 'user') ?>">
                                                <?= ucfirst(str_replace('_', ' ', esc($user['role'] ?? 'User'))) ?>
                                            </span>
                                        </td>
                                        <td><?= esc($user['department'] ?? 'N/A') ?></td>
                                        <td>
                                            <i class="fas fa-circle status-<?= $user['status'] ?? 'inactive' ?>"></i> 
                                            <?= ucfirst(esc($user['status'] ?? 'Inactive')) ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $lastLogin = $user['updated_at'] ?? $user['created_at'] ?? null;
                                            if ($lastLogin): 
                                                $diff = time() - strtotime($lastLogin);
                                                if ($diff < 3600): echo 'Less than 1 hour ago';
                                                elseif ($diff < 86400): echo floor($diff/3600) . ' hours ago';
                                                else: echo date('M j, Y', strtotime($lastLogin));
                                                endif;
                                            else: echo 'Never';
                                            endif;
                                            ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn btn-edit" onclick="editUser(<?= $user['id'] ?>)">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button class="action-btn btn-reset" onclick="resetPassword(<?= $user['id'] ?>)">
                                                    <i class="fas fa-key"></i> Reset
                                                </button>
                                                <button class="action-btn btn-delete" onclick="deleteUser(<?= $user['id'] ?>)">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 2rem;">
                                        <i class="fas fa-users" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                                        <p>No users found.</p>
                                        <?php if (!empty($search) || !empty($roleFilter) || !empty($statusFilter)): ?>
                                            <button onclick="clearFilters()" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> Clear Filters
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!--Add/Edit User Modal-->
                <?php include APPPATH . 'Views/admin/dashboard/add_user.php'; ?>

                <script src="<?= base_url('js/session-manager.js') ?>"></script>
                <script src="<?= base_url('js/utils.js') ?>"></script>
                <script src="<?= base_url('js/edit-user.js') ?>"></script>
                <script src="<?= base_url('js/delete-user.js') ?>"></script>
                <script src="<?= base_url('js/user-management.js') ?>"></script>
                <script src="<?= base_url('js/logout.js') ?>"></script>

            </main>
        </div>
</body>
</html>