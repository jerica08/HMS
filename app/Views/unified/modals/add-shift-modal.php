<!-- Add / Edit Shift Modal (hms-modal layout like edit-staff modal) -->
<div id="shiftModal" class="hms-modal-overlay" aria-hidden="true">
    <!-- Modal Debug: Check all variables -->
    
    <?php 
    $modal_doctors = $doctors_for_modal ?? $doctors ?? [];
    if (!empty($modal_doctors)): ?>
       
    <?php else: ?>
       
    <?php endif; ?>

    <div class="hms-modal" role="dialog" aria-modal="true" aria-labelledby="shiftModalTitle">
        <div class="hms-modal-header">
            <div class="hms-modal-title" id="shiftModalTitle">
                <i class="fas fa-calendar-plus" style="color:#4f46e5"></i>
                <span id="modalTitle">Create Shift</span>
            </div>
            <button type="button" class="btn btn-secondary btn-small" id="closeShiftModal" onclick="closeAddShiftModal()" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="shiftForm">
            <?= csrf_field() ?>
            <input type="hidden" id="shiftId" name="id">

            <div class="hms-modal-body">
                <div class="form-grid">
                    <div>
                        <label class="form-label" for="doctorSelect">Doctor *</label>
                        <select id="doctorSelect" name="doctor_id" class="form-select" required>
                            <option value="">Select Doctor</option>
                            <?php if (!empty($modal_doctors)): ?>
                                <?php foreach ($modal_doctors as $doctor): ?>
                                    <option value="<?= esc($doctor['staff_id'] ?? $doctor['doctor_id']) ?>">
                                        <?= esc($doctor['name'] ?? trim(($doctor['first_name'] ?? '') . ' ' . ($doctor['last_name'] ?? ''))) ?>
                                        <?= !empty($doctor['specialization']) ? ' - ' . esc($doctor['specialization']) : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="">No doctors available</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div>
                        <label class="form-label" for="weekday">Day *</label>
                        <select id="weekday" name="weekday" class="form-select" required>
                            <option value="">Select Day</option>
                            <option value="1">Monday</option>
                            <option value="2">Tuesday</option>
                            <option value="3">Wednesday</option>
                            <option value="4">Thursday</option>
                            <option value="5">Friday</option>
                            <option value="6">Saturday</option>
                            <option value="7">Sunday</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label" for="slot">Slot *</label>
                        <select id="slot" name="slot" class="form-select" required>
                            <option value="">Select Slot</option>
                            <option value="morning">Morning</option>
                            <option value="afternoon">Afternoon</option>
                            <option value="night">Night</option>
                            <option value="all_day">All Day</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label" for="shiftStatus">Status</label>
                        <select id="shiftStatus" name="status" class="form-select">
                            <option value="Scheduled">Scheduled</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div class="full">
                        <label class="form-label" for="shiftNotes">Notes</label>
                        <textarea id="shiftNotes" name="notes" class="form-input" rows="3" placeholder="Optional notes..." style="resize: vertical; min-height: 80px;"></textarea>
                    </div>
                </div>
            </div>

            <div class="hms-modal-actions">
                <button type="button" class="btn btn-secondary" id="cancelShiftBtn" onclick="closeAddShiftModal()">Cancel</button>
                <button type="submit" class="btn btn-success" id="saveShiftBtn">
                    <i class="fas fa-save"></i> Save Shift
                </button>
            </div>
        </form>
    </div>
</div>
