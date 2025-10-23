<!-- Create/Edit Shift Modal -->
<div id="shiftModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-calendar-plus"></i>
                <span id="modalTitle">Create Shift</span>
            </h3>
            <button type="button" class="modal-close" id="closeShiftModal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="shiftForm" class="modal-form">
            <?= csrf_field() ?>
            <input type="hidden" id="shiftId" name="id">
            
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="doctorSelect" class="form-label">Doctor *</label>
                        <select id="doctorSelect" name="doctor_id" class="form-select" required>
                            <option value="">Select Doctor</option>
                            <?php foreach ($availableStaff as $staff): ?>
                                <option value="<?= esc($staff['doctor_id']) ?>">
                                    <?= esc($staff['first_name'] . ' ' . $staff['last_name']) ?> 
                                    - <?= esc($staff['department']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="shiftDate" class="form-label">Date *</label>
                        <input type="date" id="shiftDate" name="shift_date" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="shiftStart" class="form-label">Start Time *</label>
                        <input type="time" id="shiftStart" name="shift_start" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="shiftEnd" class="form-label">End Time *</label>
                        <input type="time" id="shiftEnd" name="shift_end" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="shiftDepartment" class="form-label">Department</label>
                        <select id="shiftDepartment" name="department" class="form-select">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= esc($dept['department']) ?>"><?= esc($dept['department']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="shiftType" class="form-label">Shift Type</label>
                        <select id="shiftType" name="shift_type" class="form-select">
                            <option value="">Select Type</option>
                            <?php foreach ($shiftTypes as $type): ?>
                                <option value="<?= esc($type['shift_type']) ?>"><?= esc($type['shift_type']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="roomWard" class="form-label">Room/Ward</label>
                        <select id="roomWard" name="room_ward" class="form-select">
                            <option value="">Select Room/Ward</option>
                            <?php foreach ($roomsWards as $room): ?>
                                <option value="<?= esc($room['room_ward']) ?>"><?= esc($room['room_ward']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="shiftStatus" class="form-label">Status</label>
                        <select id="shiftStatus" name="status" class="form-select">
                            <option value="Scheduled">Scheduled</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="shiftNotes" class="form-label">Notes</label>
                        <textarea id="shiftNotes" name="notes" class="form-textarea" rows="3" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelShiftBtn">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveShiftBtn">
                    <i class="fas fa-save"></i> Save Shift
                </button>
            </div>
        </form>
    </div>
</div>
