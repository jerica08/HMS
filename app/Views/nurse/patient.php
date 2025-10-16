<!DOCTYPE html>
<html lang="en">
<head>
     <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Care - HMS Nurse</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Modern card styling */
        .patient-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .patient-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            border-left: 4px solid #667eea;
        }

        .patient-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .patient-header {
            display: flex;
            justify-content: between;
            align-items: start;
            margin-bottom: 1rem;
        }

        .patient-id {
            background: #667eea;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .patient-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: #374151;
            margin: 0;
        }

        .patient-info {
            color: #6b7280;
            font-size: 0.9rem;
            margin: 0.25rem 0;
        }

        .patient-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        .status-stable { background: #dcfce7; color: #166534; }
        .status-critical { background: #fecaca; color: #991b1b; }
        .status-observation { background: #fef3c7; color: #92400e; }

        .patient-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .action-btn {
            background: #667eea;
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
            background: #5a67d8;
            color: white;
        }

        .action-btn.secondary {
            background: #6b7280;
        }

        .action-btn.secondary:hover {
            background: #4b5563;
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

        .form-input, .form-select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .form-textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 0.9rem;
            min-height: 100px;
            resize: vertical;
        }
    </style>
</head>
<body class="nurse">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <?= $this->include('nurse/components/sidebar') ?>

        <main class="content">
            <h1 class="page-title">Patient Care Management</h1>
            <p class="text-muted">Monitor and care for assigned patients</p>

            <!-- Error Notice -->
            <?php if (isset($error)): ?>
                <div class="error-notice">
                    <i class="fas fa-exclamation-triangle" style="margin-right: 0.5rem;"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div style="margin-bottom: 2rem;">
                <button onclick="addPatientNote()" class="action-btn" style="background: #10b981;">
                    <i class="fas fa-plus"></i> Add Patient Note
                </button>
                <button onclick="refreshPatientList()" class="action-btn secondary" style="margin-left: 0.5rem;">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>

            <!-- Patient Cards -->
            <?php if (!empty($assigned_patients)): ?>
                <div class="patient-grid">
                    <?php foreach ($assigned_patients as $patient): ?>
                        <div class="patient-card">
                            <div class="patient-header">
                                <div>
                                    <span class="patient-id"><?php echo $patient['patient_id'] ?? 'PAT' . rand(100, 999); ?></span>
                                </div>
                            </div>
                            <h3 class="patient-name"><?php echo ($patient['first_name'] ?? 'John') . ' ' . ($patient['last_name'] ?? 'Doe'); ?></h3>
                            <p class="patient-info">
                                <i class="fas fa-birthday-cake"></i> Age: <?php echo $patient['age'] ?? 'N/A'; ?> |
                                <i class="fas fa-venus-mars"></i> <?php echo $patient['gender'] ?? 'N/A'; ?>
                            </p>
                            <p class="patient-info">
                                <i class="fas fa-bed"></i> Room: <?php echo $patient['room_number'] ?? 'Not assigned'; ?> |
                                <i class="fas fa-user-md"></i> Assigned Doctor: <?php echo $patient['assigned_doctor'] ?? 'Dr. Smith'; ?>
                            </p>
                            <span class="patient-status status-<?php echo $patient['status'] ?? 'stable'; ?>">
                                <?php echo ucfirst($patient['status'] ?? 'stable'); ?>
                            </span>

                            <div class="patient-actions">
                                <button onclick="recordVitals(<?php echo $patient['id'] ?? 1; ?>)" class="action-btn">
                                    <i class="fas fa-heartbeat"></i> Record Vitals
                                </button>
                                <button onclick="administerMedication(<?php echo $patient['id'] ?? 1; ?>)" class="action-btn">
                                    <i class="fas fa-pills"></i> Medication
                                </button>
                                <button onclick="viewPatientDetails(<?php echo $patient['id'] ?? 1; ?>)" class="action-btn secondary">
                                    <i class="fas fa-eye"></i> Details
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h3>No Assigned Patients</h3>
                    <p>You don't have any patients assigned to you at the moment.</p>
                    <p>Patients will appear here once they are assigned to your care.</p>
                </div>
            <?php endif; ?>

            <!-- Patient Statistics -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 2rem;">
                <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <div style="color: #667eea; font-size: 2rem; font-weight: bold; margin-bottom: 0.5rem;"><?php echo count($assigned_patients ?? []); ?></div>
                    <p style="margin: 0; color: #6b7280;">Total Assigned Patients</p>
                </div>
                <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <div style="color: #10b981; font-size: 2rem; font-weight: bold; margin-bottom: 0.5rem;"><?php echo count(array_filter($assigned_patients ?? [], function($p) { return ($p['status'] ?? 'stable') === 'stable'; })); ?></div>
                    <p style="margin: 0; color: #6b7280;">Stable Patients</p>
                </div>
                <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <div style="color: #f59e0b; font-size: 2rem; font-weight: bold; margin-bottom: 0.5rem;"><?php echo count(array_filter($assigned_patients ?? [], function($p) { return ($p['status'] ?? 'stable') === 'observation'; })); ?></div>
                    <p style="margin: 0; color: #6b7280;">Under Observation</p>
                </div>
            </div>
        </main>
    </div>

    <!-- Patient Note Modal -->
    <div id="noteModal" class="modal">
        <div class="modal-content">
            <h3>Add Patient Note</h3>
            <form id="noteForm">
                <div class="form-group">
                    <label class="form-label">Patient</label>
                    <select class="form-select" id="patientSelect" required>
                        <option value="">Select Patient</option>
                        <?php if (!empty($assigned_patients)): ?>
                            <?php foreach ($assigned_patients as $patient): ?>
                                <option value="<?php echo $patient['id'] ?? 1; ?>"><?php echo ($patient['first_name'] ?? 'John') . ' ' . ($patient['last_name'] ?? 'Doe'); ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Note Type</label>
                    <select class="form-select" id="noteTypeSelect" required>
                        <option value="">Select Note Type</option>
                        <option value="general">General Observation</option>
                        <option value="medication">Medication Note</option>
                        <option value="vitals">Vital Signs Note</option>
                        <option value="observation">Patient Observation</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Priority</label>
                    <select class="form-select" id="prioritySelect" required>
                        <option value="">Select Priority</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Note Content</label>
                    <textarea class="form-textarea" id="noteContent" placeholder="Enter your note here..." required></textarea>
                </div>

                <div style="display: flex; gap: 0.5rem; justify-content: end; margin-top: 1rem;">
                    <button type="button" onclick="closeNoteModal()" class="action-btn secondary">Cancel</button>
                    <button type="submit" class="action-btn" style="background: #10b981;">Add Note</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Navigation functions
        function addPatientNote() {
            document.getElementById('noteModal').style.display = 'block';
        }

        function closeNoteModal() {
            document.getElementById('noteModal').style.display = 'none';
            document.getElementById('noteForm').reset();
        }

        function refreshPatientList() {
            location.reload();
        }

        function recordVitals(patientId) {
            window.location.href = '<?= base_url('nurse/vitals') ?>?patient_id=' + patientId;
        }

        function administerMedication(patientId) {
            window.location.href = '<?= base_url('nurse/medication') ?>?patient_id=' + patientId;
        }

        function viewPatientDetails(patientId) {
            window.location.href = '<?= base_url('nurse/patient/details/') ?>' + patientId;
        }

        // Form submission for patient notes
        document.getElementById('noteForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Get form data
            const formData = {
                patient_id: document.getElementById('patientSelect').value,
                note_type: document.getElementById('noteTypeSelect').value,
                note_content: document.getElementById('noteContent').value,
                priority: document.getElementById('prioritySelect').value
            };

            // Send AJAX request
            fetch('<?= base_url('nurse/add-patient-note') ?>', {
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
                    alert('Patient note added successfully!');
                    closeNoteModal();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Failed to add patient note');
                console.error('Error:', error);
            });
        });

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('noteModal');
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
