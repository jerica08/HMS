<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Electronic Health Record - HMS Doctor</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        /* Scoped styles for Doctor EHR page */
        .filter-row { display: flex; gap: 1rem; align-items: end; flex-wrap: wrap; }
        .filter-input { padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.9rem; }
        .card { background: #fff; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
        .card-header { padding: 1rem; border-bottom: 1px solid #e5e7eb; background: #f9fafb; }
        .card-content { padding: 1rem; }
        .btn-group .btn { border-radius: 6px; }
        .table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .table thead th { background: #f8fafc; color: #374151; font-weight: 600; text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #e5e7eb; }
        .table tbody td { padding: 0.75rem 1rem; border-bottom: 1px solid #f3f4f6; }
        .badge { display: inline-block; padding: 0.25rem 0.6rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-secondary { background: #e5e7eb; color: #374151; }
        /* Modal base */
        .modal { display: none; position: fixed; z-index: 1000; inset: 0; background: rgba(0,0,0,0.5); }
        .modal.show { display: flex; align-items: center; justify-content: center; }
        .modal-content { background: #fff; border-radius: 8px; width: 90%; max-width: 840px; max-height: 90vh; overflow-y: auto; box-shadow: 0 4px 20px rgba(0,0,0,0.15); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem; border-bottom: 1px solid #e2e8f0; background: #f7fafc; }
        .modal-header h3 { margin: 0; color: #2d3748; }
        .modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #718096; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; }
        .modal-close:hover { color: #2d3748; }
        .modal-body { padding: 1.25rem; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; padding: 1rem; border-top: 1px solid #e2e8f0; background: #f7fafc; }
    </style>
</head>
<body class="doctor">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <?php include APPPATH . 'Views/doctor/components/sidebar.php'; ?>

        <main class="content">
            <h1 class="page-title">Electronic Health Record</h1>
            <div class="page-actions">
                <button class="btn btn-success" id="newRecordBtn"><i class="fas fa-plus"></i> New Record Entry</button>
            </div><br>

            <div class="search-filter">
                <h3 style="margin-bottom: 1rem;">Search Patient</h3>
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Search Patient</label>
                        <input type="text" class="filter-input" placeholder="Search by patient name, ID, or record number..." id="patientSearch" value="">
                    </div>
                    <div class="filter-group">
                        <label>&nbsp;</label>
                        <button class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-top: 2rem;">
                <div class="card-header">
                    <div class="btn-group">
                        <button class="btn btn-primary active" data-tab="summary">Summary</button>
                        <button class="btn btn-secondary" data-tab="visits">Visit History</button>
                        <button class="btn btn-secondary" data-tab="medications">Medications</button>
                        <button class="btn btn-secondary" data-tab="labs">Lab Results</button>
                        <button class="btn btn-secondary" data-tab="imaging">Imaging</button>
                        <button class="btn btn-secondary" data-tab="notes">Clinical Notes</button>
                    </div>
                </div>

                <div class="tab-content active" id="summary-tab">
                    <div class="card-content">
                        <h4 style="margin-bottom: 1rem;">Medical Summary</h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                            <div>
                                <h5>Active Diagnoses</h5>
                                <ul style="list-style: none; padding: 0;">
                                    <li style="padding: 0.5rem; background: #f7fafc; margin-bottom: 0.5rem; border-radius: 5px;">
                                        <strong>Essential Hypertension</strong> (I10)<br>
                                        <small>Diagnosed: Jan 15, 2023</small>
                                    </li>
                                    <li style="padding: 0.5rem; background: #f7fafc; margin-bottom: 0.5rem; border-radius: 5px;">
                                        <strong>Hyperlipidemia</strong> (E78.5)<br>
                                        <small>Diagnosed: Mar 22, 2023</small>
                                    </li>
                                </ul>
                            </div>
                            <div>
                                <h5>Current Medications</h5>
                                <ul style="list-style: none; padding: 0;">
                                    <li style="padding: 0.5rem; background: #f0fff4; margin-bottom: 0.5rem; border-radius: 5px;">
                                        <strong>Lisinopril 10mg</strong><br>
                                        <small>Once daily - Started: Jan 2023</small>
                                    </li>
                                    <li style="padding: 0.5rem; background: #f0fff4; margin-bottom: 0.5rem; border-radius: 5px;">
                                        <strong>Atorvastatin 20mg</strong><br>
                                        <small>Once daily - Started: Mar 2023</small>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div style="margin-top: 2rem;">
                            <h5>Recent Activity</h5>
                            <div style="border-left: 3px solid #4299e1; padding-left: 1rem;">
                                <div style="margin-bottom: 1rem;"><strong>Aug 20, 2025</strong> - Follow-up visit for hypertension management<br><small>BP: 140/90, adjusted medication dosage</small></div>
                                <div style="margin-bottom: 1rem;"><strong>Aug 15, 2025</strong> - Lab results reviewed<br><small>Lipid panel shows improvement</small></div>
                                <div><strong>Jul 30, 2025</strong> - Routine check-up<br><small>Patient compliance good, continue current regimen</small></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-content" id="visits-tab" style="display: none;">
                    <div class="card-content">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Visit Type</th>
                                    <th>Chief Complaint</th>
                                    <th>Diagnosis</th>
                                    <th>Provider</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Aug 20, 2025</td>
                                    <td>Follow-up</td>
                                    <td>Hypertension management</td>
                                    <td>Essential Hypertension</td>
                                    <td>Dr. Sarah Johnson</td>
                                    <td><button class="btn btn-primary view-visit-btn" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">View</button></td>
                                </tr>
                                <tr>
                                    <td>Jul 30, 2025</td>
                                    <td>Routine</td>
                                    <td>Annual check-up</td>
                                    <td>Routine examination</td>
                                    <td>Dr. Sarah Johnson</td>
                                    <td><button class="btn btn-primary view-visit-btn" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">View</button></td>
                                </tr>
                                <tr>
                                    <td>Jun 15, 2025</td>
                                    <td>Consultation</td>
                                    <td>Chest pain evaluation</td>
                                    <td>Atypical chest pain</td>
                                    <td>Dr. Sarah Johnson</td>
                                    <td><button class="btn btn-primary view-visit-btn" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">View</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-content" id="medications-tab" style="display: none;">
                    <div class="card-content">
                        <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 1rem;">
                            <h4>Medication History</h4>
                            <button class="btn btn-success" id="addMedicationBtn">Add Medication</button>
                        </div>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Medication</th>
                                    <th>Dosage</th>
                                    <th>Frequency</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Lisinopril</td>
                                    <td>10mg</td>
                                    <td>Once daily</td>
                                    <td>Jan 15, 2023</td>
                                    <td>-</td>
                                    <td><span class="badge badge-success">Active</span></td>
                                    <td>
                                        <button class="btn btn-secondary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Edit</button>
                                        <button class="btn btn-danger" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Stop</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Atorvastatin</td>
                                    <td>20mg</td>
                                    <td>Once daily</td>
                                    <td>Mar 22, 2023</td>
                                    <td>-</td>
                                    <td><span class="badge badge-success">Active</span></td>
                                    <td>
                                        <button class="btn btn-secondary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Edit</button>
                                        <button class="btn btn-danger" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Stop</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Metoprolol</td>
                                    <td>25mg</td>
                                    <td>Twice daily</td>
                                    <td>Dec 10, 2022</td>
                                    <td>Jan 10, 2023</td>
                                    <td><span class="badge badge-secondary">Discontinued</span></td>
                                    <td>
                                        <button class="btn btn-primary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">View</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-content" id="labs-tab" style="display: none;">
                    <div class="card-content">
                        <h4 style="margin-bottom: 1rem;">Laboratory Results</h4>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Test</th>
                                    <th>Result</th>
                                    <th>Reference Range</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Total Cholesterol</td>
                                    <td>195 mg/dL</td>
                                    <td>&lt; 200 mg/dL</td>
                                    <td>Aug 15, 2025</td>
                                    <td><span class="badge badge-success">Normal</span></td>
                                    <td><button class="btn btn-primary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">View</button></td>
                                </tr>
                                <tr>
                                    <td>LDL Cholesterol</td>
                                    <td>120 mg/dL</td>
                                    <td>&lt; 100 mg/dL</td>
                                    <td>Aug 15, 2025</td>
                                    <td><span class="badge badge-warning">High</span></td>
                                    <td><button class="btn btn-primary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">View</button></td>
                                </tr>
                                <tr>
                                    <td>HDL Cholesterol</td>
                                    <td>45 mg/dL</td>
                                    <td>&gt; 40 mg/dL</td>
                                    <td>Aug 15, 2025</td>
                                    <td><span class="badge badge-success">Normal</span></td>
                                    <td><button class="btn btn-primary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">View</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-content" id="imaging-tab" style="display: none;">
                    <div class="card-content">
                        <h4 style="margin-bottom: 1rem;">Imaging Studies</h4>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Study Type</th>
                                    <th>Date</th>
                                    <th>Indication</th>
                                    <th>Results</th>
                                    <th>Radiologist</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Chest X-Ray</td>
                                    <td>Jun 15, 2025</td>
                                    <td>Chest pain evaluation</td>
                                    <td>Normal cardiac silhouette</td>
                                    <td>Dr. Michael Chen</td>
                                    <td>
                                        <button class="btn btn-primary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">View</button>
                                        <button class="btn btn-secondary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Download</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>ECG</td>
                                    <td>Aug 20, 2025</td>
                                    <td>Routine follow-up</td>
                                    <td>Normal sinus rhythm</td>
                                    <td>Dr. Sarah Johnson</td>
                                    <td>
                                        <button class="btn btn-primary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">View</button>
                                        <button class="btn btn-secondary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Download</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-content" id="notes-tab" style="display: none;">
                    <div class="card-content">
                        <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 1rem;">
                            <h4>Clinical Notes</h4>
                            <button class="btn btn-success" id="addNoteBtn">Add Note</button>
                        </div>
                        <div style="border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 1rem;">
                            <div style="background: #f7fafc; padding: 1rem; border-bottom: 1px solid #e2e8f0;">
                                <strong>Progress Note - Aug 20, 2025</strong>
                                <span style="float: right; color: #666;">Dr. Sarah Johnson</span>
                            </div>
                            <div style="padding: 1rem;">
                                <p><strong>Chief Complaint:</strong> Follow-up for hypertension management</p>
                                <p><strong>Assessment:</strong> Patient's blood pressure remains elevated at 140/90 despite current medication.</p>
                                <p><strong>Plan:</strong> Increase Lisinopril to 15mg daily. Monitor BP, follow up in 2 weeks.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modals -->
            <div class="modal" id="newRecordModal">
                <div class="modal-content" style="max-width: 720px;">
                    <div class="modal-header">
                        <h3>New Record Entry</h3>
                        <button class="modal-close" id="closeNewRecord">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form id="newRecordForm">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                                <div>
                                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Record Type *</label>
                                    <select class="form-select" required>
                                        <option value="">Select Type</option>
                                        <option>Progress Note</option>
                                        <option>Consultation Note</option>
                                        <option>Discharge Summary</option>
                                        <option>Procedure Note</option>
                                    </select>
                                </div>
                                <div>
                                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Date *</label>
                                    <input type="date" class="form-input" required>
                                </div>
                            </div>
                            <div style="margin-bottom: 1rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Chief Complaint</label>
                                <textarea class="form-input" rows="2" placeholder="Patient's main concern..."></textarea>
                            </div>
                            <div style="margin-bottom: 1rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Assessment & Plan</label>
                                <textarea class="form-input" rows="4" placeholder="Clinical assessment and treatment plan..."></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="cancelNewRecord">Cancel</button>
                        <button type="submit" form="newRecordForm" class="btn btn-success">Save Record</button>
                    </div>
                </div>
            </div>

            <div class="modal" id="viewVisitModal">
                <div class="modal-content" style="max-width: 840px;">
                    <div class="modal-header">
                        <h3>Visit Details</h3>
                        <button class="modal-close" id="closeViewVisit">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                            <div>
                                <h4 style="margin-bottom: 1rem; color: #2d3748;">Visit Information</h4>
                                <div style="background: #f7fafc; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                                    <div style="margin-bottom: 0.5rem;"><strong>Date:</strong> <span id="visitDate">Aug 20, 2025</span></div>
                                    <div style="margin-bottom: 0.5rem;"><strong>Type:</strong> <span id="visitType">Follow-up</span></div>
                                    <div style="margin-bottom: 0.5rem;"><strong>Provider:</strong> <span id="visitProvider">Dr. Sarah Johnson</span></div>
                                    <div><strong>Duration:</strong> <span id="visitDuration">30 minutes</span></div>
                                </div>
                                <h4 style="margin-bottom: 1rem; color: #2d3748;">Chief Complaint</h4>
                                <div style="background: #e6fffa; padding: 1rem; border-radius: 8px;">
                                    <p id="visitComplaint">Follow-up for hypertension management</p>
                                </div>
                            </div>
                            <div>
                                <h4 style="margin-bottom: 1rem; color: #2d3748;">Clinical Notes</h4>
                                <div style="background: #f0fff4; padding: 1rem; border-radius: 8px;">
                                    <p><strong>Assessment:</strong> Patient's blood pressure remains elevated at 140/90 despite current medication.</p>
                                    <p><strong>Plan:</strong> Increase Lisinopril to 15mg daily. Patient to monitor BP at home and return in 2 weeks.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="closeViewVisitBtn">Close</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
    (function(){
        const tabs = document.querySelectorAll('.btn-group .btn');
        const contents = {
            summary: document.getElementById('summary-tab'),
            visits: document.getElementById('visits-tab'),
            medications: document.getElementById('medications-tab'),
            labs: document.getElementById('labs-tab'),
            imaging: document.getElementById('imaging-tab'),
            notes: document.getElementById('notes-tab')
        };
        tabs.forEach(btn => btn.addEventListener('click', function(){
            tabs.forEach(b => b.classList.remove('btn-primary','active')); tabs.forEach(b => b.classList.add('btn-secondary'));
            this.classList.add('btn-primary','active'); this.classList.remove('btn-secondary');
            Object.values(contents).forEach(el => { el.style.display = 'none'; el.classList.remove('active'); });
            const id = this.getAttribute('data-tab');
            contents[id].style.display = ''; contents[id].classList.add('active');
        }));

        // Modals
        const newRecordModal = document.getElementById('newRecordModal');
        function open(m){ m.classList.add('show'); } function close(m){ m.classList.remove('show'); }
        document.getElementById('newRecordBtn').addEventListener('click', () => open(newRecordModal));
        document.getElementById('closeNewRecord').addEventListener('click', () => close(newRecordModal));
        document.getElementById('cancelNewRecord').addEventListener('click', () => close(newRecordModal));
        document.getElementById('newRecordForm').addEventListener('submit', function(e){ e.preventDefault(); alert('Record saved.'); close(newRecordModal); this.reset(); });

        // Visit view modal demo
        const viewVisitModal = document.getElementById('viewVisitModal');
        document.querySelectorAll('.view-visit-btn').forEach(b => b.addEventListener('click', () => open(viewVisitModal)));
        document.getElementById('closeViewVisit').addEventListener('click', () => close(viewVisitModal));
        document.getElementById('closeViewVisitBtn').addEventListener('click', () => close(viewVisitModal));

        window.addEventListener('click', (e) => { [newRecordModal, viewVisitModal].forEach(m => { if (e.target === m) close(m); }); });
    })();
    </script>

    <script src="<?= base_url('js/logout.js') ?>"></script>
</body>
</html>
