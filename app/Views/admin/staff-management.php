<!DOCTYPE html>
<html lang="en">
<head>
     <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management - HMS Admin</title>
    <link rel="stylesheet" href="/assets/css/common.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .staff-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .staff-section {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .section-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .section-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: #3b82f6;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }
        .staff-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .staff-item:last-child {
            border-bottom: none;
        }
        .staff-info {
            flex: 1;
        }
        .staff-name {
            font-weight: 500;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }
        .staff-details {
            font-size: 0.8rem;
            color: #6b7280;
        }
        .staff-status {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-on-duty { background: #dcfce7; color: #166534; }
        .status-off-duty { background: #f3f4f6; color: #6b7280; }
        .status-break { background: #fef3c7; color: #92400e; }
        .status-leave { background: #fecaca; color: #991b1b; }
        .status-overtime { background: #dbeafe; color: #1e40af; }
        .schedule-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.5rem;
            margin-top: 1rem;
        }
        .schedule-day {
            text-align: center;
            padding: 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .day-header {
            background: #f3f4f6;
            color: #6b7280;
            font-weight: 600;
        }
        .day-scheduled {
            background: #dcfce7;
            color: #166534;
        }
        .day-off {
            background: #f3f4f6;
            color: #9ca3af;
        }
        .day-overtime {
            background: #dbeafe;
            color: #1e40af;
        }
        .performance-metric {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .performance-metric:last-child {
            border-bottom: none;
        }
        .metric-label {
            font-weight: 500;
            color: #1f2937;
        }
        .metric-value {
            font-weight: bold;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 2rem;
        }
        .metric-excellent { background: #dcfce7; color: #166534; }
        .metric-good { background: #dbeafe; color: #1e40af; }
        .metric-average { background: #fef3c7; color: #92400e; }
        .metric-poor { background: #fecaca; color: #991b1b; }
        .certification-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background: #f8fafc;
            border-radius: 6px;
            margin: 0.5rem 0;
            font-size: 0.9rem;
        }
        .cert-status {
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .cert-valid { background: #dcfce7; color: #166534; }
        .cert-expiring { background: #fef3c7; color: #92400e; }
        .cert-expired { background: #fecaca; color: #991b1b; }
        .payroll-summary {
            background: #f8fafc;
            border-radius: 6px;
            padding: 1rem;
            margin: 1rem 0;
        }
        .payroll-item {
            display: flex;
            justify-content: space-between;
            margin: 0.5rem 0;
            font-size: 0.9rem;
        }
        .payroll-total {
            font-weight: bold;
            border-top: 1px solid #e2e8f0;
            padding-top: 0.5rem;
            margin-top: 0.5rem;
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }
        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
        }
        .staff-alert {
            background: #fef3c7;
            border: 1px solid #fbbf24;
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .alert-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: #92400e;
            margin-bottom: 0.5rem;
        }
        .alert-content {
            color: #78350f;
            font-size: 0.9rem;
        }
        .quick-actions {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
         .metric-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
        }
        .metric-card.revenue {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .metric-card.patients {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        .metric-card.efficiency {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        
        /* Enhanced modal styles aligned with dashboard theme (prefixed to avoid conflicts) */
        .hms-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            z-index: 9990; /* overlay below modal */
        }
        .hms-modal-overlay.active { display: flex; }
        .hms-modal {
            width: 100%;
            max-width: 96vw; /* ensure it fits within viewport width */
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            overflow: hidden;
            border: 1px solid #f1f5f9;
            display: block !important; /* override any global modal rules */
            position: fixed; /* ensure centered regardless of parent stacking contexts */
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%) !important;
            opacity: 1 !important; /* ensure visible against possible framework defaults */
            visibility: visible !important;
            pointer-events: auto !important;
            min-height: 120px;
            max-height: 90vh; /* keep within viewport height */
            overflow: auto; /* scroll content if too tall */
            box-sizing: border-box;
            outline: 1px solid rgba(79,70,229,0.25); /* debug outline */
        }
        /* On larger screens, allow a comfortably wider modal while still fitting */
        @media (min-width: 1024px) {
            .hms-modal { max-width: 900px; }
        }
        .hms-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #e5e7eb;
            background: #f8f9ff;
        }
        .hms-modal-title {
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .hms-modal-body { 
            padding: 1rem 1.25rem; 
            color: #475569; 
        }
        .hms-modal-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
            padding: 0.75rem 1.25rem 1.25rem;
            background: #fff;
        }
        .form-input, .form-select, .form-textarea {
            width: 100%;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 0.6rem 0.75rem;
            font-size: 0.95rem;
            background: #fff;
            transition: border-color 0.2s;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .form-label { 
            font-size: 0.9rem; 
            color: #374151; 
            margin-bottom: 0.25rem; 
            display: block; 
            font-weight: 500;
        }
        .form-grid { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 0.75rem; 
        }
        .form-grid .full { grid-column: 1 / -1; }
        /* Stack fields on small screens to avoid overflow */
        @media (max-width: 640px) {
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="admin">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <?php include APPPATH . 'Views/admin/components/sidebar.php'; ?>
       

        
            <main class="content">
                <h1 class="page-title"> Staff Management</h1>
                <div class="page-actions">
                        <button type="button" id="openAddStaffBtn" class="btn btn-success" onclick="openAddStaffModal()">
                            <i class="fas fa-plus"></i> Add Staff
                        </button>
                        <button type="button" id="openAssignShiftBtn" class="btn btn-primary" onclick="openAssignShiftModal()">
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
                    (function(){
                        const body = document.getElementById('doctorShiftsBody');
                        function fmt(t){ return t==null || t==='' ? '-' : t; }
                        function rowHtml(r){
                            return `
                                <tr>
                                  <td style="padding:0.75rem 1rem;">${fmt(r.doctor_name)}</td>
                                  <td style="padding:0.75rem 1rem;">${fmt(r.date)}</td>
                                  <td style="padding:0.75rem 1rem;">${fmt(r.start)}</td>
                                  <td style="padding:0.75rem 1rem;">${fmt(r.end)}</td>
                                  <td style="padding:0.75rem 1rem;">${fmt(r.department)}</td>
                                  <td style="padding:0.5rem 1rem;">
                                    <button class="btn btn-primary btn-small" onclick="alert('Edit not implemented yet')"><i class="fas fa-pen"></i> Edit</button>
                                    <button class="btn btn-danger btn-small" onclick="deleteDoctorShift(${Number(r.id)||0})"><i class="fas fa-trash"></i> Delete</button>
                                  </td>
                                </tr>`;
                        }
                        function render(list){
                            if(!Array.isArray(list) || list.length===0){
                                body.innerHTML = '<tr><td colspan="6" style="text-align:center; color:#6b7280; padding:1rem;">No doctor shifts found.</td></tr>';
                                return;
                            }
                            body.innerHTML = list.map(rowHtml).join('');
                        }
                        window.deleteDoctorShift = function(id){
                            if(!id || !confirm('Delete this shift?')) return;
                            const p = new URLSearchParams();
                            p.append('id', id);
                            try { p.append('<?= csrf_token() ?>','<?= csrf_hash() ?>'); } catch(e){}
                            fetch('<?= base_url('admin/doctor-shifts/delete') ?>', { method:'POST', headers:{ 'Accept':'application/json' }, body:p })
                              .then(r=>r.json().catch(()=>({status:'error'})))
                              .then(res=>{ if(res && res.status==='success'){ load(); } else { alert('Failed to delete shift'); } })
                              .catch(()=>alert('Failed to delete shift'));
                        };
                        function load(){
                            fetch('<?= base_url('admin/doctor-shifts/api') ?>', { headers:{ 'Accept':'application/json' }})
                              .then(r=>r.json())
                              .then(res=>{ if(res && res.status==='success'){ render(res.data||[]); } else { render([]); } })
                              .catch(()=>{ render([]); });
                        }
                        document.addEventListener('DOMContentLoaded', load);
                    })();
                </script>

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

                <script>
                    (function() {
                        const roleSelect = document.getElementById('designation');
                        const roleBlocks = {
                            'doctor': document.getElementById('role-fields-doctor'),
                            'nurse': document.getElementById('role-fields-nurse'),
                            'pharmacist': document.getElementById('role-fields-pharmacist'),
                            'laboratorist': document.getElementById('role-fields-laboratorist'),
                            'accountant': document.getElementById('role-fields-accountant'),
                            'receptionist': document.getElementById('role-fields-receptionist'),
                            'it_staff': document.getElementById('role-fields-it_staff')
                        };

                        function updateRoleFields() {
                            const v = roleSelect.value;
                            // Hide all
                            Object.values(roleBlocks).forEach(b => { if (b) b.style.display = 'none'; });
                            // Show selected if exists
                            if (roleBlocks[v]) roleBlocks[v].style.display = 'block';
                        }

                        roleSelect && roleSelect.addEventListener('change', updateRoleFields);
                        // Initialize on open if value preset
                        updateRoleFields();
                    })();
                </script>

                <script>
                    (function() {
                        // Lazy DOM getters because this script appears before the modal markup
                        function getAssignModal(){ return document.getElementById('assignShiftModal'); }
                        function getAssignForm(){ return document.getElementById('assignShiftForm'); }
                        function getDoctorSelect(){ return document.getElementById('doctor_id'); }
                        function getAssignDepartment(){
                            const form = getAssignForm();
                            return form ? form.querySelector('select[name="department"]') : null;
                        }
                        const shiftsBody = document.getElementById('doctorShiftsBody');

                        const URLS = {
                            doctors: '<?= base_url('admin/doctors/api') ?>',
                            shifts: '<?= base_url('admin/doctor-shifts/api') ?>',
                            createShift: '<?= base_url('admin/doctor-shifts/create') ?>'
                        };

                        function openAssignShiftModal() {
                            // Prefill date with today
                            try {
                                const today = new Date();
                                const yyyy = today.getFullYear();
                                const mm = String(today.getMonth() + 1).padStart(2, '0');
                                const dd = String(today.getDate()).padStart(2, '0');
                                const dateEl = document.getElementById('shift_date');
                                if (dateEl) dateEl.value = `${yyyy}-${mm}-${dd}`;
                            } catch(_) {}
                            loadDoctors();
                            const modal = getAssignModal();
                            if (modal) modal.classList.add('active');
                        }
                        function closeAssignShiftModal() {
                            const modal = getAssignModal();
                            const form = getAssignForm();
                            if (modal) modal.classList.remove('active');
                            if (form) form.reset();
                        }
                        // Expose to global (buttons call these)
                        window.openAssignShiftModal = openAssignShiftModal;
                        window.closeAssignShiftModal = closeAssignShiftModal;
                        // Fallback binding in case inline onclick is blocked
                        document.addEventListener('DOMContentLoaded', () => {
                            const btn = document.getElementById('openAssignShiftBtn');
                            if (btn) btn.addEventListener('click', openAssignShiftModal);
                            const overlay = getAssignModal();
                            if (overlay) {
                                overlay.addEventListener('click', (e) => {
                                    if (e.target === overlay) closeAssignShiftModal();
                                });
                            }
                        });

                        async function loadDoctors() {
                            const select = getDoctorSelect();
                            if (!select) return;
                            try {
                                select.innerHTML = '<option value>Loading doctors...</option>';
                                const res = await fetch(URLS.doctors, {
                                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                                });
                                // If request failed (e.g., 401 via redirect), surface a helpful message
                                const ctype = res.headers.get('content-type') || '';
                                if (!res.ok || !ctype.includes('application/json')) {
                                    const msg = !res.ok ? `Error ${res.status}. Please reload the page.` : 'Session expired. Please reload the page.';
                                    select.innerHTML = `<option value>Failed to load doctors. ${msg}</option>`;
                                    return;
                                }
                                const json = await res.json();
                                const list = Array.isArray(json?.data) ? json.data : (Array.isArray(json) ? json : []);
                                if (!Array.isArray(list) || list.length === 0) {
                                    select.innerHTML = '<option value>None available</option>';
                                    return;
                                }
                                select.innerHTML = '<option value="" selected>Select a doctor</option>' +
                                    list.map(d => `<option value="${d.doctor_id}" data-department="${(d.department||'').replace(/"/g,'&quot;')}">${(d.name || 'Doctor')} ${d.specialization ? '('+d.specialization+')' : ''}</option>`).join('');
                            } catch (e) {
                                console.error('loadDoctors() failed', e);
                                select.innerHTML = '<option value>Failed to load doctors. Please reload the page.</option>';
                            }
                        }

                        function syncDepartmentFromDoctor(){
                            try {
                                const docSel = getDoctorSelect();
                                const depSel = getAssignDepartment();
                                if (!docSel || !depSel) return;
                                const opt = docSel.options[docSel.selectedIndex];
                                if (!opt) return;
                                const dept = (opt.getAttribute('data-department') || '').trim();
                                if (!dept) return;
                                // If the department value is not in the list, append it once
                                let match = Array.from(depSel.options).some(o => (o.value||'') === dept);
                                if (!match) {
                                    const o = document.createElement('option');
                                    o.value = dept; o.textContent = dept;
                                    depSel.appendChild(o);
                                }
                                depSel.value = dept;
                            } catch(_) {}
                        }

                        // When doctor changes, auto-select department
                        document.addEventListener('DOMContentLoaded', () => {
                            const docSel = getDoctorSelect();
                            if (docSel && !docSel.__boundSyncDept) {
                                docSel.__boundSyncDept = true;
                                docSel.addEventListener('change', syncDepartmentFromDoctor);
                            }
                        });

                        async function loadDoctorShifts() {
                            try {
                                shiftsBody.innerHTML = `<tr><td colspan="6" style="text-align:center; color:#6b7280; padding:1rem;">Loading doctor shifts...</td></tr>`;
                                const res = await fetch(URLS.shifts, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }});
                                if (!res.ok) {
                                    const t = await res.text().catch(()=>'');
                                    console.error('doctor-shifts/api HTTP', res.status, t);
                                    throw new Error('HTTP '+res.status);
                                }
                                const json = await res.json();
                                // Accept both {data:[...]} and plain array responses
                                const rows = Array.isArray(json?.data)
                                    ? json.data
                                    : (Array.isArray(json) ? json : []);
                                if (!Array.isArray(rows)) {
                                    console.warn('Unexpected shifts payload', json);
                                }
                                if (!Array.isArray(rows) || rows.length === 0) {
                                    shiftsBody.innerHTML = `<tr><td colspan="6" style="text-align:center; color:#6b7280; padding:1rem;">No shifts found</td></tr>`;
                                    return;
                                }
                                shiftsBody.innerHTML = rows.map(r => `
                                    <tr>
                                        <td style="padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">${escapeHtml(r.doctor_name || '')}</td>
                                        <td style="padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">${escapeHtml(r.date || '')}</td>
                                        <td style="padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">${escapeHtml(r.start || '')}</td>
                                        <td style="padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">${escapeHtml(r.end || '')}</td>
                                        <td style="padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">${escapeHtml(r.department || '')}</td>
                                        <td style="padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">
                                            <button class="btn btn-primary btn-small btn-edit-shift" data-id="${r.id}"><i class="fas fa-pen"></i> Edit</button>
                                            <button class="btn btn-danger btn-small btn-delete-shift" data-id="${r.id}"><i class="fas fa-trash"></i> Delete</button>
                                        </td>
                                    </tr>
                                `).join('');
                            } catch (e) {
                                console.error('Failed to load doctor shifts', e);
                                shiftsBody.innerHTML = `<tr><td colspan="6" style="text-align:center; color:#ef4444; padding:1rem;">Failed to load shifts</td></tr>`;
                            }
                        }
                        // Make available to other scripts
                        window.loadDoctorShifts = loadDoctorShifts;

                        function getCsrfPair(formEl) {
                            // Find the first hidden input with name starting with csrf_
                            const input = formEl.querySelector('input[type="hidden"][name^="csrf_"]');
                            return input ? { name: input.getAttribute('name'), value: input.value } : null;
                        }

                        function getGlobalCsrf() {
                            const input = document.querySelector('input[type="hidden"][name^="csrf_"]');
                            return input ? { name: input.getAttribute('name'), value: input.value } : null;
                        }

                        function refreshCsrfFromJson(json) {
                            try {
                                const c = json && json.csrf;
                                if (!c || !c.name || !c.value) return;
                                const inputs = document.querySelectorAll('input[type="hidden"][name^="csrf_"]');
                                inputs.forEach(inp => {
                                    inp.setAttribute('name', c.name);
                                    inp.value = c.value;
                                });
                            } catch (_) {}
                        }

                        // Obsolete Assign Shift handler removed (was referencing undefined URLS/getAssignForm)

                        // Delegated actions for edit/delete on shifts table
                        const bindShiftActions = () => {
                            if (!shiftsBody) return;
                            // Prevent duplicate bindings
                            if (shiftsBody.__boundShiftActions) return;
                            shiftsBody.__boundShiftActions = true;
                            shiftsBody.addEventListener('click', async (e) => {
                                    const btn = e.target.closest('button');
                                    if (!btn) return;
                                    const id = btn.getAttribute('data-id');
                                    if (!id) return;
                                    if (btn.classList.contains('btn-edit-shift')) {
                                        await openDoctorShiftAdminModal(id);
                                    } else if (btn.classList.contains('btn-delete-shift')) {
                                        const ok = confirm('Delete this shift?');
                                        if (!ok) return;
                                        const csrf = getGlobalCsrf();
                                        const fd = new FormData();
                                        fd.append('id', id);
                                        if (csrf) fd.append(csrf.name, csrf.value);
                                        try {
                                            const res = await fetch('<?= base_url('admin/doctor-shifts/delete') ?>', {
                                                method: 'POST',
                                                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                                                body: fd
                                            });
                                            const json = await res.json();
                                            refreshCsrfFromJson(json);
                                            if (json?.status === 'success') {
                                                await loadDoctorShifts();
                                            } else {
                                                const errMsg = json?.message || 'Failed to delete shift';
                                                const dbm = json?.db_error?.message ? `\nDB: ${json.db_error.message}` : '';
                                                const exm = json?.exception ? `\nEx: ${json.exception}` : '';
                                                alert(errMsg + dbm + exm);
                                            }
                                        } catch (err) {
                                            alert('Failed to delete shift');    
                                        }
                                    }
                                });
                        };
                        if (shiftsBody) {
                            bindShiftActions();
                        } else {
                            document.addEventListener('DOMContentLoaded', bindShiftActions);
                        }

                        // Admin modal lazy getters and handlers
                        function getAdminModal(){ return document.getElementById('doctorShiftAdminModal'); }
                        function getAdminForm(){ return document.getElementById('doctorShiftAdminForm'); }
                        function openDoctorShiftAdminModal(id) {
                            return (async () => {
                                try {
                                    const res = await fetch(`<?= base_url('admin/doctor-shifts') ?>/${id}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                                    if (!res.ok) {
                                        const txt = await res.text().catch(()=> '');
                                        console.error('Load shift failed', res.status, txt);
                                        alert('Failed to load shift (HTTP ' + res.status + ').');
                                        return;
                                    }
                                    const json = await res.json();
                                    const d = json?.data || {};
                                    // populate fields
                                    const form = getAdminForm();
                                    if (!form) return;
                                    form.querySelector('#doctor_shift_id').value = d.id || '';
                                    form.querySelector('#adm_shift_date').value = d.date || '';
                                    form.querySelector('#adm_shift_start').value = d.start || '';
                                    form.querySelector('#adm_shift_end').value = d.end || '';
                                    form.querySelector('#adm_department').value = d.department || '';
                                    const modal = getAdminModal();
                                    if (modal) modal.classList.add('active');
                                } catch (err) {
                                    console.error('Exception loading shift', err);
                                    alert('Failed to load shift');
                                }
                            })();
                        }

                        function closeDoctorShiftAdminModal(){ const m = getAdminModal(); if (m) m.classList.remove('active'); }
                        window.closeDoctorShiftAdminModal = closeDoctorShiftAdminModal;

                        // Bind admin form submit (immediately if present) with fallback
                        const bindAdminForm = () => {
                            const form = getAdminForm();
                            if (!form || form.__boundSubmit) return;
                            form.__boundSubmit = true;
                            form.addEventListener('submit', async (e) => {
                                e.preventDefault();
                                const fd = new FormData(form); // includes csrf + id
                                try {
                                    const res = await fetch('<?= base_url('admin/doctor-shifts/update') ?>', {
                                        method: 'POST',
                                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                                        body: fd
                                    });
                                    if (!res.ok) {
                                        const txt = await res.text().catch(()=> '');
                                        console.error('Update shift failed', res.status, txt);
                                        alert('Failed to update shift (HTTP ' + res.status + ').');
                                        return;
                                    }
                                    const json = await res.json();
                                    refreshCsrfFromJson(json);
                                    if (json?.status === 'success') {
                                        closeDoctorShiftAdminModal();
                                        await loadDoctorShifts();
                                    } else {
                                        const errMsg = json?.message || 'Failed to update shift';
                                        const dbm = json?.db_error?.message ? `\nDB: ${json.db_error.message}` : '';
                                        const exm = json?.exception ? `\nEx: ${json.exception}` : '';
                                        alert(errMsg + dbm + exm);
                                    }
                                } catch (err) {
                                    console.error('Exception updating shift', err);
                                    alert('Failed to update shift');
                                }
                            });
                        };
                        bindAdminForm();
                        document.addEventListener('DOMContentLoaded', bindAdminForm);

                        function escapeHtml(str){
                            return (str||'').toString()
                                .replace(/&/g, '&amp;')
                                .replace(/</g, '&lt;')
                                .replace(/>/g, '&gt;')
                                .replace(/"/g, '&quot;')
                                .replace(/'/g, '&#039;');
                        }

                        // Initial load
                        document.addEventListener('DOMContentLoaded', () => loadDoctorShifts());
                        // Also try immediate call
                        loadDoctorShifts();
                    })();
                </script>

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


                <script>
                    const addStaffModal = document.getElementById('addStaffModal');
                    function openAddStaffModal() {
                        addStaffModal.classList.add('active');
                        addStaffModal.setAttribute('aria-hidden', 'false');
                    }
                    function closeAddStaffModal() {
                        addStaffModal.classList.remove('active');
                        addStaffModal.setAttribute('aria-hidden', 'true');
                    }
                    // Close on overlay click
                    addStaffModal?.addEventListener('click', (e) => {
                        if (e.target === addStaffModal) closeAddStaffModal();
                    });

                    /**
                     * Handle staff form submission via AJAX
                     */
                    const addStaffForm = document.getElementById('addStaffForm');
                    
                    // Fallback binding in case inline onclick is blocked by CSP
                    document.getElementById('openAddStaffBtn')?.addEventListener('click', openAddStaffModal);
                    
                    // Form submission handler
                    addStaffForm?.addEventListener('submit', async (e) => {
                        e.preventDefault();
                        const form = e.target;
                        const action = form.getAttribute('action');
                        const formData = new FormData(form);
                        
                        try {
                            // Send AJAX request
                            const res = await fetch(action, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: formData
                            });
                            
                            const data = await res.json();
                            
                            if (res.ok && data?.status === 'success') {
                                // Success handling
                                alert('Staff member created successfully');
                                form.reset();
                                closeAddStaffModal();
                                
                                // Refresh the staff table to show new entry
                                loadStaffTable();
                            } else {
                                // Error handling with formatted message
                                const errs = data?.errors ? Object.values(data.errors).join('\n- ') : null;
                                const msg = data?.message || 'Failed to create staff';
                                alert(errs ? `${msg}:\n- ${errs}` : msg);
                            }
                        } catch (err) {
                            console.error(err);
                            alert('An error occurred while creating staff');
                        }
                    });

                    // Load Staff Table (all staff)
                    async function loadStaffTable() {
                        const tbody = document.getElementById('staffTableBody');
                        if (!tbody) return;
                        try {
                            const res = await fetch('<?= base_url('admin/staff/api') ?>', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                            if (!res.ok) throw new Error('Failed to load staff');
                            const staff = await res.json();

                            if (!Array.isArray(staff) || staff.length === 0) {
                                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:#6b7280; padding:1rem;">No staff found.</td></tr>';
                                return;
                            }

                            tbody.innerHTML = staff.map(s => {
                                const id = s.id ?? s.staff_id ?? '';
                                const first = s.first_name ?? '';
                                const last = s.last_name ?? '';
                                const name = (s.full_name ?? `${first} ${last}`).trim();
                                const role = (s.role ?? '').toString().toLowerCase();
                                const roleDisplay = role ? role.replace('_', ' ') : '';
                                const dept = s.department ?? '';
                                const email = s.email ?? '';
                                return `
                                    <tr>
                                        <td style="padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">${name || '-'}</td>
                                        <td style="padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb; text-transform:capitalize;">${roleDisplay || '-'}</td>
                                        <td style="padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">${dept || '-'}</td>
                                        <td style="padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">${email || '-'}</td>
                                            <td style="padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">
                                            <button type="button" class="btn btn-primary btn-small" onclick="openStaffViewModal(${id})"><i class="fas fa-eye"></i> View</button>
                                            <button type="button" class="btn btn-warning btn-small" onclick="openStaffEditModal(${id})"><i class="fas fa-edit"></i> Edit</button>
                                        </td>
                                    </tr>
                                `;
                            }).join('');
                        } catch (err) {
                            console.error(err);
                            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:#ef4444; padding:1rem;">Failed to load staff.</td></tr>';
                        }
                    }
                    
                    // Staff View/Edit Modals
                    const viewStaffOverlay = document.getElementById('viewStaffModal');
                    const editStaffOverlay = document.getElementById('editStaffModal');
                    const editStaffForm = document.getElementById('editStaffForm');
                    
                    // Helpers to open/close modals
                    function openStaffViewModal(id){
                        if (!viewStaffOverlay) return;
                        // Clear previous
                        document.getElementById('v_name').textContent = '-';
                        document.getElementById('v_role').textContent = '-';
                        document.getElementById('v_department').textContent = '-';
                        document.getElementById('v_email').textContent = '-';
                        document.getElementById('v_contact').textContent = '-';
                        document.getElementById('v_gender').textContent = '-';
                        document.getElementById('v_dob').textContent = '-';
                        document.getElementById('v_address').textContent = '-';
                        document.getElementById('v_staff_id').value = id || '';
                        
                        // Fetch staff details
                        fetch('<?= base_url('admin/staff/get') ?>/' + id, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With':'XMLHttpRequest' }
                        }).then(r => r.json()).then(s => {
                            const first = s.first_name || '';
                            const last = s.last_name || '';
                            const name = (first + ' ' + last).trim() || s.full_name || '-';
                            const role = (s.role || s.designation || '').toString().replace('_',' ');
                            document.getElementById('v_name').textContent = name;
                            document.getElementById('v_role').textContent = role || '-';
                            document.getElementById('v_department').textContent = s.department || '-';
                            document.getElementById('v_email').textContent = s.email || '-';
                            document.getElementById('v_contact').textContent = s.contact_no || '-';
                            document.getElementById('v_gender').textContent = (s.gender ? (s.gender.charAt(0).toUpperCase() + s.gender.slice(1)) : '-') || '-';
                            document.getElementById('v_dob').textContent = s.dob || '-';
                            document.getElementById('v_address').textContent = s.address || '-';
                        }).catch(console.error);
                        
                        viewStaffOverlay.classList.add('active');
                        viewStaffOverlay.setAttribute('aria-hidden','false');
                    }
                    function closeStaffViewModal(){
                        if (!viewStaffOverlay) return;
                        viewStaffOverlay.classList.remove('active');
                        viewStaffOverlay.setAttribute('aria-hidden','true');
                    }
                    viewStaffOverlay?.addEventListener('click', (e)=>{ if (e.target === viewStaffOverlay) closeStaffViewModal(); });
                    
                    function openStaffEditModal(id){
                        if (!editStaffOverlay) return;
                        // Reset form
                        editStaffForm?.reset();
                        document.getElementById('e_staff_id').value = id || '';
                        
                        // Load existing data
                        fetch('<?= base_url('admin/staff/get') ?>/' + id, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With':'XMLHttpRequest' }
                        }).then(r => r.json()).then(s => {
                            document.getElementById('e_first_name').value = s.first_name || '';
                            document.getElementById('e_last_name').value = s.last_name || '';
                            const g = s.gender ? (s.gender.charAt(0).toUpperCase()+s.gender.slice(1)) : '';
                            document.getElementById('e_gender').value = g;
                            document.getElementById('e_dob').value = s.dob || '';
                            document.getElementById('e_contact_no').value = s.contact_no || '';
                            document.getElementById('e_email').value = s.email || '';
                            document.getElementById('e_address').value = s.address || '';
                            document.getElementById('e_department').value = s.department || '';
                            document.getElementById('e_designation').value = s.designation || s.role || '';
                        }).catch(console.error);
                        
                        editStaffOverlay.classList.add('active');
                        editStaffOverlay.setAttribute('aria-hidden','false');
                    }
                    function closeStaffEditModal(){
                        if (!editStaffOverlay) return;
                        editStaffOverlay.classList.remove('active');
                        editStaffOverlay.setAttribute('aria-hidden','true');
                    }
                    editStaffOverlay?.addEventListener('click', (e)=>{ if (e.target === editStaffOverlay) closeStaffEditModal(); });
                    
                    // Submit edit form via AJAX
                    editStaffForm?.addEventListener('submit', async (e) => {
                        e.preventDefault();
                        const id = document.getElementById('e_staff_id').value;
                        const fd = new FormData(editStaffForm);
                        try{
                            const res = await fetch('<?= base_url('admin/edit-staff') ?>/' + id, {
                                method: 'POST',
                                headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' },
                                body: fd,
                                credentials: 'same-origin'
                            });
                            const ctype = res.headers.get('content-type') || '';
                            const json = ctype.includes('application/json') ? await res.json() : null;
                            if (!res.ok || (json && json.status !== 'success')){
                                const msg = (json && (json.message || JSON.stringify(json.errors))) || ('Failed (HTTP '+res.status+')');
                                alert('Failed to update staff. ' + msg);
                                return;
                            }
                            alert('Staff updated successfully');
                            closeStaffEditModal();
                            loadStaffTable();
                        }catch(err){
                            console.error(err);
                            alert('An error occurred while updating staff');
                        }
                    });

                    // Expose open/close for inline handlers
                    window.openStaffViewModal = openStaffViewModal;
                    window.closeStaffViewModal = closeStaffViewModal;
                    window.openStaffEditModal = openStaffEditModal;
                    window.closeStaffEditModal = closeStaffEditModal;

                    // Initial load
                    window.addEventListener('DOMContentLoaded', loadStaffTable);
                    // Assign Shift JS is defined earlier to avoid duplication
                </script>

                <!-- Assign Shift wiring: submit to backend and reload table -->
                <script>
                    (function(){
                        const assignOverlay = document.getElementById('assignShiftModal');
                        const assignForm = document.getElementById('assignShiftForm');
                        const doctorSelect = document.getElementById('doctor_id');

                        function refreshCsrfFromJson(json){
                            try{
                                const n = json?.csrf?.name; const v = json?.csrf?.value;
                                if (!n || !v) return;
                                // Update all hidden CSRF inputs across forms
                                document.querySelectorAll('input[name="'+n+'"]').forEach(inp => { inp.value = v; });
                            }catch(_){/* noop */}
                        }

                        function openAssignShiftModal(){
                            if (assignOverlay){
                                assignOverlay.classList.add('active');
                                assignOverlay.setAttribute('aria-hidden','false');
                            }
                            loadDoctors();
                        }
                        function closeAssignShiftModal(){
                            if (assignOverlay){
                                assignOverlay.classList.remove('active');
                                assignOverlay.setAttribute('aria-hidden','true');
                            }
                        }

                        async function loadDoctors(){
                            if (!doctorSelect) return;
                            try{
                                doctorSelect.innerHTML = '<option value>Loading doctors...</option>';
                                const res = await fetch('<?= base_url('admin/doctors/api') ?>', { headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' } });
                                const ctype = res.headers.get('content-type') || '';
                                if (!res.ok || !ctype.includes('application/json')){
                                    const msg = !res.ok ? `Error ${res.status}. Please reload the page.` : 'Session expired. Please reload the page.';
                                    doctorSelect.innerHTML = `<option value>Failed to load doctors. ${msg}</option>`;
                                    return;
                                }
                                const json = await res.json();
                                const list = Array.isArray(json?.data) ? json.data : (Array.isArray(json) ? json : []);
                                if (!Array.isArray(list) || list.length === 0){
                                    doctorSelect.innerHTML = '<option value>None available</option>';
                                    return;
                                }
                                doctorSelect.innerHTML = '<option value="" selected>Select a doctor</option>' +
                                    list.map(d => `<option value="${d.doctor_id}">${(d.name || 'Doctor')} ${d.specialization ? '('+d.specialization+')' : ''}</option>`).join('');
                            }catch(e){
                                console.error('loadDoctors() failed', e);
                                doctorSelect.innerHTML = '<option value>Failed to load doctors. Please reload the page.</option>';
                            }
                        }

                        // Expose for button onclick
                        window.openAssignShiftModal = openAssignShiftModal;
                        window.closeAssignShiftModal = closeAssignShiftModal;

                        // Close when clicking overlay background
                        assignOverlay?.addEventListener('click', (e)=>{ if (e.target === assignOverlay) closeAssignShiftModal(); });

                        // Bind submit to create shift
                        assignForm?.addEventListener('submit', async (e) => {
                            e.preventDefault();
                            const fd = new FormData(assignForm);
                            try{
                                const res = await fetch('<?= base_url('admin/doctor-shifts/create') ?>', {
                                    method: 'POST',
                                    headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' },
                                    body: fd,
                                    credentials: 'same-origin' // ensure session/CSRF cookies sent
                                });
                                const ctype = res.headers.get('content-type') || '';
                                if (!ctype.includes('application/json')){
                                    const txt = await res.text().catch(()=> '');
                                    console.error('Unexpected response (non-JSON)', { status: res.status, body: txt });
                                    alert(`Failed to create shift (HTTP ${res.status}).\n${txt?.slice(0,300) || 'Non-JSON response'}`);
                                    return;
                                }
                                const json = await res.json();
                                refreshCsrfFromJson(json);
                                if (!res.ok || json?.status !== 'success'){
                                    console.error('Create shift failed', { status: res.status, json });
                                    const msg = json?.message || `Failed to create shift (HTTP ${res.status})`;
                                    const dbm = json?.db_error?.message ? `\nDB: ${json.db_error.message}` : '';
                                    const exm = json?.exception ? `\nEx: ${json.exception}` : '';
                                    alert(msg + dbm + exm);
                                    return;
                                }
                                // Success
                                alert('Shift created successfully.');
                                assignForm.reset();
                                closeAssignShiftModal();
                                // Reload shifts table if loader exists
                                if (typeof loadDoctorShifts === 'function'){
                                    await loadDoctorShifts();
                                }
                            }catch(err){
                                console.error('Create shift failed', err);
                                alert('Failed to create shift.');
                            }
                        });

                        // Also load doctors when DOM ready for a faster first open
                        document.addEventListener('DOMContentLoaded', loadDoctors);
                    })();
                </script>

            </main>
        </div>
    </body>
</html>


