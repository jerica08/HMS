<!DOCTYPE html>
<html lang="en">
<head>
     <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shift Management - HMS Admin</title>
    <link rel="stylesheet" href="/assets/css/common.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="admin">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <?php include APPPATH . 'Views/admin/components/sidebar.php'; ?>
      
            <main class="content">
                <h1 class="page-title"> Shifts Management</h1>
                <div class="page-actions">                    
                        <button type="button" id="openAssignShiftBtn" class="btn btn-success" onclick="openAssignShiftModal()">
                            <i class="fas fa-plus"></i> Assign Shift
                        </button>                    
                </div><br>

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


