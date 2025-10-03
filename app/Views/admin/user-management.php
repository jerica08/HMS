<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>User Management - HMS Admin</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="admin">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <?php include APPPATH . 'Views/admin/components/sidebar.php'; ?>


        <main class="content" role="main">
            <h1 class="page-title">User Management</h1>
            <div class="page-actions">
                <button type="button" class="btn btn-primary" onclick="openAddUserModal()" aria-label="Add New User">
                    <i class="fas fa-plus" aria-hidden="true"></i> Add New User
                </button>
            </div>

            <br />

            <div class="dashboard-overview" role="region" aria-label="Dashboard Overview Cards">
                <div class="overview-card" tabindex="0">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue" aria-hidden="true">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Total Users</h3>
                            <p class="card-subtitle">All Registered Users</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value blue"><?= esc($stats['total_users'] ?? 0) ?></div>
                        </div>
                    </div>
                </div>

                <div class="overview-card" tabindex="0">
                    <div class="card-header-modern">
                        <div class="card-icon-modern purple" aria-hidden="true">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Admin Users</h3>
                            <p class="card-subtitle">System Administrators</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value purple"><?= esc($stats['admin_users'] ?? 0) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="user-filter" role="search" aria-label="User Filters">
                <!-- Filters here as before -->
            </div>

            <div class="table-container">
                <table class="table" aria-describedby="usersTableCaption">
                    <caption id="usersTableCaption">List of users with roles, departments, statuses, last login, and actions</caption>
                    <thead>
                        <tr>
                            <th scope="col">User</th>
                            <th scope="col">Role</th>
                            <th scope="col">Department</th>
                            <th scope="col">Status</th>
                            <th scope="col">Last Login</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <?php if (!empty($users) && is_array($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <tr class="user-row">
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 1rem;">
                                            <div class="user-avatar" aria-label="User initials" title="User initials">
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
                                        <span class="role-badge role-<?= str_replace('_', '-', esc($user['role'] ?? 'user')) ?>">
                                            <?= ucfirst(str_replace('_', ' ', esc($user['role'] ?? 'user'))) ?>
                                        </span>
                                    </td>
                                    <td><?= esc($user['department'] ?? 'N/A') ?></td>
                                    <td>
                                        <i class="fas fa-circle status-<?= esc($user['status'] ?? 'inactive') ?>" aria-hidden="true"></i> 
                                        <?= ucfirst(esc($user['status'] ?? 'inactive')) ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $lastLogin = $user['updated_at'] ?? $user['created_at'] ?? null;
                                            if ($lastLogin): 
                                                $diff = time() - strtotime($lastLogin);
                                                if ($diff < 3600): 
                                                    echo 'Less than 1 hour ago';
                                                elseif ($diff < 86400): 
                                                    echo floor($diff / 3600) . ' hours ago';
                                                else: 
                                                    echo date('M j, Y', strtotime($lastLogin));
                                                endif;
                                            else: 
                                                echo 'Never';
                                            endif;
                                        ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="action-btn btn-edit" onclick="editUser(<?= esc($user['user_id']) ?>)" aria-label="Edit User <?= esc($user['first_name'] . ' ' . $user['last_name']) ?>">
                                                <i class="fas fa-edit" aria-hidden="true"></i> Edit
                                            </button>
                                            <button class="action-btn btn-reset" onclick="resetPassword(<?= esc($user['user_id']) ?>)" aria-label="Reset Password for <?= esc($user['first_name'] . ' ' . $user['last_name']) ?>">
                                                <i class="fas fa-key" aria-hidden="true"></i> Reset
                                            </button>
                                            <button class="action-btn btn-delete" onclick="deleteUser(<?= esc($user['user_id']) ?>)" aria-label="Delete User <?= esc($user['first_name'] . ' ' . $user['last_name']) ?>">
                                                <i class="fas fa-trash" aria-hidden="true"></i> Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2rem;">
                                    <i class="fas fa-users" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;" aria-hidden="true"></i>
                                    <p>No users found.</p>
                                    <?php if (!empty($search) || !empty($roleFilter) || !empty($statusFilter)): ?>
                                        <button onclick="clearFilters()" class="btn btn-secondary" aria-label="Clear Filters">
                                            <i class="fas fa-times" aria-hidden="true"></i> Clear Filters
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
    <script>
function deleteUser(id) {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        // Create a form to submit GET request to the delete route
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = '<?= base_url('admin/users/delete/') ?>' + id;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

    

         

<!-- Add User Modal -->
<div id="addUserModal" class="modal" style="display: none; position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgb(0,0,0); background-color: rgba(0,0,0,0.4);">
    <div class="modal-content" style="background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 500px;">
        <span class="close" onclick="closeAddUserModal()" style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
        <h2>Add New User</h2>
        <form id="addUserForm" action="<?= base_url('admin/users/saveUser') ?>" method="post">
            <?= csrf_field() ?>
            <div style="margin-bottom: 15px;">
                <label for="staff_id">Select Staff:</label>
                <select name="staff_id" id="staff_id" required style="width: 100%; padding: 8px; margin-top: 5px;">
                    <option value="">Choose Staff</option>
                    <?php if (!empty($staff) && is_array($staff)): ?>
                        <?php foreach ($staff as $s): ?>
                            <option value="<?= esc($s['staff_id']) ?>"><?= esc($s['first_name'] . ' ' . $s['last_name'] . ' (' . $s['employee_id'] . ')') ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div style="margin-bottom: 15px;">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required style="width: 100%; padding: 8px; margin-top: 5px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required style="width: 100%; padding: 8px; margin-top: 5px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" required style="width: 100%; padding: 8px; margin-top: 5px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label for="role">Role:</label>
                <select name="role" id="role" required style="width: 100%; padding: 8px; margin-top: 5px;">
                    <option value="">Select Role</option>
                    <option value="admin">Admin</option>
                    <option value="doctor">Doctor</option>
                    <option value="nurse">Nurse</option>
                    <option value="receptionist">Receptionist</option>
                    <option value="laboratorist">Laboratorist</option>
                    <option value="pharmacist">Pharmacist</option>
                    <option value="accountant">Accountant</option>
                    <option value="it_staff">IT Staff</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Add User</button>
        </form>
    </div>
</div>

<script>
function openAddUserModal() {
    document.getElementById('addUserModal').style.display = 'block';
}

function closeAddUserModal() {
    document.getElementById('addUserModal').style.display = 'none';
}

function deleteUser(id) {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        // Create a form to submit GET request to the delete route
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = '<?= base_url('admin/users/delete/') ?>' + id;
        document.body.appendChild(form);
        form.submit();
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('addUserModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

</body>
</html>
