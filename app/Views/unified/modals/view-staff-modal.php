<!-- View Staff Modal -->
<div id="viewStaffModal" class="hms-modal-overlay" aria-hidden="true">
    <div class="hms-modal" role="dialog" aria-modal="true" aria-labelledby="viewStaffTitle">
        <div class="hms-modal-header">
            <div class="hms-modal-title" id="viewStaffTitle">
                <i class="fas fa-id-badge" style="color:#4f46e5"></i>
                Staff Details
            </div>
            <button type="button" class="btn btn-secondary btn-small" onclick="closeViewStaffModal()" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="hms-modal-body">
            <div class="form-grid">
                <div>
                    <label class="form-label">Employee ID</label>
                    <input type="text" id="v_employee_id" class="form-input" readonly disabled>
                </div>
                <div>
                    <label class="form-label">First Name</label>
                    <input type="text" id="v_first_name" class="form-input" readonly disabled>
                </div>
                <div>
                    <label class="form-label">Last Name</label>
                    <input type="text" id="v_last_name" class="form-input" readonly disabled>
                </div>
                <div>
                    <label class="form-label">Gender</label>
                    <input type="text" id="v_gender" class="form-input" readonly disabled>
                </div>
                <div>
                    <label class="form-label">Date of Birth</label>
                    <input type="text" id="v_date_of_birth" class="form-input" readonly disabled>
                </div>
                <div>
                    <label class="form-label">Contact Number</label>
                    <input type="text" id="v_contact_no" class="form-input" readonly disabled>
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" id="v_email" class="form-input" readonly disabled>
                </div>
                <div>
                    <label class="form-label">Role</label>
                    <input type="text" id="v_role" class="form-input" readonly disabled>
                </div>
                <div>
                    <label class="form-label">Department</label>
                    <input type="text" id="v_department" class="form-input" readonly disabled>
                </div>
                <div>
                    <label class="form-label">Date Joined</label>
                    <input type="text" id="v_date_joined" class="form-input" readonly disabled>
                </div>
                <div>
                    <label class="form-label">Status</label>
                    <input type="text" id="v_status" class="form-input" readonly disabled>
                </div>
                <div class="full">
                    <label class="form-label">Address</label>
                    <textarea id="v_address" class="form-input" rows="2" readonly disabled></textarea>
                </div>
            </div>
        </div>
        <div class="hms-modal-actions">
            <button type="button" class="btn btn-secondary" onclick="closeViewStaffModal()">Close</button>
        </div>
    </div>
</div>
