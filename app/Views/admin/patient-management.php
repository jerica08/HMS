<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Patient Management - HMS Admin</title>
        <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
            .patient-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
                gap: 1.5rem;
                margin-bottom: 2rem;
            }
            .patient-section {
                background: white;
                border-radius: 8px;
                padding: 1.5rem;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .section-header {
                display: flex;
                align-items: center;
                gap: 1rem;
                margin-bottom: 1.5rem;
                padding-bottom: 1rem;
                border-bottom: 1px solid #e2e8f0;
            }
            .section-icon {
                width: 40px;
                height: 40px;
                border-radius: 8px;
                background: #3b82f6;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 1.2rem;
            }
            .patient-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 1rem 0;
                border-bottom: 1px solid #f3f4f6;
            }
            .patient-item:last-child {
                border-bottom: none;
            }
            .patient-info {
                flex: 1;
            }
            .patient-name {
                font-weight: 500;
                color: #1f2937;
                margin-bottom: 0.25rem;
            }
            .patient-details {
                font-size: 0.8rem;
                color: #6b7280;
            }
            .patient-status {
                padding: 0.25rem 0.75rem;
                border-radius: 15px;
                font-size: 0.8rem;
                font-weight: 500;
            }
            .status-admitted { background: #fef3c7; color: #92400e; }
            .status-discharged { background: #dcfce7; color: #166534; }
            .status-critical { background: #fecaca; color: #991b1b; }
            .status-stable { background: #dbeafe; color: #1e40af; }
            .status-emergency { background: #fed7cc; color: #c2410c; }
            .search-filters {
                background: white;
                border-radius: 8px;
                padding: 1.5rem;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                margin-bottom: 2rem;
            }
            .filter-row {
                display: flex;
                gap: 1rem;
                align-items: end;
                flex-wrap: wrap;
            }
            .filter-group {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
                min-width: 150px;
            }
            .filter-input {
                padding: 0.5rem;
                border: 1px solid #e2e8f0;
                border-radius: 5px;
                font-size: 0.9rem;
            }
            .patient-table {
                background: white;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .table-header {
                background: #f8fafc;
                padding: 1rem;
                border-bottom: 1px solid #e2e8f0;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .patient-avatar {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background: #4299e1;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: bold;
                font-size: 0.9rem;
            }
            /* Status badges to match DB values (Active/Inactive) */
            .status-active { background: #dcfce7; color: #166534; }
            .status-inactive { background: #f3f4f6; color: #6b7280; }
            .btn-small {
                padding: 0.3rem 0.8rem;
                font-size: 0.8rem;
            }
            .critical-alert {
                background: #fef2f2;
                border: 1px solid #fecaca;
                border-left: 4px solid #ef4444;
                border-radius: 8px;
            }
            .alert-content {
                color: #7f1d1d;
                font-size: 0.9rem;
            }
            .quick-actions {
                background: white;
                border-radius: 8px;
                padding: 1.5rem;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                margin-bottom: 2rem;
            }
            /* Table style aligned with doctor lab-results */
            .table { width: 100%; border-collapse: separate; border-spacing: 0; }
            .table thead th { background: #f8fafc; color: #374151; font-weight: 600; text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #e5e7eb; }
            .table tbody td { padding: 0.75rem 1rem; border-bottom: 1px solid #f3f4f6; }
            .patient-flow {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.75rem;
                background: #f8fafc;
                border-radius: 6px;
                margin: 0.5rem 0;
                font-size: 0.9rem;
            }
            .flow-number {
                font-weight: bold;
                color: #3b82f6;
            }
        </style>
    </head>
<?php include APPPATH . 'Views/template/header.php'; ?>
    
    
        
        <div class="main-container">
               <?php include APPPATH . 'Views/admin/components/sidebar.php'; ?>      
            <main class="content">
                <h1 class="page-title"> Patient Management</h1>
                <div style="display: flex; gap: 0.5rem;">
                    <button class="btn btn-primary btn-small" onclick="addPatient()">
                        <i class="fas fa-plus"></i> Add Patient
                    </button>
                </div><br>

                <!--Dashboard overview cards-->
                <div class="dashboard-overview">
                    <!-- Total Patient Cards -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Total Patient</h3>
                                <p class="card-subtitle">All Registered Users</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $patientStats['total_patients'] ?? 0 ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Active User Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern purple">
                                <i class="fas fa-bed"></i>
                            </div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Admitted Patient</h3>
                                <p class="card-subtitle">Currently active</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value purple">0</div>
                            </div>
                        </div>   
                    </div>
                </div>       

                <div class="patient-view">         
                    <!--Filter and Actions-->    
                    <div class="search-filter">
                        <h3 style="margin-bottom: 1rem;">Patient Search & Filters</h3>
                        <div class="filter-row">
                            <div class="filter-group">
                                <label> Search Patient</label>
                                <input type="text" class="filter-input" placeholder="Search by name, email, or ID..." 
                                    id="searchInput" value="">
                            </div>
                            <div class="filter-group">
                                <label>Status Filter</label>
                                <select class="filter-input" id="statusFilter">
                                    <option value="">All Status</option>
                                    <option value="admitted">Admitted</option>
                                    <option value="discharged">Dishcarge</option>
                                    <option value="critical">Critical</option>
                                    <option value="emergency">Emergency</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label> Role Filter</label>
                                <select class="filter-input" id="roleFilter">
                                    <option value="">All Roles</option>
                                    <option value="admin">Administrator</option>
                                    <option value="doctor">Doctor</option>
                                    <option value="nurse">Nurse</option>
                                    <option value="receptionist">Receptionist</option>
                                    <option value="laboratorist">Laboratory Staff</option>
                                    <option value="pharmacist">Pharmacist</option>
                                    <option value="accountant">Accountant</option>
                                    <option value="it_staff">IT Staff</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label>Department</label>
                                <select class="filter-input" id="departmentFilter">
                                    <option value="">All Departments</option>
                                    <option value="emergency">Emergency</option>
                                    <option value="icu">ICU</option>
                                    <option value="cardiology">Cardiology</option>
                                    <option value="general">General Ward</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label>Date Range</label>
                                <select class="filter-input" id="dateFilter">
                                    <option value="today">Today</option>
                                    <option value="week">This Week</option>
                                    <option value="month">This Month</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label>&nbsp;</label>
                                <button class="btn btn-primary" onclick="applyFilters()">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>     
                        </div>          
                    </div><br>
                    <!-- Patient List Table -->
                    <div class="patient-table">
                        <div class="table-header">
                            <h3>Patients</h3>
                        </div>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>ID</th>
                                    <th>Age</th>
                                    <th>Department</th>
                                    <th>Room</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($patients) && is_array($patients)): ?>
                                    <?php foreach ($patients as $patient): ?>
                                        <tr>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                                    <div class="patient-avatar" aria-label="Patient initials" title="Patient initials">
                                                        <?= strtoupper(substr($patient['first_name'] ?? 'P', 0, 1) . substr($patient['last_name'] ?? 'P', 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <div style="font-weight: 500;">
                                                            <?= esc(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? '')) ?>
                                                        </div>
                                                        <div style="font-size: 0.8rem; color: #6b7280;">
                                                            <?= esc($patient['email'] ?? '') ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= esc($patient['patient_id'] ?? 'N/A') ?></td>
                                            <td>
                                                <?php
                                                    if (!empty($patient['date_of_birth'])) {
                                                        $dob = new DateTime($patient['date_of_birth']);
                                                        $now = new DateTime();
                                                        $age = $now->diff($dob)->y;
                                                        echo $age;
                                                    } else {
                                                        echo 'N/A';
                                                    }
                                                ?>
                                            </td>
                                            <td><?= esc($patient['department'] ?? 'N/A') ?></td>
                                            <td><?= esc($patient['room'] ?? 'N/A') ?></td>
                                            <td>
                                                <button class="btn btn-secondary btn-small" onclick="viewPatient(<?= esc($patient['patient_id'] ?? 0) ?>)">View</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 2rem;">
                                            <i class="fas fa-user-injured" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;" aria-hidden="true"></i>
                                            <p>No patients found.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>

        <!-- Add Patient Popup Modal (styled like Add User) -->
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
            // Button handler to open modal
            function addPatient() {
                openAddPatientsModal();
            }
            // Close when clicking outside the dialog
            document.addEventListener('click', function(e){
                var m = document.getElementById('patientModal');
                if (!m) return;
                if (e.target === m) closeAddPatientsModal();
            });
            // Close on Escape
            document.addEventListener('keydown', function(e){
                if (e.key === 'Escape') closeAddPatientsModal();
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

            // Submit patient form to backend
            (function(){
                var form = document.getElementById('patientForm');
                if (!form) return;
                form.addEventListener('submit', async function(e){
                    e.preventDefault();
                    var btn = document.getElementById('savePatientBtn');
                    if (btn) { btn.disabled = true; btn.textContent = 'Saving...'; }

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

                    try {
                        var res = await fetch('/admin/patients', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify(payload),
                            credentials: 'same-origin'
                        });
                        var result = await res.json().catch(function(){ return {}; });
                        if (res.ok && result.status === 'success'){
                            alert('Patient saved successfully');
                            closeAddPatientsModal();
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
                        if (btn) { btn.disabled = false; btn.textContent = 'Save Patient'; }
                    }
                });
            })();
        </script>
        <script src="/js/logout.js"></script>
    </body>
</html>