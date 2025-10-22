<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - HMS <?= ucfirst($userRole) ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/staff-management.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="base-url" content="<?= base_url() ?>">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta name="user-role" content="<?= $userRole ?>">
</head>
<body class="<?= $userRole ?>">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <?php 
        // Role-based sidebar inclusion
        switch ($userRole) {
            case 'admin':
                include APPPATH . 'Views/admin/components/sidebar.php';
                break;
            case 'doctor':
                include APPPATH . 'Views/doctor/components/sidebar.php';
                break;
            case 'nurse':
                include APPPATH . 'Views/nurse/components/sidebar.php';
                break;
            case 'receptionist':
                include APPPATH . 'Views/receptionist/components/sidebar.php';
                break;
        }
        ?>

        <main class="content">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-users-cog"></i>
                    <?php 
                    switch ($userRole) {
                        case 'admin':
                            echo 'Staff Management';
                            break;
                        case 'doctor':
                            echo 'Department Staff';
                            break;
                        case 'nurse':
                            echo 'Department Team';
                            break;
                        case 'receptionist':
                            echo 'Staff Directory';
                            break;
                        default:
                            echo 'Staff Information';
                    }
                    ?>
                </h1>
                
                <?php if ($permissions['canCreate']): ?>
                <div class="page-actions">
                    <button class="btn btn-success" id="addStaffBtn">
                        <i class="fas fa-plus"></i> Add Staff Member
                    </button>
                </div>
                <?php endif; ?>
            </div>

            <!-- Statistics Overview -->
            <div class="dashboard-overview">
                <?php if ($userRole === 'admin'): ?>
                    <!-- Admin Statistics -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-users"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Total Staff</h3>
                                <p class="card-subtitle">All employees</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $staffStats['total_staff'] ?? 0 ?></div>
                                <div class="metric-label">Total</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value green"><?= $staffStats['active_staff'] ?? 0 ?></div>
                                <div class="metric-label">Active</div>
                            </div>
                        </div>
                    </div>


                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern orange"><i class="fas fa-user-friends"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Support Staff</h3>
                                <p class="card-subtitle">Administrative & technical</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value orange"><?= $staffStats['total_receptionists'] ?? 0 ?></div>
                                <div class="metric-label">Receptionists</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value blue"><?= $staffStats['total_pharmacists'] ?? 0 ?></div>
                                <div class="metric-label">Pharmacists</div>
                            </div>
                        </div>
                    </div>

                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern red"><i class="fas fa-chart-line"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Growth</h3>
                                <p class="card-subtitle">New hires</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value red"><?= $staffStats['new_staff_month'] ?? 0 ?></div>
                                <div class="metric-label">This Month</div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($userRole === 'doctor' || $userRole === 'nurse'): ?>
                    <!-- Department Statistics -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-building"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Department Staff</h3>
                                <p class="card-subtitle">Your department</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $staffStats['department_staff'] ?? 0 ?></div>
                                <div class="metric-label">Total</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value green"><?= $staffStats['department_doctors'] ?? 0 ?></div>
                                <div class="metric-label">Doctors</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value purple"><?= $staffStats['department_nurses'] ?? 0 ?></div>
                                <div class="metric-label">Nurses</div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($userRole === 'receptionist'): ?>
                    <!-- Receptionist Statistics -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-user-md"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Medical Staff</h3>
                                <p class="card-subtitle">Available doctors</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $staffStats['total_doctors'] ?? 0 ?></div>
                                <div class="metric-label">Doctors</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value green"><?= $staffStats['active_doctors'] ?? 0 ?></div>
                                <div class="metric-label">Active</div>
                            </div>
                        </div>
                    </div>

                <?php endif; ?>
            </div>

            <!-- Staff Management Table -->
            <div class="staff-table-container">
                <div class="table-controls">
                    <div class="search-filters">
                        <h3>Filter Staff</h3>
                        <div class="filter-row">
                            <div class="filter-group">
                                <label>Search:</label>
                                <input type="text" class="filter-input" id="searchInput" placeholder="Search by name, employee ID, or email">
                            </div>
                            <div class="filter-group">
                                <label>Department:</label>
                                <select class="filter-input" id="departmentFilter">
                                    <option value="">All Departments</option>
                                    <option value="Emergency">Emergency</option>
                                    <option value="Cardiology">Cardiology</option>
                                    <option value="Neurology">Neurology</option>
                                    <option value="Pediatrics">Pediatrics</option>
                                    <option value="Orthopedics">Orthopedics</option>
                                    <option value="General">General</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label>Role:</label>
                                <select class="filter-input" id="roleFilter">
                                    <option value="">All Roles</option>
                                    <option value="doctor">Doctor</option>
                                    <option value="nurse">Nurse</option>
                                    <option value="receptionist">Receptionist</option>
                                    <option value="pharmacist">Pharmacist</option>
                                    <option value="laboratorist">Laboratorist</option>
                                    <option value="accountant">Accountant</option>
                                    <option value="it_staff">IT Staff</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-header">
                    <h3 id="staffTitle">Staff Directory</h3>
                    <div class="table-actions">
                        <?php if ($permissions['canExport']): ?>
                            <button class="btn btn-info btn-sm" id="exportBtn">
                                <i class="fas fa-download"></i> Export
                            </button>
                        <?php endif; ?>
                        <button class="btn btn-secondary btn-sm" id="refreshBtn">
                            <i class="fas fa-sync"></i> Refresh
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Name</th>
                                <th>Role</th>
                                <?php if ($permissions['canViewDepartment']): ?>
                                    <th>Department</th>
                                <?php endif; ?>
                                <th>Contact</th>
                                <th>Email</th>
                                <th>Date Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="staffTableBody">
                            <?php if (!empty($staff)): ?>
                                <?php foreach ($staff as $member): ?>
                                    <tr>
                                        <td><?= esc($member['employee_id'] ?? 'N/A') ?></td>
                                        <td>
                                            <div class="staff-info">
                                                <div class="staff-name"><?= esc($member['full_name']) ?></div>
                                                <div class="staff-id">ID: <?= esc($member['staff_id']) ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="role-badge <?= strtolower($member['designation'] ?? '') ?>">
                                                <?= esc(ucfirst($member['designation'] ?? 'N/A')) ?>
                                            </span>
                                        </td>
                                        <?php if ($permissions['canViewDepartment']): ?>
                                            <td><?= esc($member['department'] ?? 'N/A') ?></td>
                                        <?php endif; ?>
                                        <td><?= esc($member['contact_no'] ?? 'N/A') ?></td>
                                        <td><?= esc($member['email'] ?? 'N/A') ?></td>
                                        <td><?= esc($member['date_joined'] ?? 'N/A') ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <?php if ($permissions['canView']): ?>
                                                    <button class="btn btn-info btn-sm" onclick="viewStaff(<?= $member['staff_id'] ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <?php if ($permissions['canEdit']): ?>
                                                    <button class="btn btn-warning btn-sm" onclick="editStaff(<?= $member['staff_id'] ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <?php if ($permissions['canDelete']): ?>
                                                    <button class="btn btn-danger btn-sm" onclick="deleteStaff(<?= $member['staff_id'] ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="<?= $permissions['canViewDepartment'] ? '8' : '7' ?>" class="text-center">
                                        <div class="no-data">
                                            <i class="fas fa-users"></i>
                                            <p>No staff members found</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modals -->
    <?php if ($permissions['canCreate']): ?>
        <?php include APPPATH . 'Views/staff-management/add-staff-modal.php'; ?>
    <?php endif; ?>
    
    <?php if ($permissions['canView']): ?>
        <?php include APPPATH . 'Views/staff-management/view-staff-modal.php'; ?>
    <?php endif; ?>
    
    <?php if ($permissions['canEdit']): ?>
        <?php include APPPATH . 'Views/staff-management/edit-staff-modal.php'; ?>
    <?php endif; ?>

    <!-- Scripts -->
    <script src="<?= base_url('assets/js/staff-management.js') ?>"></script>

</body>
</html>