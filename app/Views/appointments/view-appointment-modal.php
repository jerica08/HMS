<!-- View Appointment Modal -->
<div id="viewAppointmentModal" class="modal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h3>
                <i class="fas fa-calendar-alt"></i>
                Appointment Details
            </h3>
            <button class="modal-close" id="closeViewModal">&times;</button>
        </div>
        <div class="modal-body" id="appointmentDetailsBody">
            <div class="loading-state">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading appointment details...</p>
            </div>
        </div>
        <div class="modal-footer" id="appointmentModalFooter">
            <button type="button" class="btn btn-secondary" id="closeViewAppointmentBtn">
                <i class="fas fa-times"></i> Close
            </button>
            
            <!-- Role-based action buttons -->
            <?php if (($permissions['canUpdateStatus'] ?? false)): ?>
            <button type="button" class="btn btn-success" id="markCompletedFromModal" style="display: none;">
                <i class="fas fa-check"></i> Mark Completed
            </button>
            <?php endif; ?>
            
            <?php if (($permissions['canEdit'] ?? false)): ?>
            <button type="button" class="btn btn-warning" id="editAppointmentFromModal" style="display: none;">
                <i class="fas fa-edit"></i> Edit
            </button>
            <?php endif; ?>
            
            <?php if (($permissions['canDelete'] ?? false)): ?>
            <button type="button" class="btn btn-danger" id="deleteAppointmentFromModal" style="display: none;">
                <i class="fas fa-trash"></i> Delete
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>