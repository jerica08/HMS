<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Appointment Management - HMS Doctor</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        /* Scoped styles for Doctor Appointments page */
        .filter-row { display: flex; gap: 1rem; align-items: end; flex-wrap: wrap; }
        .filter-input { padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.9rem; }
        .btn-group .btn { border-radius: 6px; }

        .patient-table { background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
        .table-header { background: #f9fafb; padding: 1rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
        .btn-small { padding: .5rem 1rem; font-size: .8rem; }

        /* Table styling */
        .patient-table { overflow-x: auto; }
        .table { width: 100%; border-collapse: separate; border-spacing: 0; min-width: 720px; }
        .table thead th { background: #f8fafc; color: #374151; font-weight: 600; text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #e5e7eb; }
        .table tbody td { padding: 0.75rem 1rem; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
        .table tbody tr:hover { background: #f9fafb; }
        .table th:nth-child(6), .table th:nth-child(7), .table td:nth-child(6), .table td:nth-child(7) { text-align: center; }

        /* Badges */
        .badge { display: inline-block; padding: 0.25rem 0.6rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        .badge-danger { background: #fecaca; color: #991b1b; }

        /* Modal base */
        .modal { display: none; position: fixed; z-index: 1000; inset: 0; background: rgba(0,0,0,0.5); }
        .modal.show { display: flex; align-items: center; justify-content: center; }
        .modal-content { background: #fff; border-radius: 8px; width: 90%; max-width: 700px; max-height: 90vh; overflow-y: auto; box-shadow: 0 4px 20px rgba(0,0,0,0.15); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem; border-bottom: 1px solid #e2e8f0; background: #f7fafc; }
        .modal-header h3 { margin: 0; color: #2d3748; }
        .modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #718096; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; }
        .modal-close:hover { color: #2d3748; }
        .modal-body { padding: 1.25rem; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; padding: 1rem; border-top: 1px solid #e2e8f0; background: #f7fafc; }

        @media (max-width: 640px) {
            .table-header { flex-direction: column; gap: 0.75rem; align-items: flex-start; }
        }
    </style>
</head>
<body class="doctor">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <?php include APPPATH . 'Views/doctor/components/sidebar.php'; ?>

        <main class="content">
            <h1 class="page-title">Appointments</h1>
            <div class="page-actions">
                <button class="btn btn-success" id="scheduleAppointmentBtn">
                    <i class="fas fa-plus"></i> Schedule Appointments
                </button>
            </div><br>

            <div class="dashboard-overview">
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue"><i class="fas fa-users"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Today's Appointments</h3>
                            <p class="card-subtitle">Schedule for today</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value blue">0</div>
                            <div class="metric-label">Total</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value green">0</div>
                            <div class="metric-label">Completed</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value orange">0</div>
                            <div class="metric-label">Remaining</div>
                        </div>
                    </div>
                </div>

                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue"><i class="fas fa-calendar-week"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">This Week</h3>
                            <p class="card-subtitle">Weekly overview</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value blue">0</div>
                            <div class="metric-label">Scheduled</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value green">0</div>
                            <div class="metric-label">Cancelled</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value orange">0</div>
                            <div class="metric-label">No-shows</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="patient-table">
                <div class="search-filters">
                    <h3 style="margin-bottom: 1rem;">View Options</h3>
                    <div class="filter-row">
                        <div class="btn-group">
                            <button class="btn btn-primary active" id="todayView">Today</button>
                            <button class="btn btn-secondary" id="weekView">Week</button>
                            <button class="btn btn-secondary" id="monthView">Month</button>
                        </div>
                        <div>
                            <input type="date" class="filter-input" id="dateSelector" value="2025-08-20">
                        </div>
                        <div>
                            <select class="filter-input" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="scheduled">Scheduled</option>
                                <option value="in-progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="no-show">No Show</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="table-header">
                    <h3>Today's Schedule - August 20, 2025</h3>
                    <div style="display: flex; gap: 0.5rem;">
                        <button class="btn btn-primary btn-small" id="printBtn">
                            <i class="fas fa-print"></i> Print Schedule
                        </button>
                        <button class="btn btn-secondary btn-small" id="exportBtn">
                            <i class="fas fa-sync"></i> Refresh
                        </button>
                    </div>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Patient</th>
                            <th>Type</th>
                            <th>Condition/Reason</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>10:00 AM</td>
                            <td>
                                <div>
                                    <strong>Sarah Wilson</strong><br>
                                    <small>P0012347 | Age: 45</small>
                                </div>
                            </td>
                            <td>Follow-up</td>
                            <td>Hypertension Management</td>
                            <td>30 min</td>
                            <td><span class="badge badge-success">Completed</span></td>
                            <td>
                                <button class="btn btn-primary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">View Notes</button>
                                <button class="btn btn-secondary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Reschedule</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Schedule Appointment Modal -->
    <div id="scheduleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Schedule New Appointment</h3>
                <button class="modal-close" id="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="scheduleForm">
                    <div style="margin-bottom: 1rem;">
                        <label for="patientSelect" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Patient</label>
                        <select id="patientSelect" name="patient_id" class="filter-input" required style="width: 100%;">
                            <option value="">Select Patient</option>
                            <?php if (!empty($patients)): ?>
                                <?php foreach ($patients as $patient): ?>
                                    <option value="<?= esc($patient['patient_id']) ?>"><?= esc($patient['first_name'] . ' ' . $patient['last_name']) ?> (<?= esc($patient['patient_id']) ?>)</option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="appointmentDate" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Date</label>
                        <input type="date" id="appointmentDate" class="filter-input" required style="width: 100%;">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="appointmentTime" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Time</label>
                        <input type="time" id="appointmentTime" class="filter-input" required style="width: 100%;">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="appointmentType" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Type</label>
                        <select id="appointmentType" class="filter-input" required style="width: 100%;">
                            <option value="">Select Type</option>
                            <option value="consultation">Consultation</option>
                            <option value="follow-up">Follow-up</option>
                            <option value="check-up">Check-up</option>
                            <option value="emergency">Emergency</option>
                        </select>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="appointmentReason" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Reason/Condition</label>
                        <textarea id="appointmentReason" class="filter-input" rows="3" placeholder="Describe the reason for the appointment" style="width: 100%; resize: vertical;"></textarea>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="appointmentDuration" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Duration (minutes)</label>
                        <input type="number" id="appointmentDuration" class="filter-input" min="15" max="120" step="15" value="30" required style="width: 100%;">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                <button class="btn btn-success" id="saveBtn">Schedule Appointment</button>
            </div>
        </div>
    </div>

    <script src="<?= base_url('js/logout.js') ?>"></script>
    <script>
        // Modal functionality
        const modal = document.getElementById('scheduleModal');
        const scheduleBtn = document.getElementById('scheduleAppointmentBtn');
        const closeModal = document.getElementById('closeModal');
        const cancelBtn = document.getElementById('cancelBtn');
        const saveBtn = document.getElementById('saveBtn');
        const form = document.getElementById('scheduleForm');

        // Show modal
        scheduleBtn.addEventListener('click', () => {
            modal.classList.add('show');
        });

        // Hide modal
        closeModal.addEventListener('click', () => {
            modal.classList.remove('show');
            form.reset();
        });

        cancelBtn.addEventListener('click', () => {
            modal.classList.remove('show');
            form.reset();
        });

        // Close modal when clicking outside
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('show');
                form.reset();
            }
        });

        // Handle form submission
        saveBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (form.checkValidity()) {
                const formData = new FormData(form);
                const data = {
                    patient_id: formData.get('patientSelect'),
                    date: formData.get('appointmentDate'),
                    time: formData.get('appointmentTime'),
                    type: formData.get('appointmentType'),
                    reason: formData.get('appointmentReason'),
                    duration: formData.get('appointmentDuration')
                };

                // AJAX submission (adjust URL as needed)
                fetch('<?= base_url('doctor/schedule-appointment') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert('Appointment scheduled successfully!');
                        modal.classList.remove('show');
                        form.reset();
                        // Optionally refresh the page or update the table
                        location.reload();
                    } else {
                        alert('Error scheduling appointment: ' + result.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while scheduling the appointment.');
                });
            } else {
                alert('Please fill in all required fields.');
            }
        });
    </script>
</body>
</html>
