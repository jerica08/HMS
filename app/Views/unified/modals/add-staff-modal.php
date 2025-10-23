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
                            <option value="Administration">Administration</option>
                            <option value="Emergency">Emergency</option>
                            <option value="Cardiology">Cardiology</option>
                            <option value="Intensive Care Unit">Intensive Care Unit</option>
                            <option value="Outpatient">Outpatient</option>
                            <option value="Pharmacy">Pharmacy</option>
                            <option value="Laboratory">Laboratory</option>
                            <option value="Radiology">Radiology</option>
                            <option value="Pediatrics">Pediatrics</option>
                            <option value="Surgery">Surgery</option>
                        </select>
                        <small id="err_department" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="designation">Role/Designation*</label>
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
                        <label class="form-label" for="date_joined">Date Joined</label>
                        <input type="date" id="date_joined" name="date_joined" class="form-input">
                        <small id="err_date_joined" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="status">Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <small id="err_status" style="color:#dc2626"></small>
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
