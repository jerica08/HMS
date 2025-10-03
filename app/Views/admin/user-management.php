<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>User Management - HMS Admin</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        /* === Modal styles === */
        #userModal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.4);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        #userModal > div {
            background: #fff;
            padding: 2rem;
            border-radius: 8px;
            max-width: 960px;
            width: 98%;
            margin: auto;
            position: relative;
            max-height: 90vh;
            overflow: auto;
            box-sizing: border-box;
            -webkit-overflow-scrolling: touch;
        }
        #userModal > div > .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 0 1rem 0;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 1rem;
            background: #f8f9ff;
        }
        #userModal > div > .modal-header > div {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: #1e293b;
        }
        #userModal > div > .modal-header > button {
            background: #6b7280;
            color: #fff;
            border: none;
            padding: 0.4rem 0.6rem;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body class="admin">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <?php include APPPATH . 'Views/admin/components/sidebar.php'; ?>

        <main class="content" role="main">
            <h1 class="page-title">User Management</h1>
            <div class="page-actions">
                <button type="button" class="btn btn-primary" onclick="openUserModal()" aria-label="Add New User">
                    <i class="fas fa-plus" aria-hidden="true"></i> Add New User
                </button>
            </div>

            <br />

            <div class="dashboard-overview" role="region" aria-label="Dashboard Overview Cards">
                <!-- Your cards here -->
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
                            <div class="metric-value blue"><?= esc($stats['total_users'] ?? 0) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table class="table" aria-describedby="usersTableCaption">
                    <caption id="usersTableCaption">List of users with roles, departments, statuses, last login, and actions</caption>
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
                        <?php if (!empty($users) && is_array($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:1rem;">
                                            <div class="user-avatar"><?= strtoupper(substr($user['first_name'] ?? 'U',0,1) . substr($user['last_name'] ?? 'U',0,1)) ?></div>
                                            <div>
                                                <div style="font-weight:600;"><?= esc($user['first_name'].' '.$user['last_name']) ?></div>
                                                <div style="font-size:0.8rem;color:#6b7280;"><?= esc($user['user_email']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= ucfirst(esc($user['role'])) ?></td>
                                    <td><?= esc($user['department'] ?? 'N/A') ?></td>
                                    <td><?= esc($user['status'] ?? 'N/A') ?></td>
                                    <td><?= esc($user['updated_at'] ?? 'Never') ?></td>
                                    <td>
                                        <button class="btn btn-edit" onclick="editUser(<?= esc($user['user_id']) ?>)">Edit</button>
                                        <button class="btn btn-delete" onclick="deleteUser(<?= esc($user['user_id']) ?>)">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align:center;">No users found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger">
        <ul>
            <?php foreach(session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>


    <!-- === MODAL (Add User) === -->
<div id="userModal" class="modal">
    <div>
        <div class="modal-header">
            <div>
                <i class="fas fa-user-plus" style="color:#4f46e5"></i>
                <h2 id="modalTitle">Add New User</h2>
            </div>
            <button type="button" onclick="closeUserModal()"><i class="fas fa-times"></i></button>
        </div>
        <!-- Updated form: POST action to saveUser controller method -->
        <form id="userForm" method="post" action="<?= base_url('admin/users/saveUser') ?>">
            <div style="margin-bottom:1rem;">
                <label for="staff_id">Select Staff</label>
                <select id="staff_id" name="staff_id" required>
                    <option value="">-- Select staff to link --</option>
                   <?php if (!empty($staff) &&  is_array($staff)): ?>
                    <?php foreach ($staff as $s): ?>
                      <option value="<?= esc($s['staff_id']) ?>" data-employee-id="<?= esc($s['employee_id']) ?>" data-first-name="<?= esc($s['first_name']) ?>" data-last-name="<?= esc($s['last_name']) ?>" data-email="<?= esc($s['email']) ?>">
                <?= esc($s['first_name'] . ' ' . $s['last_name'] . ' (' . $s['employee_id'] . ')') ?>
                    </option>
                     <?php endforeach; ?>
                <?php endif; ?>
                </select>
            </div>
            <input type="hidden" id="employee_id" name="employee_id" />
            <input type="hidden" id="first_name" name="first_name" />
            <input type="hidden" id="last_name" name="last_name" />
            <input type="hidden" id="email" name="email" />

            <div style="margin-bottom:1rem;">
                <label for="username">Username*</label>
                <input type="text" id="username" name="username" required value="<?= old('username') ?>">
            </div>
            <div style="margin-bottom:1rem;">
                <label for="password">Password*</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div style="margin-bottom:1rem;">
                <label for="confirm_password">Confirm Password*</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <div style="margin-bottom:1rem;">
                <label for="role">Role*</label>
                <select id="role" name="role" required>
                    <option value="">Select Role</option>
                    <option value="admin" <?= old('role') == 'admin' ? 'selected' : '' ?>>Administrator</option>
                    <option value="doctor" <?= old('role') == 'doctor' ? 'selected' : '' ?>>Doctor</option>
                    <option value="nurse" <?= old('role') == 'nurse' ? 'selected' : '' ?>>Nurse</option>
                    <option value="receptionist" <?= old('role') == 'receptionist' ? 'selected' : '' ?>>Receptionist</option>
                    <option value="laboratorist" <?= old('role') == 'laboratorist' ? 'selected' : '' ?>>Laboratory Staff</option>
                    <option value="pharmacist" <?= old('role') == 'pharmacist' ? 'selected' : '' ?>>Pharmacist</option>
                    <option value="accountant" <?= old('role') == 'accountant' ? 'selected' : '' ?>>Accountant</option>
                    <option value="it_staff" <?= old('role') == 'it_staff' ? 'selected' : '' ?>>IT Staff</option>
                </select>
            </div>
            <div style="text-align:right;">
                <button type="submit" class="btn btn-primary">Save User</button>
            </div>
        </form>
    </div>
</div>


    <script>
        // Modal open/close helpers
        (function(){
            var overlay = document.getElementById('userModal');
            window.openUserModal = function(){ overlay.style.display = 'flex'; };
            window.closeUserModal = function(){ overlay.style.display = 'none'; };
            document.addEventListener('click', function(e){ if (e.target === overlay) closeUserModal(); });
            document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeUserModal(); });
        })();
    </script>
</body>
</html>
