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

                <div class="staff-section">
                    <div class="section-header">
                        <div class="section-icon" style="background:#10b981;">
                            <i class="fas fa-user-injured"></i>
                        </div>
                        <div>
                            <div class="section-title">Patient Directory</div>
                            <div style="color:#6b7280;font-size:0.9rem;">All registered patients</div>
                        </div>
                    </div>

                    <div style="overflow:auto;">
                        <table style="width:100%; border-collapse:collapse; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 4px rgba(0,0,0,0.05);">
                            <thead>
                                <tr style="background:#f8fafc; color:#374151;">
                                    <th style="text-align:left; padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb; font-weight:600;">Name</th>
                                    <th style="text-align:left; padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb; font-weight:600;">Age</th>
                                    <th style="text-align:left; padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb; font-weight:600;">Gender</th>
                                    <th style="text-align:left; padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb; font-weight:600;">Phone</th>
                                    <th style="text-align:left; padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb; font-weight:600;">Email</th>
                                    <th style="text-align:left; padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb; font-weight:600;">Patient Type</th>
                                    <th style="text-align:left; padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb; font-weight:600;">Status</th>
                                    <th style="text-align:left; padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb; font-weight:600;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($patients) && is_array($patients)): ?>
                                    <?php foreach ($patients as $patient): ?>
                                        <tr style="border-bottom:1px solid #f3f4f6;">
                                            <td style="padding:0.75rem 1rem;">
                                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                                    <div class="patient-avatar" aria-label="Patient initials" title="Patient initials">
                                                        <?= strtoupper(substr($patient['first_name'] ?? 'P', 0, 1) . substr($patient['last_name'] ?? 'P', 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <div style="font-weight: 500; color: #1f2937;">
                                                            <?= esc(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? '')) ?>
                                                        </div>
                                                        <div style="font-size: 0.8rem; color: #6b7280;">
                                                            ID: <?= esc($patient['patient_id'] ?? 'N/A') ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td style="padding:0.75rem 1rem; color:#374151;">
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
                                            <td style="padding:0.75rem 1rem; color:#374151;">
                                                <span style="text-transform: capitalize; color: <?= ($patient['gender'] ?? '') === 'male' ? '#3b82f6' : (($patient['gender'] ?? '') === 'female' ? '#ec4899' : '#6b7280') ?>;">
                                                    <?= esc($patient['gender'] ?? 'N/A') ?>
                                                </span>
                                            </td>
                                            <td style="padding:0.75rem 1rem; color:#374151;"><?= esc($patient['phone'] ?? 'N/A') ?></td>
                                            <td style="padding:0.75rem 1rem; color:#374151;"><?= esc($patient['email'] ?? 'N/A') ?></td>
                                            <td style="padding:0.75rem 1rem;">
                                                <?php 
                                                    $type = $patient['patient_type'] ?? 'N/A';
                                                    $typeClass = '';
                                                    switch(strtolower($type)) {
                                                        case 'outpatient': $typeClass = 'background:#dcfce7; color:#166534;'; break;
                                                        case 'inpatient': $typeClass = 'background:#dbeafe; color:#1e40af;'; break;
                                                        case 'emergency': $typeClass = 'background:#fecaca; color:#991b1b;'; break;
                                                        default: $typeClass = 'background:#f3f4f6; color:#6b7280;';
                                                    }
                                                ?>
                                                <span style="padding:0.25rem 0.75rem; border-radius:15px; font-size:0.8rem; font-weight:500; <?= $typeClass ?>">
                                                    <?= esc(ucfirst($type)) ?>
                                                </span>
                                            </td>
                                            <td style="padding:0.75rem 1rem;">
                                                <?php 
                                                    $status = $patient['status'] ?? 'N/A';
                                                    $statusClass = strtolower($status) === 'active' ? 'background:#dcfce7; color:#166534;' : 'background:#f3f4f6; color:#6b7280;';
                                                ?>
                                                <span style="padding:0.25rem 0.75rem; border-radius:15px; font-size:0.8rem; font-weight:500; <?= $statusClass ?>">
                                                    <?= esc(ucfirst($status)) ?>
                                                </span>
                                            </td>
                                            <td style="padding:0.75rem 1rem;">
                                                <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                                                    <button class="btn btn-secondary btn-small" onclick="viewPatient(<?= esc($patient['patient_id'] ?? 0) ?>)" style="background:#6b7280; color:#fff; border:none; padding:0.3rem 0.8rem; border-radius:4px; font-size:0.8rem; cursor:pointer;">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                    <button class="btn btn-primary btn-small" onclick="editPatient(<?= esc($patient['patient_id'] ?? 0) ?>)" style="background:#2563eb; color:#fff; border:none; padding:0.3rem 0.8rem; border-radius:4px; font-size:0.8rem; cursor:pointer;">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" style="text-align: center; padding: 2rem; color:#6b7280;">
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
        
        <!-- View Patient Modal -->
        <div id="viewPatientModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); z-index:9999; align-items:center; justify-content:center;">
            <div style="background:#fff; padding:1.25rem 1.5rem; border-radius:8px; max-width:780px; width:96%; margin:auto; position:relative; max-height:92vh; overflow:auto; box-sizing:border-box;">
                <div class="hms-modal-header">
                    <div class="hms-modal-title">
                        <i class="fas fa-user" style="color:#4f46e5"></i>
                        <h2 style="margin:0; font-size:1.25rem;">Patient Details</h2>
                    </div>
                </div>
                <div class="hms-modal-body">
                    <div class="form-grid">
                        <div>
                            <label class="form-label">Patient ID</label>
                            <div id="vp_id">-</div>
                        </div>
                        <div>
                            <label class="form-label">Name</label>
                            <div id="vp_name">-</div>
                        </div>
                        <div>
                            <label class="form-label">Gender</label>
                            <div id="vp_gender">-</div>
                        </div>
                        <div>
                            <label class="form-label">Date of Birth</label>
                            <div id="vp_dob">-</div>
                        </div>
                        <div>
                            <label class="form-label">Age</label>
                            <div id="vp_age">-</div>
                        </div>
                        <div>
                            <label class="form-label">Phone</label>
                            <div id="vp_phone">-</div>
                        </div>
                        <div>
                            <label class="form-label">Email</label>
                            <div id="vp_email">-</div>
                        </div>
                        <div class="full">
                            <label class="form-label">Address</label>
                            <div id="vp_address">-</div>
                        </div>
                        <div>
                            <label class="form-label">Department</label>
                            <div id="vp_department">-</div>
                        </div>
                        <div>
                            <label class="form-label">Room</label>
                            <div id="vp_room">-</div>
                        </div>
                        <div>
                            <label class="form-label">Patient Type</label>
                            <div id="vp_type">-</div>
                        </div>
                        <div>
                            <label class="form-label">Status</label>
                            <div id="vp_status">-</div>
                        </div>
                        <div class="full">
                            <label class="form-label">Emergency Contact</label>
                            <div id="vp_emergency">-</div>
                        </div>
                        <div class="full">
                            <label class="form-label">Notes</label>
                            <div id="vp_notes">-</div>
                        </div>
                    </div>
                </div>
                <div class="hms-modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeViewPatientModal()">Close</button>
                </div>
                <button aria-label="Close" onclick="closeViewPatientModal()" style="position:absolute; top:10px; right:10px; background:transparent; border:none; font-size:1.25rem; color:#6b7280; cursor:pointer;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Edit Patient Modal -->
        <div id="editPatientModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); z-index:9999; align-items:center; justify-content:center;">
            <div style="background:#fff; padding:1.25rem 1.5rem; border-radius:8px; max-width:840px; width:96%; margin:auto; position:relative; max-height:92vh; overflow:auto; box-sizing:border-box;">
                <div class="hms-modal-header">
                    <div class="hms-modal-title">
                        <i class="fas fa-user-edit" style="color:#4f46e5"></i>
                        <h2 style="margin:0; font-size:1.25rem;">Edit Patient</h2>
                    </div>
                </div>
                <form id="editPatientForm">
                    <input type="hidden" id="ep_patient_id" name="patient_id">
                    <div class="form-grid" style="margin-top:0.5rem;">
                        <div>
                            <label class="form-label" for="ep_first_name">First Name</label>
                            <input type="text" id="ep_first_name" name="first_name" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label" for="ep_middle_name">Middle Name</label>
                            <input type="text" id="ep_middle_name" name="middle_name" class="form-input">
                        </div>
                        <div>
                            <label class="form-label" for="ep_last_name">Last Name</label>
                            <input type="text" id="ep_last_name" name="last_name" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label" for="ep_date_of_birth">Date of Birth</label>
                            <input type="date" id="ep_date_of_birth" name="date_of_birth" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label" for="ep_gender">Gender</label>
                            <select id="ep_gender" name="gender" class="form-select" required>
                                <option value="">Select...</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="ep_civil_status">Civil Status</label>
                            <select id="ep_civil_status" name="civil_status" class="form-select" required>
                                <option value="">Select...</option>
                                <option value="single">Single</option>
                                <option value="married">Married</option>
                                <option value="widowed">Widowed</option>
                                <option value="separated">Separated</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="ep_phone">Phone</label>
                            <input type="tel" id="ep_phone" name="phone" class="form-input">
                        </div>
                        <div>
                            <label class="form-label" for="ep_email">Email</label>
                            <input type="email" id="ep_email" name="email" class="form-input">
                        </div>
                        <div class="full">
                            <label class="form-label" for="ep_address">Address</label>
                            <input type="text" id="ep_address" name="address" class="form-input">
                        </div>
                        <div>
                            <label class="form-label" for="ep_province">Province</label>
                            <input type="text" id="ep_province" name="province" class="form-input">
                        </div>
                        <div>
                            <label class="form-label" for="ep_city">City/Municipality</label>
                            <input type="text" id="ep_city" name="city" class="form-input">
                        </div>
                        <div>
                            <label class="form-label" for="ep_barangay">Barangay</label>
                            <input type="text" id="ep_barangay" name="barangay" class="form-input">
                        </div>
                        <div>
                            <label class="form-label" for="ep_zip_code">ZIP Code</label>
                            <input type="text" id="ep_zip_code" name="zip_code" class="form-input">
                        </div>
                        <div>
                            <label class="form-label" for="ep_insurance_provider">Insurance Provider</label>
                            <input type="text" id="ep_insurance_provider" name="insurance_provider" class="form-input">
                        </div>
                        <div>
                            <label class="form-label" for="ep_insurance_number">Insurance Number</label>
                            <input type="text" id="ep_insurance_number" name="insurance_number" class="form-input">
                        </div>
                        <div>
                            <label class="form-label" for="ep_department">Department</label>
                            <input type="text" id="ep_department" name="department" class="form-input">
                        </div>
                        <div>
                            <label class="form-label" for="ep_room">Room</label>
                            <input type="text" id="ep_room" name="room" class="form-input">
                        </div>
                        <div>
                            <label class="form-label" for="ep_patient_type">Patient Type</label>
                            <select id="ep_patient_type" name="patient_type" class="form-select">
                                <option value="">Select...</option>
                                <option value="outpatient">Outpatient</option>
                                <option value="inpatient">Inpatient</option>
                                <option value="emergency">Emergency</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="ep_status">Status</label>
                            <select id="ep_status" name="status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="ep_emergency_contact_name">Emergency Contact Name</label>
                            <input type="text" id="ep_emergency_contact_name" name="emergency_contact_name" class="form-input">
                        </div>
                        <div>
                            <label class="form-label" for="ep_emergency_contact_phone">Emergency Contact Phone</label>
                            <input type="tel" id="ep_emergency_contact_phone" name="emergency_contact_phone" class="form-input">
                        </div>
                        <div class="full">
                            <label class="form-label" for="ep_medical_notes">Medical Notes</label>
                            <textarea id="ep_medical_notes" name="medical_notes" rows="3" class="form-textarea"></textarea>
                        </div>
                    </div>
                    <div class="hms-modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeEditPatientModal()">Cancel</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save</button>
                    </div>
                </form>
                <button aria-label="Close" onclick="closeEditPatientModal()" style="position:absolute; top:10px; right:10px; background:transparent; border:none; font-size:1.25rem; color:#6b7280; cursor:pointer;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <script>
            // Build a quick lookup from PHP data
            (function(){
                try {
                    var list = <?php echo json_encode($patients ?? []); ?>;
                    var map = {};
                    if (Array.isArray(list)) { for (var i=0;i<list.length;i++){ var p=list[i]; if(p && p.patient_id){ map[p.patient_id]=p; } } }
                    window.patientsById = map;
                } catch(e){ window.patientsById = {}; }
            })();

            function openViewPatientModal(){ var m=document.getElementById('viewPatientModal'); if(m){ m.style.display='flex'; } }
            function closeViewPatientModal(){ var m=document.getElementById('viewPatientModal'); if(m){ m.style.display='none'; } }
            function viewPatient(id){
                var p = (window.patientsById||{})[id];
                if(!p){ alert('Patient not found'); return; }
                // derive age
                var age='';
                if(p.date_of_birth){
                    try { var d=new Date(p.date_of_birth); var t=new Date(); var a=t.getFullYear()-d.getFullYear(); var m=t.getMonth()-d.getMonth(); if(m<0 || (m===0 && t.getDate()<d.getDate())) a--; age = a>=0? a : ''; } catch(e){}
                }
                // helper to set input/select/textarea values
                var setVal = function(i,v){ var el=document.getElementById(i); if(!el) return; if(el.tagName==='SELECT' || el.tagName==='INPUT' || el.tagName==='TEXTAREA'){ el.value = v ?? ''; } else { el.textContent = (v==null||v==='')? '-' : v; } };
                setVal('vp_id', p.patient_id || '');
                setVal('vp_first_name', p.first_name || '');
                setVal('vp_last_name', p.last_name || '');
                setVal('vp_gender', (p.gender||'').toLowerCase());
                setVal('vp_dob', p.date_of_birth || '');
                setVal('vp_age', age || '');
                setVal('vp_phone', p.contact_no || p.phone || '');
                setVal('vp_email', p.email || '');
                setVal('vp_address', p.address || '');
                setVal('vp_department', p.department || '');
                setVal('vp_room', p.room || '');
                setVal('vp_type', (p.patient_type||'').toLowerCase());
                setVal('vp_status', p.status || '');
                setVal('vp_emergency_name', p.emergency_contact || '');
                setVal('vp_emergency_phone', p.emergency_phone || '');
                setVal('vp_notes', p.medical_notes || '');
                openViewPatientModal();
            }
            // Edit Patient modal
            function openEditPatientModal(){ var m=document.getElementById('editPatientModal'); if(m){ m.style.display='flex'; } }
            function closeEditPatientModal(){ var m=document.getElementById('editPatientModal'); if(m){ m.style.display='none'; } }
            function editPatient(id){
                var p = (window.patientsById||{})[id];
                if(!p){ alert('Patient not found'); return; }
                var set = function(i,val){ var el=document.getElementById(i); if(el){ if(el.tagName==='INPUT' || el.tagName==='TEXTAREA' || el.tagName==='SELECT'){ el.value = val ?? ''; } else { el.textContent = val ?? ''; } } };
                set('ep_patient_id', p.patient_id || '');
                set('ep_first_name', p.first_name || '');
                set('ep_middle_name', p.middle_name || '');
                set('ep_last_name', p.last_name || '');
                set('ep_date_of_birth', p.date_of_birth || '');
                set('ep_gender', (p.gender||'').toLowerCase());
                set('ep_civil_status', (p.civil_status||''));
                set('ep_phone', p.contact_no || p.phone || '');
                set('ep_email', p.email || '');
                set('ep_address', p.address || '');
                set('ep_province', p.province || '');
                set('ep_city', p.city || '');
                set('ep_barangay', p.barangay || '');
                set('ep_zip_code', p.zip_code || '');
                set('ep_insurance_provider', p.insurance_provider || '');
                set('ep_insurance_number', p.insurance_number || '');
                set('ep_department', p.department || '');
                set('ep_room', p.room || '');
                set('ep_patient_type', p.patient_type || '');
                set('ep_status', (p.status||'').toLowerCase());
                set('ep_emergency_contact_name', p.emergency_contact || '');
                set('ep_emergency_contact_phone', p.emergency_phone || '');
                set('ep_medical_notes', p.medical_notes || '');
                openEditPatientModal();
            }
            (function(){
                var form = document.getElementById('editPatientForm');
                if (!form) return;
                form.addEventListener('submit', async function(e){
                    e.preventDefault();
                    var btn = form.querySelector('button[type="submit"]');
                    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving'; }
                    // Build payload mirroring backend expectations
                    var val = function(id){ var el=document.getElementById(id); return el? el.value : null; };
                    var payload = {
                        patient_id: val('ep_patient_id'),
                        first_name: val('ep_first_name'),
                        middle_name: val('ep_middle_name'),
                        last_name: val('ep_last_name'),
                        date_of_birth: val('ep_date_of_birth'),
                        gender: val('ep_gender'),
                        civil_status: val('ep_civil_status'),
                        phone: val('ep_phone'),
                        email: val('ep_email'),
                        address: val('ep_address'),
                        province: val('ep_province'),
                        city: val('ep_city'),
                        barangay: val('ep_barangay'),
                        zip_code: val('ep_zip_code'),
                        insurance_provider: val('ep_insurance_provider'),
                        insurance_number: val('ep_insurance_number'),
                        department: val('ep_department'),
                        room: val('ep_room'),
                        patient_type: val('ep_patient_type'),
                        status: val('ep_status'),
                        emergency_contact_name: val('ep_emergency_contact_name'),
                        emergency_contact_phone: val('ep_emergency_contact_phone'),
                        medical_notes: val('ep_medical_notes')
                    };
                    try {
                        var res = await fetch('<?= base_url('admin/patients/update') ?>', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify(payload),
                            credentials: 'same-origin'
                        });
                        var result = await res.json().catch(function(){ return {}; });
                        if (res.ok && result.status === 'success'){
                            alert('Patient updated successfully');
                            closeEditPatientModal();
                            window.location.reload();
                        } else {
                            var msg = result.message || 'Failed to update patient';
                            if (result.errors){ msg += '\n\n' + Object.values(result.errors).join('\n'); }
                            alert(msg);
                        }
                    } catch (err){
                        console.error('Error updating patient', err);
                        alert('Network error. Please try again.');
                    } finally {
                        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Save'; }
                    }
                });
                // Close on overlay click
                document.addEventListener('click', function(e){ var m=document.getElementById('editPatientModal'); if(m && e.target===m){ closeEditPatientModal(); }});
                // Close on ESC
                document.addEventListener('keydown', function(e){ if(e.key==='Escape'){ closeEditPatientModal(); }});
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