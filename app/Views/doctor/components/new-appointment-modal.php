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
                    <div style="margin-bottom: 1rem;">
                        <label for="patientSelect" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Patient</label>
                        <select id="patientSelect" name="patient_id" class="filter-input" required style="width: 100%;">
                            <option value="">Select Patient</option>
                            <?php if (!empty($patients)): ?>
                                <?php foreach ($patients as $patient): ?>
                                    <option value="<?= esc($patient['patient_id']) ?>"><?= esc($patient['first_name'] . ' ' . $patient['last_name']) ?> (<?= esc($patient['patient_id']) ?>)</option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="appointmentDate" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Date</label>
                        <input type="date" id="appointmentDate" name="appointmentDate" class="filter-input" required style="width: 100%;">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="appointmentTime" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Time</label>
                        <input type="time" id="appointmentTime" name="appointmentTime" class="filter-input" required style="width: 100%;">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="appointmentType" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Type</label>
                        <select id="appointmentType" name="appointmentType" class="filter-input" required style="width: 100%;">
                            <option value="">Select Type</option>
                            <option value="Consultation">Consultation</option>
                            <option value="Follow-up">Follow-up</option>
                            <option value="Check-up">Check-up</option>
                            <option value="Emergency">Emergency</option>
                        </select>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="appointmentReason" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Reason/Condition</label>
                        <textarea id="appointmentReason" name="appointmentReason" class="filter-input" rows="3" placeholder="Describe the reason for the appointment" style="width: 100%; resize: vertical;"></textarea>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="appointmentDuration" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Duration (minutes)</label>
                        <input type="number" id="appointmentDuration" name="appointmentDuration" class="filter-input" min="15" max="120" step="15" value="30" required style="width: 100%;">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                <button class="btn btn-success" id="saveBtn">Schedule Appointment</button>
            </div>
        </div>
    </div>