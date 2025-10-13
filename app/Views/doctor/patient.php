<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Patient Management - HMS Doctor</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="doctor">
     <!--header-->
    <?php include APPPATH . 'Views/template/header.php'; ?>
    <div class="main-container">
        <!--sidebar-->
        <?php include APPPATH . 'Views/doctor/components/sidebar.php'; ?>

        <main class="content">
            <h1 class="page-title">My Patient</h1>
            <div class="page-actions">
                <button class="btn btn-success" id="addPatientBtn">
                    <i class="fas fa-plus"></i> Add New Patient
                </button>
            </div><br>

            <div class="dashboard-overview">
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue"><i class="fas fa-users"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Total Patients</h3>
                            <p class="card-subtitle">Under your care</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value blue"><?= $totalPatients ?? 0 ?></div>
                        </div>
                    </div>
                </div>

                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue"><i class="fas fa-calendar-week"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Patient Type</h3>
                            <p class="card-subtitle">Under your care</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value blue"><?= $inPatients ?? 0 ?></div>
                            <div class="metric-label">In-Patient</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value green"><?= $outPatients ?? 0 ?></div>
                            <div class="metric-label">Out-Patient</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Patient Table Section -->
            <div class="patient-table-section">
                <h2>Patient List</h2>
                <div class="table-responsive">
                    <table class="patient-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Age</th>
                                <th>Gender</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Patient Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($patients)): ?>
                                <?php foreach ($patients as $patient): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['middle_name'] . ' ' . $patient['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($patient['age']); ?></td>
                                        <td><?php echo htmlspecialchars(ucfirst($patient['gender'])); ?></td>
                                        <td><?php echo htmlspecialchars($patient['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($patient['email'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars(ucfirst($patient['patient_type'] ?? 'N/A')); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo strtolower($patient['status'] ?? 'active'); ?>">
                                                <?php echo htmlspecialchars(ucfirst($patient['status'] ?? 'Active')); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary view-patient" data-id="<?php echo $patient['id']; ?>">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <button class="btn btn-sm btn-warning edit-patient" data-id="<?php echo $patient['id']; ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">No patients found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- View Patient Modal -->
    <div id="viewPatientModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:#fff; padding:2rem; border-radius:8px; max-width:800px; width:98%; margin:auto; position:relative; max-height:90vh; overflow:auto; box-sizing:border-box; -webkit-overflow-scrolling:touch;">
            <div class="hms-modal-header">
                <div class="hms-modal-title">
                    <i class="fas fa-eye" style="color:#4f46e5"></i>
                    <h2>View Patient Details</h2>
                </div>
            </div>
            <div id="viewPatientContent">
                <!-- Patient details will be loaded here -->
            </div>
            <button aria-label="Close" onclick="closeViewPatientModal()" style="position:absolute; top:10px; right:10px; background:transparent; border:none; font-size:1.25rem; color:#6b7280; cursor:pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

     <!-- Add/Edit Patient Popup Modal (styled like Add User) -->
        <div id="patientModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); z-index:9999; align-items:center; justify-content:center;">
            <div style="background:#fff; padding:2rem; border-radius:8px; max-width:960px; width:98%; margin:auto; position:relative; max-height:90vh; overflow:auto; box-sizing:border-box; -webkit-overflow-scrolling:touch;">
                <div class="hms-modal-header">
                    <div class="hms-modal-title">
                        <i class="fas fa-user-plus" style="color:#4f46e5"></i>
                        <h2 id="patientModalTitle" style="margin:0; font-size:1.25rem;">Add New Patient</h2>
                    </div>
                </div>
                <form id="patientForm">
                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:1rem; padding-bottom:5rem;">
                        <div>
                            <label for="first_name">First Name*</label>
                            <input type="text" id="first_name" name="first_name" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                            <small id="err_first_name" style="color:#dc2626"></small>
                        </div>
                        <div>
                            <label for="middle_name">Middle Name</label>
                            <input type="text" id="middle_name" name="middle_name" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                        </div>
                        <div>
                            <label for="last_name">Last Name*</label>
                            <input type="text" id="last_name" name="last_name" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                            <small id="err_last_name" style="color:#dc2626"></small>
                        </div>
                        <div>
                            <label for="date_of_birth">Date of Birth*</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                            <small id="err_date_of_birth" style="color:#dc2626"></small>
                        </div>
                        <div>
                            <label for="age">Age</label>
                            <input type="number" id="age" name="age" readonly style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px; background:#f9fafb;">
                        </div>
                        <div>
                            <label for="gender">Gender*</label>
                            <select id="gender" name="gender" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                                <option value="">Select...</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            <small id="err_gender" style="color:#dc2626"></small>
                        </div>
                        <div>
                            <label for="civil_status">Civil Status</label>
                            <select id="civil_status" name="civil_status" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                                <option value="">Select...</option>
                                <option value="single">Single</option>
                                <option value="married">Married</option>
                                <option value="widowed">Widowed</option>
                                <option value="separated">Separated</option>
                            </select>
                            <small id="err_civil_status" style="color:#dc2626"></small>
                        </div>
                        <div>
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                            <small id="err_phone" style="color:#dc2626"></small>
                        </div>
                        <div>
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                            <small id="err_address" style="color:#dc2626"></small>
                        </div>
                        <div>
                            <label for="province">Province</label>
                            <input type="text" id="province" name="province" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                            <small id="err_province" style="color:#dc2626"></small>
                        </div>
                        <div>
                            <label for="city">City/Municipality</label>
                            <input type="text" id="city" name="city" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                            <small id="err_city" style="color:#dc2626"></small>
                        </div>
                        <div>
                            <label for="barangay">Barangay</label>
                            <input type="text" id="barangay" name="barangay" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                            <small id="err_barangay" style="color:#dc2626"></small>
                        </div>
                        <div>
                            <label for="zip_code">ZIP Code</label>
                            <input type="text" id="zip_code" name="zip_code" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                            <small id="err_zip_code" style="color:#dc2626"></small>
                        </div>
                        <div>
                            <label for="insurance_provider">Insurance Provider</label>
                            <input type="text" id="insurance_provider" name="insurance_provider" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                        </div>
                        <div>
                            <label for="insurance_number">Insurance Number</label>
                            <input type="text" id="insurance_number" name="insurance_number" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                        </div>
                        <div>
                            <label for="emergency_contact_name">Emergency Contact Name</label>
                            <input type="text" id="emergency_contact_name" name="emergency_contact_name" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                            <small id="err_emergency_contact_name" style="color:#dc2626"></small>
                        </div>
                        <div>
                            <label for="emergency_contact_phone">Emergency Contact Phone</label>
                            <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                            <small id="err_emergency_contact_phone" style="color:#dc2626"></small>
                        </div>
                        <div>
                            <label for="patient_type">Patient Type</label>
                            <select id="patient_type" name="patient_type" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                                <option value="">Select...</option>
                                <option value="outpatient">Outpatient</option>
                                <option value="inpatient">Inpatient</option>
                                <option value="emergency">Emergency</option>
                            </select>
                        </div>
                        <div>
                            <label for="status">Status</label>
                            <select id="status" name="status" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label for="medical_notes">Medical Notes</label>
                            <textarea id="medical_notes" name="medical_notes" rows="3" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;"></textarea>
                        </div>
                    </div>
                    <div style="display:flex; gap:1rem; justify-content:flex-end; margin-top:1.5rem; position:sticky; bottom:0; background:#fff; padding-top:1rem; border-top:1px solid #e5e7eb;">
                        <button type="button" onclick="closeAddPatientsModal()" style="background:#6b7280; color:#fff; border:none; padding:0.75rem 1.5rem; border-radius:4px; cursor:pointer;">Cancel</button>
                        <button type="submit" id="savePatientBtn" style="background:#2563eb; color:#fff; border:none; padding:0.75rem 1.5rem; border-radius:4px; cursor:pointer;">Save Patient</button>
                    </div>
                </form>
                <button aria-label="Close" onclick="closeAddPatientsModal()" style="position:absolute; top:10px; right:10px; background:transparent; border:none; font-size:1.25rem; color:#6b7280; cursor:pointer;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <script>
            function openAddPatientsModal() {
                var m = document.getElementById('patientModal');
                if (m) { m.style.display = 'flex'; }
            }
            function closeAddPatientsModal() {
                var m = document.getElementById('patientModal');
                if (m) { m.style.display = 'none'; }
            }
            function openViewPatientModal() {
                var m = document.getElementById('viewPatientModal');
                if (m) { m.style.display = 'flex'; }
            }
            function closeViewPatientModal() {
                var m = document.getElementById('viewPatientModal');
                if (m) { m.style.display = 'none'; }
            }
            // Button handler to open modal
            function addPatient() {
                openAddPatientsModal();
            }
            // Close when clicking outside the dialog
            document.addEventListener('click', function(e){
                var m = document.getElementById('patientModal');
                if (!m) return;
                if (e.target === m) closeAddPatientsModal();
                var vm = document.getElementById('viewPatientModal');
                if (!vm) return;
                if (e.target === vm) closeViewPatientModal();
            });
            // Close on Escape
            document.addEventListener('keydown', function(e){
                if (e.key === 'Escape') {
                    closeAddPatientsModal();
                    closeViewPatientModal();
                }
            });
            // Optional: attach by ID if present (button already uses inline onclick)
            (function(){
                var addBtn = document.getElementById('addPatientBtn');
                if (addBtn) addBtn.addEventListener('click', addPatient);
            })();
            // Auto-calc age from DOB
            (function(){
                var dob = document.getElementById('date_of_birth');
                var age = document.getElementById('age');
                function calcAge(value){
                    if (!value) { age && (age.value = ''); return; }
                    var d = new Date(value);
                    if (isNaN(d.getTime())) { age && (age.value = ''); return; }
                    var today = new Date();
                    var a = today.getFullYear() - d.getFullYear();
                    var m = today.getMonth() - d.getMonth();
                    if (m < 0 || (m === 0 && today.getDate() < d.getDate())) a--;
                    if (age) age.value = a >= 0 ? a : '';
                }
                if (dob) {
                    dob.addEventListener('change', function(){ calcAge(this.value); });
                }
            })();

            // View Patient Details
            (function(){
                document.addEventListener('click', function(e){
                    if (e.target.classList.contains('view-patient') || e.target.closest('.view-patient')) {
                        e.preventDefault();
                        var btn = e.target.classList.contains('view-patient') ? e.target : e.target.closest('.view-patient');
                        var patientId = btn.getAttribute('data-id');
                        if (patientId) {
                            viewPatient(patientId);
                        }
                    }
                });
            })();

            async function viewPatient(patientId) {
                try {
                    var res = await fetch('/doctor/patient/' + patientId, {
                        method: 'GET',
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    });
                    var result = await res.json().catch(function(){ return {}; });
                    if (res.ok && result.status === 'success'){
                        var patient = result.patient;
                        var content = document.getElementById('viewPatientContent');
                        if (content) {
                            content.innerHTML = `
                                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:1rem;">
                                    <div><strong>Name:</strong> ${patient.first_name} ${patient.middle_name || ''} ${patient.last_name}</div>
                                    <div><strong>Age:</strong> ${patient.age || 'N/A'}</div>
                                    <div><strong>Gender:</strong> ${patient.gender ? patient.gender.charAt(0).toUpperCase() + patient.gender.slice(1) : 'N/A'}</div>
                                    <div><strong>Phone:</strong> ${patient.contact_no || 'N/A'}</div>
                                    <div><strong>Email:</strong> ${patient.email || 'N/A'}</div>
                                    <div><strong>Patient Type:</strong> ${patient.patient_type ? patient.patient_type.charAt(0).toUpperCase() + patient.patient_type.slice(1) : 'N/A'}</div>
                                    <div><strong>Status:</strong> ${patient.status ? patient.status.charAt(0).toUpperCase() + patient.status.slice(1) : 'N/A'}</div>
                                    <div><strong>Date of Birth:</strong> ${patient.date_of_birth || 'N/A'}</div>
                                    <div><strong>Civil Status:</strong> ${patient.civil_status || 'N/A'}</div>
                                    <div style="grid-column: 1 / -1;"><strong>Address:</strong> ${patient.address || 'N/A'}</div>
                                    <div><strong>Province:</strong> ${patient.province || 'N/A'}</div>
                                    <div><strong>City:</strong> ${patient.city || 'N/A'}</div>
                                    <div><strong>Barangay:</strong> ${patient.barangay || 'N/A'}</div>
                                    <div><strong>ZIP Code:</strong> ${patient.zip_code || 'N/A'}</div>
                                    <div><strong>Insurance Provider:</strong> ${patient.insurance_provider || 'N/A'}</div>
                                    <div><strong>Insurance Number:</strong> ${patient.insurance_number || 'N/A'}</div>
                                    <div><strong>Emergency Contact:</strong> ${patient.emergency_contact || 'N/A'}</div>
                                    <div><strong>Emergency Phone:</strong> ${patient.emergency_phone || 'N/A'}</div>
                                    <div style="grid-column: 1 / -1;"><strong>Medical Notes:</strong> ${patient.medical_notes || 'N/A'}</div>
                                </div>
                            `;
                        }
                        openViewPatientModal();
                    } else {
                        alert('Failed to load patient details');
                    }
                } catch (err){
                    console.error('Error loading patient details', err);
                    alert('Network error. Please try again.');
                }
            }

            // Edit Patient
            (function(){
                document.addEventListener('click', function(e){
                    if (e.target.classList.contains('edit-patient') || e.target.closest('.edit-patient')) {
                        e.preventDefault();
                        var btn = e.target.classList.contains('edit-patient') ? e.target : e.target.closest('.edit-patient');
                        var patientId = btn.getAttribute('data-id');
                        if (patientId) {
                            editPatient(patientId);
                        }
                    }
                });
            })();

            async function editPatient(patientId) {
                try {
                    var res = await fetch('/doctor/patient/' + patientId, {
                        method: 'GET',
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    });
                    var result = await res.json().catch(function(){ return {}; });
                    if (res.ok && result.status === 'success'){
                        var patient = result.patient;
                        // Populate form with patient data
                        document.getElementById('first_name').value = patient.first_name || '';
                        document.getElementById('middle_name').value = patient.middle_name || '';
                        document.getElementById('last_name').value = patient.last_name || '';
                        document.getElementById('date_of_birth').value = patient.date_of_birth || '';
                        document.getElementById('age').value = patient.age || '';
                        document.getElementById('gender').value = patient.gender || '';
                        document.getElementById('civil_status').value = patient.civil_status || '';
                        document.getElementById('phone').value = patient.contact_no || '';
                        document.getElementById('email').value = patient.email || '';
                        document.getElementById('address').value = patient.address || '';
                        document.getElementById('province').value = patient.province || '';
                        document.getElementById('city').value = patient.city || '';
                        document.getElementById('barangay').value = patient.barangay || '';
                        document.getElementById('zip_code').value = patient.zip_code || '';
                        document.getElementById('insurance_provider').value = patient.insurance_provider || '';
                        document.getElementById('insurance_number').value = patient.insurance_number || '';
                        document.getElementById('emergency_contact_name').value = patient.emergency_contact || '';
                        document.getElementById('emergency_contact_phone').value = patient.emergency_phone || '';
                        document.getElementById('patient_type').value = patient.patient_type || '';
                        document.getElementById('status').value = patient.status || '';
                        document.getElementById('medical_notes').value = patient.medical_notes || '';
                        // Change modal title and button
                        document.getElementById('patientModalTitle').innerHTML = '<i class="fas fa-edit" style="color:#f59e0b"></i> Edit Patient';
                        document.getElementById('savePatientBtn').textContent = 'Update Patient';
                        // Store patient ID for update
                        document.getElementById('patientForm').setAttribute('data-patient-id', patientId);
                        openAddPatientsModal();
                    } else {
                        alert('Failed to load patient details');
                    }
                } catch (err){
                    console.error('Error loading patient details', err);
                    alert('Network error. Please try again.');
                }
            }

            // Submit patient form to backend (for both add and edit)
            (function(){
                var form = document.getElementById('patientForm');
                if (!form) return;
                form.addEventListener('submit', async function(e){
                    e.preventDefault();
                    var btn = document.getElementById('savePatientBtn');
                    if (btn) { btn.disabled = true; btn.textContent = btn.textContent === 'Update Patient' ? 'Updating...' : 'Saving...'; }

                    // Collect values
                    var getVal = function(id){ var el = document.getElementById(id); return el ? el.value : null; };
                    var payload = {
                        first_name: getVal('first_name'),
                        middle_name: getVal('middle_name'),
                        last_name: getVal('last_name'),
                        date_of_birth: getVal('date_of_birth'),
                        age: getVal('age'),
                        gender: getVal('gender'),
                        civil_status: getVal('civil_status'),
                        phone: getVal('phone'),
                        email: getVal('email'),
                        address: getVal('address'),
                        province: getVal('province'),
                        city: getVal('city'),
                        barangay: getVal('barangay'),
                        zip_code: getVal('zip_code'),
                        insurance_provider: getVal('insurance_provider'),
                        insurance_number: getVal('insurance_number'),
                        emergency_contact_name: getVal('emergency_contact_name'),
                        emergency_contact_phone: getVal('emergency_contact_phone'),
                        patient_type: getVal('patient_type'),
                        status: getVal('status'),
                        medical_notes: getVal('medical_notes')
                    };

                    var patientId = form.getAttribute('data-patient-id');
                    var method = patientId ? 'PUT' : 'POST';
                    var url = patientId ? '/doctor/patient/' + patientId : '/doctor/patients';

                    try {
                        var res = await fetch(url, {
                            method: method,
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify(payload),
                            credentials: 'same-origin'
                        });
                        var result = await res.json().catch(function(){ return {}; });
                        if (res.ok && result.status === 'success'){
                            alert('Patient ' + (patientId ? 'updated' : 'saved') + ' successfully');
                            closeAddPatientsModal();
                            // Reset form
                            form.reset();
                            form.removeAttribute('data-patient-id');
                            document.getElementById('patientModalTitle').innerHTML = '<i class="fas fa-user-plus" style="color:#4f46e5"></i> Add New Patient';
                            document.getElementById('savePatientBtn').textContent = 'Save Patient';
                            // Reload to refresh counts/list
                            window.location.reload();
                        } else {
                            var msg = result.message || 'Failed to save patient';
                            if (result.errors){
                                var details = Object.values(result.errors).join('\n');
                                msg += '\n\n' + details;
                            }
                            alert(msg);
                        }
                    } catch (err){
                        console.error('Error saving patient', err);
                        alert('Network error. Please try again.');
                    } finally {
                        if (btn) { btn.disabled = false; btn.textContent = patientId ? 'Update Patient' : 'Save Patient'; }
                    }
                });
            })();
        </script>
</body>
</html>
