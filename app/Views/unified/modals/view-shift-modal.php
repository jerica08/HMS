<!-- View Shift Modal -->
<div id="viewShiftModal" class="hms-modal-overlay" aria-hidden="true">
    <div class="hms-modal" role="dialog" aria-modal="true" aria-labelledby="viewScheduleTitle">
        <div class="hms-modal-header">
            <div class="hms-modal-title" id="viewScheduleTitle"><i class="fas fa-calendar-check" style="color:#4f46e5"></i> Schedule Details</div>
            <button type="button" class="btn btn-secondary btn-small" id="closeViewShiftModal" aria-label="Close"><i class="fas fa-times"></i></button>
        </div>
        <div class="hms-modal-body">
            <div class="form-grid">
                <div class="full">
                    <label class="form-label">Doctor</label>
                    <input type="text" id="viewDoctorName" class="form-input" readonly disabled>
                </div>
                <div>
                    <label class="form-label">Weekday</label>
                    <input type="text" id="viewScheduleWeekday" class="form-input" readonly disabled>
                </div>
                <div>
                    <label class="form-label">Time</label>
                    <input type="text" id="viewScheduleSlot" class="form-input" readonly disabled>
                </div>
                <div>
                    <label class="form-label">Status</label>
                    <input type="text" id="viewShiftStatus" class="form-input" readonly disabled>
                </div>
            </div>
        </div>
        <div class="hms-modal-actions">
            <button type="button" class="btn btn-success" id="closeViewShiftBtn">Close</button>
        </div>
    </div>
</div>
