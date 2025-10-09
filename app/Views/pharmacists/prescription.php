<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy - Prescription - HMS</title>
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="pharmacy-theme">
    <!-- Header -->
    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <!-- Sidebar -->
        <?php include APPPATH . 'Views/pharmacists/components/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="content">
            <h1 class="page-title">Prescription</h1>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="#form" class="btn btn-primary"><i class="fas fa-plus"></i> New Prescription</a>
                <a href="#queue" class="btn btn-secondary"><i class="fas fa-file-import"></i> Import eRx</a>
                <a href="#interactions" class="btn btn-secondary"><i class="fas fa-bell"></i> Check Interactions</a>
            </div>

            <!-- Prescription Form -->
            <div class="table-container" style="margin-top: 1.5rem">
                <h3 id="form" style="margin-bottom: 1rem">Create / Verify Prescription</h3>
                <form id="rx-form">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem">
                        <div>
                            <label style="display:block; font-weight:600; margin-bottom: .5rem">Rx Number</label>
                            <input id="rxNo" name="rxNo" type="text" placeholder="Auto or e.g., RX-2025-100" style="width:100%; padding:.75rem; border:1px solid #e2e8f0; border-radius:8px" />
                        </div>
                        <div>
                            <label style="display:block; font-weight:600; margin-bottom: .5rem">Patient ID</label>
                            <input id="patientId" name="patientId" type="text" placeholder="e.g., P-2025-001" style="width:100%; padding:.75rem; border:1px solid #e2e8f0; border-radius:8px" required />
                        </div>
                        <div>
                            <label style="display:block; font-weight:600; margin-bottom: .5rem">Patient Name</label>
                            <input id="patientName" name="patientName" type="text" placeholder="e.g., Juan Dela Cruz" style="width:100%; padding:.75rem; border:1px solid #e2e8f0; border-radius:8px" required />
                        </div>
                        <div>
                            <label style="display:block; font-weight:600; margin-bottom: .5rem">Medication</label>
                            <input id="medication" name="medication" type="text" placeholder="e.g., Amoxicillin 500mg" style="width:100%; padding:.75rem; border:1px solid #e2e8f0; border-radius:8px" required />
                        </div>
                        <div>
                            <label style="display:block; font-weight:600; margin-bottom: .5rem">Dosage</label>
                            <input id="dosage" name="dosage" type="text" placeholder="e.g., 1 cap" style="width:100%; padding:.75rem; border:1px solid #e2e8f0; border-radius:8px" />
                        </div>
                        <div>
                            <label style="display:block; font-weight:600; margin-bottom: .5rem">Frequency</label>
                            <input id="frequency" name="frequency" type="text" placeholder="e.g., TID" style="width:100%; padding:.75rem; border:1px solid #e2e8f0; border-radius:8px" />
                        </div>
                        <div>
                            <label style="display:block; font-weight:600; margin-bottom: .5rem">Days Supply</label>
                            <input id="days" name="days" type="number" min="1" placeholder="e.g., 7" style="width:100%; padding:.75rem; border:1px solid #e2e8f0; border-radius:8px" />
                        </div>
                        <div>
                            <label style="display:block; font-weight:600; margin-bottom: .5rem">Quantity</label>
                            <input id="qty" name="qty" type="number" min="1" placeholder="e.g., 21" style="width:100%; padding:.75rem; border:1px solid #e2e8f0; border-radius:8px" />
                        </div>
                        <div>
                            <label style="display:block; font-weight:600; margin-bottom: .5rem">Prescriber</label>
                            <input id="prescriber" name="prescriber" type="text" placeholder="e.g., Dr. Santos" style="width:100%; padding:.75rem; border:1px solid #e2e8f0; border-radius:8px" />
                        </div>
                        <div>
                            <label style="display:block; font-weight:600; margin-bottom: .5rem">Priority</label>
                            <select id="priority" name="priority" style="width:100%; padding:.75rem; border:1px solid #e2e8f0; border-radius:8px">
                                <option value="routine">Routine</option>
                                <option value="priority">Priority</option>
                                <option value="stat">STAT</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-top: 1rem">
                        <label style="display:block; font-weight:600; margin-bottom: .5rem">Notes</label>
                        <textarea id="notes" name="notes" rows="3" placeholder="Additional instructions..." style="width:100%; padding:.75rem; border:1px solid #e2e8f0; border-radius:8px; resize: vertical"></textarea>
                    </div>
                    <div class="quick-actions" style="justify-content: flex-end; margin-top: 1rem">
                        <button type="reset" class="btn btn-secondary"><i class="fas fa-eraser"></i> Clear</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-list"></i> Save/Queue</button>
                        <button type="button" class="btn btn-warning" onclick="dispenseNow()"><i class="fas fa-pills"></i> Dispense</button>
                    </div>
                </form>
            </div>

            <!-- Priority Queue Anchor -->
            <div class="table-container" style="margin-top: 2rem">
                <h3 id="priority" style="margin-bottom: 1rem">Priority Queue</h3>
                <table class="table">
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
                        <tr>
                            <td>RX-2025-050</td>
                            <td>Maria Garcia</td>
                            <td>Insulin Glargine</td>
                            <td><span class="badge badge-danger">STAT</span></td>
                            <td><span class="badge badge-warning">Verifying</span></td>
                            <td>
                                <a href="#" class="btn btn-primary" style="padding:.3rem .8rem; font-size:.8rem" onclick="alert('Process (demo)')">Process</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Queue Table -->
            <div class="table-container" style="margin-top: 2rem">
                <h3 id="queue" style="margin-bottom: 1rem">Prescription Queue</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Rx Number</th>
                            <th>Patient</th>
                            <th>Medication</th>
                            <th>Qty</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>RX-2025-061</td>
                            <td>David Lee</td>
                            <td>Metformin 500mg</td>
                            <td>60</td>
                            <td><span class="badge badge-info">Routine</span></td>
                            <td><span class="badge badge-info">Queued</span></td>
                            <td>
                                <a href="#" class="btn btn-success" style="padding:.3rem .8rem; font-size:.8rem" onclick="alert('Start (demo)')">Start</a>
                            </td>
                        </tr>
                        <tr>
                            <td>RX-2025-062</td>
                            <td>Lisa Anderson</td>
                            <td>Atorvastatin 20mg</td>
                            <td>30</td>
                            <td><span class="badge badge-warning">Priority</span></td>
                            <td><span class="badge badge-success">Ready</span></td>
                            <td>
                                <a href="#dispense" class="btn btn-warning" style="padding:.3rem .8rem; font-size:.8rem">Dispense</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Interactions -->
            <div class="table-container" style="margin-top: 2rem">
                <h3 id="interactions" style="margin-bottom: 1rem">Drug Interactions</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Medication A</th>
                            <th>Medication B</th>
                            <th>Severity</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Maria Garcia</td>
                            <td>Warfarin</td>
                            <td>Amoxicillin</td>
                            <td><span class="badge badge-danger">Critical</span></td>
                            <td><a href="#" class="btn btn-danger" style="padding:.3rem .8rem; font-size:.8rem" onclick="alert('Notify prescriber (demo)')">Notify</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Dispense section -->
            <div class="table-container" style="margin-top: 2rem">
                <h3 id="dispense" style="margin-bottom: 1rem">Dispense Medication</h3>
                <form id="dispense-form">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem">
                        <div>
                            <label style="display:block; font-weight:600; margin-bottom: .5rem">Rx Number</label>
                            <input id="dRxNo" name="dRxNo" type="text" placeholder="RX-..." style="width:100%; padding:.75rem; border:1px solid #e2e8f0; border-radius:8px" />
                        </div>
                        <div>
                            <label style="display:block; font-weight:600; margin-bottom: .5rem">Quantity to dispense</label>
                            <input id="dQty" name="dQty" type="number" min="1" placeholder="e.g., 30" style="width:100%; padding:.75rem; border:1px solid #e2e8f0; border-radius:8px" />
                        </div>
                    </div>
                    <div class="quick-actions" style="justify-content: flex-end; margin-top: 1rem">
                        <button type="submit" class="btn btn-warning"><i class="fas fa-pills"></i> Dispense</button>
                    </div>
                </form>
            </div>

            <!-- History -->
            <div class="table-container" style="margin-top: 2rem">
                <h3 id="history" style="margin-bottom: 1rem">Dispense History</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>Rx Number</th>
                            <th>Patient</th>
                            <th>Medication</th>
                            <th>Qty</th>
                            <th>Staff</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>2025-10-01 09:45</td>
                            <td>RX-2025-040</td>
                            <td>James Brown</td>
                            <td>Insulin Glargine</td>
                            <td>1 vial</td>
                            <td>PH-01</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        // Logout (demo)
        function handleLogout(){ if(confirm('Are you sure you want to logout?')) alert('Logged out (demo)'); }

        // Save/Queue demo
        document.getElementById('rx-form').addEventListener('submit', function(e){
            e.preventDefault();
            alert('Prescription saved to queue (demo).');
            this.reset();
        });

        function dispenseNow(){
            alert('Direct dispense triggered (demo). Use Dispense section below for details.');
        }

        document.getElementById('dispense-form').addEventListener('submit', function(e){
            e.preventDefault();
            alert('Medication dispensed (demo).');
            this.reset();
        });
    </script>
</body>
</html>
