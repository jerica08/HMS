<!DOCTYPE html>
<html lang="en">
<head>
     <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management - HMS Admin</title>
    <link rel="stylesheet" href="/assets/css/common.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="admin">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <?php include APPPATH . 'Views/admin/components/sidebar.php'; ?>
      
            <main class="content">
                <h1 class="page-title"> Staff Management</h1>
                <div class="page-actions">
                        <button type="button" id="openAddStaffBtn" class="btn btn-primary" onclick="openAddStaffModal()">
                            <i class="fas fa-plus"></i> Add Staff
                        </button>
                        <button type="button" id="openAssignShiftBtn" class="btn btn-success" onclick="openAssignShiftModal()">
                            <i class="fas fa-plus"></i> Assign Shift
                        </button>
                        <button class="btn btn-warning">
                            <i class="fas fa-plus"></i> Approve Leave
                        </button>
                </div><br>


                <!--Dashboard overview cards-->
                <div class="dashboard-overview">
                    <!--Total Staff Cards-->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue">
                                <i class="fas fa-users"></i>
                            </div>
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

                    <!-- On Duty Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern purple">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="card-info">
                                <h3 class="card-title-modern">On Duty</h3>
                                <p class="card-subtitle">Currently working</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value purple">0</div>
                            </div>
                        </div>   
                    </div>

                    <!-- Overtime Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern purple">
                                <i class="fas fa-user-times"></i>
                            </div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Overtime Hours</h3>
                                <p class="card-subtitle">This week</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value purple">0</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Doctor Shifts Table -->
                <div class="staff-section">
                    <div class="section-header">
                        <div class="section-icon" style="background:#2563eb;">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div>
                            <div class="section-title">Doctor Shifts</div>
                            <div style="color:#6b7280;font-size:0.9rem;">All scheduled doctor shifts</div>
                        </div>
                    </div>

                    <div style="overflow:auto;">
                        <table style="width:100%; border-collapse:collapse; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 4px rgba(0,0,0,0.05);">
                            <thead>
                                <tr style="background:#f8fafc; color:#374151;">
                                    <th style="text-align:left; padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">Doctor</th>
                                    <th style="text-align:left; padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">Date</th>
                                    <th style="text-align:left; padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">Start</th>
                                    <th style="text-align:left; padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">End</th>
                                    <th style="text-align:left; padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">Department</th>
                                    <th style="text-align:left; padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="doctorShiftsBody">
                                <tr>
                                    <td colspan="6" style="text-align:center; color:#6b7280; padding:1rem;">Loading doctor shifts...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div><br>
                <script>
                    window.STAFF_CFG = {
                        apiUrl: '<?= base_url('admin/doctor-shifts/api') ?>',
                        deleteUrl: '<?= base_url('admin/doctor-shifts/delete') ?>',
                        doctorsUrl: '<?= base_url('admin/doctors/api') ?>',
                        shiftsUrl: '<?= base_url('admin/doctor-shifts/api') ?>',
                        createShiftUrl: '<?= base_url('admin/doctor-shifts/create') ?>',
                        shiftShowBase: '<?= base_url('admin/doctor-shifts') ?>',
                        shiftUpdateUrl: '<?= base_url('admin/doctor-shifts/update') ?>',
                        staffApiUrl: '<?= base_url('admin/staff/api') ?>',
                        staffGetBase: '<?= base_url('admin/staff/get') ?>',
                        staffCreateUrl: '<?= base_url('admin/staff/create') ?>',
                        staffUpdateUrl: '<?= base_url('admin/staff/update') ?>',
                        csrfToken: '<?= csrf_token() ?>',
                        csrfHash: '<?= csrf_hash() ?>'
                    };
                </script>
                <script src="<?= base_url('js/admin/staff-management.js') ?>"></script>

                <!-- View/Edit Doctor Shift Modal -->
                <div id="doctorShiftAdminModal" class="hms-modal-overlay" aria-hidden="true">
                    <div class="hms-modal" role="dialog" aria-modal="true" aria-labelledby="doctorShiftAdminTitle">
                        <div class="hms-modal-header">
                            <div class="hms-modal-title" id="doctorShiftAdminTitle">
                                <i class="fas fa-user-md" style="color:#4f46e5"></i>
                                <span id="doctorShiftAdminMode">View Shift</span>
                            </div>
                            <button type="button" class="btn btn-secondary btn-small" onclick="closeDoctorShiftAdminModal()" aria-label="Close">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <form id="doctorShiftAdminForm">
                            <?= csrf_field() ?>
                            <input type="hidden" id="doctor_shift_id" name="id">
                            <div class="hms-modal-body">
                                <div class="form-grid">
                                    <div>
                                        <label class="form-label" for="adm_shift_date">Date</label>
                                        <input type="date" id="adm_shift_date" name="shift_date" class="form-input" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="adm_shift_start">Start</label>
                                        <input type="time" id="adm_shift_start" name="shift_start" class="form-input" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="adm_shift_end">End</label>
                                        <input type="time" id="adm_shift_end" name="shift_end" class="form-input" required>
                                    </div>
                                    <div class="full">
                                        <label class="form-label" for="adm_department">Department</label>
                                        <input type="text" id="adm_department" name="department" class="form-input" placeholder="e.g., Emergency, Cardiology">
                                    </div>
                                </div>
                            </div>
                            <div class="hms-modal-actions">
                                <button type="button" class="btn btn-secondary" onclick="closeDoctorShiftAdminModal()">Close</button>
                                <button type="submit" class="btn btn-success" id="doctorShiftAdminSaveBtn">
                                    <i class="fas fa-save"></i> Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
        
                <!-- Staff List Table -->
                <div class="staff-section">
                    <div class="section-header">
                        <div class="section-icon" style="background:#10b981;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <div class="section-title">Staffs Directory</div>
                            <div style="color:#6b7280;font-size:0.9rem;">All registered staffs</div>
                        </div>
                    </div>

                    <div style="overflow:auto;">
                        <table style="width:100%; border-collapse:collapse; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 4px rgba(0,0,0,0.05);">
                            <thead>
                                <tr style="background:#f8fafc; color:#374151;">
                                    <th style="text-align:left; padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">Name</th>
                                    <th style="text-align:left; padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">Role</th>
                                    <th style="text-align:left; padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">Department</th>
                                    <th style="text-align:left; padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">Email</th>
                                    <th style="text-align:left; padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="staffTableBody">
                                <tr>
                                    <td colspan="5" style="text-align:center; color:#6b7280; padding:1rem;">Loading staff...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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
                                <div class="full"><strong>Name:</strong> <span id="v_name">-</span></div>
                                <div><strong>Role:</strong> <span id="v_role">-</span></div>
                                <div><strong>Department:</strong> <span id="v_department">-</span></div>
                                <div><strong>Email:</strong> <span id="v_email">-</span></div>
                                <div><strong>Contact No:</strong> <span id="v_contact">-</span></div>
                                <div><strong>Gender:</strong> <span id="v_gender">-</span></div>
                                <div><strong>DOB:</strong> <span id="v_dob">-</span></div>
                                <div class="full"><strong>Address:</strong><br><span id="v_address">-</span></div>
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


