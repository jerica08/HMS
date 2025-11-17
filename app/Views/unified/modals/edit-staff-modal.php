<!-- Edit Staff Modal -->
<div id="editStaffModal" class="hms-modal-overlay" aria-hidden="true">
    <div class="hms-modal" role="dialog" aria-modal="true" aria-labelledby="editStaffTitle">
        <div class="hms-modal-header">
            <div class="hms-modal-title" id="editStaffTitle">
                <i class="fas fa-user-edit" style="color:#4f46e5"></i>
                Edit Staff
            </div>
            <button type="button" class="btn btn-secondary btn-small" onclick="closeEditStaffModal()" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="editStaffForm">
            <input type="hidden" id="e_staff_id" name="staff_id">
            <div class="hms-modal-body">
                <div class="form-grid">
                    <div>
                        <label class="form-label" for="e_employee_id">Employee ID</label>
                        <input type="text" id="e_employee_id" name="employee_id" class="form-input">
                        <small id="e_err_employee_id" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="e_first_name">First Name*</label>
                        <input type="text" id="e_first_name" name="first_name" class="form-input" required>
                        <small id="e_err_first_name" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="e_last_name">Last Name*</label>
                        <input type="text" id="e_last_name" name="last_name" class="form-input" required>
                        <small id="e_err_last_name" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="e_gender">Gender</label>
                        <select id="e_gender" name="gender" class="form-select">
                            <option value="">Select...</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                        <small id="e_err_gender" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="e_date_of_birth">Date of Birth</label>
                        <input type="date" id="e_date_of_birth" name="date_of_birth" class="form-input">
                        <small id="e_err_date_of_birth" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="e_contact_no">Contact Number</label>
                        <input type="text" id="e_contact_no" name="contact_no" class="form-input">
                        <small id="e_err_contact_no" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="e_email">Email</label>
                        <input type="email" id="e_email" name="email" class="form-input">
                        <small id="e_err_email" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="e_department">Department</label>
                        <select id="e_department" name="department" class="form-select">
                            <option value="">Select department</option>
                            <?php if (!empty($departments) && is_array($departments)): ?>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= esc($dept['name'] ?? '') ?>"><?= esc($dept['name'] ?? '') ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <?php foreach ([
                                    'Administration','Emergency','Cardiology','Intensive Care Unit','Outpatient','Pharmacy','Laboratory','Radiology','Pediatrics','Surgery'
                                ] as $name): ?>
                                    <option value="<?= esc($name) ?>"><?= esc($name) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <small id="e_err_department" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="e_designation">Role/Designation</label>
                        <select id="e_designation" name="designation" class="form-select">
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
                        <small id="e_err_designation" style="color:#dc2626"></small>
                    </div>
                    <!-- Doctor-specific fields -->
                    <div id="e_doctorFields" class="full" style="display:none; grid-column: 1 / -1;">
                        <div class="form-grid">
                            <div>
                                <label class="form-label" for="e_doctor_specialization">Doctor Specialization*</label>
                                <select id="e_doctor_specialization" name="doctor_specialization" class="form-select">
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
                                <small id="e_err_doctor_specialization" style="color:#dc2626"></small>
                            </div>
                            <div>
                                <label class="form-label" for="e_doctor_license_no">License No.</label>
                                <input type="text" id="e_doctor_license_no" name="doctor_license_no" class="form-input" placeholder="e.g., PRC-1234567">
                                <small id="e_err_doctor_license_no" style="color:#dc2626"></small>
                            </div>
                            <div>
                                <label class="form-label" for="e_doctor_consultation_fee">Consultation Fee</label>
                                <input type="number" step="0.01" id="e_doctor_consultation_fee" name="doctor_consultation_fee" class="form-input" placeholder="e.g., 500.00">
                                <small id="e_err_doctor_consultation_fee" style="color:#dc2626"></small>
                            </div>
                        </div>
                    </div>
                    <!-- Accountant-specific fields -->
                    <div id="e_accountantFields" class="full" style="display:none; grid-column: 1 / -1;">
                        <div class="form-grid">
                            <div>
                                <label class="form-label" for="e_accountant_license_no">Accountant License No.*</label>
                                <input type="text" id="e_accountant_license_no" name="accountant_license_no" class="form-input" placeholder="e.g., ACC-1234567">
                                <small id="e_err_accountant_license_no" style="color:#dc2626"></small>
                            </div>
                        </div>
                    </div>
                    <!-- Pharmacist-specific fields -->
                    <div id="e_pharmacistFields" class="full" style="display:none; grid-column: 1 / -1;">
                        <div class="form-grid">
                            <div>
                                <label class="form-label" for="e_pharmacist_license_no">Pharmacist License No.*</label>
                                <input type="text" id="e_pharmacist_license_no" name="pharmacist_license_no" class="form-input" placeholder="e.g., PHA-1357924">
                                <small id="e_err_pharmacist_license_no" style="color:#dc2626"></small>
                            </div>
                            <div>
                                <label class="form-label" for="e_pharmacist_specialization">Pharmacist Specialization</label>
                                <input type="text" id="e_pharmacist_specialization" name="pharmacist_specialization" class="form-input" placeholder="e.g., Clinical Pharmacy">
                                <small id="e_err_pharmacist_specialization" style="color:#dc2626"></small>
                            </div>
                        </div>
                    </div>
                    <!-- Laboratorist-specific fields -->
                    <div id="e_laboratoristFields" class="full" style="display:none; grid-column: 1 / -1;">
                        <div class="form-grid">
                            <div>
                                <label class="form-label" for="e_laboratorist_license_no">Laboratorist License No.*</label>
                                <input type="text" id="e_laboratorist_license_no" name="laboratorist_license_no" class="form-input" placeholder="e.g., LAB-9876543">
                                <small id="e_err_laboratorist_license_no" style="color:#dc2626"></small>
                            </div>
                            <div>
                                <label class="form-label" for="e_laboratorist_specialization">Laboratorist Specialization</label>
                                <input type="text" id="e_laboratorist_specialization" name="laboratorist_specialization" class="form-input" placeholder="e.g., Hematology">
                                <small id="e_err_laboratorist_specialization" style="color:#dc2626"></small>
                            </div>
                            <div>
                                <label class="form-label" for="e_lab_room_no">Lab Room No.</label>
                                <input type="text" id="e_lab_room_no" name="lab_room_no" class="form-input" placeholder="e.g., R-201">
                                <small id="e_err_lab_room_no" style="color:#dc2626"></small>
                            </div>
                        </div>
                    </div>
                    <!-- Nurse-specific fields -->
                    <div id="e_nurseFields" class="full" style="display:none; grid-column: 1 / -1;">
                        <div class="form-grid">
                            <div>
                                <label class="form-label" for="e_nurse_license_no">Nurse License No.*</label>
                                <input type="text" id="e_nurse_license_no" name="nurse_license_no" class="form-input" placeholder="e.g., PRC-7654321">
                                <small id="e_err_nurse_license_no" style="color:#dc2626"></small>
                            </div>
                            <div>
                                <label class="form-label" for="e_nurse_specialization">Nurse Specialization</label>
                                <input type="text" id="e_nurse_specialization" name="nurse_specialization" class="form-input" placeholder="e.g., ICU">
                                <small id="e_err_nurse_specialization" style="color:#dc2626"></small>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="form-label" for="e_date_joined">Date Joined</label>
                        <input type="date" id="e_date_joined" name="date_joined" class="form-input">
                        <small id="e_err_date_joined" style="color:#dc2626"></small>
                    </div>
                    <div class="full">
                        <label class="form-label" for="e_address">Address</label>
                        <textarea id="e_address" name="address" class="form-input" rows="2"></textarea>
                        <small id="e_err_address" style="color:#dc2626"></small>
                    </div>
                </div>
            </div>
            <div class="hms-modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeEditStaffModal()">Cancel</button>
                <button type="submit" id="updateStaffBtn" class="btn btn-success"><i class="fas fa-save"></i> Update Staff</button>
            </div>
        </form>
    </div>
</div>
