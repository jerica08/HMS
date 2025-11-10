<!-- View Shift Modal -->
<div id="viewShiftModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>
                <i class="fas fa-eye"></i>
                Shift Details
            </h3>
            <button type="button" class="modal-close" id="closeViewShiftModal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="modal-body">
            <div class="shift-details">
                <div class="detail-section">
                    <h4>Basic Information</h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Doctor:</label>
                            <span id="viewDoctorName">-</span>
                        </div>
                        <div class="detail-item">
                            <label>Date:</label>
                            <span id="viewShiftDate">-</span>
                        </div>
                        <div class="detail-item">
                            <label>Time:</label>
                            <span id="viewShiftTime">-</span>
                        </div>
                        <div class="detail-item">
                            <label>Duration:</label>
                            <span id="viewShiftDuration">-</span>
                        </div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h4>Assignment Details</h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Department:</label>
                            <span id="viewShiftDepartment">-</span>
                        </div>
                        <div class="detail-item">
                            <label>Type:</label>
                            <span id="viewShiftType">-</span>
                        </div>
                        <div class="detail-item">
                            <label>Room/Ward:</label>
                            <span id="viewRoomWard">-</span>
                        </div>
                        <div class="detail-item">
                            <label>Status:</label>
                            <span id="viewShiftStatus" class="status-badge">-</span>
                        </div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h4>Notes</h4>
                    <div class="notes-content" id="viewShiftNotes">
                        No notes available
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
