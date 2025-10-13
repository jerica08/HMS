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
                <!-- Today's Appointments Card -->
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue"><i class="fas fa-calendar-day"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Today's Appointments</h3>
                            <p class="card-subtitle"><?= date('F j, Y') ?></p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value blue"><?= $todayStats['total'] ?? 0 ?></div>
                            <div class="metric-label">Total</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value green"><?= $todayStats['completed'] ?? 0 ?></div>
                            <div class="metric-label">Completed</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value orange"><?= $todayStats['pending'] ?? 0 ?></div>
                            <div class="metric-label">Pending</div>
                        </div>
                    </div>
                </div>

                <!-- This Week Card -->
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern purple"><i class="fas fa-calendar-week"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">This Week</h3>
                            <p class="card-subtitle">Weekly overview</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value purple"><?= $weekStats['total'] ?? 0 ?></div>
                            <div class="metric-label">Scheduled</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value red"><?= $weekStats['cancelled'] ?? 0 ?></div>
                            <div class="metric-label">Cancelled</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value orange"><?= $weekStats['no_shows'] ?? 0 ?></div>
                            <div class="metric-label">No-shows</div>
                        </div>
                    </div>
                </div>

                <!-- Today's Schedule Card -->
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern green"><i class="fas fa-clock"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Today's Schedule</h3>
                            <p class="card-subtitle">Current time: <?= date('h:i A') ?></p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value green"><?= $scheduleStats['next_appointment'] ?? 'None' ?></div>
                            <div class="metric-label">Next Appointment</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value blue"><?= $scheduleStats['hours_scheduled'] ?? 0 ?></div>
                            <div class="metric-label">Hours Scheduled</div>
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
                    <h3 id="scheduleTitle">Today's Schedule - <?= date('F j, Y') ?></h3>
                    <div style="display: flex; gap: 0.5rem;">
                        <button class="btn btn-primary btn-small" id="printBtn">
                            <i class="fas fa-print"></i> Print Schedule
                        </button>
                        <button class="btn btn-secondary btn-small" id="refreshBtn">
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
                    <tbody id="appointmentsTableBody">
                        <?php if (!empty($todayAppointments) && is_array($todayAppointments)): ?>
                            <?php foreach ($todayAppointments as $appointment): ?>
                                <tr>
                                    <td>
                                        <strong><?= date('g:i A', strtotime($appointment['appointment_time'] ?? '00:00:00')) ?></strong>
                                        <?php if (!empty($appointment['duration'])): ?>
                                            <br><small><?= esc($appointment['duration']) ?> min</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?= esc(($appointment['patient_first_name'] ?? '') . ' ' . ($appointment['patient_last_name'] ?? '')) ?></strong><br>
                                            <small><?= esc($appointment['patient_id'] ?? 'N/A') ?> | Age: <?= esc($appointment['patient_age'] ?? 'N/A') ?></small>
                                        </div>
                                    </td>
                                    <td><?= esc($appointment['appointment_type'] ?? 'N/A') ?></td>
                                    <td><?= esc($appointment['reason'] ?? 'General consultation') ?></td>
                                    <td><?= esc($appointment['duration'] ?? '30') ?> min</td>
                                    <td>
                                        <?php 
                                            $status = $appointment['status'] ?? 'scheduled';
                                            $badgeClass = '';
                                            switch(strtolower($status)) {
                                                case 'completed': $badgeClass = 'badge-success'; break;
                                                case 'in-progress': $badgeClass = 'badge-info'; break;
                                                case 'cancelled': $badgeClass = 'badge-danger'; break;
                                                case 'no-show': $badgeClass = 'badge-warning'; break;
                                                default: $badgeClass = 'badge-info';
                                            }
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= esc(ucfirst($status)) ?></span>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem; flex-wrap: wrap;">
                                            <button class="btn btn-primary" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" onclick="viewAppointment(<?= esc($appointment['appointment_id'] ?? 0) ?>)">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <?php if (strtolower($status) !== 'completed'): ?>
                                                <button class="btn btn-success" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" onclick="markCompleted(<?= esc($appointment['appointment_id'] ?? 0) ?>)">
                                                    <i class="fas fa-check"></i> Complete
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn btn-secondary" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" onclick="rescheduleAppointment(<?= esc($appointment['appointment_id'] ?? 0) ?>)">
                                                <i class="fas fa-calendar-alt"></i> Reschedule
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2rem; color: #6b7280;">
                                    <i class="fas fa-calendar-times" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                                    <p>No appointments scheduled for today.</p>
                                    <button class="btn btn-primary" onclick="document.getElementById('scheduleAppointmentBtn').click()">
                                        <i class="fas fa-plus"></i> Schedule New Appointment
                                    </button>
                                </td>
                            </tr>
                        <?php endif; ?>
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
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>" id="csrfToken">
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
                        <input type="date" id="appointmentDate" name="appointmentDate" class="filter-input" required style="width: 100%;">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="appointmentTime" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Time</label>
                        <input type="time" id="appointmentTime" name="appointmentTime" class="filter-input" required style="width: 100%;">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="appointmentType" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Type</label>
                        <select id="appointmentType" name="appointmentType" class="filter-input" required style="width: 100%;">
                            <option value="">Select Type</option>
                            <option value="Consultation">Consultation</option>
                            <option value="Follow-up">Follow-up</option>
                            <option value="Check-up">Check-up</option>
                            <option value="Emergency">Emergency</option>
                        </select>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="appointmentReason" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Reason/Condition</label>
                        <textarea id="appointmentReason" name="appointmentReason" class="filter-input" rows="3" placeholder="Describe the reason for the appointment" style="width: 100%; resize: vertical;"></textarea>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="appointmentDuration" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Duration (minutes)</label>
                        <input type="number" id="appointmentDuration" name="appointmentDuration" class="filter-input" min="15" max="120" step="15" value="30" required style="width: 100%;">
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
        // Global variables
        let currentView = 'today';
        let currentDate = new Date();
        
        // Modal functionality
        const modal = document.getElementById('scheduleModal');
        const scheduleBtn = document.getElementById('scheduleAppointmentBtn');
        const closeModal = document.getElementById('closeModal');
        const cancelBtn = document.getElementById('cancelBtn');
        const saveBtn = document.getElementById('saveBtn');
        const form = document.getElementById('scheduleForm');

        // View switching functionality
        const todayViewBtn = document.getElementById('todayView');
        const weekViewBtn = document.getElementById('weekView');
        const monthViewBtn = document.getElementById('monthView');
        const dateSelector = document.getElementById('dateSelector');
        const refreshBtn = document.getElementById('refreshBtn');
        const scheduleTitle = document.getElementById('scheduleTitle');

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Set current date
            dateSelector.value = formatDate(currentDate);
            
            // Auto-refresh every 5 minutes
            setInterval(refreshAppointments, 300000);
            
            // Update current time every minute
            setInterval(updateCurrentTime, 60000);
        });

        // View switching
        todayViewBtn.addEventListener('click', () => switchView('today'));
        weekViewBtn.addEventListener('click', () => switchView('week'));
        monthViewBtn.addEventListener('click', () => switchView('month'));
        dateSelector.addEventListener('change', () => {
            currentDate = new Date(dateSelector.value);
            refreshAppointments();
        });
        refreshBtn.addEventListener('click', refreshAppointments);

        function switchView(view) {
            currentView = view;
            
            // Update button states
            document.querySelectorAll('.btn-group .btn').forEach(btn => {
                btn.classList.remove('btn-primary', 'active');
                btn.classList.add('btn-secondary');
            });
            
            const activeBtn = document.getElementById(view + 'View');
            activeBtn.classList.remove('btn-secondary');
            activeBtn.classList.add('btn-primary', 'active');
            
            // Update title
            updateScheduleTitle();
            
            // Refresh data
            refreshAppointments();
        }

        function updateScheduleTitle() {
            let title = '';
            switch(currentView) {
                case 'today':
                    title = "Today's Schedule - " + formatDateDisplay(currentDate);
                    break;
                case 'week':
                    title = "Weekly Schedule - " + getWeekRange(currentDate);
                    break;
                case 'month':
                    title = "Monthly Schedule - " + currentDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
                    break;
            }
            scheduleTitle.textContent = title;
        }

        function refreshAppointments() {
            const tbody = document.getElementById('appointmentsTableBody');
            tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 2rem;"><i class="fas fa-spinner fa-spin"></i> Loading appointments...</td></tr>';
            
            // AJAX call to fetch appointments
            fetch(`<?= base_url('doctor/appointments/data') ?>?view=${currentView}&date=${formatDate(currentDate)}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                updateAppointmentsTable(data.appointments || []);
                updateStats(data.stats || {});
            })
            .catch(error => {
                console.error('Error:', error);
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 2rem; color: #ef4444;"><i class="fas fa-exclamation-triangle"></i> Error loading appointments</td></tr>';
            });
        }

        function updateAppointmentsTable(appointments) {
            const tbody = document.getElementById('appointmentsTableBody');
            
            if (appointments.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2rem; color: #6b7280;">
                            <i class="fas fa-calendar-times" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                            <p>No appointments found for the selected ${currentView}.</p>
                            <button class="btn btn-primary" onclick="document.getElementById('scheduleAppointmentBtn').click()">
                                <i class="fas fa-plus"></i> Schedule New Appointment
                            </button>
                        </td>
                    </tr>
                `;
                return;
            }
            
            tbody.innerHTML = appointments.map(appointment => {
                const status = appointment.status || 'scheduled';
                const badgeClass = getBadgeClass(status);
                
                return `
                    <tr>
                        <td>
                            <strong>${formatTime(appointment.appointment_time)}</strong>
                            ${appointment.duration ? `<br><small>${appointment.duration} min</small>` : ''}
                        </td>
                        <td>
                            <div>
                                <strong>${appointment.patient_first_name || ''} ${appointment.patient_last_name || ''}</strong><br>
                                <small>${appointment.patient_id || 'N/A'} | Age: ${appointment.patient_age || 'N/A'}</small>
                            </div>
                        </td>
                        <td>${appointment.appointment_type || 'N/A'}</td>
                        <td>${appointment.reason || 'General consultation'}</td>
                        <td>${appointment.duration || '30'} min</td>
                        <td><span class="badge ${badgeClass}">${status.charAt(0).toUpperCase() + status.slice(1)}</span></td>
                        <td>
                            <div style="display: flex; gap: 0.25rem; flex-wrap: wrap;">
                                <button class="btn btn-primary" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" onclick="viewAppointment(${appointment.appointment_id})">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                ${status !== 'completed' ? `
                                    <button class="btn btn-success" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" onclick="markCompleted(${appointment.appointment_id})">
                                        <i class="fas fa-check"></i> Complete
                                    </button>
                                ` : ''}
                                <button class="btn btn-secondary" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" onclick="rescheduleAppointment(${appointment.appointment_id})">
                                    <i class="fas fa-calendar-alt"></i> Reschedule
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function updateStats(stats) {
            // Update dashboard cards with new stats
            if (stats.today) {
                document.querySelector('.overview-card:nth-child(1) .metric:nth-child(1) .metric-value').textContent = stats.today.total || 0;
                document.querySelector('.overview-card:nth-child(1) .metric:nth-child(2) .metric-value').textContent = stats.today.completed || 0;
                document.querySelector('.overview-card:nth-child(1) .metric:nth-child(3) .metric-value').textContent = stats.today.pending || 0;
            }
            
            if (stats.week) {
                document.querySelector('.overview-card:nth-child(2) .metric:nth-child(1) .metric-value').textContent = stats.week.total || 0;
                document.querySelector('.overview-card:nth-child(2) .metric:nth-child(2) .metric-value').textContent = stats.week.cancelled || 0;
                document.querySelector('.overview-card:nth-child(2) .metric:nth-child(3) .metric-value').textContent = stats.week.no_shows || 0;
            }
        }

        // Appointment management functions
        function viewAppointment(appointmentId) {
            // Open appointment details modal
            window.location.href = `<?= base_url('doctor/appointment/view/') ?>${appointmentId}`;
        }

        function markCompleted(appointmentId) {
            if (confirm('Mark this appointment as completed?')) {
                updateAppointmentStatus(appointmentId, 'completed');
            }
        }

        function rescheduleAppointment(appointmentId) {
            // Open reschedule modal or redirect
            window.location.href = `<?= base_url('doctor/appointment/reschedule/') ?>${appointmentId}`;
        }

        function updateAppointmentStatus(appointmentId, status) {
            fetch(`<?= base_url('doctor/appointment/update-status') ?>`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    appointment_id: appointmentId,
                    status: status,
                    csrf_token: '<?= csrf_token() ?>'
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    refreshAppointments();
                    showNotification('Appointment status updated successfully', 'success');
                } else {
                    showNotification('Error updating appointment status', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred', 'error');
            });
        }

        // Utility functions
        function formatDate(date) {
            return date.toISOString().split('T')[0];
        }

        function formatDateDisplay(date) {
            return date.toLocaleDateString('en-US', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
        }

        function formatTime(timeString) {
            if (!timeString) return 'N/A';
            const time = new Date('2000-01-01 ' + timeString);
            return time.toLocaleTimeString('en-US', { 
                hour: 'numeric', 
                minute: '2-digit', 
                hour12: true 
            });
        }

        function getWeekRange(date) {
            const startOfWeek = new Date(date);
            startOfWeek.setDate(date.getDate() - date.getDay());
            const endOfWeek = new Date(startOfWeek);
            endOfWeek.setDate(startOfWeek.getDate() + 6);
            
            return `${startOfWeek.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${endOfWeek.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}`;
        }

        function getBadgeClass(status) {
            switch(status.toLowerCase()) {
                case 'completed': return 'badge-success';
                case 'in-progress': return 'badge-info';
                case 'cancelled': return 'badge-danger';
                case 'no-show': return 'badge-warning';
                default: return 'badge-info';
            }
        }

        function updateCurrentTime() {
            const now = new Date();
            const timeElement = document.querySelector('.overview-card:nth-child(3) .card-subtitle');
            if (timeElement) {
                timeElement.textContent = `Current time: ${now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })}`;
            }
        }

        function showNotification(message, type) {
            // Simple notification system
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem;
                border-radius: 4px;
                color: white;
                background: ${type === 'success' ? '#10b981' : '#ef4444'};
                z-index: 10000;
                animation: slideIn 0.3s ease;
            `;
            
            document.body.appendChild(notification);
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Modal functionality
        scheduleBtn.addEventListener('click', () => {
            modal.classList.add('show');
        });

        closeModal.addEventListener('click', () => {
            modal.classList.remove('show');
            form.reset();
        });

        cancelBtn.addEventListener('click', () => {
            modal.classList.remove('show');
            form.reset();
        });

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
                    patient_id: formData.get('patient_id'),
                    date: formData.get('appointmentDate'),
                    time: formData.get('appointmentTime'),
                    type: formData.get('appointmentType'),
                    reason: formData.get('appointmentReason'),
                    duration: formData.get('appointmentDuration'),
                    csrf_token: document.getElementById('csrfToken').value
                };

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
                        showNotification('Appointment scheduled successfully!', 'success');
                        modal.classList.remove('show');
                        form.reset();
                        refreshAppointments();
                    } else {
                        showNotification('Error scheduling appointment: ' + (result.message || 'Unknown error'), 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while scheduling the appointment.', 'error');
                });
            } else {
                showNotification('Please fill in all required fields.', 'error');
            }
        });
    </script>
</body>
</html>
