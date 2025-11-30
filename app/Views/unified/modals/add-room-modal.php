<div id="addRoomModal" class="hms-modal-overlay" aria-hidden="true">
    <div class="hms-modal" role="dialog" aria-modal="true" aria-labelledby="addRoomTitle">
        <div class="hms-modal-header">
            <div class="hms-modal-title" id="addRoomTitle">
                <i class="fas fa-hotel" style="color:#0ea5e9"></i>
                Add New Room
            </div>
            <button type="button" class="btn btn-secondary btn-small" aria-label="Close" data-dismiss="modal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="addRoomForm">
            <?= csrf_field() ?>
            <div class="hms-modal-body">
                <div class="form-grid">
                    <div>
<<<<<<< HEAD
<<<<<<< HEAD
=======
                        <label class="form-label" for="modal_department">Department</label>
                        <select id="modal_department" name="department_id" class="form-input">
                            <option value="">Select department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= esc($dept['department_id']) ?>"><?= esc($dept['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-grid">
                    <div>
>>>>>>> 3b7d5d2 (Commit)
=======
>>>>>>> f13ff7a (Modified migraation file)
                        <label class="form-label" for="modal_room_type">Room Type</label>
                        <select id="modal_room_type" name="room_type_id" class="form-input" required>
                            <option value="">Select room type</option>
                            <?php foreach ($roomTypes as $type): ?>
                                <option value="<?= esc($type['room_type_id']) ?>"><?= esc($type['type_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-hint">Choose a room type. Known types will auto-fill related details.</small>
                    </div>
                    <div>
                        <label class="form-label" for="modal_room_number">Room Number*</label>
                        <input type="text" id="modal_room_number" name="room_number" class="form-input" required />
                    </div>
                </div>
                <div class="form-grid">
                    <div>
                        <label class="form-label" for="modal_room_name">Room Name (optional)</label>
                        <input type="text" id="modal_room_name" name="room_name" class="form-input" />
                    </div>
                    <div>
                        <label class="form-label" for="modal_floor">Floor</label>
                        <input type="text" id="modal_floor" name="floor_number" class="form-input" />
                    </div>
                </div>
                <div class="form-grid">
                    <div>
                        <label class="form-label" for="modal_rate_range">Daily Rate Range</label>
                        <input type="text" id="modal_rate_range" name="rate_range" class="form-input" placeholder="e.g. 1,000–2,000" />
                    </div>
                    <div>
                        <label class="form-label" for="modal_hourly_rate">Hourly Rate (optional)</label>
                        <input type="number" step="0.01" id="modal_hourly_rate" name="hourly_rate" class="form-input" />
                    </div>
                    <div>
                        <label class="form-label" for="modal_overtime_charge">Overtime Charge</label>
                        <input type="number" step="0.01" id="modal_overtime_charge" name="overtime_charge_per_hour" class="form-input" />
                    </div>
                </div>
                <div class="form-grid">
                    <div>
                        <label class="form-label" for="modal_extra_charge">Extra Person Charge (PHP)</label>
                        <input type="number" step="0.01" id="modal_extra_charge" name="extra_person_charge" class="form-input" />
                    </div>
                    <div>
                        <label class="form-label" for="modal_bed_capacity">Bed Capacity</label>
                        <input type="number" id="modal_bed_capacity" name="bed_capacity" class="form-input" min="1" />
                    </div>
                </div>
                <div class="form-grid">
                    <div>
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> f13ff7a (Modified migraation file)
                        <label class="form-label" for="modal_department">Department</label>
                        <select id="modal_department" name="department_id" class="form-input">
                            <option value="">Select department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= esc($dept['department_id']) ?>"><?= esc($dept['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
<<<<<<< HEAD
=======
>>>>>>> 3b7d5d2 (Commit)
=======
>>>>>>> f13ff7a (Modified migraation file)
                        <label class="form-label" for="modal_status">Room Status</label>
                        <select id="modal_status" name="status" class="form-input">
                            <option value="available">Available</option>
                            <option value="occupied">Occupied</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                </div>
                <div class="form-grid-full">
                    <div>
                        <label class="form-label" for="modal_notes">Notes</label>
                        <textarea id="modal_notes" name="notes" class="form-input" rows="2" placeholder="Enter room notes"></textarea>
                    </div>
                </div>
            </div>
            <div class="hms-modal-actions">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="saveRoomBtn"><i class="fas fa-save"></i> Save Room</button>
            </div>
        </form>
    </div>
</div>
