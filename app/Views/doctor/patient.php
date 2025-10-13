<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Patient Management - HMS Doctor</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        .patient-table { background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 4px rgba(0,0,0,0.1); }
        .table-header { background:#f8fafc; padding:1rem; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; }
        .table { width:100%; border-collapse:separate; border-spacing:0; }
        .table thead th { background:#f8fafc; color:#374151; font-weight:600; text-align:left; padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb; }
        .table tbody td { padding:0.75rem 1rem; border-bottom:1px solid #f3f4f6; }
        .patient-avatar { width:40px; height:40px; border-radius:50%; background:#4299e1; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:bold; font-size:0.9rem; }
        .btn-small { padding:0.3rem 0.8rem; font-size:0.8rem; }
    </style>
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
             <div class="patient-view">         
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
                                    <th>Assigned Doctor</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="doctorPatientsBody">
                                <tr><td colspan="7" style="text-align:center; color:#6b7280; padding:1rem;">Loading patients...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
        </main>
    </div>
     <!-- Add Patient Modal -->
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

        <!-- Assign Doctor Modal -->
        <div id="assignDoctorModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); z-index:9999; align-items:center; justify-content:center;">
            <div style="background:#fff; padding:2rem; border-radius:8px; max-width:500px; width:90%; margin:auto; position:relative; box-sizing:border-box;">
                <div class="hms-modal-header">
                    <div class="hms-modal-title">
                        <i class="fas fa-user-md" style="color:#4f46e5"></i>
                        <h2 style="margin:0; font-size:1.25rem;">Assign Doctor</h2>
                    </div>
                </div>
                <form id="assignDoctorForm">
                    <input type="hidden" id="assignPatientId" name="patient_id">
                    <div style="margin:1rem 0;">
                        <label for="doctorSelect">Select Doctor*</label>
                        <select id="doctorSelect" name="doctor_id" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px; margin-top:0.5rem;">
                            <option value="">Loading doctors...</option>
                        </select>
                        <small id="err_doctor" style="color:#dc2626"></small>
                    </div>
                    <div style="display:flex; gap:1rem; justify-content:flex-end; margin-top:1.5rem;">
                        <button type="button" onclick="closeAssignDoctorModal()" style="background:#6b7280; color:#fff; border:none; padding:0.75rem 1.5rem; border-radius:4px; cursor:pointer;">Cancel</button>
                        <button type="submit" id="assignDoctorBtn" style="background:#2563eb; color:#fff; border:none; padding:0.75rem 1.5rem; border-radius:4px; cursor:pointer;">Assign Doctor</button>
                    </div>
                </form>
                <button aria-label="Close" onclick="closeAssignDoctorModal()" style="position:absolute; top:10px; right:10px; background:transparent; border:none; font-size:1.25rem; color:#6b7280; cursor:pointer;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- View Patient Modal -->
        <div id="viewPatientModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); z-index:9999; align-items:center; justify-content:center;">
            <div style="background:#fff; padding:2rem; border-radius:8px; max-width:800px; width:95%; margin:auto; position:relative; max-height:90vh; overflow:auto; box-sizing:border-box;">
                <div class="hms-modal-header">
                    <div class="hms-modal-title">
                        <i class="fas fa-user" style="color:#4f46e5"></i>
                        <h2 style="margin:0; font-size:1.25rem;">Patient Details</h2>
                    </div>
                </div>
                <div id="viewPatientContent" style="margin:1rem 0;">
                    <div style="text-align:center; padding:2rem; color:#6b7280;">Loading patient details...</div>
                </div>
                <div style="display:flex; gap:1rem; justify-content:flex-end; margin-top:1.5rem;">
                    <button type="button" onclick="closeViewPatientModal()" style="background:#6b7280; color:#fff; border:none; padding:0.75rem 1.5rem; border-radius:4px; cursor:pointer;">Close</button>
                </div>
                <button aria-label="Close" onclick="closeViewPatientModal()" style="position:absolute; top:10px; right:10px; background:transparent; border:none; font-size:1.25rem; color:#6b7280; cursor:pointer;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Edit Patient Modal -->
        <div id="editPatientModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); z-index:9999; align-items:center; justify-content:center;">
            <div style="background:#fff; padding:2rem; border-radius:8px; max-width:960px; width:98%; margin:auto; position:relative; max-height:90vh; overflow:auto; box-sizing:border-box; -webkit-overflow-scrolling:touch;">
                <div class="hms-modal-header">
                    <div class="hms-modal-title">
                        <i class="fas fa-user-edit" style="color:#4f46e5"></i>
                        <h2 style="margin:0; font-size:1.25rem;">Edit Patient</h2>
                    </div>
                </div>
                <form id="editPatientForm">
                    <input type="hidden" id="editPatientId" name="patient_id">
                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:1rem; padding-bottom:5rem;">
                        <div>
                            <label for="edit_first_name">First Name*</label>
                            <input type="text" id="edit_first_name" name="first_name" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                            <small id="edit_err_first_name" style="color:#dc2626"></small>
                        </div>
                        <div>
                            <label for="edit_middle_name">Middle Name</label>
                            <input type="text" id="edit_middle_name" name="middle_name" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                        </div>
                        <div>
                            <label for="edit_last_name">Last Name*</label>
                            <input type="text" id="edit_last_name" name="last_name" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                            <small id="edit_err_last_name" style="color:#dc2626"></small>
                        </div>
                        <div>
                            <label for="edit_date_of_birth">Date of Birth*</label>
                            <input type="date" id="edit_date_of_birth" name="date_of_birth" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                            <small id="edit_err_date_of_birth" style="color:#dc2626"></small>
                        </div>
                        <div>
                            <label for="edit_age">Age</label>
                            <input type="number" id="edit_age" name="age" readonly style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px; background:#f9fafb;">
                        </div>
                        <div>
                            <label for="edit_gender">Gender*</label>
                            <select id="edit_gender" name="gender" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                                <option value="">Select...</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            <small id="edit_err_gender" style="color:#dc2626"></small>
                        </div>
                        <div>
                            <label for="edit_civil_status">Civil Status</label>
                            <select id="edit_civil_status" name="civil_status" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                                <option value="">Select...</option>
                                <option value="single">Single</option>
                                <option value="married">Married</option>
                                <option value="widowed">Widowed</option>
                                <option value="separated">Separated</option>
                            </select>
                            <small id="edit_err_civil_status" style="color:#dc2626"></small>
                        </div>
                        <div>
                            <label for="edit_phone">Phone</label>
                            <input type="tel" id="edit_phone" name="phone" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                            <small id="edit_err_phone" style="color:#dc2626"></small>
                        </div>
                        <div>
                            <label for="edit_email">Email</label>
                            <input type="email" id="edit_email" name="email" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label for="edit_address">Address</label>
                            <input type="text" id="edit_address" name="address" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                            <small id="edit_err_address" style="color:#dc2626"></small>
                        </div>
                        <div>
                            <label for="edit_province">Province</label>
                            <input type="text" id="edit_province" name="province" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                            <small id="edit_err_province" style="color:#dc2626"></small>
                        </div>
                        <div>
                            <label for="edit_city">City/Municipality</label>
                            <input type="text" id="edit_city" name="city" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                            <small id="edit_err_city" style="color:#dc2626"></small>
                        </div>
                        <div>
                            <label for="edit_barangay">Barangay</label>
                            <input type="text" id="edit_barangay" name="barangay" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                            <small id="edit_err_barangay" style="color:#dc2626"></small>
                        </div>
                        <div>
                            <label for="edit_zip_code">ZIP Code</label>
                            <input type="text" id="edit_zip_code" name="zip_code" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                            <small id="edit_err_zip_code" style="color:#dc2626"></small>
                        </div>
                        <div>
                            <label for="edit_insurance_provider">Insurance Provider</label>
                            <input type="text" id="edit_insurance_provider" name="insurance_provider" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                        </div>
                        <div>
                            <label for="edit_insurance_number">Insurance Number</label>
                            <input type="text" id="edit_insurance_number" name="insurance_number" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                        </div>
                        <div>
                            <label for="edit_emergency_contact_name">Emergency Contact Name</label>
                            <input type="text" id="edit_emergency_contact_name" name="emergency_contact_name" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                            <small id="edit_err_emergency_contact_name" style="color:#dc2626"></small>
                        </div>
                        <div>
                            <label for="edit_emergency_contact_phone">Emergency Contact Phone</label>
                            <input type="tel" id="edit_emergency_contact_phone" name="emergency_contact_phone" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                            <small id="edit_err_emergency_contact_phone" style="color:#dc2626"></small>
                        </div>
                        <div>
                            <label for="edit_patient_type">Patient Type</label>
                            <select id="edit_patient_type" name="patient_type" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                                <option value="">Select...</option>
                                <option value="outpatient">Outpatient</option>
                                <option value="inpatient">Inpatient</option>
                                <option value="emergency">Emergency</option>
                            </select>
                        </div>
                        <div>
                            <label for="edit_status">Status</label>
                            <select id="edit_status" name="status" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label for="edit_medical_notes">Medical Notes</label>
                            <textarea id="edit_medical_notes" name="medical_notes" rows="3" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;"></textarea>
                        </div>
                    </div>
                    <div style="display:flex; gap:1rem; justify-content:flex-end; margin-top:1.5rem; position:sticky; bottom:0; background:#fff; padding-top:1rem; border-top:1px solid #e5e7eb;">
                        <button type="button" onclick="closeEditPatientModal()" style="background:#6b7280; color:#fff; border:none; padding:0.75rem 1.5rem; border-radius:4px; cursor:pointer;">Cancel</button>
                        <button type="submit" id="updatePatientBtn" style="background:#2563eb; color:#fff; border:none; padding:0.75rem 1.5rem; border-radius:4px; cursor:pointer;">Update Patient</button>
                    </div>
                </form>
                <button aria-label="Close" onclick="closeEditPatientModal()" style="position:absolute; top:10px; right:10px; background:transparent; border:none; font-size:1.25rem; color:#6b7280; cursor:pointer;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <script>
            // Global utility functions
            function escapeHtml(str){
                return (str||'').toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
            }
            
            // Fetch and render patients in real-time (on load)
            (function(){
                const tbody = document.getElementById('doctorPatientsBody');
                const URL = '<?= base_url('doctor/patients/api') ?>';
                function initials(first, last){
                    const a = (first||'').trim();
                    const b = (last||'').trim();
                    return ((a[0]||'P') + (b[0]||'P')).toUpperCase();
                }
                function calcAge(dob){
                    try { if(!dob) return 'N/A'; const d=new Date(dob); if(isNaN(d)) return 'N/A'; const t=new Date(); let a=t.getFullYear()-d.getFullYear(); const m=t.getMonth()-d.getMonth(); if(m<0 || (m===0 && t.getDate()<d.getDate())) a--; return a>=0? a : 'N/A'; } catch(_) { return 'N/A'; }
                }
                async function loadPatients(){
                    if (!tbody) return;
                    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; color:#6b7280; padding:1rem;">Loading patients...</td></tr>';
                    try {
                        const res = await fetch(URL, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }});
                        if (!res.ok) { throw new Error('HTTP '+res.status); }
                        const json = await res.json();
                        const list = Array.isArray(json?.data) ? json.data : (Array.isArray(json)? json : []);
                        if (!list.length){
                            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; color:#6b7280; padding:1rem;">No patients found</td></tr>';
                            return;
                        }
                        tbody.innerHTML = list.map(p => {
                            const name = escapeHtml((p.first_name||'') + ' ' + (p.last_name||''));
                            const email = escapeHtml(p.email||'');
                            const age = calcAge(p.date_of_birth);
                            const id = escapeHtml(p.patient_id);
                            const assigned = escapeHtml(p.assigned_doctor_name || '');
                            const status = escapeHtml(p.status||'N/A');
                            const init = initials(p.first_name, p.last_name);
                            return `
                                <tr>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:0.75rem;">
                                            <div class="patient-avatar" aria-label="Patient initials" title="Patient initials">${init}</div>
                                            <div>
                                                <div style="font-weight:500;">${name}</div>
                                                <div style="font-size:0.8rem; color:#6b7280;">${email}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>${id}</td>
                                    <td>${age}</td>
                                    <td>${assigned}</td>
                                    <td>${status}</td>
                                    <td>
                                        <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                                            <button class="btn btn-secondary btn-small" data-action="view" data-id="${id}">View</button>
                                            <button class="btn btn-primary btn-small" data-action="edit" data-id="${id}">Edit</button>
                                            <button class="btn btn-success btn-small" data-action="assign" data-id="${id}">Assign Doctor</button>
                                        </div>
                                    </td>
                                </tr>`;
                        }).join('');
                    } catch (e) {
                        console.error('Failed to load patients', e);
                        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; color:#ef4444; padding:1rem;">Failed to load patients</td></tr>';
                    }
                }
                document.addEventListener('DOMContentLoaded', loadPatients);
                // Optional: delegate view/edit buttons if needed later
                if (tbody && !tbody.__bound) {
                    tbody.__bound = true;
                    tbody.addEventListener('click', function(e){
                        const btn = e.target.closest('button[data-action]');
                        if (!btn) return;
                        const id = btn.getAttribute('data-id');
                        if (btn.getAttribute('data-action') === 'view') {
                            viewPatient(id);
                        } else if (btn.getAttribute('data-action') === 'edit') {
                            editPatient(id);
                        } else if (btn.getAttribute('data-action') === 'assign') {
                            openAssignDoctorModal(id);
                        }
                    });
                }
            })();
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
                        var res = await fetch('<?= base_url('doctor/patients') ?>', {
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
            // Assign Doctor Modal Functions
            let doctorsCache = null;
            
            async function loadDoctors() {
                if (doctorsCache) return doctorsCache;
                
                try {
                    const response = await fetch('<?= base_url('doctor/doctors/api') ?>', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    if (!response.ok) throw new Error('Failed to fetch doctors');
                    
                    const result = await response.json();
                    if (result.success && result.data) {
                        doctorsCache = result.data;
                        return doctorsCache;
                    }
                    throw new Error('Invalid response format');
                } catch (error) {
                    console.error('Error loading doctors:', error);
                    return [];
                }
            }
            
            async function openAssignDoctorModal(patientId) {
                const modal = document.getElementById('assignDoctorModal');
                const patientIdInput = document.getElementById('assignPatientId');
                const doctorSelect = document.getElementById('doctorSelect');
                
                if (!modal || !patientIdInput || !doctorSelect) return;
                
                // Set patient ID
                patientIdInput.value = patientId;
                
                // Load doctors
                doctorSelect.innerHTML = '<option value="">Loading doctors...</option>';
                const doctors = await loadDoctors();
                
                // Populate doctor dropdown
                doctorSelect.innerHTML = '<option value="">Select a doctor...</option>';
                doctors.forEach(doctor => {
                    const option = document.createElement('option');
                    option.value = doctor.staff_id;
                    option.textContent = `${doctor.full_name} - ${doctor.department || 'N/A'}`;
                    doctorSelect.appendChild(option);
                });
                
                // Show modal
                modal.style.display = 'flex';
            }
            
            function closeAssignDoctorModal() {
                const modal = document.getElementById('assignDoctorModal');
                if (modal) {
                    modal.style.display = 'none';
                    // Reset form
                    document.getElementById('assignDoctorForm').reset();
                    document.getElementById('err_doctor').textContent = '';
                }
            }
            
            // Handle assign doctor form submission
            (function() {
                const form = document.getElementById('assignDoctorForm');
                if (!form) return;
                
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const btn = document.getElementById('assignDoctorBtn');
                    const errorEl = document.getElementById('err_doctor');
                    
                    if (btn) {
                        btn.disabled = true;
                        btn.textContent = 'Assigning...';
                    }
                    
                    errorEl.textContent = '';
                    
                    const formData = new FormData(form);
                    const payload = {
                        patient_id: formData.get('patient_id'),
                        doctor_id: formData.get('doctor_id')
                    };
                    
                    try {
                        const response = await fetch('<?= base_url('doctor/assign-doctor') ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(payload)
                        });
                        
                        const result = await response.json();
                        
                        if (response.ok && result.success) {
                            alert('Doctor assigned successfully!');
                            closeAssignDoctorModal();
                            // Reload patients to show updated assignment
                            loadPatients();
                        } else {
                            errorEl.textContent = result.message || 'Failed to assign doctor';
                        }
                    } catch (error) {
                        console.error('Error assigning doctor:', error);
                        errorEl.textContent = 'Network error. Please try again.';
                    } finally {
                        if (btn) {
                            btn.disabled = false;
                            btn.textContent = 'Assign Doctor';
                        }
                    }
                });
            })();
            
            // Close modal when clicking outside
            document.addEventListener('click', function(e) {
                const modal = document.getElementById('assignDoctorModal');
                if (modal && e.target === modal) {
                    closeAssignDoctorModal();
                }
            });
            
            // Close modal on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeAssignDoctorModal();
                    closeViewPatientModal();
                    closeEditPatientModal();
                }
            });

            // View Patient Functions
            async function viewPatient(patientId) {
                const modal = document.getElementById('viewPatientModal');
                const content = document.getElementById('viewPatientContent');
                
                if (!modal || !content) return;
                
                // Show modal and loading state
                modal.style.display = 'flex';
                content.innerHTML = '<div style="text-align:center; padding:2rem; color:#6b7280;">Loading patient details...</div>';
                
                try {
                    const response = await fetch(`<?= base_url('doctor/patient/') ?>${patientId}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    if (!response.ok) throw new Error('Failed to fetch patient');
                    
                    const result = await response.json();
                    if (result.status === 'success' && result.patient) {
                        displayPatientDetails(result.patient);
                    } else {
                        throw new Error(result.message || 'Patient not found');
                    }
                } catch (error) {
                    console.error('Error loading patient:', error);
                    content.innerHTML = `<div style="text-align:center; padding:2rem; color:#ef4444;">Error: ${error.message}</div>`;
                }
            }
            
            function displayPatientDetails(patient) {
                const content = document.getElementById('viewPatientContent');
                const age = patient.age || calculateAge(patient.date_of_birth);
                
                content.innerHTML = `
                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap:1.5rem;">
                        <div style="background:#f8fafc; padding:1rem; border-radius:6px;">
                            <h4 style="margin:0 0 0.5rem 0; color:#374151; font-size:0.9rem; text-transform:uppercase; letter-spacing:0.05em;">Personal Information</h4>
                            <div style="space-y:0.5rem;">
                                <p><strong>Name:</strong> ${escapeHtml((patient.first_name || '') + ' ' + (patient.middle_name || '') + ' ' + (patient.last_name || ''))}</p>
                                <p><strong>Date of Birth:</strong> ${patient.date_of_birth || 'N/A'}</p>
                                <p><strong>Age:</strong> ${age}</p>
                                <p><strong>Gender:</strong> ${patient.gender || 'N/A'}</p>
                                <p><strong>Civil Status:</strong> ${patient.civil_status || 'N/A'}</p>
                            </div>
                        </div>
                        
                        <div style="background:#f8fafc; padding:1rem; border-radius:6px;">
                            <h4 style="margin:0 0 0.5rem 0; color:#374151; font-size:0.9rem; text-transform:uppercase; letter-spacing:0.05em;">Contact Information</h4>
                            <div style="space-y:0.5rem;">
                                <p><strong>Phone:</strong> ${patient.contact_no || 'N/A'}</p>
                                <p><strong>Email:</strong> ${patient.email || 'N/A'}</p>
                                <p><strong>Address:</strong> ${patient.address || 'N/A'}</p>
                                <p><strong>City:</strong> ${patient.city || 'N/A'}</p>
                                <p><strong>Province:</strong> ${patient.province || 'N/A'}</p>
                                <p><strong>ZIP Code:</strong> ${patient.zip_code || 'N/A'}</p>
                            </div>
                        </div>
                        
                        <div style="background:#f8fafc; padding:1rem; border-radius:6px;">
                            <h4 style="margin:0 0 0.5rem 0; color:#374151; font-size:0.9rem; text-transform:uppercase; letter-spacing:0.05em;">Medical Information</h4>
                            <div style="space-y:0.5rem;">
                                <p><strong>Patient Type:</strong> ${patient.patient_type || 'N/A'}</p>
                                <p><strong>Status:</strong> <span style="color:${patient.status === 'active' ? '#059669' : '#dc2626'}">${patient.status || 'N/A'}</span></p>
                                <p><strong>Blood Group:</strong> ${patient.blood_group || 'N/A'}</p>
                                <p><strong>Insurance Provider:</strong> ${patient.insurance_provider || 'N/A'}</p>
                                <p><strong>Insurance Number:</strong> ${patient.insurance_number || 'N/A'}</p>
                            </div>
                        </div>
                        
                        <div style="background:#f8fafc; padding:1rem; border-radius:6px;">
                            <h4 style="margin:0 0 0.5rem 0; color:#374151; font-size:0.9rem; text-transform:uppercase; letter-spacing:0.05em;">Emergency Contact</h4>
                            <div style="space-y:0.5rem;">
                                <p><strong>Name:</strong> ${patient.emergency_contact || 'N/A'}</p>
                                <p><strong>Phone:</strong> ${patient.emergency_phone || 'N/A'}</p>
                            </div>
                        </div>
                        
                        ${patient.medical_notes ? `
                        <div style="grid-column: 1 / -1; background:#f8fafc; padding:1rem; border-radius:6px;">
                            <h4 style="margin:0 0 0.5rem 0; color:#374151; font-size:0.9rem; text-transform:uppercase; letter-spacing:0.05em;">Medical Notes</h4>
                            <p>${escapeHtml(patient.medical_notes)}</p>
                        </div>
                        ` : ''}
                    </div>
                `;
            }
            
            function closeViewPatientModal() {
                const modal = document.getElementById('viewPatientModal');
                if (modal) {
                    modal.style.display = 'none';
                }
            }
            
            // Edit Patient Functions
            async function editPatient(patientId) {
                const modal = document.getElementById('editPatientModal');
                const form = document.getElementById('editPatientForm');
                
                if (!modal || !form) return;
                
                // Show modal
                modal.style.display = 'flex';
                
                try {
                    const response = await fetch(`<?= base_url('doctor/patient/') ?>${patientId}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    if (!response.ok) throw new Error('Failed to fetch patient');
                    
                    const result = await response.json();
                    if (result.status === 'success' && result.patient) {
                        populateEditForm(result.patient);
                    } else {
                        throw new Error(result.message || 'Patient not found');
                    }
                } catch (error) {
                    console.error('Error loading patient for edit:', error);
                    alert('Error loading patient data: ' + error.message);
                    closeEditPatientModal();
                }
            }
            
            function populateEditForm(patient) {
                // Clear all error messages
                document.querySelectorAll('[id^="edit_err_"]').forEach(el => el.textContent = '');
                
                // Populate form fields
                document.getElementById('editPatientId').value = patient.patient_id || '';
                document.getElementById('edit_first_name').value = patient.first_name || '';
                document.getElementById('edit_middle_name').value = patient.middle_name || '';
                document.getElementById('edit_last_name').value = patient.last_name || '';
                document.getElementById('edit_date_of_birth').value = patient.date_of_birth || '';
                document.getElementById('edit_age').value = patient.age || calculateAge(patient.date_of_birth);
                document.getElementById('edit_gender').value = (patient.gender || '').toLowerCase();
                document.getElementById('edit_civil_status').value = patient.civil_status || '';
                document.getElementById('edit_phone').value = patient.contact_no || '';
                document.getElementById('edit_email').value = patient.email || '';
                document.getElementById('edit_address').value = patient.address || '';
                document.getElementById('edit_province').value = patient.province || '';
                document.getElementById('edit_city').value = patient.city || '';
                document.getElementById('edit_barangay').value = patient.barangay || '';
                document.getElementById('edit_zip_code').value = patient.zip_code || '';
                document.getElementById('edit_insurance_provider').value = patient.insurance_provider || '';
                document.getElementById('edit_insurance_number').value = patient.insurance_number || '';
                document.getElementById('edit_emergency_contact_name').value = patient.emergency_contact || '';
                document.getElementById('edit_emergency_contact_phone').value = patient.emergency_phone || '';
                document.getElementById('edit_patient_type').value = patient.patient_type || '';
                document.getElementById('edit_status').value = patient.status || 'active';
                document.getElementById('edit_medical_notes').value = patient.medical_notes || '';
            }
            
            function closeEditPatientModal() {
                const modal = document.getElementById('editPatientModal');
                if (modal) {
                    modal.style.display = 'none';
                    // Reset form
                    document.getElementById('editPatientForm').reset();
                    // Clear error messages
                    document.querySelectorAll('[id^="edit_err_"]').forEach(el => el.textContent = '');
                }
            }
            
            // Auto-calculate age for edit form
            (function() {
                const dob = document.getElementById('edit_date_of_birth');
                const age = document.getElementById('edit_age');
                
                if (dob && age) {
                    dob.addEventListener('change', function() {
                        age.value = calculateAge(this.value) || '';
                    });
                }
            })();
            
            // Helper function to calculate age
            function calculateAge(dateOfBirth) {
                if (!dateOfBirth) return '';
                const dob = new Date(dateOfBirth);
                if (isNaN(dob.getTime())) return '';
                const today = new Date();
                let age = today.getFullYear() - dob.getFullYear();
                const monthDiff = today.getMonth() - dob.getMonth();
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
                    age--;
                }
                return age >= 0 ? age : '';
            }
            
            // Handle edit patient form submission
            (function() {
                const form = document.getElementById('editPatientForm');
                if (!form) return;
                
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const btn = document.getElementById('updatePatientBtn');
                    const patientId = document.getElementById('editPatientId').value;
                    
                    if (btn) {
                        btn.disabled = true;
                        btn.textContent = 'Updating...';
                    }
                    
                    // Clear previous errors
                    document.querySelectorAll('[id^="edit_err_"]').forEach(el => el.textContent = '');
                    
                    const formData = new FormData(form);
                    const payload = {
                        first_name: formData.get('first_name'),
                        middle_name: formData.get('middle_name'),
                        last_name: formData.get('last_name'),
                        date_of_birth: formData.get('date_of_birth'),
                        age: formData.get('age'),
                        gender: formData.get('gender'),
                        civil_status: formData.get('civil_status'),
                        phone: formData.get('phone'),
                        email: formData.get('email'),
                        address: formData.get('address'),
                        province: formData.get('province'),
                        city: formData.get('city'),
                        barangay: formData.get('barangay'),
                        zip_code: formData.get('zip_code'),
                        insurance_provider: formData.get('insurance_provider'),
                        insurance_number: formData.get('insurance_number'),
                        emergency_contact_name: formData.get('emergency_contact_name'),
                        emergency_contact_phone: formData.get('emergency_contact_phone'),
                        patient_type: formData.get('patient_type'),
                        status: formData.get('status'),
                        medical_notes: formData.get('medical_notes')
                    };
                    
                    try {
                        const response = await fetch(`<?= base_url('doctor/patient/') ?>${patientId}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(payload)
                        });
                        
                        const result = await response.json();
                        
                        if (response.ok && result.status === 'success') {
                            alert('Patient updated successfully!');
                            closeEditPatientModal();
                            // Reload patients to show updated data
                            loadPatients();
                        } else {
                            if (result.errors) {
                                // Display field-specific errors
                                Object.keys(result.errors).forEach(field => {
                                    const errorEl = document.getElementById(`edit_err_${field}`);
                                    if (errorEl) {
                                        errorEl.textContent = result.errors[field];
                                    }
                                });
                            } else {
                                alert(result.message || 'Failed to update patient');
                            }
                        }
                    } catch (error) {
                        console.error('Error updating patient:', error);
                        alert('Network error. Please try again.');
                    } finally {
                        if (btn) {
                            btn.disabled = false;
                            btn.textContent = 'Update Patient';
                        }
                    }
                });
            })();
            
            // Close modals when clicking outside
            document.addEventListener('click', function(e) {
                const viewModal = document.getElementById('viewPatientModal');
                const editModal = document.getElementById('editPatientModal');
                
                if (viewModal && e.target === viewModal) {
                    closeViewPatientModal();
                }
                if (editModal && e.target === editModal) {
                    closeEditPatientModal();
                }
            });
        </script>
</body>
</html>
