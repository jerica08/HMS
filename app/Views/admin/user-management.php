<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>User Management - HMS Admin</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
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
  window.USER_MGMT_CFG = {
    getUserBase: '<?= base_url('admin/user-management/user/') ?>',
    resetUserBase: '<?= base_url('admin/user-management/reset-password/') ?>',
    deleteUserBase: '<?= base_url('admin/user-management/delete/') ?>',
    hasErrors: <?= json_encode(!empty(session()->get('errors'))) ?>
  };
</script>
<script src="<?= base_url('js/admin/user-management.js') ?>"></script>

<!-- Add User Modal -->
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
      <form id="addUserForm" action="<?= base_url('admin/user-management/create') ?>" method="post">
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
    <form id="editUserForm" action="<?= base_url('admin/user-management/update') ?>" method="post">
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



</body>
</html>
