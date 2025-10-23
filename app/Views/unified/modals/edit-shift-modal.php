<!-- Edit Shift Modal -->
<div id="editShiftModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-edit"></i>
                Edit Shift
            </h3>
            <button type="button" class="modal-close" id="closeEditShiftModal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="editShiftForm" class="modal-form">
            <?= csrf_field() ?>
            <input type="hidden" id="editShiftId" name="id">
            
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="editDoctorSelect" class="form-label">Doctor *</label>
                        <select id="editDoctorSelect" name="doctor_id" class="form-select" required>
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
                        <label for="editShiftDate" class="form-label">Date *</label>
                        <input type="date" id="editShiftDate" name="shift_date" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="editShiftStart" class="form-label">Start Time *</label>
                        <input type="time" id="editShiftStart" name="shift_start" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="editShiftEnd" class="form-label">End Time *</label>
                        <input type="time" id="editShiftEnd" name="shift_end" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="editShiftDepartment" class="form-label">Department</label>
                        <select id="editShiftDepartment" name="department" class="form-select">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= esc($dept['department']) ?>"><?= esc($dept['department']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="editShiftType" class="form-label">Shift Type</label>
                        <select id="editShiftType" name="shift_type" class="form-select">
                            <option value="">Select Type</option>
                            <?php foreach ($shiftTypes as $type): ?>
                                <option value="<?= esc($type['shift_type']) ?>"><?= esc($type['shift_type']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="editRoomWard" class="form-label">Room/Ward</label>
                        <select id="editRoomWard" name="room_ward" class="form-select">
                            <option value="">Select Room/Ward</option>
                            <?php foreach ($roomsWards as $room): ?>
                                <option value="<?= esc($room['room_ward']) ?>"><?= esc($room['room_ward']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="editShiftStatus" class="form-label">Status</label>
                        <select id="editShiftStatus" name="status" class="form-select">
                            <option value="Scheduled">Scheduled</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="editShiftNotes" class="form-label">Notes</label>
                        <textarea id="editShiftNotes" name="notes" class="form-textarea" rows="3" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelEditShiftBtn">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveEditShiftBtn">
                    <i class="fas fa-save"></i> Update Shift
                </button>
            </div>
        </form>
    </div>
</div>
