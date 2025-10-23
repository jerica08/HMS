<!-- View Prescription Modal -->
<div id="viewPrescriptionModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-eye"></i>
                Prescription Details
            </h3>
            <button type="button" class="modal-close" id="closeViewPrescriptionModal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="modal-body">
            <div class="prescription-details">
                <div class="detail-section">
                    <h4>Prescription Information</h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Prescription ID:</label>
                            <span id="viewPrescriptionId">-</span>
                        </div>
                        <div class="detail-item">
                            <label>Date Issued:</label>
                            <span id="viewPrescriptionDate">-</span>
                        </div>
                        <div class="detail-item">
                            <label>Status:</label>
                            <span id="viewPrescriptionStatus" class="status-badge">-</span>
                        </div>
                        <div class="detail-item">
                            <label>Doctor:</label>
                            <span id="viewDoctorName">-</span>
                        </div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h4>Patient Information</h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Patient Name:</label>
                            <span id="viewPatientName">-</span>
                        </div>
                        <div class="detail-item">
                            <label>Patient ID:</label>
                            <span id="viewPatientId">-</span>
                        </div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h4>Medication Details</h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Medication:</label>
                            <span id="viewMedication">-</span>
                        </div>
                        <div class="detail-item">
                            <label>Dosage:</label>
                            <span id="viewDosage">-</span>
                        </div>
                        <div class="detail-item">
                            <label>Frequency:</label>
                            <span id="viewFrequency">-</span>
                        </div>
                        <div class="detail-item">
                            <label>Duration:</label>
                            <span id="viewDuration">-</span>
                        </div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h4>Notes</h4>
                    <div class="notes-content" id="viewPrescriptionNotes">
                        No notes available
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="closeViewPrescriptionBtn">Close</button>
            <?php if ($permissions['canEdit']): ?>
            <button type="button" class="btn btn-primary" id="editFromViewBtn">
                <i class="fas fa-edit"></i> Edit
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>
