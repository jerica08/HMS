<!-- Edit Shift Modal -->
<div id="editShiftModal" class="hms-modal-overlay" aria-hidden="true">
    <div class="hms-modal" role="dialog" aria-modal="true" aria-labelledby="editShiftTitle">
        <div class="hms-modal-header">
            <div class="hms-modal-title" id="editShiftTitle"><i class="fas fa-edit" style="color:#4f46e5"></i> Edit Shift</div>
            <button type="button" class="btn btn-secondary btn-small" id="closeEditShiftModal" aria-label="Close"><i class="fas fa-times"></i></button>
        </div>
        <form id="editShiftForm">
            <?= csrf_field() ?>
            <input type="hidden" id="editShiftId" name="id">
            <div class="hms-modal-body">
                <div class="form-grid">
                    <div>
                        <label class="form-label" for="editDoctorSelect">Doctor *</label>
                        <select id="editDoctorSelect" name="staff_id" class="form-select" required>
                            <option value="">Select Doctor</option>
                            <?php foreach ($availableDoctors ?? [] as $doctor): ?>
                                <option value="<?= esc($doctor['staff_id']) ?>">
                                    <?= esc(trim(($doctor['first_name'] ?? '') . ' ' . ($doctor['last_name'] ?? ''))) ?>
                                    <?= !empty($doctor['specialization']) ? ' - ' . esc($doctor['specialization']) : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="editWeekday">Weekday *</label>
                        <select id="editWeekday" name="weekday" class="form-select" required>
                            <option value="">Select Day</option>
                            <?php foreach (['Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6, 'Sunday' => 7] as $day => $val): ?>
                                <option value="<?= $val ?>"><?= esc($day) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="editShiftStart">Start Time *</label>
                        <input type="time" id="editShiftStart" name="start_time" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label" for="editShiftEnd">End Time *</label>
                        <input type="time" id="editShiftEnd" name="end_time" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label" for="editShiftStatus">Status</label>
                        <select id="editShiftStatus" name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="hms-modal-actions">
                <button type="button" class="btn btn-secondary" id="cancelEditShiftBtn">Cancel</button>
                <button type="submit" class="btn btn-success" id="saveEditShiftBtn"><i class="fas fa-save"></i> Update Shift</button>
            </div>
        </form>
    </div>
</div>
