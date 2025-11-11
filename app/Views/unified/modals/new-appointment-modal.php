<!-- New Appointment Modal -->
<div id="newAppointmentModal" class="hms-modal-overlay" aria-hidden="true">
    <div class="hms-modal" role="dialog" aria-modal="true" aria-labelledby="newAppointmentTitle">
        <div class="hms-modal-header">
            <div class="hms-modal-title" id="newAppointmentTitle">
                <i class="fas fa-calendar-plus" style="color:#4f46e5"></i>
                Schedule New Appointment
            </div>
            <button type="button" class="btn btn-secondary btn-small" onclick="closeNewAppointmentModal()" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="newAppointmentForm">
            <div class="hms-modal-body">
                <div class="form-grid"> 
                    <div>
                        <label class="form-label" for="appointment_patient">Patient*</label>
                        <select id="appointment_patient" name="patient_id" class="form-select" required>
                            <option value="">Select Patient...</option>
                        </select>
                        <small id="err_appointment_patient" style="color:#dc2626"></small>
                    </div>
                    <?php if ($userRole === 'admin'): ?>
                    <div>
                        <label class="form-label" for="appointment_doctor">Doctor*</label>
                        <!-- Debug: Check doctors_for_modal -->
                        <?php if (!empty($doctors_for_modal)): ?>
                            <!-- DEBUG: Found <?php echo count($doctors_for_modal); ?> doctors -->
                        <?php else: ?>
                            <!-- DEBUG: doctors_for_modal is empty -->
                        <?php endif; ?>
                        
                        <select id="appointment_doctor" name="doctor_id" class="form-select" required>
                            <option value="">Select Doctor...</option>
                            <?php if (!empty($doctors_for_modal)): ?>
                                <?php foreach ($doctors_for_modal as $doctor): ?>
                                    <option value="<?= esc($doctor['staff_id'] ?? $doctor['id']) ?>">
                                        <?= esc(trim(($doctor['first_name'] ?? '') . ' ' . ($doctor['last_name'] ?? ''))) ?>
                                        <?= !empty($doctor['specialization']) ? ' - ' . esc($doctor['specialization']) : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="">No doctors available</option>
                            <?php endif; ?>
                        </select>
                        <small id="err_appointment_doctor" style="color:#dc2626"></small>
                    </div>
                    <?php endif; ?>
                    <div>
                        <label class="form-label" for="appointment_date">Date*</label>
                        <input type="date" id="appointment_date" name="appointment_date" class="form-input" required min="<?= date('Y-m-d') ?>">
                        <small id="err_appointment_date" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="appointment_time">Time*</label>
                        <input type="time" id="appointment_time" name="appointment_time" class="form-input" required>
                        <small id="err_appointment_time" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="appointment_duration">Duration (minutes)*</label>
                        <select id="appointment_duration" name="duration" class="form-select" required>
                            <option value="15">15 minutes</option>
                            <option value="30" selected>30 minutes</option>
                            <option value="45">45 minutes</option>
                            <option value="60">1 hour</option>
                            <option value="90">1.5 hours</option>
                            <option value="120">2 hours</option>
                        </select>
                        <small id="err_appointment_duration" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="appointment_type">Appointment Type*</label>
                        <select id="appointment_type" name="appointment_type" class="form-select" required>
                            <option value="">Select Type...</option>
                            <option value="Consultation">Consultation</option>
                            <option value="Follow-up">Follow-up</option>
                            <option value="Check-up">Check-up</option>
                            <option value="Emergency">Emergency</option>
                            <option value="Surgery">Surgery</option>
                            <option value="Therapy">Therapy</option>
                            <option value="Vaccination">Vaccination</option>
                            <option value="Other">Other</option>
                        </select>
                        <small id="err_appointment_type" style="color:#dc2626"></small>
                    </div>
                    <div class="full">
                        <label class="form-label" for="appointment_reason">Reason/Condition*</label>
                        <textarea id="appointment_reason" name="reason" class="form-input" rows="3" required placeholder="Describe the reason for the appointment..."></textarea>
                        <small id="err_appointment_reason" style="color:#dc2626"></small>
                    </div>
                    <div class="full">
                        <label class="form-label" for="appointment_notes">Additional Notes</label>
                        <textarea id="appointment_notes" name="notes" class="form-input" rows="2" placeholder="Any additional notes or instructions..."></textarea>
                    </div>
                </div>
            </div>
            <div class="hms-modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeNewAppointmentModal()">Cancel</button>
                <button type="submit" id="saveAppointmentBtn" class="btn btn-success">
                    <i class="fas fa-calendar-check"></i> Schedule Appointment
                </button>
            </div>
        </form>
    </div>
</div>
