<!-- View User Modal -->
<div id="viewUserModal" class="hms-modal-overlay" aria-hidden="true">
    <div class="hms-modal" role="dialog" aria-modal="true" aria-labelledby="viewUserTitle">
        <div class="hms-modal-header">
            <div class="hms-modal-title" id="viewUserTitle">
                <i class="fas fa-id-badge" style="color:#4f46e5"></i>
                User Details
            </div>
            <button type="button" class="btn btn-secondary btn-small" onclick="closeViewUserModal()" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="hms-modal-body">
            <div class="form-grid">
                <div>
                    <label class="form-label">Username</label>
                    <input type="text" id="v_username" class="form-input" readonly disabled>
                </div>
                <div>
                    <label class="form-label">Full Name</label>
                    <input type="text" id="v_full_name" class="form-input" readonly disabled>
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
                    <label class="form-label">Status</label>
                    <input type="text" id="v_status" class="form-input" readonly disabled>
                </div>
                <div>
                    <label class="form-label">Employee ID</label>
                    <input type="text" id="v_employee_id" class="form-input" readonly disabled>
                </div>
                <div>
                    <label class="form-label">Created At</label>
                    <input type="text" id="v_created_at" class="form-input" readonly disabled>
                </div>
                <div>
                    <label class="form-label">Last Login</label>
                    <input type="text" id="v_last_login" class="form-input" readonly disabled>
                </div>
            </div>
        </div>
        <div class="hms-modal-actions">
            <button type="button" class="btn btn-secondary" onclick="closeViewUserModal()">Close</button>
        </div>
    </div>
</div>
