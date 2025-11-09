<!-- Add Patient Modal -->
<div id="addPatientModal" class="hms-modal-overlay" hidden>
    <div class="hms-modal" role="dialog" aria-modal="true" aria-labelledby="addPatientTitle">
        <div class="hms-modal-header">
            <div class="hms-modal-title" id="addPatientTitle">
                <i class="fas fa-user-plus" style="color:#4f46e5"></i>
                Add New Patient
            </div>
            <button type="button" class="btn btn-secondary btn-small" onclick="closeAddPatientModal()" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="addPatientForm">
            <div class="hms-modal-body">
                <div class="form-grid">
                    <div>
                        <label class="form-label" for="first_name">First Name*</label>
                        <input type="text" id="first_name" name="first_name" class="form-input" required>
                        <small id="err_first_name" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="middle_name">Middle Name</label>
                        <input type="text" id="middle_name" name="middle_name" class="form-input">
                    </div>
                    <div>
                        <label class="form-label" for="last_name">Last Name*</label>
                        <input type="text" id="last_name" name="last_name" class="form-input" required>
                        <small id="err_last_name" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="date_of_birth">Date of Birth*</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" class="form-input" required>
                        <small id="err_date_of_birth" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="gender">Gender*</label>
                        <select id="gender" name="gender" class="form-select" required>
                            <option value="">Select...</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                        <small id="err_gender" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="civil_status">Civil Status*</label>
                        <select id="civil_status" name="civil_status" class="form-select" required>
                            <option value="">Select...</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Divorced">Divorced</option>
                            <option value="Widowed">Widowed</option>
                        </select>
                        <small id="err_civil_status" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="phone">Phone Number*</label>
                        <input type="text" id="phone" name="phone" class="form-input" required>
                        <small id="err_phone" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-input">
                        <small id="err_email" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="address">Address*</label>
                        <input type="text" id="address" name="address" class="form-input" required>
                        <small id="err_address" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="province">Province*</label>
                        <input type="text" id="province" name="province" class="form-input" required>
                        <small id="err_province" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="city">City*</label>
                        <input type="text" id="city" name="city" class="form-input" required>
                        <small id="err_city" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="barangay">Barangay*</label>
                        <input type="text" id="barangay" name="barangay" class="form-input" required>
                        <small id="err_barangay" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="zip_code">ZIP Code*</label>
                        <input type="text" id="zip_code" name="zip_code" class="form-input" required>
                        <small id="err_zip_code" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="patient_type">Patient Type</label>
                        <select id="patient_type" name="patient_type" class="form-select">
                            <option value="Outpatient">Outpatient</option>
                            <option value="Inpatient">Inpatient</option>
                            <option value="Emergency">Emergency</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="blood_group">Blood Group</label>
                        <select id="blood_group" name="blood_group" class="form-select">
                            <option value="">Select...</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                    </div>
                    <?php if (in_array($userRole ?? '', ['admin', 'receptionist', 'it_staff'])): ?>
                    <div class="form-group">
                            <label class="form-label" for="assigned_doctor">Assign Doctor</label>
                            <select id="assigned_doctor" name="assigned_doctor" class="form-select">
                                <?php if (!empty($availableDoctors)): ?>
                                    <option value="">Select Doctor (Optional)</option>
                                    <?php foreach ($availableDoctors as $d): ?>
                                        <option value="<?= esc($d['staff_id'] ?? $d['id']) ?>">
                                            <?= esc(trim(($d['first_name'] ?? '') . ' ' . ($d['last_name'] ?? ''))) ?><?= !empty($d['specialization']) ? ' - ' . esc($d['specialization']) : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="">No doctors available</option>
                                <?php endif; ?>
                            </select>
                            <small id="err_assigned_doctor" style="color:#dc2626"></small>
                        </div>
                    <?php endif; ?>
                    <div>
                        <label class="form-label" for="insurance_provider">Insurance Provider</label>
                        <input type="text" id="insurance_provider" name="insurance_provider" class="form-input">
                    </div>
                    <div>
                        <label class="form-label" for="insurance_number">Insurance Number</label>
                        <input type="text" id="insurance_number" name="insurance_number" class="form-input">
                    </div>
                    <div>
                        <label class="form-label" for="emergency_contact_name">Emergency Contact Name*</label>
                        <input type="text" id="emergency_contact_name" name="emergency_contact_name" class="form-input" required>
                        <small id="err_emergency_contact_name" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="emergency_contact_phone">Emergency Contact Phone*</label>
                        <input type="text" id="emergency_contact_phone" name="emergency_contact_phone" class="form-input" required>
                        <small id="err_emergency_contact_phone" style="color:#dc2626"></small>
                    </div>
                    <div class="full">
                        <label class="form-label" for="medical_notes">Medical Notes</label>
                        <textarea id="medical_notes" name="medical_notes" class="form-input" rows="3" style="resize:vertical;"></textarea>
                    </div>
                </div>
            </div>
            <div class="hms-modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeAddPatientModal()">Cancel</button>
                <button type="submit" id="savePatientBtn" class="btn btn-success"><i class="fas fa-save"></i> Save Patient</button>
            </div>
        </form>
    </div>
</div>
