<!-- Add Staff Modal -->
<div id="addStaffModal" class="hms-modal-overlay" aria-hidden="true">
    <div class="hms-modal" role="dialog" aria-modal="true" aria-labelledby="addStaffTitle">
        <div class="hms-modal-header">
            <div class="hms-modal-title" id="addStaffTitle">
                <i class="fas fa-user-plus" style="color:#4f46e5"></i>
                Add New Staff
            </div>
            <button type="button" class="btn btn-secondary btn-small" onclick="closeAddStaffModal()" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="addStaffForm">
            <div class="hms-modal-body">
                <div class="form-grid">
                    <div>
                        <label class="form-label" for="designation">Role/Designation*</label>
                        <p class="form-hint">Select a role first to load the correct requirements.</p>
                        <select id="designation" name="designation" class="form-select" required>
                            <option value="">Select role</option>
                            <option value="admin">Admin</option>
                            <option value="doctor">Doctor</option>
                            <option value="nurse">Nurse</option>
                            <option value="pharmacist">Pharmacist</option>
                            <option value="receptionist">Receptionist</option>
                            <option value="laboratorist">Laboratorist</option>
                            <option value="it_staff">IT Staff</option>
                            <option value="accountant">Accountant</option>
                        </select>
                        <small id="err_designation" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="employee_id">Employee ID</label>
                        <input type="text" id="employee_id" name="employee_id" class="form-input" placeholder="e.g., DOC003">
                        <small id="err_employee_id" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="first_name">First Name*</label>
                        <input type="text" id="first_name" name="first_name" class="form-input" required>
                        <small id="err_first_name" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="last_name">Last Name*</label>
                        <input type="text" id="last_name" name="last_name" class="form-input" required>
                        <small id="err_last_name" style="color:#dc2626"></small>
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
                        <label class="form-label" for="date_of_birth">Date of Birth</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" class="form-input">
                        <small id="err_date_of_birth" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="contact_no">Contact Number</label>
                        <input type="text" id="contact_no" name="contact_no" class="form-input">
                        <small id="err_contact_no" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-input">
                        <small id="err_email" style="color:#dc2626"></small>
                    </div>
                    <div class="full">
                        <label class="form-label" for="address">Address</label>
                        <textarea id="address" name="address" class="form-input" rows="2"></textarea>
                        <small id="err_address" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="department">Department</label>
                        <select id="department" name="department" class="form-select">
                            <option value="">Select department</option>
                            <?php if (!empty($departments)): ?>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= esc($dept['name']) ?>" data-id="<?= esc($dept['department_id']) ?>"><?= esc($dept['name']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <input type="hidden" id="department_id" name="department_id" value="">
                        <small id="err_department" style="color:#dc2626"></small>
                    </div>
                    <!-- Doctor-specific fields -->
                    <div id="doctorFields" class="full" style="display:none; grid-column: 1 / -1;">
                        <div class="form-grid">
                            <div>
                                <label class="form-label" for="doctor_specialization">Doctor Specialization*</label>
                                <select id="doctor_specialization" name="doctor_specialization" class="form-select">
                                    <option value="">Select specialization</option>
                                    <option value="Pediatrics">Pediatrics</option>
                                    <option value="Cardiology">Cardiology</option>
                                    <option value="Internal Medicine">Internal Medicine</option>
                                    <option value="General Practice">General Practice</option>
                                    <option value="Obstetrics and Gynecology">Obstetrics and Gynecology</option>
                                    <option value="Surgery">Surgery</option>
                                    <option value="Orthopedics">Orthopedics</option>
                                    <option value="Neurology">Neurology</option>
                                    <option value="Psychiatry">Psychiatry</option>
                                    <option value="Dermatology">Dermatology</option>
                                    <option value="Ophthalmology">Ophthalmology</option>
                                    <option value="Otolaryngology">Otolaryngology</option>
                                    <option value="Emergency Medicine">Emergency Medicine</option>
                                    <option value="Radiology">Radiology</option>
                                    <option value="Anesthesiology">Anesthesiology</option>
                                </select>
                                <small id="err_doctor_specialization" style="color:#dc2626"></small>
                            </div>
                            <div>
                                <label class="form-label" for="doctor_license_no">License No.</label>
                                <input type="text" id="doctor_license_no" name="doctor_license_no" class="form-input" placeholder="e.g., PRC-1234567">
                                <small id="err_doctor_license_no" style="color:#dc2626"></small>
                            </div>
                        </div>
                    </div>
                    <!-- Accountant-specific fields -->
                    <div id="accountantFields" class="full" style="display:none; grid-column: 1 / -1;">
                        <div class="form-grid">
                            <div>
                                <label class="form-label" for="accountant_license_no">Accountant License No.*</label>
                                <input type="text" id="accountant_license_no" name="accountant_license_no" class="form-input" placeholder="e.g., ACC-1234567">
                                <small id="err_accountant_license_no" style="color:#dc2626"></small>
                            </div>
                        </div>
                    </div>
                    <!-- Pharmacist-specific fields -->
                    <div id="pharmacistFields" class="full" style="display:none; grid-column: 1 / -1;">
                        <div class="form-grid">
                            <div>
                                <label class="form-label" for="pharmacist_license_no">Pharmacist License No.*</label>
                                <input type="text" id="pharmacist_license_no" name="pharmacist_license_no" class="form-input" placeholder="e.g., PHA-1357924">
                                <small id="err_pharmacist_license_no" style="color:#dc2626"></small>
                            </div>
                            <div>
                                <label class="form-label" for="pharmacist_specialization">Pharmacist Specialization</label>
                                <input type="text" id="pharmacist_specialization" name="pharmacist_specialization" class="form-input" placeholder="e.g., Clinical Pharmacy">
                                <small id="err_pharmacist_specialization" style="color:#dc2626"></small>
                            </div>
                        </div>
                    </div>
                    <!-- Laboratorist-specific fields -->
                    <div id="laboratoristFields" class="full" style="display:none; grid-column: 1 / -1;">
                        <div class="form-grid">
                            <div>
                                <label class="form-label" for="laboratorist_license_no">Laboratorist License No.*</label>
                                <input type="text" id="laboratorist_license_no" name="laboratorist_license_no" class="form-input" placeholder="e.g., LAB-9876543">
                                <small id="err_laboratorist_license_no" style="color:#dc2626"></small>
                            </div>
                            <div>
                                <label class="form-label" for="laboratorist_specialization">Laboratorist Specialization</label>
                                <input type="text" id="laboratorist_specialization" name="laboratorist_specialization" class="form-input" placeholder="e.g., Hematology">
                                <small id="err_laboratorist_specialization" style="color:#dc2626"></small>
                            </div>
                            <div>
                                <label class="form-label" for="lab_room_no">Lab Room No.</label>
                                <input type="text" id="lab_room_no" name="lab_room_no" class="form-input" placeholder="e.g., R-201">
                                <small id="err_lab_room_no" style="color:#dc2626"></small>
                            </div>
                        </div>
                    </div>
                    <!-- Nurse-specific fields -->
                    <div id="nurseFields" class="full" style="display:none; grid-column: 1 / -1;">
                        <div class="form-grid">
                            <div>
                                <label class="form-label" for="nurse_license_no">Nurse License No.*</label>
                                <input type="text" id="nurse_license_no" name="nurse_license_no" class="form-input" placeholder="e.g., PRC-7654321">
                                <small id="err_nurse_license_no" style="color:#dc2626"></small>
                            </div>
                            <div>
                                <label class="form-label" for="nurse_specialization">Nurse Specialization</label>
                                <input type="text" id="nurse_specialization" name="nurse_specialization" class="form-input" placeholder="e.g., ICU">
                                <small id="err_nurse_specialization" style="color:#dc2626"></small>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="form-label" for="date_joined">Date Joined</label>
                        <input type="date" id="date_joined" name="date_joined" class="form-input">
                        <small id="err_date_joined" style="color:#dc2626"></small>
                    </div>
                </div>
            </div>
            <div class="hms-modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeAddStaffModal()">Cancel</button>
                <button type="submit" id="saveStaffBtn" class="btn btn-success"><i class="fas fa-save"></i> Save Staff</button>
            </div>
        </form>
    </div>
</div>
