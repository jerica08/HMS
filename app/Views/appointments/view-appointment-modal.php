<!-- View Appointment Modal -->
<div id="viewAppointmentModal" class="hms-modal-overlay">
    <div class="hms-modal">
        <div class="hms-modal-header">
            <h3 class="hms-modal-title">
                <i class="fas fa-calendar-alt"></i>
                Appointment Details
            </h3>
            <button class="close-btn" id="closeViewModal">&times;</button>
        </div>
        <div class="hms-modal-body" id="appointmentDetailsBody">
            <div class="loading-state" style="text-align: center; padding: 2rem;">
                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #6366f1; margin-bottom: 1rem;"></i>
                <p style="color: #6b7280;">Loading appointment details...</p>
            </div>
        </div>
        <div class="hms-modal-actions" id="appointmentModalFooter">
            <button type="button" class="action-btn secondary" id="closeViewAppointmentBtn">
                <i class="fas fa-times"></i> Close
            </button>
            
            <!-- Role-based action buttons -->
            <?php if (($permissions['canUpdateStatus'] ?? false)): ?>
            <button type="button" class="action-btn" style="background: #10b981; color: white; display: none;" id="markCompletedFromModal">
                <i class="fas fa-check"></i> Mark Completed
            </button>
            <?php endif; ?>
            
            <?php if (($permissions['canEdit'] ?? false)): ?>
            <button type="button" class="action-btn" style="background: #f59e0b; color: white; display: none;" id="editAppointmentFromModal">
                <i class="fas fa-edit"></i> Edit
            </button>
            <?php endif; ?>
            
            <?php if (($permissions['canDelete'] ?? false)): ?>
            <button type="button" class="action-btn" style="background: #ef4444; color: white; display: none;" id="deleteAppointmentFromModal">
                <i class="fas fa-trash"></i> Delete
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>