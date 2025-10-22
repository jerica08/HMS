<!-- Edit Staff Modal -->
<div id="editStaffModal" class="modal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h3>
                <i class="fas fa-user-edit"></i>
                Edit Staff Member
            </h3>
            <button class="modal-close" id="closeEditStaffModal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editStaffForm">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>" id="editCsrfToken">
                <input type="hidden" name="staff_id" id="editStaffId">
                
                <!-- Basic Information -->
                <div class="form-section">
                    <h4><i class="fas fa-user"></i> Basic Information</h4>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="edit_employee_id">Employee ID *</label>
                            <input type="text" id="edit_employee_id" name="employee_id" class="form-control" required readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="edit_designation">Role/Designation *</label>
                            <select id="edit_designation" name="designation" class="form-control" required>
                                <option value="">Select Role</option>
                                <option value="doctor">Doctor</option>
                                <option value="nurse">Nurse</option>
                                <option value="receptionist">Receptionist</option>
                                <option value="pharmacist">Pharmacist</option>
                                <option value="laboratorist">Laboratorist</option>
                                <option value="accountant">Accountant</option>
                                <option value="it_staff">IT Staff</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="edit_first_name">First Name *</label>
                            <input type="text" id="edit_first_name" name="first_name" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="edit_last_name">Last Name</label>
                            <input type="text" id="edit_last_name" name="last_name" class="form-control">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="edit_gender">Gender</label>
                            <select id="edit_gender" name="gender" class="form-control">
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="edit_dob">Date of Birth</label>
                            <input type="date" id="edit_dob" name="dob" class="form-control">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="edit_date_joined">Date Joined</label>
                            <input type="date" id="edit_date_joined" name="date_joined" class="form-control">
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="form-section">
                    <h4><i class="fas fa-address-book"></i> Contact Information</h4>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="edit_contact_no">Phone Number</label>
                            <input type="tel" id="edit_contact_no" name="contact_no" class="form-control">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="edit_email">Email Address</label>
                            <input type="email" id="edit_email" name="email" class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_address">Address</label>
                        <textarea id="edit_address" name="address" class="form-control" rows="2"></textarea>
                    </div>
                </div>

                <!-- Department Information -->
                <div class="form-section">
                    <h4><i class="fas fa-building"></i> Department Information</h4>
                    <div class="form-group">
                        <label for="edit_department">Department</label>
                        <select id="edit_department" name="department" class="form-control">
                            <option value="">Select Department</option>
                            <option value="Emergency">Emergency</option>
                            <option value="Cardiology">Cardiology</option>
                            <option value="Neurology">Neurology</option>
                            <option value="Pediatrics">Pediatrics</option>
                            <option value="Orthopedics">Orthopedics</option>
                            <option value="General">General Medicine</option>
                            <option value="Surgery">Surgery</option>
                            <option value="Radiology">Radiology</option>
                            <option value="Laboratory">Laboratory</option>
                            <option value="Pharmacy">Pharmacy</option>
                            <option value="Administration">Administration</option>
                            <option value="IT">Information Technology</option>
                        </select>
                    </div>
                </div>

                <!-- Status -->
                <div class="form-section">
                    <h4><i class="fas fa-toggle-on"></i> Employment Status</h4>
                    <div class="form-group">
                        <label for="edit_status">Status</label>
                        <select id="edit_status" name="status" class="form-control">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                            <option value="On Leave">On Leave</option>
                            <option value="Suspended">Suspended</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="cancelEditStaffBtn">Cancel</button>
            <button type="button" class="btn btn-warning" id="updateStaffBtn">
                <i class="fas fa-save"></i> Update Staff Member
            </button>
        </div>
    </div>
</div>