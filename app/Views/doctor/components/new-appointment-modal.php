 <!-- Schedule Appointment Modal -->
    <div id="scheduleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Schedule New Appointment</h3>
                <button class="modal-close" id="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="scheduleForm">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>" id="csrfToken">
                    <div class="modal-form-group">
                        <label for="patientSelect" class="modal-form-label">Patient</label>
                        <select id="patientSelect" name="patient_id" class="filter-input modal-form-input" required>
                            <option value="">Select Patient</option>
                            <?php if (!empty($patients)): ?>
                                <?php foreach ($patients as $patient): ?>
                                    <option value="<?= esc($patient['patient_id']) ?>"><?= esc($patient['first_name'] . ' ' . $patient['last_name']) ?> (<?= esc($patient['patient_id']) ?>)</option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="modal-form-group">
                        <label for="appointmentDate" class="modal-form-label">Date</label>
                        <input type="date" id="appointmentDate" name="appointmentDate" class="filter-input modal-form-input" required>
                    </div>
                    <div class="modal-form-group">
                        <label for="appointmentTime" class="modal-form-label">Time</label>
                        <input type="time" id="appointmentTime" name="appointmentTime" class="filter-input modal-form-input" required>
                    </div>
                    <div class="modal-form-group">
                        <label for="appointmentType" class="modal-form-label">Type</label>
                        <select id="appointmentType" name="appointmentType" class="filter-input modal-form-input" required>
                            <option value="">Select Type</option>
                            <option value="Consultation">Consultation</option>
                            <option value="Follow-up">Follow-up</option>
                            <option value="Check-up">Check-up</option>
                            <option value="Emergency">Emergency</option>
                        </select>
                    </div>
                    <div class="modal-form-group">
                        <label for="appointmentReason" class="modal-form-label">Reason/Condition</label>
                        <textarea id="appointmentReason" name="appointmentReason" class="filter-input modal-form-textarea" rows="3" placeholder="Describe the reason for the appointment"></textarea>
                    </div>
                    <div class="modal-form-group">
                        <label for="appointmentDuration" class="modal-form-label">Duration (minutes)</label>
                        <input type="number" id="appointmentDuration" name="appointmentDuration" class="filter-input modal-form-input" min="15" max="120" step="15" value="30" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                <button class="btn btn-success" id="saveBtn">Schedule Appointment</button>
            </div>
        </div>
    </div>