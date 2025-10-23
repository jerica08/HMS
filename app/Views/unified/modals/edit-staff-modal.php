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
                    <div>
                        <label class="form-label" for="e_date_joined">Date Joined</label>
                        <input type="date" id="e_date_joined" name="date_joined" class="form-input">
                        <small id="e_err_date_joined" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="e_status">Status</label>
                        <select id="e_status" name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <small id="e_err_status" style="color:#dc2626"></small>
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
