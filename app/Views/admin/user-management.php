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

            <?php if (session()->getFlashdata('success') || session()->getFlashdata('error')): ?>
                <div id="flashNotice" role="alert" aria-live="polite" style="
                    margin-top: 1rem; padding: 0.75rem 1rem; border-radius: 8px;
                    border: 1px solid <?= session()->getFlashdata('success') ? '#86efac' : '#fecaca' ?>;
                    background: <?= session()->getFlashdata('success') ? '#dcfce7' : '#fee2e2' ?>;
                    color: <?= session()->getFlashdata('success') ? '#166534' : '#991b1b' ?>; display:flex; align-items:center; gap:0.5rem;">
                    <i class="fas <?= session()->getFlashdata('success') ? 'fa-check-circle' : 'fa-exclamation-triangle' ?>" aria-hidden="true"></i>
                    <span>
                        <?= esc(session()->getFlashdata('success') ?: session()->getFlashdata('error')) ?>
                    </span>
                    <button type="button" onclick="dismissFlash()" aria-label="Dismiss notification" style="margin-left:auto; background:transparent; border:none; cursor:pointer; color:inherit;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <script>
                    function dismissFlash(){ const n = document.getElementById('flashNotice'); if(n){ n.style.display='none'; } }
                    setTimeout(() => dismissFlash(), 4000);
                </script>
            <?php endif; ?>

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

    

         

<!-- Add User Modal (styled like Add Staff) -->
<style>
/* Modal and form styles adapted from staff-management */
.hms-modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.55); display: none; align-items: center; justify-content: center; padding: 1rem; z-index: 9990; }
.hms-modal-overlay.active { display: flex; }
.hms-modal { width: 100%; max-width: 900px; background: #fff; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); overflow: hidden; border: 1px solid #f1f5f9; position: fixed; left: 50%; top: 50%; transform: translate(-50%, -50%); max-height: 90vh; overflow: auto; box-sizing: border-box; }
.hms-modal-header { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; border-bottom: 1px solid #e5e7eb; background: #f8f9ff; }
.hms-modal-title { font-weight: 600; color: #1e293b; display: flex; align-items: center; gap: 0.5rem; }
.hms-modal-body { padding: 1rem 1.25rem; color: #475569; }
.hms-modal-actions { display: flex; gap: 0.5rem; justify-content: flex-end; padding: 0.75rem 1.25rem 1.25rem; background: #fff; }
.form-input, .form-select, .form-textarea { width: 100%; border: 1px solid #e5e7eb; border-radius: 8px; padding: 0.6rem 0.75rem; font-size: 0.95rem; background: #fff; transition: border-color 0.2s; }
.form-input:focus, .form-select:focus, .form-textarea:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
.form-label { font-size: 0.9rem; color: #374151; margin-bottom: 0.25rem; display: block; font-weight: 500; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.form-grid .full { grid-column: 1 / -1; }
@media (max-width: 640px) { .form-grid { grid-template-columns: 1fr; } }
</style>
<div id="addUserModal" class="hms-modal-overlay" aria-hidden="true">
  <div class="hms-modal" role="dialog" aria-modal="true" aria-labelledby="addUserTitle">
    <div class="hms-modal-header">
      <div class="hms-modal-title" id="addUserTitle">
        <i class="fas fa-user-plus" style="color:#4f46e5"></i>
        Add User
      </div>
      <button type="button" class="btn btn-secondary btn-small" onclick="closeAddUserModal()" aria-label="Close">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <form id="addUserForm" action="<?= base_url('admin/users/saveUser') ?>" method="post">
      <?= csrf_field() ?>
      <div class="hms-modal-body">
        <div class="form-grid">
          <div class="full">
            <label class="form-label" for="staff_id">Select Staff</label>
            <select name="staff_id" id="staff_id" class="form-select" required>
              <option value="">Choose Staff</option>
              <?php if (!empty($staff) && is_array($staff)): ?>
                <?php foreach ($staff as $s): ?>
                  <option 
                    value="<?= esc($s['staff_id']) ?>"
                    data-first-name="<?= esc($s['first_name'] ?? '') ?>"
                    data-last-name="<?= esc($s['last_name'] ?? '') ?>"
                    data-email="<?= esc($s['email'] ?? '') ?>"
                  >
                    <?= esc(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? '') . ' (' . ($s['employee_id'] ?? 'N/A') . ')') ?>
                  </option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>
          <div>
            <label class="form-label" for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" class="form-input" readonly>
          </div>
          <div>
            <label class="form-label" for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" class="form-input" readonly>
          </div>
          <div class="full">
            <label class="form-label" for="email">Email</label>
            <input type="email" name="email" id="email" class="form-input" required readonly>
          </div>
          <div>
            <label class="form-label" for="username">Username</label>
            <input type="text" name="username" id="username" class="form-input" required>
          </div>
          <div>
            <label class="form-label" for="role">Role</label>
            <select name="role" id="role" class="form-select" required>
              <option value="" disabled selected>Select role</option>
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
          <div>
            <label class="form-label" for="password">Password</label>
            <input type="password" name="password" id="password" class="form-input" required>
          </div>
          <div>
            <label class="form-label" for="confirm_password">Confirm Password</label>
            <input type="password" name="confirm_password" id="confirm_password" class="form-input" required>
          </div>
          <div>
            <label class="form-label" for="status">Status</label>
            <select name="status" id="status" class="form-select" required>
              <option value="active" selected>Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
      </div>
      <div class="hms-modal-actions">
        <button type="button" class="btn btn-secondary" onclick="closeAddUserModal()">Cancel</button>
        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save</button>
      </div>
    </form>
  </div>
</div>

<script>
function populateFromStaff() {
    const sel = document.getElementById('staff_id');
    if (!sel) return;
    const opt = sel.options[sel.selectedIndex];
    const first = document.getElementById('first_name');
    const last = document.getElementById('last_name');
    const email = document.getElementById('email');
    if (!opt || !sel.value) {
        if (first) first.value = '';
        if (last) last.value = '';
        if (email) email.value = '';
        return;
    }
    if (first) first.value = opt.getAttribute('data-first-name') || '';
    if (last) last.value = opt.getAttribute('data-last-name') || '';
    if (email) email.value = opt.getAttribute('data-email') || '';
}

function openAddUserModal() {
    const overlay = document.getElementById('addUserModal');
    if (overlay) overlay.classList.add('active');
    // Populate immediately in case a staff is already selected
    populateFromStaff();
}

function closeAddUserModal() {
    const overlay = document.getElementById('addUserModal');
    if (overlay) overlay.classList.remove('active');
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

// Close modal when clicking the overlay background
window.addEventListener('click', function(event) {
    const overlay = document.getElementById('addUserModal');
    if (overlay && event.target === overlay) {
        overlay.classList.remove('active');
    }
});

// Populate fields when staff changes
document.getElementById('staff_id')?.addEventListener('change', populateFromStaff);
</script>

</body>
</html>
