<!-- Schedule Appointment Modal -->
<div id="scheduleModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>
                <?php 
                switch ($userRole ?? 'doctor') {
                    case 'admin':
                        echo 'Schedule System Appointment';
                        break;
                    case 'receptionist':
                        echo 'Book Patient Appointment';
                        break;
                    default:
                        echo 'Schedule New Appointment';
                }
                ?>
            </h3>
            <button class="modal-close" id="closeModal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="scheduleForm">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>" id="csrfToken">
                
                <div class="form-group">
                    <label for="patientSelect">Patient *</label>
                    <select id="patientSelect" name="patient_id" class="form-control" required>
                        <option value="">Select Patient</option>
                        <!-- Populated via JavaScript -->
                    </select>
                </div>

                <?php if (($userRole ?? 'doctor') === 'admin' || ($userRole ?? 'doctor') === 'receptionist'): ?>
                <div class="form-group">
                    <label for="doctorSelect">Assign Doctor *</label>
                    <select id="doctorSelect" name="doctor_id" class="form-control" required>
                        <option value="">Select Doctor</option>
                        <!-- Populated via JavaScript -->
                    </select>
                </div>
                <?php endif; ?>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="appointmentDate">Date *</label>
                        <input type="date" id="appointmentDate" name="appointmentDate" class="form-control" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="appointmentTime">Time *</label>
                        <input type="time" id="appointmentTime" name="appointmentTime" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="appointmentType">Type *</label>
                        <select id="appointmentType" name="appointmentType" class="form-control" required>
                            <option value="">Select Type</option>
                            <option value="Consultation">Consultation</option>
                            <option value="Follow-up">Follow-up</option>
                            <option value="Check-up">Check-up</option>
                            <option value="Emergency">Emergency</option>
                            <?php if (($userRole ?? 'doctor') === 'admin'): ?>
                                <option value="Surgery">Surgery</option>
                                <option value="Procedure">Procedure</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="appointmentDuration">Duration (minutes) *</label>
                        <select id="appointmentDuration" name="appointmentDuration" class="form-control" required>
                            <option value="15">15 minutes</option>
                            <option value="30" selected>30 minutes</option>
                            <option value="45">45 minutes</option>
                            <option value="60">1 hour</option>
                            <?php if (($userRole ?? 'doctor') === 'admin'): ?>
                                <option value="90">1.5 hours</option>
                                <option value="120">2 hours</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="appointmentReason">Reason/Condition *</label>
                    <textarea id="appointmentReason" name="appointmentReason" class="form-control" rows="3" 
                              placeholder="Describe the reason for the appointment" required></textarea>
                </div>

                <?php if (($userRole ?? 'doctor') === 'receptionist' || ($userRole ?? 'doctor') === 'admin'): ?>
                <div class="form-group">
                    <label for="appointmentNotes">Additional Notes</label>
                    <textarea id="appointmentNotes" name="appointmentNotes" class="form-control" rows="2" 
                              placeholder="Any additional notes or special instructions"></textarea>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" id="sendNotification" name="sendNotification" class="form-check-input" checked>
                        <label for="sendNotification" class="form-check-label">
                            Send appointment confirmation to patient
                        </label>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="cancelBtn">Cancel</button>
            <button type="button" class="btn btn-success" id="saveBtn">
                <i class="fas fa-calendar-plus"></i>
                <?= ($userRole ?? 'doctor') === 'receptionist' ? 'Book Appointment' : 'Schedule Appointment' ?>
            </button>
        </div>
    </div>
</div>