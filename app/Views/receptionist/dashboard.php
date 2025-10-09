<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width", initial-scale="1.0">
        <title>Receptionist Dashboard</title>
        <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
            /* Table styling for Recent Patient Registrations */
            .table-container { background:#fff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.06); overflow:auto; }
            .table-header { display:flex; align-items:center; justify-content:space-between; padding:1rem; border-bottom:1px solid #e5e7eb; background:#f8fafc; }
            .table-actions { display:flex; gap:.5rem; flex-wrap:wrap; }
            .table { width:100%; border-collapse:separate; border-spacing:0; min-width: 900px; }
            .table thead th { position:sticky; top:0; background:#f8fafc; color:#374151; font-weight:600; text-align:left; padding:.75rem 1rem; border-bottom:1px solid #e5e7eb; z-index:1; }
            .table tbody td { padding:.75rem 1rem; border-bottom:1px solid #f3f4f6; vertical-align:middle; }
            .table tbody tr:nth-child(odd) { background:#fcfcfd; }
            .table tbody tr:hover { background:#f9fafb; }
            .table th:last-child, .table td:last-child { text-align:right; white-space:nowrap; }
        </style>
    </head>
    <body class="receptionist-theme">
       <?php include APPPATH . 'Views/template/header.php'; ?>
        <div class="main-container">
            <!-- Sidebar -->
             <?php include APPPATH . 'Views/receptionist/components/sidebar.php'; ?> 
            <main class="content">
                <h1 class="page-title">Receptionist Dashboard</h1>
                <p class="text-muted">Welcome to the Reception Management System</p>

                <!-- Error Notice -->
                <?php if (isset($error)): ?>
                    <div style="background: #fee2e2; border: 1px solid #fecaca; color: #dc2626; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; text-align: center;">
                        <i class="fas fa-exclamation-triangle" style="margin-right: 0.5rem;"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <!-- Quick Actions -->
                <div style="display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap;">
                    <button onclick="showAppointmentBooking()" class="btn btn-primary" style="background: #667eea; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; color: white; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-calendar-plus"></i> Book Appointment
                    </button>
                    <button onclick="showPatientRegistration()" class="btn btn-secondary" style="background: #6b7280; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; color: white; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-user-plus"></i> Register Patient
                    </button>
                </div>
                <!-- Statistics Cards -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                    <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div>
                                <div style="font-size: 2rem; font-weight: bold; color: #667eea; margin-bottom: 0.25rem;"><?php echo $statistics['today_appointments'] ?? 0; ?></div>
                                <p style="margin: 0; color: #6b7280; font-size: 0.9rem;">Today's Appointments</p>
                            </div>
                        </div>
                    </div>

                    <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <div style="font-size: 2rem; font-weight: bold; color: #f093fb; margin-bottom: 0.25rem;"><?php echo $statistics['pending_appointments'] ?? 0; ?></div>
                                <p style="margin: 0; color: #6b7280; font-size: 0.9rem;">Pending Appointments</p>
                            </div>
                        </div>
                    </div>

                    <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                                <i class="fas fa-users"></i>
                            </div>
                            <div>
                                <div style="font-size: 2rem; font-weight: bold; color: #4facfe; margin-bottom: 0.25rem;"><?php echo $statistics['total_patients'] ?? 0; ?></div>
                                <p style="margin: 0; color: #6b7280; font-size: 0.9rem;">Total Patients</p>
                            </div>
                        </div>
                    </div>

                    <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                                <i class="fas fa-user-clock"></i>
                            </div>
                            <div>
                                <div style="font-size: 2rem; font-weight: bold; color: #43e97b; margin-bottom: 0.25rem;"><?php echo $statistics['waiting_patients'] ?? 0; ?></div>
                                <p style="margin: 0; color: #6b7280; font-size: 0.9rem;">Waiting Patients</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Patient Registrations -->
                <div class="table-container">
                    <div class="table-header">
                        <h3>Recent Patient Registrations</h3>
                        <div class="table-actions">
                            <button onclick="refreshDashboard()" class="btn btn-secondary">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                            <a href="<?= base_url('receptionist/patient-registration') ?>" class="btn btn-primary">
                                <i class="fas fa-list"></i> View All
                            </a>
                        </div>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Patient ID</th>
                                <th>Patient Name</th>
                                <th>Age</th>
                                <th>Gender</th>
                                <th>Type</th>
                                <th>Registration Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="recent-patients-table">
                            <?php if (!empty($trackingData['recent_registrations'])): ?>
                                <?php foreach ($trackingData['recent_registrations'] as $patient): ?>
                                    <tr>
                                        <td><strong><?= esc($patient['patient_id']) ?></strong></td>
                                        <td><?= esc($patient['first_name'] . ' ' . $patient['last_name']) ?></td>
                                        <td><?= esc($patient['age'] ?? 'N/A') ?></td>
                                        <td><?= esc($patient['gender']) ?></td>
                                        <td>
                                            <span class="badge badge-<?= $patient['patient_type'] == 'Emergency' ? 'danger' : ($patient['patient_type'] == 'Inpatient' ? 'warning' : 'info') ?>">
                                                <?= esc($patient['patient_type']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('M j, Y g:i A', strtotime($patient['created_at'])) ?></td>
                                        <td>
                                            <span class="badge badge-<?= $patient['status'] == 'Active' ? 'success' : 'secondary' ?>">
                                                <?= esc($patient['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?= base_url('receptionist/patient-registration/show/' . $patient['id']) ?>" class="btn btn-sm btn-secondary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No recent patient registrations found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
        <script>
        // Simple navigation functionality - removed preventDefault to allow page navigation
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                // Allow navigation to proceed - don't prevent default
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Navigation functions for quick actions
        function showAppointmentBooking() {
            window.location.href = '<?= base_url('receptionist/appointment-booking') ?>';
        }

        function showPatientRegistration() {
            window.location.href = '<?= base_url('receptionist/patient-registration') ?>';
        }

        // Logout functionality
        function handleLogout() {
            if(confirm('Are you sure you want to logout?')) {
                window.location.href = '<?= base_url('auth/logout') ?>';
            }
        }

        // Real-time dashboard refresh functionality
        function refreshDashboard() {
            const refreshBtn = document.querySelector('button[onclick="refreshDashboard()"]');
            const originalText = refreshBtn.innerHTML;
            
            // Show loading state
            refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
            refreshBtn.disabled = true;

            fetch('<?= base_url('receptionist/dashboard/tracking-stats') ?>')
                .then(response => response.json())
                .then(data => {
                    // Update tracking statistics
                    updateStatistic('registrations-today', data.registrations_today);
                    updateStatistic('registrations-week', data.registrations_this_week);
                    updateStatistic('pending-patients', data.active_patients);
                    updateStatistic('total-patients', data.total_patients);
                    updateStatistic('registrations-month', data.registrations_this_month);
                    updateStatistic('registrations-yesterday', data.registrations_yesterday);

                    // Update recent registrations table if data is available
                    if (data.recent_registrations) {
                        updateRecentRegistrationsTable(data.recent_registrations);
                    }

                    // Show success message
                    showNotification('Patient tracking data refreshed successfully!', 'success');
                })
                .catch(error => {
                    console.error('Error refreshing tracking data:', error);
                    showNotification('Failed to refresh tracking data', 'error');
                })
                .finally(() => {
                    // Reset button state
                    refreshBtn.innerHTML = originalText;
                    refreshBtn.disabled = false;
                });
        }

        function updateRecentRegistrationsTable(patients) {
            const tbody = document.querySelector('#recent-patients-table');
            if (!tbody) return;

            if (patients.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center text-muted">No recent patient registrations found</td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = patients.map(patient => {
                const statusClass = patient.status === 'Active' ? 'success' : 'secondary';
                const typeClass = patient.patient_type === 'Emergency' ? 'danger' : 
                                 patient.patient_type === 'Inpatient' ? 'warning' : 'info';
                
                return `
                    <tr>
                        <td><strong>${escapeHtml(patient.patient_id)}</strong></td>
                        <td>${escapeHtml(patient.first_name)} ${escapeHtml(patient.last_name)}</td>
                        <td>${patient.age || 'N/A'}</td>
                        <td>${escapeHtml(patient.gender)}</td>
                        <td>
                            <span class="badge badge-${typeClass}">
                                ${escapeHtml(patient.patient_type)}
                            </span>
                        </td>
                        <td>${formatDate(patient.created_at)}</td>
                        <td>
                            <span class="badge badge-${statusClass}">
                                ${escapeHtml(patient.status)}
                            </span>
                        </td>
                        <td>
                            <a href="<?= base_url('receptionist/patient-registration/show/') ?>${patient.id}" 
                               class="btn btn-sm btn-secondary">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    </script>
    <script src="<?= base_url('js/logout.js') ?>"></script>
    </body>
    </html>