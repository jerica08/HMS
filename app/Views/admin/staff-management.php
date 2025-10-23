<!DOCTYPE html>
<html lang="en">
<head>
     <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management - HMS Admin</title>
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
                <h1 class="page-title">Staff Management</h1>
                <div class="page-actions">
                    <button type="button" id="openAddStaffBtn" class="btn btn-primary" onclick="openAddStaffModal()" aria-label="Add New Staff">
                        <i class="fas fa-plus" aria-hidden="true"></i> Add New Staff
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
                    <!--Total Staff Cards-->
                    <div class="overview-card" tabindex="0">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue">
                                <i class="fas fa-users"></i>
                            </div>
                            <script id="staff-config" type="application/json">
<?= json_encode([
    'staffApiUrl'    => base_url('admin/staff-management/api'),
    'staffGetBase'   => base_url('admin/staff-management/staff'),
    'staffCreateUrl' => base_url('admin/staff-management/create'),
    'staffUpdateUrl' => base_url('admin/staff-management/update'),
    'csrfToken'      => csrf_token(),
    'csrfHash'       => csrf_hash(),
], JSON_UNESCAPED_SLASHES) ?>
                            </script>
                            <script>
                            function dismissFlash() {
                                const flashNotice = document.getElementById('flashNotice');
                                if (flashNotice) {
                                    flashNotice.style.display = 'none';
                                }
                            }
                            
                            function clearFilters() {
                                // Reset any filter controls if they exist
                                const statusFilter = document.getElementById('statusFilter');
                                const roleFilter = document.getElementById('roleFilter');
                                const searchFilter = document.getElementById('searchFilter');
                                
                                if (statusFilter) statusFilter.value = '';
                                if (roleFilter) roleFilter.value = '';
                                if (searchFilter) searchFilter.value = '';
                                
                                // Reload page to clear filters
                                window.location.href = window.location.pathname;
                            }
                            
                            function deleteStaff(staffId) {
                                if (!confirm('Are you sure you want to delete this staff member? This action cannot be undone.')) {
                                    return;
                                }
                                
                                // Redirect to delete URL
                                window.location.href = '<?= base_url('admin/staff-management/delete/') ?>' + staffId;
                            }
                            </script>
                            <script src="<?= base_url('js/admin/staff-management.js') ?>"></script>
                            <div class="card-info">
                                <h3 class="card-title-modern">Total Staff</h3>
                                <p class="card-subtitle">Active Employees</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= esc($total_staff) ?></div>
                            </div>
                        </div>
                    </div>
                </div>


        
                <div class="staff-filter" role="search" aria-label="Staff Filters">
                    <!-- Filters here as before -->
                </div>

                <div class="staff-table">
                    <div class="table-header">
                        <h3>Staff</h3>
                    </div>
                    <table class="table" aria-describedby="staffTableCaption">
                        <thead>
                            <tr>
                                <th scope="col">Staff</th>
                                <th scope="col">Role</th>
                                <th scope="col">Department</th>
                                <th scope="col">Status</th>
                                <th scope="col">Date Joined</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="staffTableBody">
                            <?php
                            if (!empty($staff) && is_array($staff)) {
                                usort($staff, function($a, $b) {
                                    $aCreated = $a['created_at'] ?? null;
                                    $bCreated = $b['created_at'] ?? null;
                                    if ($aCreated && $bCreated) {
                                        return strtotime($bCreated) <=> strtotime($aCreated); // newest first
                                    }
                                    // Fallback: sort by staff_id desc (newest IDs first)
                                    return ((int)($b['staff_id'] ?? 0)) <=> ((int)($a['staff_id'] ?? 0));
                                });
                            }
                            ?>
                            <?php if (!empty($staff) && is_array($staff)): ?>
                                <?php foreach ($staff as $s): ?>
                                    <tr class="staff-row">
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                <div>
                                                    <div style="font-weight: 600;">
                                                        <?= esc(trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''))) ?>
                                                    </div>
                                                    <div style="font-size: 0.8rem; color: #6b7280;">
                                                        <?= esc($s['email'] ?? 'No email') ?>
                                                    </div>
                                                    <div style="font-size: 0.8rem; color: #6b7280;">
                                                        ID: <?= esc($s['employee_id'] ?? $s['staff_id'] ?? 'N/A') ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php $role = $s['role'] ?? $s['designation'] ?? 'staff'; ?>
                                            <span class="role-badge role-<?= str_replace('_', '-', esc($role)) ?>">
                                                <?= ucfirst(str_replace('_', ' ', esc($role))) ?>
                                            </span>
                                        </td>
                                        <td><?= esc($s['department'] ?? 'N/A') ?></td>
                                        <td>
                                            <i class="fas fa-circle status-<?= esc($s['status'] ?? 'active') ?>" aria-hidden="true"></i> 
                                            <?= ucfirst(esc($s['status'] ?? 'Active')) ?>
                                        </td>
                                        <td>
                                            <?php
                                                $dateJoined = $s['date_joined'] ?? $s['created_at'] ?? null;
                                                echo $dateJoined ? date('M j, Y', strtotime($dateJoined)) : 'N/A';
                                            ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-warning btn-small" onclick="openStaffEditModal(<?= esc($s['staff_id']) ?>)" aria-label="Edit Staff <?= esc(trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''))) ?>">
                                                    <i class="fas fa-edit" aria-hidden="true"></i> Edit
                                                </button>
                                                <button class="btn btn-primary btn-small" onclick="openStaffViewModal(<?= esc($s['staff_id']) ?>)" aria-label="View Staff <?= esc(trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''))) ?>">
                                                    <i class="fas fa-eye" aria-hidden="true"></i> View
                                                </button>
                                                <button class="btn btn-danger btn-small" onclick="deleteStaff(<?= esc($s['staff_id']) ?>)" aria-label="Delete Staff <?= esc(trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''))) ?>">
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
                                        <p>No staff found.</p>
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

                <!-- View Staff Modal -->
                <div id="viewStaffModal" class="hms-modal-overlay" aria-hidden="true">
                    <div class="hms-modal" role="dialog" aria-modal="true" aria-labelledby="viewStaffTitle">
                        <div class="hms-modal-header">
                            <div class="hms-modal-title" id="viewStaffTitle">
                                <i class="fas fa-id-badge" style="color:#4f46e5"></i>
                                Staff Details
                            </div>
                            <button type="button" class="btn btn-secondary btn-small" onclick="closeStaffViewModal()" aria-label="Close">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="hms-modal-body">
                            <div class="form-grid">
                                <div class="full">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" id="v_full_name" class="form-input" readonly disabled>
                                </div>
                                <div>
                                    <label class="form-label">Role</label>
                                    <input type="text" id="v_role_input" class="form-input" readonly disabled>
                                </div>
                                <div>
                                    <label class="form-label">Department</label>
                                    <input type="text" id="v_department_input" class="form-input" readonly disabled>
                                </div>
                                <div>
                                    <label class="form-label">Email</label>
                                    <input type="email" id="v_email_input" class="form-input" readonly disabled>
                                </div>
                                <div>
                                    <label class="form-label">Contact No</label>
                                    <input type="text" id="v_contact_input" class="form-input" readonly disabled>
                                </div>
                                <div>
                                    <label class="form-label">Gender</label>
                                    <input type="text" id="v_gender_input" class="form-input" readonly disabled>
                                </div>
                                <div>
                                    <label class="form-label">Date of Birth</label>
                                    <input type="text" id="v_dob_input" class="form-input" readonly disabled>
                                </div>
                                <div class="full">
                                    <label class="form-label">Address</label>
                                    <textarea id="v_address_input" class="form-textarea" rows="2" readonly disabled></textarea>
                                </div>
                                <input type="hidden" id="v_staff_id" value="">
                            </div>
                        </div>
                        <div class="hms-modal-actions">
                            <button type="button" class="btn btn-secondary" onclick="closeStaffViewModal()">Close</button>
                        </div>
                    </div>
                </div>

                <!-- Edit Staff Modal -->
                <div id="editStaffModal" class="hms-modal-overlay" aria-hidden="true">
                    <div class="hms-modal" role="dialog" aria-modal="true" aria-labelledby="editStaffTitle">
                        <div class="hms-modal-header">
                            <div class="hms-modal-title" id="editStaffTitle">
                                <i class="fas fa-user-edit" style="color:#4f46e5"></i>
                                Edit Staff
                            </div>
                            <button type="button" class="btn btn-secondary btn-small" onclick="closeStaffEditModal()" aria-label="Close">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <form id="editStaffForm" method="post" action="#">
                            <?= csrf_field() ?>
                            <input type="hidden" id="e_staff_id" name="staff_id" value="">
                            <div class="hms-modal-body">
                                <div class="form-grid">
                                    <div>
                                        <label class="form-label" for="e_first_name">First Name</label>
                                        <input type="text" id="e_first_name" name="first_name" class="form-input" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="e_last_name">Last Name</label>
                                        <input type="text" id="e_last_name" name="last_name" class="form-input">
                                    </div>
                                    <div>
                                        <label class="form-label" for="e_gender">Gender</label>
                                        <select id="e_gender" name="gender" class="form-select">
                                            <option value="">Select gender</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label" for="e_dob">Date of Birth</label>
                                        <input type="date" id="e_dob" name="dob" class="form-input">
                                    </div>
                                    <div>
                                        <label class="form-label" for="e_contact_no">Contact Number</label>
                                        <input type="text" id="e_contact_no" name="contact_no" class="form-input">
                                    </div>
                                    <div>
                                        <label class="form-label" for="e_email">Email</label>
                                        <input type="email" id="e_email" name="email" class="form-input">
                                    </div>
                                    <div class="full">
                                        <label class="form-label" for="e_address">Address</label>
                                        <textarea id="e_address" name="address" class="form-textarea" rows="2"></textarea>
                                    </div>
                                    <div>
                                        <label class="form-label" for="e_department">Department</label>
                                        <input type="text" id="e_department" name="department" class="form-input">
                                    </div>
                                    <div>
                                        <label class="form-label" for="e_designation">Designation / Role</label>
                                        <select id="e_designation" name="designation" class="form-select">
                                            <option value="">Select designation</option>
                                            <option value="admin">Admin</option>
                                            <option value="doctor">Doctor</option>
                                            <option value="nurse">Nurse</option>
                                            <option value="pharmacist">Pharmacist</option>
                                            <option value="receptionist">Receptionist</option>
                                            <option value="laboratorist">Laboratorist</option>
                                            <option value="it_staff">IT Staff</option>
                                            <option value="accountant">Accountant</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="hms-modal-actions">
                                <button type="button" class="btn btn-secondary" onclick="closeStaffEditModal()">Cancel</button>
                                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Add Staff Modal -->
                <div id="addStaffModal" class="hms-modal-overlay" aria-hidden="true">
                    <div class="hms-modal" role="dialog" aria-modal="true" aria-labelledby="addStaffTitle">
                        <div class="hms-modal-header">
                            <div class="hms-modal-title" id="addStaffTitle">
                                <i class="fas fa-user-plus" style="color:#4f46e5"></i>
                                Add Staff
                            </div>
                            <button type="button" class="btn btn-secondary btn-small" onclick="closeAddStaffModal()" aria-label="Close">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <form id="addStaffForm" method="post" action="<?= base_url('admin/staff/create') ?>">
                            <?= csrf_field() ?>
                            <div class="hms-modal-body">
                                <div class="form-grid">
                                    <div>
                                        <label class="form-label" for="employee_id">Employee ID</label>
                                        <input type="text" id="employee_id" name="employee_id" class="form-input" placeholder="e.g., DOC003">
                                    </div>
                                    <div class="first_name">
                                        <label class="form-label" for="first_name">First Name</label>
                                        <input type="text" id="first_name" name="first_name" class="form-input" placeholder="e.g., Juan " required>
                                    </div>
                                    <div class="last_name">
                                        <label class="form-label" for="last_name">Last Name</label>
                                        <input type="text" id="last_name" name="last_name" class="form-input" placeholder="e.g., Dela Cruz" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="gender">Gender</label>
                                        <select id="gender" name="gender" class="form-select">
                                            <option value="" selected>Select gender</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label" for="dob">Date of Birth</label>
                                        <input type="date" id="dob" name="dob" class="form-input">
                                    </div>
                                    <div>
                                        <label class="form-label" for="contact_no">Contact Number</label>
                                        <input type="text" id="contact_no" name="contact_no" class="form-input">
                                    </div>
                                    <div>
                                        <label class="form-label" for="email">Email (optional)</label>
                                        <input type="email" id="email" name="email" class="form-input" placeholder="name@example.com">
                                    </div>
                                    <div class="full">
                                        <label class="form-label" for="address">Address</label>
                                        <textarea id="address" name="address" rows="2" class="form-textarea" placeholder="House No., Street, Barangay, City, Province, ZIP"></textarea>
                                    </div>
                                    <div>
                                        <label class="form-label" for="department">Department</label>
                                        <select id="department" name="department" class="form-select">
                                            <option value="" selected>Select department</option>
                                            <option value="Administration">Administration</option>
                                            <option value="Emergency">Emergency</option>
                                            <option value="Cardiology">Cardiology</option>
                                            <option value="Intensive Care Unit">Intensive Care Unit</option>
                                            <option value="Outpatient">Outpatient</option>
                                            <option value="Pharmacy">Pharmacy</option>
                                            <option value="Laboratory">Laboratory</option>
                                            <option value="Radiology">Radiology</option>
                                            <option value="Pediatrics">Pediatrics</option>
                                            <option value="Surgery">Surgery</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label" for="designation">Designation / Role</label>
                                        <select id="designation" name="designation" class="form-select" required>
                                            <option value="" disabled selected>Select designation</option>
                                            <option value="admin">Admin</option>
                                            <option value="doctor">Doctor</option>
                                            <option value="nurse">Nurse</option>
                                            <option value="pharmacist">Pharmacist</option>
                                            <option value="receptionist">Receptionist</option>
                                            <option value="laboratorist">Laboratorist</option>
                                            <option value="it_staff">IT Staff</option>
                                            <option value="accountant">Accountant</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Role-specific fields (hidden by default) -->
                                    <div class="full role-fields" id="role-fields-doctor" style="display:none;">
                                        <div class="form-grid">
                                            <div>
                                                <label class="form-label" for="doctor_specialization">Specialization</label>
                                                <input type="text" id="doctor_specialization" name="doctor_specialization" class="form-input" placeholder="e.g., Cardiology">
                                            </div>
                                            <div>
                                                <label class="form-label" for="doctor_license_no">License No</label>
                                                <input type="text" id="doctor_license_no" name="doctor_license_no" class="form-input" placeholder="Optional">
                                            </div>
                                            <div class="full">
                                                <label class="form-label" for="doctor_consultation_fee">Consultation Fee</label>
                                                <input type="number" step="0.01" id="doctor_consultation_fee" name="doctor_consultation_fee" class="form-input" placeholder="e.g., 500.00">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="full role-fields" id="role-fields-nurse" style="display:none;">
                                        <div class="form-grid">
                                            <div>
                                                <label class="form-label" for="nurse_license_no">License No</label>
                                                <input type="text" id="nurse_license_no" name="nurse_license_no" class="form-input">
                                            </div>
                                            <div>
                                                <label class="form-label" for="nurse_specialization">Specialization</label>
                                                <input type="text" id="nurse_specialization" name="nurse_specialization" class="form-input" placeholder="Optional">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="full role-fields" id="role-fields-pharmacist" style="display:none;">
                                        <div class="form-grid">
                                            <div>
                                                <label class="form-label" for="pharmacist_license_no">License No</label>
                                                <input type="text" id="pharmacist_license_no" name="pharmacist_license_no" class="form-input">
                                            </div>
                                            <div>
                                                <label class="form-label" for="pharmacist_specialization">Specialization</label>
                                                <input type="text" id="pharmacist_specialization" name="pharmacist_specialization" class="form-input" placeholder="Optional">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="full role-fields" id="role-fields-laboratorist" style="display:none;">
                                        <div class="form-grid">
                                            <div>
                                                <label class="form-label" for="laboratorist_license_no">License No</label>
                                                <input type="text" id="laboratorist_license_no" name="laboratorist_license_no" class="form-input">
                                            </div>
                                            <div>
                                                <label class="form-label" for="laboratorist_specialization">Specialization</label>
                                                <input type="text" id="laboratorist_specialization" name="laboratorist_specialization" class="form-input" placeholder="Optional">
                                            </div>
                                            <div>
                                                <label class="form-label" for="laboratorist_lab_room_no">Lab Room No</label>
                                                <input type="text" id="laboratorist_lab_room_no" name="laboratorist_lab_room_no" class="form-input" placeholder="Optional">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="full role-fields" id="role-fields-accountant" style="display:none;">
                                        <div class="form-grid">
                                            <div class="full">
                                                <label class="form-label" for="accountant_license_no">License No</label>
                                                <input type="text" id="accountant_license_no" name="accountant_license_no" class="form-input">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="full role-fields" id="role-fields-receptionist" style="display:none;">
                                        <div class="form-grid">
                                            <div class="full">
                                                <label class="form-label" for="receptionist_desk_no">Desk No</label>
                                                <input type="text" id="receptionist_desk_no" name="receptionist_desk_no" class="form-input" placeholder="Optional">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="full role-fields" id="role-fields-it_staff" style="display:none;">
                                        <div class="form-grid">
                                            <div class="full">
                                                <label class="form-label" for="it_expertise">Expertise</label>
                                                <input type="text" id="it_expertise" name="it_expertise" class="form-input" placeholder="Optional">
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label" for="date_joined">Date of Joining</label>
                                        <input type="date" id="date_joined" name="date_joined" class="form-input">
                                    </div>
                                </div>
                            </div>
                            <div class="hms-modal-actions">
                                <button type="button" class="btn btn-secondary" onclick="closeAddStaffModal()">Cancel</button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                

              <!-- Assign Shift Modal (Doctors only) -->
                <div id="assignShiftModal" class="hms-modal-overlay" aria-hidden="true">
                    <div class="hms-modal" role="dialog" aria-modal="true" aria-labelledby="assignShiftTitle">
                        <div class="hms-modal-header">
                            <div class="hms-modal-title" id="assignShiftTitle">
                                <i class="fas fa-user-md" style="color:#4f46e5"></i>
                                Assign Shift (Doctors)
                            </div>
                            <button type="button" class="btn btn-secondary btn-small" onclick="closeAssignShiftModal()" aria-label="Close">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <form id="assignShiftForm" method="post" action="#">
                            <?= csrf_field() ?>
                            <div class="hms-modal-body">
                                <div class="staff-alert" id="assignShiftInfo" style="display:none">
                                    <div class="alert-header"><i class="fas fa-info-circle"></i> Info</div>
                                    <div class="alert-content">This assignment modal is limited to doctors. Only doctors will appear in the list.</div>
                                </div>
                                <div class="form-grid">
                                    <div>
                                        <label for="doctor_id" class="form-label">Doctor Name</label>
                                        <select id="doctor_id" name="doctor_id" class="form-select" required>
                                            <option value="">Loading doctors...</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="department" class="form-label">Department</label>
                                        <select id="department" name="department" class="form-select">
                                            <option value="" selected>Select department</option>
                                            <option value="Emergency">Emergency</option>
                                            <option value="Cardiology">Cardiology</option>
                                            <option value="Intensive Care Unit">Intensive Care Unit</option>
                                            <option value="Outpatient">Outpatient</option>
                                            <option value="Pharmacy">Pharmacy</option>
                                            <option value="Laboratory">Laboratory</option>
                                            <option value="Radiology">Radiology</option>
                                            <option value="Pediatrics">Pediatrics</option>
                                            <option value="Surgery">Surgery</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="shift_date" class="form-label">Date</label>
                                        <input type="date" id="shift_date" name="shift_date" class="form-input" required>
                                    </div>
                                    <div>
                                        <label for="shift_type" class="form-label">Shift Type</label>
                                        <select id="shift_type" name="shift_type" class="form-select">
                                            <option value="" selected>Select shift type</option>
                                            <option value="Morning">Morning</option>
                                            <option value="Afternoon">Afternoon</option>
                                            <option value="Night">Night</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="start_time" class="form-label">Start Time</label>
                                        <input type="time" id="start_time" name="shift_start" class="form-input" required>
                                    </div>
                                    <div>
                                        <label for="end_time" class="form-label">End Time</label>
                                        <input type="time" id="end_time" name="shift_end" class="form-input" required>
                                    </div>
                                    <div class="full">
                                        <label for="room_ward" class="form-label">Room/Ward</label>
                                        <select id="room_ward" name="room_ward" class="form-select">
                                            <option value="" selected>Select room/ward</option>
                                            <option value="ER-1">Emergency ER-1</option>
                                            <option value="Cardio A-12">Cardio A-12</option>
                                            <option value="ICU 1">ICU 1</option>
                                            <option value="ICU 2">ICU 2</option>
                                            <option value="OPD 1">OPD 1</option>
                                            <option value="OR-2">Surgery OR-2</option>
                                        </select>
                                    </div>
                                    <div class="full">
                                        <label for="notes" class="form-label">Notes</label>
                                        <textarea id="notes" name="notes" class="form-textarea" rows="2" placeholder="Optional notes (e.g., Regular OPD shift)"></textarea>
                                    </div>
                                    <input type="hidden" name="status" value="Scheduled">
                                </div>
                            </div>
                            <div class="hms-modal-actions">
                                <button type="button" class="btn btn-secondary" onclick="closeAssignShiftModal()">Cancel</button>
                                <button type="button" class="btn" onclick="document.getElementById('assignShiftForm')?.reset()">Clear</button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> Assign
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </body>
</html>


