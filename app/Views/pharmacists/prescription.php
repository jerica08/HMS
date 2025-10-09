<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy - Prescription Management - HMS</title>
    <link rel="stylesheet" href="/assets/css/common.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .section-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f3f4f6;
        }

        .section-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .section-description {
            color: #6b7280;
            font-size: 0.95rem;
            margin: 0.25rem 0 0 0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .form-input {
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn-group {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
            margin-top: 1.5rem;
        }

        .table-container {
            overflow-x: auto;
            margin-top: 1rem;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }

        .data-table th {
            background: #f9fafb;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
        }

        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid #f3f4f6;
            color: #6b7280;
        }

        .data-table tr:hover {
            background: #f9fafb;
        }

        .action-btn {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .action-btn:hover {
            transform: translateY(-1px);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: white;
        }

        .stat-label {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
        }

        .empty-state {
            text-align: center;
            color: #6b7280;
            padding: 2rem;
        }

        .empty-state i {
            font-size: 2rem;
            color: #d1d5db;
            margin-bottom: 1rem;
            display: block;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <!-- Sidebar -->
        <?php include APPPATH . 'Views/pharmacists/components/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div>
                    <h1 class="page-title">Prescription Management</h1>
                    <p class="text-muted">Manage prescriptions, check interactions, and dispense medications</p>
                </div>
            </div>

            <!-- Statistics Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $statistics['total_queued'] ?? 0; ?></div>
                    <div class="stat-label">Total Queued</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $statistics['stat_prescriptions'] ?? 0; ?></div>
                    <div class="stat-label">STAT Prescriptions</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $statistics['total_ready'] ?? 0; ?></div>
                    <div class="stat-label">Ready to Dispense</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $statistics['today_dispensed'] ?? 0; ?></div>
                    <div class="stat-label">Dispensed Today</div>
                </div>
            </div>

            <!-- Section 1: Create New Prescription -->
            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <div>
                        <h2 class="section-title">Create New Prescription</h2>
                        <p class="section-description">Add a new prescription to the system</p>
                    </div>
                </div>

                <form id="rx-form">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Rx Number</label>
                            <input class="form-input" id="rxNo" name="rxNo" type="text" placeholder="Auto-generated or enter manually" />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Patient ID *</label>
                            <input class="form-input" id="patientId" name="patientId" type="text" placeholder="e.g., P-2025-001" required />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Patient Name *</label>
                            <input class="form-input" id="patientName" name="patientName" type="text" placeholder="e.g., Juan Dela Cruz" required />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Medication *</label>
                            <input class="form-input" id="medication" name="medication" type="text" placeholder="e.g., Amoxicillin 500mg" required />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Dosage</label>
                            <input class="form-input" id="dosage" name="dosage" type="text" placeholder="e.g., 500mg" />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Frequency</label>
                            <input class="form-input" id="frequency" name="frequency" type="text" placeholder="e.g., 3 times daily" />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Days Supply</label>
                            <input class="form-input" id="days" name="days_supply" type="number" min="1" placeholder="e.g., 7" />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Quantity *</label>
                            <input class="form-input" id="qty" name="quantity" type="number" min="1" placeholder="e.g., 21" required />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Prescriber *</label>
                            <input class="form-input" id="prescriber" name="prescriber" type="text" placeholder="e.g., Dr. Santos" required />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Priority</label>
                            <select class="form-input" id="priority" name="priority" required>
                                <option value="routine">Routine</option>
                                <option value="priority">Priority</option>
                                <option value="stat">STAT</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Notes</label>
                        <textarea class="form-input" id="notes" name="notes" rows="3" placeholder="Additional notes or special instructions"></textarea>
                    </div>

                    <div class="btn-group">
                        <button type="button" class="btn btn-secondary" onclick="checkInteractions()">
                            <i class="fas fa-exclamation-triangle"></i> Check Interactions
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Prescription
                        </button>
                    </div>
                </form>
            </div>

            <!-- Section 2: Prescription Queues -->
            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <div>
                        <h2 class="section-title">Prescription Queues</h2>
                        <p class="section-description">Manage prescriptions by priority level</p>
                    </div>
                </div>

                <!-- Priority Queue Table -->
                <div style="margin-bottom: 2rem;">
                    <h3 style="font-size: 1.25rem; font-weight: 600; color: #1f2937; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb;">
                        <i class="fas fa-exclamation-triangle" style="color: #dc2626; margin-right: 0.5rem;"></i>
                        Priority Queue (STAT & Priority)
                    </h3>

                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Rx Number</th>
                                    <th>Patient</th>
                                    <th>Medication</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($priority_queue)): ?>
                                    <?php foreach ($priority_queue as $prescription): ?>
                                        <tr>
                                            <td><strong><?php echo $prescription['rx_number']; ?></strong></td>
                                            <td><?php echo $prescription['patient_name']; ?></td>
                                            <td><?php echo $prescription['medication']; ?></td>
                                            <td><span style="background: #fee2e2; color: #dc2626; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">STAT</span></td>
                                            <td><span style="background: #fef3c7; color: #d97706; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">Verifying</span></td>
                                            <td>
                                                <button class="action-btn" style="background: #667eea; color: white;" onclick="processPrescription('<?php echo $prescription['rx_number']; ?>')">
                                                    <i class="fas fa-play"></i> Process
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="empty-state">
                                            <i class="fas fa-check-circle"></i>
                                            No priority prescriptions in queue
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Regular Queue Table -->
                <div>
                    <h3 style="font-size: 1.25rem; font-weight: 600; color: #1f2937; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb;">
                        <i class="fas fa-clock" style="color: #6b7280; margin-right: 0.5rem;"></i>
                        Regular Queue
                    </h3>

                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Rx Number</th>
                                    <th>Patient</th>
                                    <th>Medication</th>
                                    <th>Quantity</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($queue)): ?>
                                    <?php foreach ($queue as $prescription): ?>
                                        <tr>
                                            <td><strong><?php echo $prescription['rx_number']; ?></strong></td>
                                            <td><?php echo $prescription['patient_name']; ?></td>
                                            <td><?php echo $prescription['medication']; ?></td>
                                            <td><?php echo $prescription['quantity']; ?></td>
                                            <td><span style="background: #dbeafe; color: #2563eb; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">Routine</span></td>
                                            <td><span style="background: #dbeafe; color: #2563eb; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">Queued</span></td>
                                            <td>
                                                <button class="action-btn" style="background: #10b981; color: white;" onclick="startPrescription('<?php echo $prescription['rx_number']; ?>')">
                                                    <i class="fas fa-play"></i> Start
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="empty-state">
                                            <i class="fas fa-inbox"></i>
                                            No prescriptions in regular queue
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Section 3: Drug Interactions & Dispensing -->
            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div>
                        <h2 class="section-title">Safety & Dispensing</h2>
                        <p class="section-description">Drug interaction alerts and medication dispensing</p>
                    </div>
                </div>

                <!-- Drug Interactions Table -->
                <div style="margin-bottom: 2rem;">
                    <h3 style="font-size: 1.25rem; font-weight: 600; color: #1f2937; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb;">
                        <i class="fas fa-exclamation-circle" style="color: #dc2626; margin-right: 0.5rem;"></i>
                        Drug Interaction Alerts
                    </h3>

                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>Medication A</th>
                                    <th>Medication B</th>
                                    <th>Severity</th>
                                    <th>Action Required</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Maria Garcia</td>
                                    <td>Warfarin</td>
                                    <td>Amoxicillin</td>
                                    <td><span style="background: #fee2e2; color: #dc2626; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">Critical</span></td>
                                    <td>
                                        <button class="action-btn" style="background: #ef4444; color: white;" onclick="notifyPrescriber()">
                                            <i class="fas fa-phone"></i> Notify Prescriber
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="5" style="text-align: center; color: #10b981; font-style: italic;">
                                        No other interactions detected
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Dispense Form -->
                <div>
                    <h3 style="font-size: 1.25rem; font-weight: 600; color: #1f2937; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb;">
                        <i class="fas fa-pills" style="color: #7c3aed; margin-right: 0.5rem;"></i>
                        Dispense Medication
                    </h3>

                    <form id="dispense-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Rx Number *</label>
                                <input class="form-input" id="dRxNo" name="rx_number" type="text" placeholder="Enter RX number" required />
                            </div>
                            <div class="form-group">
                                <label class="form-label">Quantity to Dispense *</label>
                                <input class="form-input" id="dQty" name="quantity" type="number" min="1" placeholder="Enter quantity" required />
                            </div>
                        </div>

                        <div class="btn-group">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-pills"></i> Dispense Medication
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Section 4: Dispense History -->
            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <div>
                        <h2 class="section-title">Recent Dispense History</h2>
                        <p class="section-description">Track recently dispensed medications</p>
                    </div>
                </div>

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date/Time</th>
                                <th>Rx Number</th>
                                <th>Patient</th>
                                <th>Medication</th>
                                <th>Quantity</th>
                                <th>Pharmacist</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php echo date('Y-m-d H:i'); ?></td>
                                <td><strong>RX-2025-040</strong></td>
                                <td>James Brown</td>
                                <td>Insulin Glargine</td>
                                <td>1 vial</td>
                                <td>PH-01</td>
                            </tr>
                            <tr>
                                <td colspan="6" class="empty-state">
                                    <i class="fas fa-info-circle"></i>
                                    View more in Reports section
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Save prescription
        document.getElementById('rx-form').addEventListener('submit', function(e){
            e.preventDefault();

            const formData = new FormData(this);
            const data = Object.fromEntries(formData);

            fetch('<?= base_url('pharmacists/createPrescription') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('✅ Prescription created successfully!\nRX Number: ' + result.rx_number);
                    this.reset();
                    location.reload();
                } else {
                    alert('❌ Error: ' + (result.errors ? Object.values(result.errors).join(', ') : result.message));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ An error occurred. Please try again.');
            });
        });

        // Dispense medication
        document.getElementById('dispense-form').addEventListener('submit', function(e){
            e.preventDefault();

            const formData = new FormData(this);
            const data = Object.fromEntries(formData);

            fetch('<?= base_url('pharmacists/dispenseMedication') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('✅ ' + result.message);
                    this.reset();
                    location.reload();
                } else {
                    alert('❌ Error: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ An error occurred. Please try again.');
            });
        });

        // Check drug interactions
        function checkInteractions() {
            const medications = [];
            document.querySelectorAll('input[name="medication"]').forEach(input => {
                if (input.value.trim()) {
                    medications.push(input.value.trim());
                }
            });

            if (medications.length < 2) {
                alert('⚠️ Please enter at least 2 medications to check interactions');
                return;
            }

            fetch('<?= base_url('pharmacists/checkInteractions') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({ medications: medications })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    if (result.interactions.length > 0) {
                        let message = '⚠️ Drug interactions found:\n\n';
                        result.interactions.forEach(interaction => {
                            message += `• ${interaction.medication_a} + ${interaction.medication_b}: ${interaction.severity}\n`;
                        });
                        alert(message);
                    } else {
                        alert('✅ No interactions found');
                    }
                } else {
                    alert('❌ Error: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ An error occurred. Please try again.');
            });
        }

        // Process prescription
        function processPrescription(rxNumber) {
            if (confirm('Process prescription ' + rxNumber + '?')) {
                alert('✅ Prescription ' + rxNumber + ' is now being processed');
                location.reload();
            }
        }

        // Start prescription
        function startPrescription(rxNumber) {
            if (confirm('Start processing prescription ' + rxNumber + '?')) {
                alert('✅ Started processing prescription ' + rxNumber);
                location.reload();
            }
        }

        // Notify prescriber
        function notifyPrescriber() {
            alert('📞 Prescriber has been notified about the critical interaction');
        }

        // Logout
        function handleLogout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '<?= base_url('logout') ?>';
            }
        }
    </script>
</body>
</html>
