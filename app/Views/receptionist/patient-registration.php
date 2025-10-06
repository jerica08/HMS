<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width", initial-scale="1.0">
        <title>Patients Registration</title>
        <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>
    <body class="receptionist-theme">
        <?php include APPPATH . 'Views/template/header.php'; ?>

        <div class="main-container">
            <!-- Sidebar -->
            <?php include APPPATH . 'Views/receptionist/components/sidebar.php'; ?>

            <main class="content">
                <div class="content-header">
                    <h2 class="page-title"><i class="fas fa-user-plus"></i> Patient Registration</h2>
                    <p class="page-subtitle">Create a new patient profile and capture demographics, contact, and insurance information.</p>
                </div>

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Please correct the following errors:</strong>
                        <ul>
                            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <section class="card">
                    <div class="card-header">
                        <h3 class="card-title">Patient Details</h3>
                    </div>
                    <div class="card-body">
                        <form id="patientRegistrationForm" action="<?= base_url('receptionist/patient-registration/store') ?>" method="post" novalidate>
                            <?= csrf_field() ?>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="firstName">First Name</label>
                                    <input type="text" id="firstName" name="first_name" placeholder="e.g., Juan" value="<?= old('first_name') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="lastName">Last Name</label>
                                    <input type="text" id="lastName" name="last_name" placeholder="e.g., Dela Cruz" value="<?= old('last_name') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="middleName">Middle Name</label>
                                    <input type="text" id="middleName" name="middle_name" placeholder="Optional" value="<?= old('middle_name') ?>">
                                </div>
                                <div class="form-group">
                                    <label for="dob">Date of Birth</label>
                                    <input type="date" id="dob" name="date_of_birth" value="<?= old('date_of_birth') ?>" required>
                                    <small id="calculated-age" class="age-display"></small>
                                </div>
                                <div class="form-group">
                                    <label for="gender">Gender</label>
                                    <select id="gender" name="gender" required>
                                        <option value="">Select gender</option>
                                        <option value="Male" <?= old('gender') == 'Male' ? 'selected' : '' ?>>Male</option>
                                        <option value="Female" <?= old('gender') == 'Female' ? 'selected' : '' ?>>Female</option>
                                        <option value="Other" <?= old('gender') == 'Other' ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="civilStatus">Civil Status</label>
                                    <select id="civilStatus" name="civil_status">
                                        <option value="">Select status</option>
                                        <option value="Single" <?= old('civil_status') == 'Single' ? 'selected' : '' ?>>Single</option>
                                        <option value="Married" <?= old('civil_status') == 'Married' ? 'selected' : '' ?>>Married</option>
                                        <option value="Divorced" <?= old('civil_status') == 'Divorced' ? 'selected' : '' ?>>Divorced</option>
                                        <option value="Widowed" <?= old('civil_status') == 'Widowed' ? 'selected' : '' ?>>Widowed</option>
                                        <option value="Separated" <?= old('civil_status') == 'Separated' ? 'selected' : '' ?>>Separated</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Mobile Number</label>
                                    <input type="tel" id="phone" name="phone" placeholder="e.g., 09XXXXXXXXX" pattern="^[0-9\-\+\s\(\)]{7,}$" value="<?= old('phone') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" placeholder="name@example.com" value="<?= old('email') ?>">
                                </div>
                                <div class="form-group form-group-full">
                                    <label for="address">Address</label>
                                    <input type="text" id="address" name="address" placeholder="House No., Street, Barangay, City/Municipality, Province" value="<?= old('address') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="province">Province</label>
                                    <input type="text" id="province" name="province" value="<?= old('province') ?>">
                                </div>
                                <div class="form-group">
                                    <label for="city">City/Municipality</label>
                                    <input type="text" id="city" name="city" value="<?= old('city') ?>">
                                </div>
                                <div class="form-group">
                                    <label for="barangay">Barangay</label>
                                    <input type="text" id="barangay" name="barangay" value="<?= old('barangay') ?>">
                                </div>
                                <div class="form-group">
                                    <label for="zip">ZIP Code</label>
                                    <input type="text" id="zip" name="zip_code" pattern="^[0-9]{4,5}$" placeholder="e.g., 1000" value="<?= old('zip_code') ?>">
                                </div>
                            </div>

                            <hr class="section-divider">

                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="insuranceProvider">Insurance Provider</label>
                                    <input type="text" id="insuranceProvider" name="insurance_provider" placeholder="e.g., PhilHealth" value="<?= old('insurance_provider') ?>">
                                </div>
                                <div class="form-group">
                                    <label for="insuranceNumber">Insurance Number</label>
                                    <input type="text" id="insuranceNumber" name="insurance_number" placeholder="Policy/Member ID" value="<?= old('insurance_number') ?>">
                                </div>
                                <div class="form-group">
                                    <label for="emergencyName">Emergency Contact Name</label>
                                    <input type="text" id="emergencyName" name="emergency_contact_name" placeholder="Full name" value="<?= old('emergency_contact_name') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="emergencyPhone">Emergency Contact Number</label>
                                    <input type="tel" id="emergencyPhone" name="emergency_contact_phone" placeholder="e.g., 09XXXXXXXXX" pattern="^[0-9\-\+\s\(\)]{7,}$" value="<?= old('emergency_contact_phone') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="patientType">Patient Type</label>
                                    <select id="patientType" name="patient_type">
                                        <option value="Outpatient" <?= old('patient_type') == 'Outpatient' ? 'selected' : '' ?>>Outpatient</option>
                                        <option value="Inpatient" <?= old('patient_type') == 'Inpatient' ? 'selected' : '' ?>>Inpatient</option>
                                        <option value="Emergency" <?= old('patient_type') == 'Emergency' ? 'selected' : '' ?>>Emergency</option>
                                    </select>
                                </div>
                                <div class="form-group form-group-full">
                                    <label for="notes">Clinical Notes</label>
                                    <textarea id="notes" name="medical_notes" rows="3" placeholder="Optional notes (allergies, conditions, etc.)"><?= old('medical_notes') ?></textarea>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Patient</button>
                                <button type="reset" class="btn btn-secondary"><i class="fas fa-undo"></i> Reset</button>
                            </div>
                        </form>
                    </div>
                </section>
            </main>
        </div>

        <style>
        .content { padding: 24px; width: 100%; overflow: auto; }
        .content-header { margin-bottom: 16px; }
        .page-title { margin: 0 0 6px 0; font-weight: 700; }
        .page-subtitle { margin: 0; opacity: 0.8; }
        .card { background: var(--card-bg, #fff); border-radius: 12px; box-shadow: var(--shadow-md, 0 2px 12px rgba(0,0,0,0.06)); overflow: hidden; }
        .card-header { padding: 16px 20px; border-bottom: 1px solid rgba(0,0,0,0.06); display: flex; align-items: center; justify-content: space-between; }
        .card-title { margin: 0; font-size: 1.05rem; font-weight: 600; }
        .card-body { padding: 20px; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group-full { grid-column: 1 / -1; }
        .form-group label { font-weight: 600; margin-bottom: 6px; }
        .form-group input, .form-group select, .form-group textarea { padding: 10px 12px; border: 1px solid rgba(0,0,0,0.15); border-radius: 8px; background: var(--input-bg, #fff); }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: var(--primary, #2563eb); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15); }
        .section-divider { border: none; border-top: 1px solid rgba(0,0,0,0.06); margin: 16px 0; }
        .form-actions { display: flex; gap: 10px; justify-content: flex-end; padding-top: 8px; }
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; }
        .btn-primary { background: var(--primary, #2563eb); color: #fff; }
        .btn-secondary { background: var(--muted, #e5e7eb); color: #111827; }
        .btn:hover { filter: brightness(0.98); }
        @media (max-width: 900px) { .form-grid { grid-template-columns: 1fr; } }
        .main-container { display: flex; }
        .sidebar { flex: 0 0 280px; }
        .content { flex: 1 1 auto; }
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; display: flex; align-items: flex-start; gap: 8px; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .alert ul { margin: 8px 0 0 0; padding-left: 20px; }
        .alert li { margin-bottom: 4px; }
        .form-group input.error, .form-group select.error, .form-group textarea.error { border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.15); }
        .field-error { color: #dc2626; font-size: 0.875rem; margin-top: 0.25rem; display: flex; align-items: center; gap: 0.25rem; }
        .field-error::before { content: "⚠"; font-size: 0.75rem; }
        .age-display { color: #6b7280; font-size: 0.875rem; margin-top: 0.25rem; font-style: italic; }
        .notification { position: fixed; top: 20px; right: 20px; padding: 12px 16px; border-radius: 8px; display: flex; align-items: center; gap: 8px; font-weight: 500; z-index: 1000; transform: translateX(100%); transition: transform 0.3s ease-in-out; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); }
        .notification.show { transform: translateX(0); }
        .notification-success { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .notification-error { background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .btn:disabled { opacity: 0.6; cursor: not-allowed; }
        .fa-spinner { animation: spin 1s linear infinite; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        </style>

        <script src="<?= base_url('js/logout.js') ?>"></script>
        <script src="<?= base_url('js/patient-registration.js') ?>"></script>
    </body>
</html>
