<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>User Management - HMS Admin</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
      .table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.95rem; }
      .table thead th { text-align: left; background: #f8fafc; color: #374151; font-weight: 600; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e5e7eb; }
      .table tbody td { padding: 0.75rem 1.25rem; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
      .table tbody tr:last-child td { border-bottom: none; }
      .role-badge { display: inline-block; padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.8rem; font-weight: 600; }
      .role-admin { background: #fee2e2; color: #991b1b; }
      .role-doctor { background: #dbeafe; color: #1e40af; }
      .role-nurse { background: #dcfce7; color: #166534; }
      .role-receptionist { background: #fef3c7; color: #92400e; }
      .role-laboratorist { background: #fae8ff; color: #6b21a8; }
      .role-pharmacist { background: #e0f2fe; color: #075985; }
      .role-accountant { background: #f0fdf4; color: #166534; }
      .role-it-staff { background: #eef2ff; color: #3730a3; }
      .status-active { color: #16a34a; }
      .status-inactive { color: #9ca3af; }
      .action-buttons { display: flex; gap: 0.5rem;  margin-top: 1rem; flex-wrap: wrap; }
      .btn-small { padding: 0.5rem 1rem; font-size: 0.8rem; }
      .dashboard-overview { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 0.75rem; }
      .overview-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 0.75rem; }
      .card-header-modern { display: flex; align-items: center; gap: 0.75rem; }
      .card-icon-modern.blue { background: #eff6ff; color: #1d4ed8; border-radius: 10px; padding: 0.5rem; }
      .card-icon-modern.purple { background: #f5f3ff; color: #7c3aed; border-radius: 10px; padding: 0.5rem; }
      .card-title-modern { margin: 0; font-size: 1rem; }
      .card-subtitle { margin: 0; font-size: 0.8rem; color: #6b7280; }
      .user-table { background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
      .table-header { background: #f8fafc; padding: 1rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
      @media (max-width: 640px) {
        .table thead { display: none; }
        .table tbody tr { display: grid; grid-template-columns: 1fr; gap: 0.25rem; padding: 0.5rem 0; }
        .table tbody td { border: none; padding: 0.25rem 1rem; }
      }
    </style>
    <?php
      // Initialize optional filter vars to avoid notices
      $search = $search ?? null;
      $roleFilter = $roleFilter ?? null;
      $statusFilter = $statusFilter ?? null;
    ?>
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

        <?php $errors = session()->get('errors'); ?>
        <?php if (!empty($errors) && is_array($errors)): ?>
            <div role="alert" aria-live="polite" style="margin-top:0.75rem; padding:0.75rem 1rem; border-radius:8px; border:1px solid #fecaca; background:#fee2e2; color:#991b1b;">
                <div style="font-weight:600; margin-bottom:0.25rem;"><i class="fas fa-exclamation-circle"></i> Please fix the following errors:</div>
                <ul style="margin:0; padding-left:1.25rem;">
                    <?php foreach ($errors as $field => $msg): ?>
                        <li><?= esc(is_array($msg) ? implode(', ', $msg) : $msg) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
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

          <div class="user-table">
              <div class="table-header">
                  <h3>Users</h3>
              </div>
              <table class="table" aria-describedby="usersTableCaption">
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
                      <?php
                        if (!empty($users) && is_array($users)) {
                            usort($users, function($a, $b) {
                                $aCreated = $a['created_at'] ?? null;
                                $bCreated = $b['created_at'] ?? null;
                                if ($aCreated && $bCreated) {
                                    return strtotime($bCreated) <=> strtotime($aCreated); // newest first
                                }
                                // Fallback: sort by user_id desc (newest IDs first)
                                return ((int)($b['user_id'] ?? 0)) <=> ((int)($a['user_id'] ?? 0));
                            });
                        }
                      ?>
                      <?php if (!empty($users) && is_array($users)): ?>
                          <?php foreach ($users as $user): ?>
                              <tr class="user-row">
                                  <td>
                                      <div style="display: flex; align-items: center; gap: 0.5rem;">
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
                                          echo $lastLogin ? date('M j, Y g:i A', strtotime($lastLogin)) : 'Never';
                                      ?>
                                  </td>
                                  <td>
                                      <div class="action-buttons">
                                          <button class="btn btn-warning btn-small" onclick="editUser(<?= esc($user['user_id']) ?>)" aria-label="Edit User <?= esc(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?>">
                                              <i class="fas fa-edit" aria-hidden="true"></i> Edit
                                          </button>
                                          <button class="btn btn-primary btn-small" onclick="resetPassword(<?= esc($user['user_id']) ?>)" aria-label="Reset Password for <?= esc(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?>">
                                              <i class="fas fa-key" aria-hidden="true"></i> Reset
                                          </button>
                                          <button class="btn btn-danger btn-small" onclick="deleteUser(<?= esc($user['user_id']) ?>)" aria-label="Delete User <?= esc(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?>">
                                              <i class="fas fa-trash" aria-hidden="true"></i> Delete
                                          </button>
                                      </div>
                                  </td>
                              </tr>
                          <?php endforeach; ?>
                      <?php else: ?>
                          <tr>
                              <td colspan="6" style="text-align: center; padding: 2rem;">
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
      </main>
</div>
<script>
function editUser(id) {
    fetch('<?= base_url('admin/users/get/') ?>' + id)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert('Error: ' + data.error);
                return;
            }
            // Populate edit modal
            document.getElementById('edit_user_id').value = data.user_id;
            document.getElementById('edit_username').value = data.username;
            document.getElementById('edit_role').value = data.role;
            document.getElementById('edit_status').value = data.status;
            // Show modal
            document.getElementById('editUserModal').classList.add('active');
        })
        .catch(error => {
            console.error('Error fetching user:', error);
            alert('Failed to load user data.');
        });
}

function resetPassword(id) {
    if (!id) return;
    if (!confirm('Reset this user\'s password? A temporary password will be generated.')) return;
    const form = document.createElement('form');
    form.method = 'GET';
    form.action = '<?= base_url('admin/users/reset/') ?>' + id;
    document.body.appendChild(form);
    form.submit();
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
</script>

<!-- Add User Modal -->
<style>
/* Modal and form styles */
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
                    data-designation="<?= esc($s['designation'] ?? '') ?>"
                    data-role="<?= esc($s['role'] ?? '') ?>"
                    <?= old('staff_id') == ($s['staff_id'] ?? null) ? 'selected' : '' ?>
                  >
                    <?= esc(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? '') . ' (' . ($s['employee_id'] ?? 'N/A') . ')') ?>
                  </option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>
          <div>
            <label class="form-label" for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" class="form-input" value="<?= esc(old('first_name') ?? '') ?>" readonly>
          </div>
          <div>
            <label class="form-label" for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" class="form-input" value="<?= esc(old('last_name') ?? '') ?>" readonly>
          </div>
          <div class="full">
            <label class="form-label" for="email">Email</label>
            <input type="email" name="email" id="email" class="form-input" value="<?= esc(old('email') ?? '') ?>" required readonly>
          </div>
          <div>
            <label class="form-label" for="username">Username</label>
            <input type="text" name="username" id="username" class="form-input" value="<?= esc(old('username') ?? '') ?>" required>
          </div>
          <div>
            <label class="form-label" for="role">Role</label>
            <select name="role" id="role" class="form-select" required>
              <option value="" disabled <?= old('role') ? '' : 'selected' ?>>Select role</option>
              <option value="admin" <?= old('role')==='admin'?'selected':'' ?>>Admin</option>
              <option value="doctor" <?= old('role')==='doctor'?'selected':'' ?>>Doctor</option>
              <option value="nurse" <?= old('role')==='nurse'?'selected':'' ?>>Nurse</option>
              <option value="receptionist" <?= old('role')==='receptionist'?'selected':'' ?>>Receptionist</option>
              <option value="laboratorist" <?= old('role')==='laboratorist'?'selected':'' ?>>Laboratorist</option>
              <option value="pharmacist" <?= old('role')==='pharmacist'?'selected':'' ?>>Pharmacist</option>
              <option value="accountant" <?= old('role')==='accountant'?'selected':'' ?>>Accountant</option>
              <option value="it_staff" <?= old('role')==='it_staff'?'selected':'' ?>>IT Staff</option>
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
              <option value="active" <?= old('status')==='inactive'?'':'selected' ?>>Active</option>
              <option value="inactive" <?= old('status')==='inactive'?'selected':'' ?>>Inactive</option>
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
    const roleSel = document.getElementById('role');
    if (!opt || !sel.value) {
        if (first) first.value = '';
        if (last) last.value = '';
        if (email) email.value = '';
        if (roleSel) roleSel.value = '';
        return;
    }
    if (first) first.value = opt.getAttribute('data-first-name') || '';
    if (last) last.value = opt.getAttribute('data-last-name') || '';
    if (email) email.value = opt.getAttribute('data-email') || '';
    // Auto-select role from staff record (prefer specific role, fallback to designation)
    if (roleSel) {
        const autoRole = (opt.getAttribute('data-role') || opt.getAttribute('data-designation') || '').toLowerCase();
        if (autoRole) {
            roleSel.value = autoRole;
        }
    }
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

<?php if (!empty(session()->get('errors'))): ?>
<script>
  // Ensure the modal opens when there are validation errors and repopulate
  window.addEventListener('DOMContentLoaded', function(){
    try { openAddUserModal(); populateFromStaff(); } catch(e) {}
  });
</script>
<?php endif; ?>

<!-- Edit User Modal -->
<div id="editUserModal" class="hms-modal-overlay" aria-hidden="true">
  <div class="hms-modal" role="dialog" aria-modal="true" aria-labelledby="editUserTitle">
    <div class="hms-modal-header">
      <div class="hms-modal-title" id="editUserTitle">
        <i class="fas fa-user-edit" style="color:#4f46e5"></i>
        Edit User
      </div>
      <button type="button" class="btn btn-secondary btn-small" onclick="closeEditUserModal()" aria-label="Close">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <form id="editUserForm" action="<?= base_url('admin/users/updateUser') ?>" method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="user_id" id="edit_user_id">
      <div class="hms-modal-body">
        <div class="form-grid">
          <div>
            <label class="form-label" for="edit_username">Username</label>
            <input type="text" name="username" id="edit_username" class="form-input" required>
          </div>
          <div>
            <label class="form-label" for="edit_role">Role</label>
            <select name="role" id="edit_role" class="form-select" required>
              <option value="" disabled>Select role</option>
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
            <label class="form-label" for="edit_status">Status</label>
            <select name="status" id="edit_status" class="form-select" required>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
      </div>
      <div class="hms-modal-actions">
        <button type="button" class="btn btn-secondary" onclick="closeEditUserModal()">Cancel</button>
        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Update</button>
      </div>
    </form>
  </div>
</div>

<script>
function closeEditUserModal() {
    const overlay = document.getElementById('editUserModal');
    if (overlay) overlay.classList.remove('active');
}

// Close modal when clicking the overlay background
window.addEventListener('click', function(event) {
    const overlay = document.getElementById('editUserModal');
    if (overlay && event.target === overlay) {
        overlay.classList.remove('active');
    }
});
</script>

</body>
</html>
