<!-- Add Shift Modal -->
<div id="shiftModal" class="modal-overlay" aria-hidden="true" style="display: none !important;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>
                <i class="fas fa-calendar-plus"></i>
                <span id="modalTitle">Create Shift</span>
            </h3>
            <button type="button" class="modal-close" id="closeShiftModal" onclick="closeAddShiftModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="shiftForm" class="modal-form">
            <?= csrf_field() ?>
            <input type="hidden" id="shiftId" name="id">
            
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="doctorSelect">Doctor *</label>
                        <select id="doctorSelect" name="doctor_id" class="form-control" required>
                            <option value="">Loading doctors...</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="shiftDate">Date *</label>
                        <input type="date" id="shiftDate" name="shift_date" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="shiftStart">Start Time *</label>
                        <input type="time" id="shiftStart" name="shift_start" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="shiftEnd">End Time *</label>
                        <input type="time" id="shiftEnd" name="shift_end" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="shiftDepartment">Department</label>
                        <select id="shiftDepartment" name="department" class="form-control">
                            <option value="">Select Department</option>
                            <?php 
                            if (isset($departments) && is_array($departments)) {
                                foreach ($departments as $dept): ?>
                                    <option value="<?= esc($dept['department']) ?>"><?= esc($dept['department']) ?></option>
                                <?php endforeach; 
                            } else {
                                echo '<option value="">No departments available</option>';
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="shiftType">Shift Type</label>
                        <select id="shiftType" name="shift_type" class="form-control">
                            <option value="">Select Type</option>
                            <?php 
                            if (isset($shiftTypes) && is_array($shiftTypes)) {
                                foreach ($shiftTypes as $type): ?>
                                    <option value="<?= esc($type['shift_type']) ?>"><?= esc($type['shift_type']) ?></option>
                                <?php endforeach; 
                            } else {
                                echo '<option value="">No shift types available</option>';
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="roomWard">Room/Ward</label>
                        <select id="roomWard" name="room_ward" class="form-control">
                            <option value="">Select Room/Ward</option>
                            <?php 
                            if (isset($roomsWards) && is_array($roomsWards)) {
                                foreach ($roomsWards as $room): ?>
                                    <option value="<?= esc($room['room_ward']) ?>"><?= esc($room['room_ward']) ?></option>
                                <?php endforeach; 
                            } else {
                                echo '<option value="">No rooms/wards available</option>';
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="shiftStatus">Status</label>
                        <select id="shiftStatus" name="status" class="form-control">
                            <option value="Scheduled">Scheduled</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="shiftNotes">Notes</label>
                        <textarea id="shiftNotes" name="notes" class="form-control" rows="3" placeholder="Optional notes..." style="resize: vertical; min-height: 80px;"></textarea>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelShiftBtn" onclick="closeAddShiftModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveShiftBtn">
                    <i class="fas fa-save"></i> Save Shift
                </button>
            </div>
        </form>
    </div>
</div>
