<div id="addRoomModal" class="modal" aria-hidden="true" style="display:none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">
                    <i class="fas fa-hotel"></i> Add Room
                </h2>
                <button type="button" class="close" aria-label="Close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addRoomForm">
                    <div class="form-group">
                        <label for="modal_room_number">Room Number</label>
                        <input type="text" id="modal_room_number" name="room_number" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label for="modal_room_name">Room Name</label>
                        <input type="text" id="modal_room_name" name="room_name" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label for="modal_bed_capacity">Bed Capacity</label>
                        <input type="number" id="modal_bed_capacity" name="bed_capacity" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label for="modal_status">Status</label>
                        <select id="modal_status" name="status" class="form-control">
                            <option value="available">Available</option>
                            <option value="occupied">Occupied</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveRoomBtn">Save Room</button>
            </div>
        </div>
    </div>
</div>
