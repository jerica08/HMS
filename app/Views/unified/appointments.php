<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= base_url() ?>">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta name="csrf-hash" content="<?= csrf_hash() ?>">
    <meta name="user-role" content="<?= esc($userRole ?? 'guest') ?>">
    <title><?= esc($title ?? 'Appointment Management') ?> - HMS</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('assets/css/unified/appointments.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <script src="<?= base_url('assets/js/unified/prescription-management.js') ?>"></script>
</head>

<?php include APPPATH . 'Views/template/header.php'; ?> 
<div class="main-container">
    <!-- Unified Sidebar -->
     <?php include APPPATH . 'Views/unified/components/sidebar.php'; ?>

    <main class="content" role="main">
        <h1 class="page-title">
            <i class="fas fa-calendar-alt"></i>
            <?php 
            $pageTitles = [
                'admin' => 'System Appointments',
                'doctor' => 'My Appointments',
                'nurse' => 'Department Appointments',
                'receptionist' => 'Appointment Booking'
            ];
            echo esc($pageTitles[$userRole] ?? 'Appointments');
            ?>
        </h1>
        <div class="page-actions">
            <?php if (in_array($userRole, ['admin', 'doctor', 'receptionist'])): ?>
                <button type="button" class="btn btn-primary" id="scheduleAppointmentBtn" aria-label="Add New Appointment">
                    <i class="fas fa-plus" aria-hidden="true"></i> Add Appointment
                </button>
            <?php endif; ?>
            <?php if (in_array($userRole ?? '', ['admin', 'doctor'])): ?>
                <button type="button" class="btn btn-secondary" id="exportBtn" aria-label="Export Data">
                    <i class="fas fa-download" aria-hidden="true"></i> Export
                </button>
            <?php endif; ?>
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

        <!-- Statistics Overview -->
        <div class="dashboard-overview" role="region" aria-label="Dashboard Overview Cards">
            <!-- Today's Appointments Card -->
            <div class="overview-card" tabindex="0">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue"><i class="fas fa-calendar-day"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Today's Appointments</h3>
                            <p class="card-subtitle"><?= date('F j, Y') ?></p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value blue"><?= $appointmentStats['today_total'] ?? 0 ?></div>
                            <div class="metric-label">Total</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value green"><?= $appointmentStats['today_completed'] ?? 0 ?></div>
                            <div class="metric-label">Completed</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value orange"><?= $appointmentStats['today_pending'] ?? 0 ?></div>
                            <div class="metric-label">Pending</div>
                        </div>
                    </div>
                </div>

            <!-- This Week Card -->
            <div class="overview-card" tabindex="0">
                    <div class="card-header-modern">
                        <div class="card-icon-modern purple"><i class="fas fa-calendar-alt"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">This Week</h3>
                            <p class="card-subtitle">Weekly overview</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value purple"><?= $appointmentStats['week_total'] ?? 0 ?></div>
                            <div class="metric-label">Scheduled</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value red"><?= $appointmentStats['week_cancelled'] ?? 0 ?></div>
                            <div class="metric-label">Cancelled</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value orange"><?= $appointmentStats['week_no_shows'] ?? 0 ?></div>
                            <div class="metric-label">No-shows</div>
                        </div>
                    </div>
                </div>

            <!-- Schedule Overview Card -->
            <div class="overview-card" tabindex="0">
                    <div class="card-header-modern">
                        <div class="card-icon-modern green"><i class="fas fa-clock"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">
                                <?= $userRole === 'admin' ? 'System Status' : 'Today\'s Schedule' ?>
                            </h3>
                            <p class="card-subtitle">Current time: <?= date('h:i A') ?></p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value green"><?= $appointmentStats['next_appointment'] ?? 'None' ?></div>
                            <div class="metric-label">
                                <?= $userRole === 'admin' ? 'Active Doctors' : 'Next Appointment' ?>
                            </div>
                        </div>
                        <div class="metric">
                            <div class="metric-value blue"><?= $appointmentStats['hours_scheduled'] ?? 0 ?></div>
                            <div class="metric-label">
                                <?= $userRole === 'admin' ? 'Total Hours' : 'Hours Scheduled' ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Appointments Table -->
            <div class="patient-table">
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
                            <?php if ($userRole === 'admin'): ?>
                                <th>Doctor</th>
                            <?php endif; ?>
                            <th>Type</th>
                            <th>Condition/Reason</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="appointmentsTableBody">
                        <?php if (!empty($appointments) && is_array($appointments)): ?>
                            <?php foreach ($appointments as $appointment): ?>
                                <tr>
                                    <td>
                                        <strong><?= date('g:i A', strtotime($appointment['appointment_time'] ?? '00:00:00')) ?></strong>
                                        <?php if (!empty($appointment['duration'])): ?>
                                            <br><small><?= esc($appointment['duration']) ?> min</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>
                                                <a href="<?= base_url($userRole . '/patient-management?patient_id=' . ($appointment['patient_id'] ?? '')) ?>" 
                                                   class="patient-link" style="color: #3b82f6; text-decoration: none;">
                                                    <?= esc(($appointment['patient_first_name'] ?? '') . ' ' . ($appointment['patient_last_name'] ?? '')) ?>
                                                </a>
                                            </strong><br>
                                            <small style="color: #6b7280;">
                                                ID: <?= esc($appointment['patient_id'] ?? 'N/A') ?> | 
                                                Age: <?= esc($appointment['patient_age'] ?? 'N/A') ?> |
                                                Phone: <?= esc($appointment['patient_phone'] ?? 'N/A') ?>
                                            </small>
                                        </div>
                                    </td>
                                    <?php if ($userRole === 'admin'): ?>
                                    <td>
                                        <div>
                                            <strong>Dr. <?= esc(($appointment['doctor_first_name'] ?? '') . ' ' . ($appointment['doctor_last_name'] ?? '')) ?></strong><br>
                                            <small><?= esc($appointment['doctor_department'] ?? 'N/A') ?></small>
                                        </div>
                                    </td>
                                    <?php endif; ?>
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
                                            <?php if (in_array($userRole, ['admin', 'doctor']) && strtolower($status) !== 'completed'): ?>
                                                <button class="btn btn-success" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" onclick="markCompleted(<?= esc($appointment['appointment_id'] ?? 0) ?>)">
                                                    <i class="fas fa-check"></i> Complete
                                                </button>
                                            <?php endif; ?>
                                            <?php if (in_array($userRole, ['admin', 'doctor', 'receptionist'])): ?>
                                                <button class="btn btn-warning" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" onclick="editAppointment(<?= esc($appointment['appointment_id'] ?? 0) ?>)">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                            <?php endif; ?>
                                            <?php if (in_array($userRole, ['admin', 'doctor'])): ?>
                                                <button class="btn btn-info" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" onclick="openPrescriptionModal(<?= esc($appointment['appointment_id'] ?? 0) ?>, <?= esc($appointment['patient_id'] ?? 0) ?>)">
                                                    <i class="fas fa-prescription-bottle"></i> Prescription
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($userRole === 'admin'): ?>
                                                <button class="btn btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" onclick="deleteAppointment(<?= esc($appointment['appointment_id'] ?? 0) ?>)">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= $userRole === 'admin' ? '8' : '7' ?>" style="text-align: center; padding: 2rem; color: #6b7280;">
                                    <i class="fas fa-calendar-times" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                                    <p>No appointments found for the selected criteria.</p>
                                    <?php if (in_array($userRole, ['admin', 'doctor', 'receptionist'])): ?>
                                        <button class="btn btn-primary" onclick="document.getElementById('scheduleAppointmentBtn').click()">
                                            <i class="fas fa-plus"></i> Schedule New Appointment
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <div id="appointmentsNotification" role="alert" aria-live="polite" style="
        display:none; margin: 1rem 1.5rem 0 1.5rem; padding: 0.75rem 1rem; border-radius: 8px;
        border: 1px solid #86efac; background: #dcfce7; color: #166534;
        display:flex; align-items:center; gap:0.5rem;">
        <i id="appointmentsNotificationIcon" class="fas fa-check-circle" aria-hidden="true"></i>
        <span id="appointmentsNotificationText"></span>
        <button type="button" onclick="dismissAppointmentNotification()" aria-label="Dismiss notification" style="margin-left:auto; background:transparent; border:none; cursor:pointer; color:inherit;">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- Include modal with forced doctors data like shift management -->
    <?php 
    // Force set the doctors variable directly like shift management
    $doctors_for_modal = $doctors ?? [];
    
    include(APPPATH . 'Views/unified/modals/new-appointment-modal.php');
    ?>
    <?php include APPPATH . 'Views/unified/modals/view-appointment-modal.php'; ?>
    <?php include APPPATH . 'Views/unified/modals/add-prescription-modal.php'; ?>

    <script>
    // Export appointments to Excel
    function exportToExcel() {
        const table = document.querySelector('.table').cloneNode(true);
        
        // Remove action buttons column
        const headers = table.querySelectorAll('thead th');
        const lastHeaderIndex = headers.length - 1;
        headers[lastHeaderIndex].remove();
        
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length > 0) {
                cells[cells.length - 1].remove();
            }
        });
        
        // Create Excel content
        let html = '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
        html += '<head><meta charset="UTF-8">';
        html += '<style>';
        html += 'table { border-collapse: collapse; width: 100%; }';
        html += 'th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }';
        html += 'th { background-color: #667eea; color: white; font-weight: bold; }';
        html += 'tr:nth-child(even) { background-color: #f2f2f2; }';
        html += '</style></head><body>';
        html += '<h2>Appointment Schedule - <?= date("F j, Y") ?></h2>';
        html += '<table>' + table.innerHTML + '</table>';
        html += '</body></html>';
        
        const blob = new Blob([html], { type: 'application/vnd.ms-excel' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        const date = new Date().toISOString().split('T')[0];
        const userRole = document.querySelector('meta[name="user-role"]')?.content || 'user';
        
        link.setAttribute('href', url);
        link.setAttribute('download', `appointments_${userRole}_${date}.xls`);
        link.style.visibility = 'hidden';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Dismiss flash notification
    function dismissFlash() {
        const flash = document.getElementById('flashNotice');
        if (flash) flash.remove();
    }

    // Initialize export button
    document.addEventListener('DOMContentLoaded', function() {
        const exportBtn = document.getElementById('exportBtn');
        if (exportBtn) {
            exportBtn.addEventListener('click', exportToExcel);
        }

        // Initialize appointment modal
        initializeAppointmentModal();

        // Initialize filters
        const dateFilter = document.getElementById('dateSelector');
        const statusFilter = document.getElementById('statusFilter');
        const doctorFilter = document.getElementById('doctorFilter');
        const refreshBtnMain = document.getElementById('refreshBtn');

        if (dateFilter)   dateFilter.addEventListener('change', refreshAppointments);
        if (statusFilter) statusFilter.addEventListener('change', refreshAppointments);
        if (doctorFilter) doctorFilter.addEventListener('change', refreshAppointments);
        if (refreshBtnMain) refreshBtnMain.addEventListener('click', function(e) {
            e.preventDefault();
            refreshAppointments();
        });

        // Initial load based on filters
        refreshAppointments();

        // When date changes in the new appointment modal, reload available doctors
        const dateInput = document.getElementById('appointment_date');
        if (dateInput) {
            dateInput.addEventListener('change', function() {
                if (this.value) {
                    loadAvailableDoctors(this.value);
                }
            });
        }
    });

    // Appointment Modal Functions
    function initializeAppointmentModal() {
        console.log('Initializing appointment modal...');
        
        const scheduleBtn = document.getElementById('scheduleAppointmentBtn');
        console.log('Schedule button found:', !!scheduleBtn);
        
        if (scheduleBtn) {
            scheduleBtn.addEventListener('click', function() {
                console.log('Schedule button clicked!');
                openNewAppointmentModal();
            });
        }

        // Close modal when clicking outside
        const modal = document.getElementById('newAppointmentModal');
        console.log('Modal found:', !!modal);
        
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeNewAppointmentModal();
                }
            });
        }

        // Handle form submission
        const form = document.getElementById('newAppointmentForm');
        console.log('Form found:', !!form);
        
        if (form) {
            form.addEventListener('submit', handleAppointmentSubmit);
        }
    }

    function openNewAppointmentModal() {
        console.log('openNewAppointmentModal called!');
        const modal = document.getElementById('newAppointmentModal');
        console.log('Modal element:', modal);
        
        if (modal) {
            console.log('Opening modal - current classes:', modal.className);
            modal.classList.add('active');
            modal.removeAttribute('hidden');
            modal.setAttribute('aria-hidden', 'false');
            console.log('Modal opened - new classes:', modal.className);
            
            loadPatients();

            // Load available doctors for the selected date (simple: has schedule that day)
            const dateInput = document.getElementById('appointment_date');
            let dateValue = dateInput ? dateInput.value : '';

            if (!dateValue) {
                // Default to today if no date selected yet
                const today = new Date().toISOString().split('T')[0];
                if (dateInput) {
                    dateInput.value = today;
                }
                dateValue = today;
            }

            loadAvailableDoctors(dateValue);
        } else {
            console.error('Modal not found!');
        }
    }

    function closeNewAppointmentModal() {
        console.log('closeNewAppointmentModal called!');
        const modal = document.getElementById('newAppointmentModal');
        if (modal) {
            modal.classList.remove('active');
            modal.setAttribute('hidden', 'true');
            modal.setAttribute('aria-hidden', 'true');
            document.getElementById('newAppointmentForm').reset();
            clearFormErrors();
        }
    }

    function loadPatients() {
        const baseUrl = document.querySelector('meta[name="base-url"]').content;
        const patientSelect = document.getElementById('appointment_patient');
        
        fetch(`${baseUrl}/appointments/patients`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    patientSelect.innerHTML = '<option value="">Select Patient...</option>';
                    data.data.forEach(patient => {
                        const option = document.createElement('option');
                        option.value = patient.patient_id;
                        option.textContent = `${patient.first_name} ${patient.last_name}`;
                        patientSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading patients:', error);
            });
    }

    function getWeekdayName(dateStr) {
        const d = new Date(dateStr);
        const days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        if (isNaN(d.getTime())) return '';
        return days[d.getDay()];
    }

    function loadAvailableDoctors(date) {
        const baseUrl = document.querySelector('meta[name="base-url"]').content;
        const doctorSelect = document.getElementById('appointment_doctor');
        const dateHelp = document.getElementById('appointment_date_help');

        if (!doctorSelect) return; // Only for admin (doctor select not shown for others)

        doctorSelect.innerHTML = '<option value="">Loading available doctors...</option>';
        if (dateHelp) dateHelp.textContent = '';

        const weekday = getWeekdayName(date);
        const url = `${baseUrl}/appointments/available-doctors?date=${encodeURIComponent(date)}&weekday=${encodeURIComponent(weekday)}`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                doctorSelect.innerHTML = '<option value="">Select Doctor...</option>';

                if (data.status === 'success' && Array.isArray(data.data) && data.data.length) {
                    data.data.forEach(doctor => {
                        const option = document.createElement('option');
                        option.value = doctor.staff_id;
                        const specialization = doctor.specialization ? ` - ${doctor.specialization}` : '';
                        option.textContent = `${doctor.first_name} ${doctor.last_name}${specialization}`;
                        doctorSelect.appendChild(option);
                    });
                    if (dateHelp) dateHelp.textContent = 'Doctors listed are available on this date.';
                } else {
                    if (dateHelp) dateHelp.textContent = 'No doctors are available on this date.';
                }
            })
            .catch(error => {
                console.error('Error loading available doctors:', error);
                doctorSelect.innerHTML = '<option value="">Error loading doctors</option>';
                if (dateHelp) dateHelp.textContent = 'Failed to load doctor availability.';
            });
    }

    function handleAppointmentSubmit(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        
        const baseUrl = document.querySelector('meta[name="base-url"]').content;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        
        fetch(`${baseUrl}/appointments/create`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAppointmentsNotification('Appointment scheduled successfully.', 'success');
                closeNewAppointmentModal();
                setTimeout(function() {
                    location.reload();
                }, 800);
            } else {
                const generalError = document.getElementById('appointment_error');
                if (generalError) {
                    generalError.style.display = 'block';
                    generalError.textContent = data.message || 'Failed to schedule appointment. Please check the form and try again.';
                }
                // Also show toast notification for backend validation errors
                if (data.message) {
                    showAppointmentsNotification(data.message, 'error');
                }
                if (data.errors) {
                    showFormErrors(data.errors);
                }
                const dateHelp = document.getElementById('appointment_date_help');
                if (dateHelp && data.message) {
                    dateHelp.textContent = data.message;
                }
            }
        })
        .catch(error => {
            console.error('Error creating appointment:', error);
            const generalError = document.getElementById('appointment_error');
            if (generalError) {
                generalError.style.display = 'block';
                generalError.textContent = 'An unexpected error occurred while scheduling the appointment. Please try again.';
            }

            showAppointmentsNotification('Failed to schedule appointment. Please try again.', 'error');
        });
    }

    function clearFormErrors() {
        const errorElements = document.querySelectorAll('[id^="err_appointment_"]');
        errorElements.forEach(element => {
            element.textContent = '';
        });

        const generalError = document.getElementById('appointment_error');
        if (generalError) {
            generalError.style.display = 'none';
            generalError.textContent = '';
        }
    }

    function showFormErrors(errors) {
        clearFormErrors();
        Object.keys(errors).forEach(field => {
            const errorElement = document.getElementById(`err_appointment_${field}`);
            if (errorElement) {
                errorElement.textContent = errors[field];
            }
        });
    }

    function showAppointmentsNotification(message, type) {
        const container = document.getElementById('appointmentsNotification');
        const iconEl = document.getElementById('appointmentsNotificationIcon');
        const textEl = document.getElementById('appointmentsNotificationText');
        if (!container || !iconEl || !textEl) return;

        const isError = type === 'error';

        // Match user-management flashNotice styling
        container.style.border = isError ? '1px solid #fecaca' : '1px solid #86efac';
        container.style.background = isError ? '#fee2e2' : '#dcfce7';
        container.style.color = isError ? '#991b1b' : '#166534';

        iconEl.className = 'fas ' + (isError ? 'fa-exclamation-triangle' : 'fa-check-circle');
        textEl.textContent = message || '';

        container.style.display = 'flex';

        // Auto-hide after a few seconds
        setTimeout(function() {
            container.style.display = 'none';
        }, 4000);
    }

    function dismissAppointmentNotification() {
        const container = document.getElementById('appointmentsNotification');
        if (container) {
            container.style.display = 'none';
        }
    }

    function formatAppointmentTime(timeStr) {
        if (!timeStr) return '—';
        const [h, m] = timeStr.split(':');
        let hour = parseInt(h, 10);
        const minutes = m || '00';
        const ampm = hour >= 12 ? 'PM' : 'AM';
        hour = hour % 12;
        if (hour === 0) hour = 12;
        return `${hour}:${minutes} ${ampm}`;
    }

    function getStatusBadgeClass(status) {
        const s = (status || 'scheduled').toLowerCase();
        switch (s) {
            case 'completed': return 'badge-success';
            case 'in-progress': return 'badge-info';
            case 'cancelled': return 'badge-danger';
            case 'no-show': return 'badge-warning';
            default: return 'badge-info';
        }
    }

    function refreshAppointments() {
        const baseUrl = document.querySelector('meta[name="base-url"]').content;
        const userRole = document.querySelector('meta[name="user-role"]').content || 'guest';
        const params = new URLSearchParams();

        // Always show today's schedule
        const today = new Date().toISOString().split('T')[0];
        params.append('date', today);

        const url = `${baseUrl}/appointments/api?${params.toString()}`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    renderAppointmentsTable(data.data || []);
                } else {
                    renderAppointmentsTable([]);
                }

                // Title is already rendered with today's date by PHP
            })
            .catch(error => {
                console.error('Error loading appointments:', error);
                renderAppointmentsTable([]);
            });
    }

    function renderAppointmentsTable(appointments) {
        const tbody = document.getElementById('appointmentsTableBody');
        if (!tbody) return;

        const userRole = document.querySelector('meta[name="user-role"]').content || 'guest';
        const isAdmin = userRole === 'admin';

        while (tbody.firstChild) {
            tbody.removeChild(tbody.firstChild);
        }

        if (!appointments || !appointments.length) {
            const tr = document.createElement('tr');
            const td = document.createElement('td');
            td.colSpan = isAdmin ? 8 : 7;
            td.style.textAlign = 'center';
            td.style.padding = '2rem';
            td.style.color = '#6b7280';
            td.innerHTML = `
                <i class="fas fa-calendar-times" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                <p>No appointments found for the selected criteria.</p>
            `;
            tr.appendChild(td);
            tbody.appendChild(tr);
            return;
        }

        appointments.forEach(appt => {
            const tr = document.createElement('tr');

            const timeTd = document.createElement('td');
            const timeStrong = document.createElement('strong');
            timeStrong.textContent = formatAppointmentTime(appt.appointment_time);
            timeTd.appendChild(timeStrong);
            if (appt.duration) {
                const br = document.createElement('br');
                const small = document.createElement('small');
                small.textContent = `${appt.duration} min`;
                timeTd.appendChild(br);
                timeTd.appendChild(small);
            }
            tr.appendChild(timeTd);

            const patientTd = document.createElement('td');
            const patientDiv = document.createElement('div');
            const patientStrong = document.createElement('strong');
            const baseUrl = document.querySelector('meta[name="base-url"]').content;
            const link = document.createElement('a');
            link.href = `${baseUrl}/${userRole}/patient-management?patient_id=${appt.patient_id || ''}`;
            link.className = 'patient-link';
            link.style.color = '#3b82f6';
            link.style.textDecoration = 'none';
            link.textContent = `${appt.patient_first_name || ''} ${appt.patient_last_name || ''}`.trim();
            patientStrong.appendChild(link);
            patientDiv.appendChild(patientStrong);
            const br2 = document.createElement('br');
            const smallInfo = document.createElement('small');
            smallInfo.style.color = '#6b7280';
            const age = appt.patient_age != null ? appt.patient_age : 'N/A';
            const phone = appt.patient_phone || 'N/A';
            smallInfo.textContent = `ID: ${appt.patient_id || 'N/A'} | Age: ${age} | Phone: ${phone}`;
            patientDiv.appendChild(br2);
            patientDiv.appendChild(smallInfo);
            patientTd.appendChild(patientDiv);
            tr.appendChild(patientTd);

            if (isAdmin) {
                const doctorTd = document.createElement('td');
                const docDiv = document.createElement('div');
                const docStrong = document.createElement('strong');
                docStrong.textContent = `Dr. ${(appt.doctor_first_name || '') + ' ' + (appt.doctor_last_name || '')}`.trim();
                const br3 = document.createElement('br');
                const docSmall = document.createElement('small');
                docSmall.textContent = appt.doctor_department || 'N/A';
                docDiv.appendChild(docStrong);
                docDiv.appendChild(br3);
                docDiv.appendChild(docSmall);
                doctorTd.appendChild(docDiv);
                tr.appendChild(doctorTd);
            }

            const typeTd = document.createElement('td');
            typeTd.textContent = appt.appointment_type || 'N/A';
            tr.appendChild(typeTd);

            const reasonTd = document.createElement('td');
            reasonTd.textContent = appt.reason || 'General consultation';
            tr.appendChild(reasonTd);

            const durationTd = document.createElement('td');
            durationTd.textContent = `${appt.duration || 30} min`;
            tr.appendChild(durationTd);

            const statusTd = document.createElement('td');
            const badge = document.createElement('span');
            const status = appt.status || 'scheduled';
            badge.className = `badge ${getStatusBadgeClass(status)}`;
            badge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
            statusTd.appendChild(badge);
            tr.appendChild(statusTd);

            const actionsTd = document.createElement('td');
            const actionsDiv = document.createElement('div');
            actionsDiv.style.display = 'flex';
            actionsDiv.style.gap = '0.25rem';
            actionsDiv.style.flexWrap = 'wrap';

            const viewBtn = document.createElement('button');
            viewBtn.className = 'btn btn-primary';
            viewBtn.style.padding = '0.3rem 0.6rem';
            viewBtn.style.fontSize = '0.75rem';
            viewBtn.innerHTML = '<i class="fas fa-eye"></i> View';
            viewBtn.onclick = function() { viewAppointment(appt.appointment_id); };
            actionsDiv.appendChild(viewBtn);

            const statusLower = (appt.status || 'scheduled').toLowerCase();

            if ((userRole === 'admin' || userRole === 'doctor') && statusLower !== 'completed') {
                const completeBtn = document.createElement('button');
                completeBtn.className = 'btn btn-success';
                completeBtn.style.padding = '0.3rem 0.6rem';
                completeBtn.style.fontSize = '0.75rem';
                completeBtn.innerHTML = '<i class="fas fa-check"></i> Complete';
                completeBtn.onclick = function() { markCompleted(appt.appointment_id); };
                actionsDiv.appendChild(completeBtn);
            }

            if (['admin', 'doctor', 'receptionist'].includes(userRole)) {
                const editBtn = document.createElement('button');
                editBtn.className = 'btn btn-warning';
                editBtn.style.padding = '0.3rem 0.6rem';
                editBtn.style.fontSize = '0.75rem';
                editBtn.innerHTML = '<i class="fas fa-edit"></i> Edit';
                editBtn.onclick = function() { editAppointment(appt.appointment_id); };
                actionsDiv.appendChild(editBtn);
            }

            if (userRole === 'admin' || userRole === 'doctor') {
                const presBtn = document.createElement('button');
                presBtn.className = 'btn btn-info';
                presBtn.style.padding = '0.3rem 0.6rem';
                presBtn.style.fontSize = '0.75rem';
                presBtn.innerHTML = '<i class="fas fa-prescription-bottle"></i> Prescription';
                presBtn.onclick = function() { openPrescriptionModal(appt.appointment_id, appt.patient_id); };
                actionsDiv.appendChild(presBtn);
            }

            if (userRole === 'admin') {
                const delBtn = document.createElement('button');
                delBtn.className = 'btn btn-danger';
                delBtn.style.padding = '0.3rem 0.6rem';
                delBtn.style.fontSize = '0.75rem';
                delBtn.innerHTML = '<i class="fas fa-trash"></i> Delete';
                delBtn.onclick = function() { deleteAppointment(appt.appointment_id); };
                actionsDiv.appendChild(delBtn);
            }

            actionsTd.appendChild(actionsDiv);
            tr.appendChild(actionsTd);

            tbody.appendChild(tr);
        });
    }

    // Prescription Modal Functions
    function openPrescriptionModal(appointmentId, patientId) {
        const modal = document.getElementById('prescriptionModal');
        if (modal) {
            // Set the patient in the dropdown
            const patientSelect = document.getElementById('patientSelect');
            if (patientSelect && patientId) {
                patientSelect.value = patientId;
                patientSelect.disabled = true; // Lock patient selection
            }
            
            // Show modal
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
        }
    }

    function closePrescriptionModal() {
        const modal = document.getElementById('prescriptionModal');
        if (modal) {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
            
            // Reset form
            const form = document.getElementById('prescriptionForm');
            if (form) {
                form.reset();
                document.getElementById('patientSelect').disabled = false;
            }
        }
    }

    // Initialize prescription modal close button
    document.addEventListener('DOMContentLoaded', function() {
        const closeBtn = document.getElementById('closePrescriptionModal');
        if (closeBtn) {
            closeBtn.addEventListener('click', closePrescriptionModal);
        }
        
        // Close modal when clicking outside
        const modal = document.getElementById('prescriptionModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closePrescriptionModal();
                }
            });
        }
    });
    </script>
</body>
</html>
