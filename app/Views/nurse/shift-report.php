<!DOCTYPE html>
<html lang="en">
<head>
     <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shift Reports - HMS Nurse</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Filters */
        .filter-row { display: flex; gap: 1rem; align-items: end; flex-wrap: wrap; }
        .filter-group { display: flex; flex-direction: column; gap: 0.5rem; min-width: 150px; }
        .filter-input { padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 5px; font-size: 0.9rem; }

        /* Table enhancements */
        .table-container { background:#fff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.06); overflow:auto; max-height:60vh; }
        .table { width:100%; border-collapse: separate; border-spacing:0; min-width: 900px; }
        .table thead th { position: sticky; top: 0; background:#f8fafc; color:#374151; font-weight:600; text-align:left; padding: .75rem 1rem; border-bottom:1px solid #e5e7eb; z-index:1; }
        .table tbody td { padding:.75rem 1rem; border-bottom:1px solid #f3f4f6; vertical-align: middle; }
        .table tbody tr:nth-child(odd) { background:#fcfcfd; }
        .table tbody tr:hover { background:#f9fafb; }
        .table th:last-child, .table td:last-child { text-align:right; white-space: nowrap; }

        /* Badges & compact buttons */
        .badge { display:inline-block; padding:.25rem .6rem; border-radius:999px; font-size:.75rem; font-weight:600; }
        .badge-success { background:#dcfce7; color:#166534; }
        .badge-warning { background:#fef3c7; color:#92400e; }
        .badge-danger  { background:#fecaca; color:#991b1b; }
        .btn.btn-primary.btn-small, .btn.btn-secondary.btn-small { padding: .45rem .75rem; font-size:.85rem; }

        /* Modal base */
        .modal { display: none; position: fixed; z-index: 1000; inset: 0; background: rgba(0,0,0,0.5); }
        .modal.show { display: flex; align-items: center; justify-content: center; }
        .modal-content { background: #fff; border-radius: 8px; width: 90%; max-width: 800px; max-height: 90vh; overflow-y: auto; box-shadow: 0 4px 20px rgba(0,0,0,0.15); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border-bottom: 1px solid #e2e8f0; background: #f7fafc; }
        .modal-header h3 { margin: 0; color: #2d3748; }
        .modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #718096; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; }
        .modal-close:hover { color: #2d3748; }
        .modal-body { padding: 1.5rem; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 1rem; padding: 1.5rem; border-top: 1px solid #e2e8f0; background: #f7fafc; }
        .form-input { width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.9rem; }
        .form-input:focus { outline: none; border-color: #4299e1; box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1); }
    </style>
</head>
<body class="nurse">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <?= $this->include('Views/nurse/components/sidebar') ?>

        <main class="content">
            <h1 class="page-title">Shift Reports Management</h1>
            <div class="page-actions">
                <button class="btn btn-success" id="createReportBtn"><i class="fas fa-plus"></i> Create Shift Report</button>
            </div>
            <br>

            <div class="dashboard-overview">
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue"><i class="fas fa-file-medical"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Today's Reports</h3>
                            <p class="card-subtitle">Shift reports submitted today</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric"><div class="metric-value blue">0</div><div class="metric-label">Total Reports</div></div>
                        <div class="metric"><div class="metric-value green">0</div><div class="metric-label">Completed</div></div>
                        <div class="metric"><div class="metric-value orange">0</div><div class="metric-label">Pending</div></div>
                    </div>
                </div>
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern green"><i class="fas fa-users"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Shift Coverage</h3>
                            <p class="card-subtitle">Current shift assignments</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric"><div class="metric-value green">0</div><div class="metric-label">On Duty</div></div>
                        <div class="metric"><div class="metric-value blue">0</div><div class="metric-label">Next Shift</div></div>
                        <div class="metric"><div class="metric-value red">0</div><div class="metric-label">Critical Issues</div></div>
                    </div>
                </div>
            </div>

            <div class="search-filters">
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Nurse Name</label>
                        <input type="text" class="filter-input" placeholder="Search nurse...">
                    </div>
                    <div class="filter-group">
                        <label>Shift Type</label>
                        <select class="filter-input">
                            <option value="">All Shifts</option>
                            <option value="day">Day Shift</option>
                            <option value="night">Night Shift</option>
                            <option value="evening">Evening Shift</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Status</label>
                        <select class="filter-input">
                            <option value="">All Status</option>
                            <option value="completed">Completed</option>
                            <option value="pending">Pending</option>
                            <option value="in-progress">In Progress</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Date</label>
                        <input type="date" class="filter-input">
                    </div>
                    <div class="filter-group">
                        <label>&nbsp;</label>
                        <button class="btn btn-primary btn-small">Apply Filters</button>
                    </div>
                </div>
            </div>

            <div class="patient-table">
                <div class="table-header">
                    <h3>Shift Reports</h3>
                    <div class="action-buttons">
                        <button class="btn btn-primary btn-small"><i class="fas fa-download"></i> Export</button>
                        <button class="btn btn-secondary btn-small"><i class="fas fa-print"></i> Print</button>
                    </div>
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nurse</th>
                                <th>Shift</th>
                                <th>Date</th>
                                <th>Patients</th>
                                <th>Critical Events</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Sarah Johnson</strong><br><small>RN, ICU</small></td>
                                <td>Day Shift<br><small>7:00 AM - 7:00 PM</small></td>
                                <td>Dec 16, 2024</td>
                                <td>8 patients</td>
                                <td><span style="color:#ef4444;font-weight:bold;">2 Critical</span></td>
                                <td><span class="badge badge-success">Completed</span></td>
                                <td>6:45 PM</td>
                                <td>
                                    <button class="btn btn-primary btn-small">View</button>
                                    <button class="btn btn-secondary btn-small">Edit</button>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Michael Chen</strong><br><small>RN, Emergency</small></td>
                                <td>Night Shift<br><small>7:00 PM - 7:00 AM</small></td>
                                <td>Dec 16, 2024</td>
                                <td>12 patients</td>
                                <td><span style="color:#f59e0b;font-weight:bold;">1 Urgent</span></td>
                                <td><span class="badge badge-warning">In Progress</span></td>
                                <td>-</td>
                                <td>
                                    <button class="btn btn-primary btn-small">Continue</button>
                                    <button class="btn btn-secondary btn-small">View</button>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Emily Rodriguez</strong><br><small>RN, Pediatrics</small></td>
                                <td>Evening Shift<br><small>3:00 PM - 11:00 PM</small></td>
                                <td>Dec 16, 2024</td>
                                <td>6 patients</td>
                                <td><span style="color:#10b981;">No Issues</span></td>
                                <td><span class="badge badge-success">Completed</span></td>
                                <td>10:55 PM</td>
                                <td>
                                    <button class="btn btn-primary btn-small">View</button>
                                    <button class="btn btn-secondary btn-small">Edit</button>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>David Wilson</strong><br><small>RN, Surgery</small></td>
                                <td>Day Shift<br><small>7:00 AM - 7:00 PM</small></td>
                                <td>Dec 15, 2024</td>
                                <td>4 patients</td>
                                <td><span style="color:#ef4444;font-weight:bold;">1 Critical</span></td>
                                <td><span class="badge badge-danger">Pending Review</span></td>
                                <td>7:15 PM</td>
                                <td>
                                    <button class="btn btn-primary btn-small">Review</button>
                                    <button class="btn btn-secondary btn-small">View</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Create Shift Report Modal -->
            <div class="modal" id="createReportModal">
                <div class="modal-content" style="max-width: 800px;">
                    <div class="modal-header">
                        <h3>Create New Shift Report</h3>
                        <button class="modal-close" data-close>&times;</button>
                    </div>
                    <div class="modal-body">
                        <form id="createReportForm">
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                                <div>
                                    <label>Nurse Name</label>
                                    <input type="text" class="form-input" name="nurse_name" value="<?= \App\Helpers\UserHelper::getDisplayName($currentUser ?? null) ?>" readonly>
                                </div>
                                <div>
                                    <label>Department</label>
                                    <select class="form-input" name="department" required>
                                        <option value="">Select Department</option>
                                        <option value="ICU">ICU</option>
                                        <option value="Emergency">Emergency</option>
                                        <option value="Pediatrics">Pediatrics</option>
                                        <option value="Surgery">Surgery</option>
                                        <option value="General">General Ward</option>
                                        <option value="Maternity">Maternity</option>
                                    </select>
                                </div>
                            </div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                                <div>
                                    <label>Shift Type</label>
                                    <select class="form-input" name="shift_type" required>
                                        <option value="">Select Shift</option>
                                        <option value="day">Day Shift (7:00 AM - 7:00 PM)</option>
                                        <option value="night">Night Shift (7:00 PM - 7:00 AM)</option>
                                        <option value="evening">Evening Shift (3:00 PM - 11:00 PM)</option>
                                    </select>
                                </div>
                                <div>
                                    <label>Date</label>
                                    <input type="date" class="form-input" name="shift_date" required>
                                </div>
                            </div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                                <div>
                                    <label>Total Patients</label>
                                    <input type="number" class="form-input" name="total_patients" placeholder="Number of patients" min="0" required>
                                </div>
                                <div>
                                    <label>Critical Events</label>
                                    <input type="number" class="form-input" name="critical_events" placeholder="Number of critical events" min="0">
                                </div>
                            </div>
                            <div style="margin-bottom:1rem;">
                                <label>Patient Care Summary</label>
                                <textarea class="form-input" name="patient_care" rows="4" placeholder="Summarize patient care activities, treatments administered, and patient responses..." required></textarea>
                            </div>
                            <div style="margin-bottom:1rem;">
                                <label>Critical Incidents/Events</label>
                                <textarea class="form-input" name="critical_incidents" rows="3" placeholder="Document any critical incidents, emergencies, or significant events during the shift..."></textarea>
                            </div>
                            <div style="margin-bottom:1rem;">
                                <label>Medication Administration</label>
                                <textarea class="form-input" name="medication_admin" rows="3" placeholder="Document medications administered, any issues or reactions observed..."></textarea>
                            </div>
                            <div style="margin-bottom:1rem;">
                                <label>Handover Notes</label>
                                <textarea class="form-input" name="handover_notes" rows="4" placeholder="Important information to pass on to the next shift..." required></textarea>
                            </div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                                <div>
                                    <label>Equipment Issues</label>
                                    <textarea class="form-input" name="equipment_issues" rows="2" placeholder="Any equipment problems or maintenance needs..."></textarea>
                                </div>
                                <div>
                                    <label>Staffing Notes</label>
                                    <textarea class="form-input" name="staffing_notes" rows="2" placeholder="Staffing levels, coverage issues, etc..."></textarea>
                                </div>
                            </div>
                            <div>
                                <label>Overall Shift Assessment</label>
                                <select class="form-input" name="shift_assessment" required>
                                    <option value="">Select Assessment</option>
                                    <option value="routine">Routine - No major issues</option>
                                    <option value="busy">Busy - High patient load</option>
                                    <option value="challenging">Challenging - Multiple critical cases</option>
                                    <option value="critical">Critical - Emergency situations</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-close>Cancel</button>
                        <button type="submit" form="createReportForm" class="btn btn-success">Submit Report</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        const createReportModal = document.getElementById('createReportModal');
        function openModal(m){ m.classList.add('show'); document.body.style.overflow='hidden'; }
        function closeModal(m){ m.classList.remove('show'); document.body.style.overflow='auto'; }
        document.getElementById('createReportBtn').addEventListener('click', () => {
            const today = new Date().toISOString().split('T')[0];
            document.querySelector('input[name="shift_date"]').value = today;
            openModal(createReportModal);
        });
        document.querySelectorAll('[data-close]').forEach(btn => btn.addEventListener('click', () => closeModal(createReportModal)));
        window.addEventListener('click', (e) => { if (e.target === createReportModal) closeModal(createReportModal); });
        document.getElementById('createReportForm').addEventListener('submit', function(e){ e.preventDefault(); alert('Shift report submitted successfully!'); closeModal(createReportModal); this.reset(); });
    </script>

    <script src="<?= base_url('js/logout.js') ?>"></script>
</body>
</html>
