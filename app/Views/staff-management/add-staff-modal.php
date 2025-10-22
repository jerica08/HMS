<!-- Add Staff Modal -->
<div id="addStaffModal" class="modal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h3>
                <i class="fas fa-user-plus"></i>
                Add New Staff Member
            </h3>
            <button class="modal-close" id="closeAddStaffModal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="addStaffForm">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>" id="csrfToken">
                
                <!-- Basic Information -->
                <div class="form-section">
                    <h4><i class="fas fa-user"></i> Basic Information</h4>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="employee_id">Employee ID *</label>
                            <input type="text" id="employee_id" name="employee_id" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="designation">Role/Designation *</label>
                            <select id="designation" name="designation" class="form-control" required>
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
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
                            <label for="first_name">First Name *</label>
                            <input type="text" id="first_name" name="first_name" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="form-control">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="gender">Gender</label>
                            <select id="gender" name="gender" class="form-control">
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="dob">Date of Birth</label>
                            <input type="date" id="dob" name="dob" class="form-control">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="date_joined">Date Joined</label>
                            <input type="date" id="date_joined" name="date_joined" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="form-section">
                    <h4><i class="fas fa-address-book"></i> Contact Information</h4>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="contact_no">Phone Number</label>
                            <input type="tel" id="contact_no" name="contact_no" class="form-control">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" class="form-control" rows="2"></textarea>
                    </div>
                </div>

                <!-- Department Information -->
                <div class="form-section">
                    <h4><i class="fas fa-building"></i> Department Information</h4>
                    <div class="form-group">
                        <label for="department">Department</label>
                        <select id="department" name="department" class="form-control">
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

            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="cancelAddStaffBtn">Cancel</button>
            <button type="button" class="btn btn-success" id="saveStaffBtn">
                <i class="fas fa-save"></i> Add Staff Member
            </button>
        </div>
    </div>
</div>