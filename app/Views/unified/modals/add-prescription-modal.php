<!-- Create/Edit Prescription Modal -->
<div id="prescriptionModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-prescription-bottle"></i>
                <span id="modalTitle">Create Prescription</span>
            </h3>
            <button type="button" class="modal-close" id="closePrescriptionModal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="prescriptionForm" class="modal-form">
            <?= csrf_field() ?>
            <input type="hidden" id="prescriptionId" name="id">
            
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="patientSelect" class="form-label">Patient *</label>
                        <select id="patientSelect" name="patient_id" class="form-select" required>
                            <option value="">Select Patient</option>
                            <?php foreach ($availablePatients as $patient): ?>
                                <option value="<?= esc($patient['patient_id']) ?>">
                                    <?= esc($patient['first_name'] . ' ' . $patient['last_name']) ?> 
                                    (ID: <?= esc($patient['patient_id']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="prescriptionDate" class="form-label">Date Issued *</label>
                        <input type="date" id="prescriptionDate" name="date_issued" class="form-input" required value="<?= date('Y-m-d') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="medication" class="form-label">Medication *</label>
                        <input type="text" id="medication" name="medication" class="form-input" required placeholder="Enter medication name">
                    </div>
                    
                    <div class="form-group">
                        <label for="dosage" class="form-label">Dosage *</label>
                        <input type="text" id="dosage" name="dosage" class="form-input" required placeholder="e.g., 500mg">
                    </div>
                    
                    <div class="form-group">
                        <label for="frequency" class="form-label">Frequency *</label>
                        <input type="text" id="frequency" name="frequency" class="form-input" required placeholder="e.g., Twice daily">
                    </div>
                    
                    <div class="form-group">
                        <label for="duration" class="form-label">Duration *</label>
                        <input type="text" id="duration" name="duration" class="form-input" required placeholder="e.g., 7 days">
                    </div>
                    
                    <div class="form-group">
                        <label for="prescriptionStatus" class="form-label">Status</label>
                        <select id="prescriptionStatus" name="status" class="form-select">
                            <?php 
                            // Fallback statuses if not provided
                            $statusList = $statuses ?? [
                                ['status' => 'active'],
                                ['status' => 'pending'],
                                ['status' => 'ready'],
                                ['status' => 'completed'],
                                ['status' => 'cancelled'],
                                ['status' => 'expired']
                            ];
                            foreach ($statusList as $status): 
                            ?>
                                <option value="<?= esc($status['status']) ?>" <?= $status['status'] === 'active' ? 'selected' : '' ?>>
                                    <?= esc(ucfirst($status['status'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="prescriptionNotes" class="form-label">Notes</label>
                        <textarea id="prescriptionNotes" name="notes" class="form-textarea" rows="3" placeholder="Additional instructions or notes..."></textarea>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelPrescriptionBtn">Cancel</button>
                <button type="submit" class="btn btn-primary" id="savePrescriptionBtn">
                    <i class="fas fa-save"></i> Save Prescription
                </button>
            </div>
        </form>
    </div>
</div>
