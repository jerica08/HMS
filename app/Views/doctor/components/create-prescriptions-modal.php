<!-- Modals -->
    <div class="modal" id="newPrescriptionModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>New Prescription</h3>
                <button class="modal-close" id="closeNewRx">&times;</button>
            </div>
            <div class="modal-body">
                <form id="newPrescriptionForm">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Patient *</label>
                            <select class="form-select" name="patient_id" required>
                                <option value="">Select Patient</option>
                                <?php if (isset($patients) && !empty($patients)): ?>
                                    <?php foreach ($patients as $patient): ?>
                                        <option value="<?= $patient['patient_id'] ?>">
                                            <?= $patient['first_name'] . ' ' . $patient['last_name'] ?> (<?= $patient['patient_id'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Date Issued *</label>
                            <input type="date" class="form-input" name="date_issued" required>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Medication *</label>
                            <input type="text" class="form-input" name="medication" placeholder="e.g., Lisinopril" required>
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Dosage *</label>
                            <input type="text" class="form-input" name="dosage" placeholder="e.g., 10mg once daily" required>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Frequency *</label>
                            <select class="form-select" name="frequency" required>
                                <option value="">Select Frequency</option>
                                <option>Once daily</option>
                                <option>Twice daily</option>
                                <option>Three times daily</option>
                                <option>As needed</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Duration *</label>
                            <select class="form-select" name="duration" required>
                                <option value="">Select Duration</option>
                                <option>7 days</option>
                                <option>14 days</option>
                                <option>30 days</option>
                                <option>90 days</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Notes</label>
                        <textarea class="form-input" name="notes" rows="3" placeholder="Additional instructions or notes"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelNewRx">Cancel</button>
                <button type="submit" form="newPrescriptionForm" class="btn btn-success">Save Prescription</button>
            </div>
        </div>
    </div>

    <div class="modal" id="viewPrescriptionModal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3>Prescription Details</h3>
                <button class="modal-close" id="closeViewRx">&times;</button>
            </div>
            <div class="modal-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <div>
                        <h4 style="margin-bottom: 1rem; color: #2d3748;">Prescription Information</h4>
                        <div style="background: #f7fafc; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                            <div style="margin-bottom: 0.5rem;"><strong>ID:</strong> <span id="rxId">RX001234</span></div>
                            <div style="margin-bottom: 0.5rem;"><strong>Date:</strong> <span id="rxDate">Aug 20, 2025</span></div>
                            <div style="margin-bottom: 0.5rem;"><strong>Medication:</strong> <span id="rxMedication">Lisinopril</span></div>
                            <div style="margin-bottom: 0.5rem;"><strong>Dosage:</strong> <span id="rxDosage">10mg once daily</span></div>
                            <div style="margin-bottom: 0.5rem;"><strong>Duration:</strong> <span id="rxDuration">30 days</span></div>
                            <div><strong>Status:</strong> <span id="rxStatus" class="badge badge-success">Active</span></div>
                        </div>
                        <h4 style="margin-bottom: 1rem; color: #2d3748;">Notes</h4>
                        <div style="background: #e6fffa; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                            <div id="rxNotes">Take with water. Monitor BP daily.</div>
                        </div>
                    </div>
                    <div>
                        <h4 style="margin-bottom: 1rem; color: #2d3748;">Patient Information</h4>
                        <div style="background: #f0fff4; padding: 1rem; border-radius: 8px;">
                            <div style="margin-bottom: 0.5rem;"><strong>Name:</strong> <span id="rxPatientName">Sarah Wilson</span></div>
                            <div style="margin-bottom: 0.5rem;"><strong>Patient ID:</strong> <span id="rxPatientId">P0012347</span></div>
                            <div style="margin-bottom: 0.5rem;"><strong>Age:</strong> <span id="rxPatientAge">45 years</span></div>
                            <div><strong>Phone:</strong> <span id="rxPatientPhone">(555) 123-4567</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="closeViewRxBtn">Close</button>
                <button type="button" class="btn btn-primary" id="editFromViewBtn">Edit</button>
            </div>
        </div>
    </div>