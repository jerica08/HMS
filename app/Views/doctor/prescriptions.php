<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Prescription Management - HMS Doctor</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        /* Scoped styles for Doctor Prescriptions page */
        .filter-row { display: flex; gap: 1rem; align-items: end; flex-wrap: wrap; }
        .filter-input { padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.9rem; }
        .patient-table { background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
        .table-header { background: #f9fafb; padding: 1rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
        .btn-small { padding: .5rem 1rem; font-size: .8rem; }
        .patient-table { overflow-x: auto; }
        .table { width: 100%; border-collapse: separate; border-spacing: 0; min-width: 720px; }
        .table thead th { background: #f8fafc; color: #374151; font-weight: 600; text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #e5e7eb; }
        .table tbody td { padding: 0.75rem 1rem; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
        .table tbody tr:hover { background: #f9fafb; }
        .table th:nth-child(7), .table th:nth-child(8), .table td:nth-child(7), .table td:nth-child(8) { text-align: center; }
        .badge { display: inline-block; padding: 0.25rem 0.6rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        .badge-danger { background: #fecaca; color: #991b1b; }
        /* Modal base */
        .modal { display: none; position: fixed; z-index: 1000; inset: 0; background: rgba(0,0,0,0.5); }
        .modal.show { display: flex; align-items: center; justify-content: center; }
        .modal-content { background: #fff; border-radius: 8px; width: 90%; max-width: 720px; max-height: 90vh; overflow-y: auto; box-shadow: 0 4px 20px rgba(0,0,0,0.15); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem; border-bottom: 1px solid #e2e8f0; background: #f7fafc; }
        .modal-header h3 { margin: 0; color: #2d3748; }
        .modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #718096; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; }
        .modal-close:hover { color: #2d3748; }
        .modal-body { padding: 1.25rem; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; padding: 1rem; border-top: 1px solid #e2e8f0; background: #f7fafc; }
        .form-input, .form-select { width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.9rem; }
        .form-input:focus, .form-select:focus { outline: none; border-color: #4299e1; box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.15); }
    </style>
</head>
<body class="doctor">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <?php include APPPATH . 'Views/doctor/components/sidebar.php'; ?>

        <main class="content">
            <h1 class="page-title">Prescriptions</h1>
            <div class="page-actions">
                <button class="btn btn-success" id="addPrescriptionBtn">
                    <i class="fas fa-plus"></i> New Prescription
                </button>
            </div><br>

            <div class="dashboard-overview">
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue"><i class="fas fa-prescription-bottle"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Today's Prescriptions</h3>
                            <p class="card-subtitle">Issues today</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric"><div class="metric-value blue"><?= $totalToday ?? 0 ?></div><div class="metric-label">Total</div></div>
                        <div class="metric"><div class="metric-value green"><?= $pending ?? 0 ?></div><div class="metric-label">Pending</div></div>
                        <div class="metric"><div class="metric-value orange"><?= $sent ?? 0 ?></div><div class="metric-label">Sent</div></div>
                    </div>
                </div>
            </div>

            <div class="patient-table">
                <div class="search-filter">
                    <h3 style="margin-bottom: 1rem;">Search Prescriptions</h3>
                    <div class="filter-row">
                        <div class="filter-group">
                            <label>Search Patient</label>
                            <input type="text" class="filter-input" placeholder="Search by patient name, medication, or prescription ID..." id="prescriptionSearch" value="">
                        </div>
                        <div class="filter-group" id="statusFilter">
                            <label>Status</label>
                            <select class="filter-input" id="conditionsFilter">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="filter-group" id="dateFilter">
                            <label>Date</label>
                            <select class="filter-input" id="roleFilter">
                                <option value="">All Dates</option>
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>&nbsp;</label>
                            <button class="btn btn-primary" onclick="applyFilters()"><i class="fas fa-search"></i> Search</button>
                        </div>
                    </div>
                </div>
                <div class="table-header">
                    <h3>Recent Prescription</h3>
                    <div style="display: flex; gap: 0.5rem;">
                        <button class="btn btn-primary btn-small" id="printBtn"><i class="fas fa-download"></i> Export</button>
                        <button class="btn btn-secondary btn-small" id="exportBtn"><i class="fas fa-sync"></i> Refresh</button>
                    </div>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Prescription ID</th>
                            <th>Patient</th>
                            <th>Medication</th>
                            <th>Dosage</th>
                            <th>Duration</th>
                            <th>Date Issued</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($prescriptions) && !empty($prescriptions)): ?>
                            <?php foreach ($prescriptions as $rx): ?>
                                <tr>
                                    <td><?= $rx['prescription_id'] ?></td>
                                    <td>
                                        <div>
                                            <strong><?= $rx['first_name'] . ' ' . $rx['last_name'] ?></strong><br>
                                            <small><?= $rx['pat_id'] ?> | Age: <?php
                                                if (!empty($rx['date_of_birth'])) {
                                                    $dob = new DateTime($rx['date_of_birth']);
                                                    $now = new DateTime();
                                                    $age = $now->diff($dob)->y;
                                                    echo $age;
                                                } else {
                                                    echo 'N/A';
                                                }
                                            ?> years</small>
                                        </div>
                                    </td>
                                    <td><?= $rx['medication'] ?></td>
                                    <td><?= $rx['dosage'] ?></td>
                                    <td><?= $rx['duration'] ?></td>
                                    <td><?= date('F j, Y', strtotime($rx['date_issued'])) ?></td>
                                    <td><span class="badge badge-<?= $rx['status'] == 'active' ? 'success' : ($rx['status'] == 'completed' ? 'info' : 'danger') ?>"><?= ucfirst($rx['status']) ?></span></td>
                                    <td>
                                        <button class="btn btn-primary view-rx-btn" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">View</button>
                                        <button class="btn btn-secondary edit-rx-btn" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Edit</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center;">No prescriptions found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modals -->
    <div class="modal" id="newPrescriptionModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>New Prescription</h3>
                <button class="modal-close" id="closeNewRx">&times;</button>
            </div>
            <div class="modal-body">
                <form id="newPrescriptionForm">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Patient *</label>
                            <select class="form-select" name="patient_id" required>
                                <option value="">Select Patient</option>
                                <?php if (isset($patients) && !empty($patients)): ?>
                                    <?php foreach ($patients as $patient): ?>
                                        <option value="<?= $patient['patient_id'] ?>">
                                            <?= $patient['first_name'] . ' ' . $patient['last_name'] ?> (<?= $patient['patient_id'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Date Issued *</label>
                            <input type="date" class="form-input" name="date_issued" required>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Medication *</label>
                            <input type="text" class="form-input" name="medication" placeholder="e.g., Lisinopril" required>
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Dosage *</label>
                            <input type="text" class="form-input" name="dosage" placeholder="e.g., 10mg once daily" required>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Frequency *</label>
                            <select class="form-select" name="frequency" required>
                                <option value="">Select Frequency</option>
                                <option>Once daily</option>
                                <option>Twice daily</option>
                                <option>Three times daily</option>
                                <option>As needed</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Duration *</label>
                            <select class="form-select" name="duration" required>
                                <option value="">Select Duration</option>
                                <option>7 days</option>
                                <option>14 days</option>
                                <option>30 days</option>
                                <option>90 days</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Notes</label>
                        <textarea class="form-input" name="notes" rows="3" placeholder="Additional instructions or notes"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelNewRx">Cancel</button>
                <button type="submit" form="newPrescriptionForm" class="btn btn-success">Save Prescription</button>
            </div>
        </div>
    </div>

    <div class="modal" id="viewPrescriptionModal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3>Prescription Details</h3>
                <button class="modal-close" id="closeViewRx">&times;</button>
            </div>
            <div class="modal-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <div>
                        <h4 style="margin-bottom: 1rem; color: #2d3748;">Prescription Information</h4>
                        <div style="background: #f7fafc; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                            <div style="margin-bottom: 0.5rem;"><strong>ID:</strong> <span id="rxId">RX001234</span></div>
                            <div style="margin-bottom: 0.5rem;"><strong>Date:</strong> <span id="rxDate">Aug 20, 2025</span></div>
                            <div style="margin-bottom: 0.5rem;"><strong>Medication:</strong> <span id="rxMedication">Lisinopril</span></div>
                            <div style="margin-bottom: 0.5rem;"><strong>Dosage:</strong> <span id="rxDosage">10mg once daily</span></div>
                            <div style="margin-bottom: 0.5rem;"><strong>Duration:</strong> <span id="rxDuration">30 days</span></div>
                            <div><strong>Status:</strong> <span id="rxStatus" class="badge badge-success">Active</span></div>
                        </div>
                        <h4 style="margin-bottom: 1rem; color: #2d3748;">Notes</h4>
                        <div style="background: #e6fffa; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                            <div id="rxNotes">Take with water. Monitor BP daily.</div>
                        </div>
                    </div>
                    <div>
                        <h4 style="margin-bottom: 1rem; color: #2d3748;">Patient Information</h4>
                        <div style="background: #f0fff4; padding: 1rem; border-radius: 8px;">
                            <div style="margin-bottom: 0.5rem;"><strong>Name:</strong> <span id="rxPatientName">Sarah Wilson</span></div>
                            <div style="margin-bottom: 0.5rem;"><strong>Patient ID:</strong> <span id="rxPatientId">P0012347</span></div>
                            <div style="margin-bottom: 0.5rem;"><strong>Age:</strong> <span id="rxPatientAge">45 years</span></div>
                            <div><strong>Phone:</strong> <span id="rxPatientPhone">(555) 123-4567</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="closeViewRxBtn">Close</button>
                <button type="button" class="btn btn-primary" id="editFromViewBtn">Edit</button>
            </div>
        </div>
    </div>

    <script>
    (function() {
        const newRxModal = document.getElementById('newPrescriptionModal');
        const viewRxModal = document.getElementById('viewPrescriptionModal');
        function open(modal) { modal.classList.add('show'); }
        function close(modal) { modal.classList.remove('show'); }
        document.getElementById('addPrescriptionBtn').addEventListener('click', function() { open(newRxModal); });
        document.getElementById('closeNewRx').addEventListener('click', function() { close(newRxModal); });
        document.getElementById('cancelNewRx').addEventListener('click', function() { close(newRxModal); });
        document.getElementById('newPrescriptionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('<?= base_url('doctor/create-prescription') ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Prescription created successfully! ID: ' + data.prescription_id);
                    close(newRxModal);
                    this.reset();
                    location.reload(); // Refresh the page to show the new prescription
                } else {
                    alert('Error: ' + (data.message || 'Failed to create prescription'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while creating the prescription.');
            });
        });
        document.querySelectorAll('.view-rx-btn').forEach(function(btn) { btn.addEventListener('click', function() { open(viewRxModal); }); });
        document.getElementById('closeViewRx').addEventListener('click', function() { close(viewRxModal); });
        document.getElementById('closeViewRxBtn').addEventListener('click', function() { close(viewRxModal); });
        document.getElementById('editFromViewBtn').addEventListener('click', function() { close(viewRxModal); open(newRxModal); });
        document.querySelectorAll('.edit-rx-btn').forEach(function(btn) { btn.addEventListener('click', function() { open(newRxModal); }); });
        window.addEventListener('click', function(event) { if (event.target === newRxModal) close(newRxModal); if (event.target === viewRxModal) close(viewRxModal); });
    })();
    </script>

</body>
</html>
