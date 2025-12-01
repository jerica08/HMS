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
                            <!-- Patients will be loaded dynamically via JavaScript -->
                        </select>
                    </div>
                    
                    
                    <div class="form-group">
                        <label for="prescriptionDate" class="form-label">Date Issued *</label>
                        <input type="date" id="prescriptionDate" name="date_issued" class="form-input" required value="<?= date('Y-m-d') ?>">
                    </div>
                    
                    <div class="form-group full-width">
                        <label class="form-label">Medicines *</label>
                        <div class="medicines-table-wrapper">
                            <table class="medicines-table">
                                <thead>
                                    <tr>
                                        <th>Medication</th>
                                        <th>Dosage</th>
                                        <th>Frequency</th>
                                        <th>Duration</th>
                                        <th>Quantity</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="medicinesTableBody">
                                    <tr class="medicine-row">
                                        <td>
                                            <select name="medication_resource_id[]" class="form-select medicine-medication-select">
                                                <option value="">Select medication</option>
                                                <!-- Options will be loaded dynamically via JavaScript from Resource Management -->
                                            </select>
                                            <input type="hidden" name="medication_name[]" class="medicine-name-hidden">
                                            <small class="error-text" style="color:#dc2626"></small>
                                        </td>
                                        <td>
                                            <input type="text" name="dosage[]" class="form-input" placeholder="e.g., 500mg">
                                        </td>
                                        <td>
                                            <select name="frequency[]" class="form-select">
                                                <option value="">Select Frequency</option>
                                                <option value="Once daily">Once daily</option>
                                                <option value="Twice daily">Twice daily</option>
                                                <option value="Three times daily">Three times daily</option>
                                                <option value="Every 4 hours">Every 4 hours</option>
                                                <option value="Every 6 hours">Every 6 hours</option>
                                                <option value="Every 8 hours">Every 8 hours</option>
                                                <option value="As needed">As needed</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="duration[]" class="form-select">
                                                <option value="">Select Duration</option>
                                                <option value="3 days">3 days</option>
                                                <option value="5 days">5 days</option>
                                                <option value="7 days">7 days</option>
                                                <option value="10 days">10 days</option>
                                                <option value="14 days">14 days</option>
                                                <option value="30 days">30 days</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="quantity[]" class="form-input" min="1" placeholder="e.g., 30">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger remove-medicine-row" title="Remove medicine">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-secondary" id="addMedicineRowBtn" type="button">
                                <i class="fas fa-plus"></i> Add Medicine
                            </button>
                        </div>
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
