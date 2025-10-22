<!-- View Staff Modal -->
<div id="viewStaffModal" class="modal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h3>
                <i class="fas fa-user"></i>
                Staff Member Details
            </h3>
            <button class="modal-close" id="closeViewStaffModal">&times;</button>
        </div>
        <div class="modal-body" id="staffDetailsBody">
            <div class="loading-state">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading staff details...</p>
            </div>
        </div>
        <div class="modal-footer" id="staffModalFooter">
            <button type="button" class="btn btn-secondary" id="closeViewStaffBtn">
                <i class="fas fa-times"></i> Close
            </button>
            
            <!-- Role-based action buttons -->
            <?php if (($permissions['canEdit'] ?? false)): ?>
            <button type="button" class="btn btn-warning" id="editStaffFromModal" style="display: none;">
                <i class="fas fa-edit"></i> Edit
            </button>
            <?php endif; ?>
            
            <?php if (($permissions['canDelete'] ?? false)): ?>
            <button type="button" class="btn btn-danger" id="deleteStaffFromModal" style="display: none;">
                <i class="fas fa-trash"></i> Delete
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Staff Details Template (Hidden) -->
<div id="staffDetailsTemplate" style="display: none;">
    <div class="staff-details-container">
        <!-- Basic Information -->
        <div class="details-section">
            <h4><i class="fas fa-user"></i> Basic Information</h4>
            <div class="details-grid">
                <div class="detail-item">
                    <label>Employee ID:</label>
                    <span id="detail-employee-id">-</span>
                </div>
                <div class="detail-item">
                    <label>Full Name:</label>
                    <span id="detail-full-name">-</span>
                </div>
                <div class="detail-item">
                    <label>Role/Designation:</label>
                    <span id="detail-designation" class="role-badge">-</span>
                </div>
                <div class="detail-item">
                    <label>Department:</label>
                    <span id="detail-department">-</span>
                </div>
                <div class="detail-item">
                    <label>Gender:</label>
                    <span id="detail-gender">-</span>
                </div>
                <div class="detail-item">
                    <label>Date of Birth:</label>
                    <span id="detail-dob">-</span>
                </div>
                <div class="detail-item">
                    <label>Date Joined:</label>
                    <span id="detail-date-joined">-</span>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="details-section">
            <h4><i class="fas fa-address-book"></i> Contact Information</h4>
            <div class="details-grid">
                <div class="detail-item">
                    <label>Phone Number:</label>
                    <span id="detail-contact-no">-</span>
                </div>
                <div class="detail-item">
                    <label>Email Address:</label>
                    <span id="detail-email">-</span>
                </div>
                <div class="detail-item full-width">
                    <label>Address:</label>
                    <span id="detail-address">-</span>
                </div>
            </div>
        </div>

        <!-- Role-Specific Information -->
        <div class="details-section" id="roleSpecificDetails" style="display: none;">
            <h4><i class="fas fa-cog"></i> Role-Specific Information</h4>
            <div class="details-grid" id="roleSpecificGrid">
                <!-- Content populated by JavaScript -->
            </div>
        </div>

        <!-- Employment Status -->
        <div class="details-section">
            <h4><i class="fas fa-briefcase"></i> Employment Status</h4>
            <div class="details-grid">
                <div class="detail-item">
                    <label>Status:</label>
                    <span id="detail-status" class="status-badge">Active</span>
                </div>
                <div class="detail-item">
                    <label>Years of Service:</label>
                    <span id="detail-years-service">-</span>
                </div>
            </div>
        </div>
    </div>
</div>