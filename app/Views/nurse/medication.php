<!DOCTYPE html>
<html lang="en">
<head>
     <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medication - HMS Nurse</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Modern card styling */
        .medication-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .medication-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            border-left: 4px solid #4facfe;
        }

        .medication-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .medication-header {
            display: flex;
            justify-content: between;
            align-items: start;
            margin-bottom: 1rem;
        }

        .medication-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #374151;
            margin: 0 0 0.5rem 0;
        }

        .medication-info {
            color: #6b7280;
            font-size: 0.9rem;
            margin: 0.25rem 0;
        }

        .medication-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        .status-due { background: #fef3c7; color: #92400e; }
        .status-overdue { background: #fecaca; color: #991b1b; }
        .status-completed { background: #dcfce7; color: #166534; }

        .medication-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .action-btn {
            background: #4facfe;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            color: white;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            text-decoration: none;
            font-size: 0.8rem;
            transition: background 0.2s;
        }

        .action-btn:hover {
            background: #3b82f6;
            color: white;
        }

        .action-btn.secondary {
            background: #6b7280;
        }

        .action-btn.secondary:hover {
            background: #4b5563;
        }

        .action-btn.success {
            background: #10b981;
        }

        .action-btn.success:hover {
            background: #059669;
        }

        .error-notice {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            text-align: center;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #d1d5db;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background: white;
            margin: 10% auto;
            padding: 2rem;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body class="nurse">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <?= $this->include('nurse/components/sidebar') ?>

        <main class="content">
            <h1 class="page-title">Medication Management</h1>
            <p class="text-muted">Administer and track patient medications</p>

            <!-- Error Notice -->
            <?php if (isset($error)): ?>
                <div class="error-notice">
                    <i class="fas fa-exclamation-triangle" style="margin-right: 0.5rem;"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div style="margin-bottom: 2rem;">
                <button onclick="showAdministerModal()" class="action-btn success">
                    <i class="fas fa-plus"></i> Record Administration
                </button>
                <button onclick="refreshMedicationList()" class="action-btn secondary" style="margin-left: 0.5rem;">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>

            <!-- Medication Cards -->
            <?php if (!empty($patient_medications)): ?>
                <div class="medication-grid">
                    <?php foreach ($patient_medications as $medication): ?>
                        <div class="medication-card">
                            <h3 class="medication-name"><?php echo $medication['medication_name'] ?? 'Paracetamol'; ?></h3>
                            <p class="medication-info">
                                <strong>Patient:</strong> <?php echo $medication['patient_name'] ?? 'John Doe'; ?>
                            </p>
                            <p class="medication-info">
                                <strong>Dosage:</strong> <?php echo $medication['dosage'] ?? '500mg'; ?> |
                                <strong>Route:</strong> <?php echo $medication['route'] ?? 'Oral'; ?>
                            </p>
                            <p class="medication-info">
                                <strong>Frequency:</strong> <?php echo $medication['frequency'] ?? 'Twice daily'; ?> |
                                <strong>Next Due:</strong> <?php echo $medication['next_due'] ?? '2:00 PM'; ?>
                            </p>
                            <span class="medication-status status-<?php echo $medication['status'] ?? 'due'; ?>">
                                <?php echo ucfirst($medication['status'] ?? 'due'); ?>
                            </span>

                            <div class="medication-actions">
                                <button onclick="administerMedication(<?php echo $medication['id'] ?? 1; ?>)" class="action-btn success">
                                    <i class="fas fa-check"></i> Administer
                                </button>
                                <button onclick="viewMedicationHistory(<?php echo $medication['id'] ?? 1; ?>)" class="action-btn">
                                    <i class="fas fa-history"></i> History
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-pills"></i>
                    <h3>No Medications Scheduled</h3>
                    <p>No medications are currently scheduled for administration.</p>
                    <p>Medications will appear here when they are prescribed to patients.</p>
                </div>
            <?php endif; ?>

            <!-- Medication Statistics -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 2rem;">
                <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <div style="color: #4facfe; font-size: 2rem; font-weight: bold; margin-bottom: 0.5rem;"><?php echo count($patient_medications ?? []); ?></div>
                    <p style="margin: 0; color: #6b7280;">Total Medications</p>
                </div>
                <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <div style="color: #f59e0b; font-size: 2rem; font-weight: bold; margin-bottom: 0.5rem;"><?php echo count(array_filter($patient_medications ?? [], function($m) { return ($m['status'] ?? 'due') === 'due'; })); ?></div>
                    <p style="margin: 0; color: #6b7280;">Due Now</p>
                </div>
                <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <div style="color: #ef4444; font-size: 2rem; font-weight: bold; margin-bottom: 0.5rem;"><?php echo count(array_filter($patient_medications ?? [], function($m) { return ($m['status'] ?? 'due') === 'overdue'; })); ?></div>
                    <p style="margin: 0; color: #6b7280;">Overdue</p>
                </div>
            </div>
        </main>
    </div>

    <!-- Medication Administration Modal -->
    <div id="administerModal" class="modal">
        <div class="modal-content">
            <h3>Record Medication Administration</h3>
            <form id="medicationForm">
                <div class="form-group">
                    <label class="form-label">Patient</label>
                    <select class="form-input" id="patientSelect" required>
                        <option value="">Select Patient</option>
                        <?php if (!empty($assigned_patients)): ?>
                            <?php foreach ($assigned_patients as $patient): ?>
                                <option value="<?php echo $patient['id'] ?? 1; ?>"><?php echo ($patient['first_name'] ?? 'John') . ' ' . ($patient['last_name'] ?? 'Doe'); ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Medication</label>
                    <select class="form-input" id="medicationSelect" required>
                        <option value="">Select Medication</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Dosage</label>
                    <input type="text" class="form-input" id="dosageInput" placeholder="e.g., 500mg" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Route</label>
                    <select class="form-input" id="routeSelect" required>
                        <option value="">Select Route</option>
                        <option value="oral">Oral</option>
                        <option value="intravenous">Intravenous</option>
                        <option value="intramuscular">Intramuscular</option>
                        <option value="subcutaneous">Subcutaneous</option>
                        <option value="topical">Topical</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea class="form-input" id="notesInput" rows="3" placeholder="Additional notes..."></textarea>
                </div>

                <div style="display: flex; gap: 0.5rem; justify-content: end; margin-top: 1rem;">
                    <button type="button" onclick="closeAdministerModal()" class="action-btn secondary">Cancel</button>
                    <button type="submit" class="action-btn success">Record Administration</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Navigation functions
        function showAdministerModal() {
            document.getElementById('administerModal').style.display = 'block';
        }

        function closeAdministerModal() {
            document.getElementById('administerModal').style.display = 'none';
        }

        function refreshMedicationList() {
            location.reload();
        }

        function administerMedication(medicationId) {
            // For now, show the modal
            showAdministerModal();
        }

        function viewMedicationHistory(medicationId) {
            alert('Medication history for ID: ' + medicationId);
        }

        // Form submission
        document.getElementById('medicationForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Get form data
            const formData = {
                patient_id: document.getElementById('patientSelect').value,
                medication_id: document.getElementById('medicationSelect').value,
                dosage: document.getElementById('dosageInput').value,
                route: document.getElementById('routeSelect').value,
                notes: document.getElementById('notesInput').value
            };

            // Send AJAX request
            fetch('<?= base_url('nurse/administer-medication') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Medication administered successfully!');
                    closeAdministerModal();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Failed to record medication administration');
                console.error('Error:', error);
            });
        });

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('administerModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Logout functionality
        function handleLogout() {
            if(confirm('Are you sure you want to logout?')) {
                window.location.href = '<?= base_url('logout') ?>';
            }
        }
    </script>
    <script src="<?= base_url('js/logout.js') ?>"></script>
</body>
</html>
