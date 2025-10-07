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
        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
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
                                <div class="metric-value blue"><? esc($total_staff) ?></div>
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
                
                <div class="staff-grid">
                <!-- Current Shift Status -->
                <div class="staff-section">
                    <div class="section-header">
                        <div class="section-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <div class="section-title">Current Shift Status</div>
                        </div>
                    </div>

                    <div class="staff-item">
                        <div class="staff-info">
                            <div class="staff-name">Dr. Staff1</div>
                            <div class="staff-details">Emergency Department - 8:00 AM - 6:00 PM</div>
                        </div>
                        <div class="staff-status status-on-duty">On Duty</div>
                    </div>

                    <div class="staff-item">
                        <div class="staff-info">
                            <div class="staff-name">Nurse Staff1</div>
                            <div class="staff-details">ICU - 6:00 AM - 6:00 PM</div>
                        </div>
                        <div class="staff-status status-break">On Break</div>
                    </div>

                    <div class="staff-item">
                        <div class="staff-info">
                            <div class="staff-name">Dr. Staff2</div>
                            <div class="staff-details">Cardiology - 9:00 AM - 5:00 PM</div>
                        </div>
                        <div class="staff-status status-on-duty">On Duty</div>
                    </div>

                    <div class="staff-item">
                        <div class="staff-info">
                            <div class="staff-name">Tech Staff1</div>
                            <div class="staff-details">Laboratory - 7:00 AM - 3:00 PM</div>
                        </div>
                        <div class="staff-status status-overtime">Overtime</div>
                    </div>

                    <div class="action-buttons">
                        <button class="btn btn-primary btn-small" onclick="viewAllShifts()">
                            <i class="fas fa-list"></i> View All Shifts
                        </button>
                        <button class="btn btn-secondary btn-small" onclick="shiftReports()">
                            <i class="fas fa-chart-bar"></i> Shift Reports
                        </button>
                    </div>
                </div>

                <!-- Leave Management -->
                <div class="staff-section">
                    <div class="section-header">
                        <div class="section-icon" style="background: #06b6d4;">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <div>
                            <div class="section-title">Leave Requests</div>
                        </div>
                    </div>

                    <div class="staff-item">
                        <div class="staff-info">
                            <div class="staff-name">Dr. Staff3</div>
                            <div class="staff-details">Vacation Leave - Dec 20-27, 2024</div>
                        </div>
                        <div class="staff-status status-break">Pending</div>
                    </div>

                    <div class="staff-item">
                        <div class="staff-info">
                            <div class="staff-name">Nurse Staff2</div>
                            <div class="staff-details">Sick Leave - Dec 18, 2024</div>
                        </div>
                        <div class="staff-status status-on-duty">Approved</div>
                    </div>

                    <div class="staff-item">
                        <div class="staff-info">
                            <div class="staff-name">Tech Staff2</div>
                            <div class="staff-details">Personal Leave - Dec 22, 2024</div>
                        </div>
                        <div class="staff-status status-break">Pending</div>
                    </div>

                    <div style="margin-top: 1rem; font-size: 0.9rem; color: #6b7280;">
                        <div>Pending Requests: 8</div>
                        <div>Approved This Month: 23</div>
                        <div>Leave Balance Usage: 67%</div>
                    </div>

                    <div class="action-buttons">
                        <button class="btn btn-primary btn-small" onclick="reviewLeaveRequests()">
                            <i class="fas fa-clipboard-check"></i> Review Requests
                        </button>
                        <button class="btn btn-secondary btn-small" onclick="leaveReports()">
                            <i class="fas fa-calendar-alt"></i> Leave Reports
                        </button>
                    </div>
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
                                    <th style="text-align:left; padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">Status</th>
                                    <th style="text-align:left; padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="staffTableBody">
                                <tr>
                                    <td colspan="6" style="text-align:center; color:#6b7280; padding:1rem;">Loading staff...</td>
                                </tr>
                            </tbody>
                        </table>
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
                                    <div class="full">
                                        <label for="doctor_id" class="form-label">Doctor</label>
                                        <select id="doctor_id" name="doctor_id" class="form-select" required>
                                            <option value="">Loading doctors...</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="shift_date" class="form-label">Shift Date</label>
                                        <input type="date" id="shift_date" name="shift_date" class="form-input" required>
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
                                    <input type="hidden" name="status" value="Scheduled">
                                </div>
                            </div>
                            <div class="hms-modal-actions">
                                <button type="button" class="btn btn-secondary" onclick="closeAssignShiftModal()">Cancel</button>
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
                                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; color:#6b7280; padding:1rem;">No staff found.</td></tr>';
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
                                // Status is not part of staff schema; show placeholder
                                const statusBadge = '<span class="staff-status status-off-duty">-</span>';
                                const viewUrl = '<?= base_url('admin/view-staff') ?>/' + id;
                                return `
                                    <tr>
                                        <td style="padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">${name || '-'}</td>
                                        <td style="padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb; text-transform:capitalize;">${roleDisplay || '-'}</td>
                                        <td style="padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">${dept || '-'}</td>
                                        <td style="padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">${email || '-'}</td>
                                        <td style="padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">${statusBadge}</td>
                                        <td style="padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">
                                            <a class="btn btn-primary btn-small" href="${viewUrl}"><i class="fas fa-eye"></i> View</a>
                                        </td>
                                    </tr>
                                `;
                            }).join('');
                        } catch (err) {
                            console.error(err);
                            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; color:#ef4444; padding:1rem;">Failed to load staff.</td></tr>';
                        }
                    }
                    // Initial load
                    window.addEventListener('DOMContentLoaded', loadStaffTable);
                    // Assign Shift (Doctors only)
                    const assignShiftModal = document.getElementById('assignShiftModal');
                    const doctorSelect = document.getElementById('doctor_id');
                    const shiftTypeSelect = document.getElementById('shift_type');

                    function openAssignShiftModal() {
                        assignShiftModal.classList.add('active');
                        assignShiftModal.setAttribute('aria-hidden', 'false');
                        // Prefill date with today
                        const today = new Date();
                        const yyyy = today.getFullYear();
                        const mm = String(today.getMonth() + 1).padStart(2, '0');
                        const dd = String(today.getDate()).padStart(2, '0');
                        document.getElementById('shift_date').value = `${yyyy}-${mm}-${dd}`;
                        // Load doctors list (UI only, optional)
                        loadDoctorsIntoSelect();
                        // Sync default times to selected shift type
                        syncTimesToShiftType();
                    }
                    function closeAssignShiftModal() {
                        assignShiftModal.classList.remove('active');
                        assignShiftModal.setAttribute('aria-hidden', 'true');
                    }
                    // Close on overlay click
                    assignShiftModal?.addEventListener('click', (e) => {
                        if (e.target === assignShiftModal) closeAssignShiftModal();
                    });

                    function syncTimesToShiftType() {
                        const type = shiftTypeSelect?.value;
                        const start = document.getElementById('start_time');
                        const end = document.getElementById('end_time');
                        if (!start || !end) return;
                        if (type === 'morning') {
                            start.value = '06:00';
                            end.value = '14:00';
                        } else if (type === 'afternoon') {
                            start.value = '14:00';
                            end.value = '22:00';
                        } else if (type === 'night') {
                            start.value = '22:00';
                            end.value = '06:00';
                        }
                    }
                    shiftTypeSelect?.addEventListener('change', () => {
                        if (shiftTypeSelect.value !== 'custom') {
                            syncTimesToShiftType();
                        }
                    });

                     async function loadDoctorsIntoSelect() {
                        if (!doctorSelect) return;
                        try {
                            // Should return JSON: [{id, full_name}, ...]
                            const res = await fetch('<?= base_url('admin/staff/doctors') ?>', { headers: { 'Accept': 'application/json' } });
                            if (!res.ok) throw new Error('Failed to load doctors');
                            const data = await res.json();
                            const doctors = Array.isArray(data?.doctors) ? data.doctors : [];
                            if (doctors.length === 0) throw new Error('No doctors list');
                            doctorSelect.innerHTML = '<option value="">Select doctor</option>' +
                                doctors.map(d => `<option value="${d.id}">${d.full_name}</option>`).join('');
                        } catch (err) {
                            // Fallback to static placeholders if endpoint not available
                            doctorSelect.innerHTML = `
                                <option value="">Select doctor</option>
                                <option value="1">Dr. Staff1</option>
                                <option value="2">Dr. Staff2</option>
                                <option value="3">Dr. Staff3</option>
                            `;
                        }
                    }

                    const assignShiftForm = document.getElementById('assignShiftForm');
                    assignShiftForm?.addEventListener('submit', async (e) => {
                        e.preventDefault();
                        const form = e.target;
                        const formData = new FormData(form);
                        const doctorId = formData.get('doctor_id');
                        if (!doctorId) {
                            alert('Please select a doctor.');
                            return;
                        }
                        const url = `<?= base_url('admin/staff') ?>/${doctorId}/shifts`;
                        try {
                            const res = await fetch(url, {
                                method: 'POST',
                                headers: { 'Accept': 'application/json' },
                                body: formData
                            });
                            const data = await res.json();
                            if (!res.ok || data?.status !== 'success') {
                                const msg = data?.message || 'Failed to create shift';
                                const errs = data?.errors ? ('\n' + JSON.stringify(data.errors)) : '';
                                alert(msg + errs);
                                return;
                            }
                            alert('Shift assigned successfully');
                            form.reset();
                            closeAssignShiftModal();
                        } catch (err) {
                            console.error(err);
                            alert('An error occurred while assigning the shift');
                        }
                    });
                </script>

            </main>
        </div>
    </body>
</html>


