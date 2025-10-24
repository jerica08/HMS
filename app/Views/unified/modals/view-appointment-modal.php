<!-- View Appointment Modal -->
<div id="viewAppointmentModal" class="hms-modal-overlay" aria-hidden="true">
    <div class="hms-modal" role="dialog" aria-modal="true" aria-labelledby="viewAppointmentTitle">
        <div class="hms-modal-header">
            <div class="hms-modal-title" id="viewAppointmentTitle">
                <i class="fas fa-calendar-alt" style="color:#4f46e5"></i>
                Appointment Details
            </div>
            <button type="button" class="btn btn-secondary btn-small" onclick="closeViewAppointmentModal()" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="hms-modal-body">
            <div class="appointment-details">
                <div class="detail-section">
                    <h4 class="detail-section-title">
                        <i class="fas fa-user"></i> Patient Information
                    </h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label class="detail-label">Patient Name:</label>
                            <span id="view_patient_name" class="detail-value">-</span>
                        </div>
                        <div class="detail-item">
                            <label class="detail-label">Patient ID:</label>
                            <span id="view_patient_id" class="detail-value">-</span>
                        </div>
                        <div class="detail-item">
                            <label class="detail-label">Age:</label>
                            <span id="view_patient_age" class="detail-value">-</span>
                        </div>
                        <div class="detail-item">
                            <label class="detail-label">Phone:</label>
                            <span id="view_patient_phone" class="detail-value">-</span>
                        </div>
                    </div>
                </div>

                <?php if ($userRole === 'admin'): ?>
                <div class="detail-section">
                    <h4 class="detail-section-title">
                        <i class="fas fa-user-md"></i> Doctor Information
                    </h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label class="detail-label">Doctor Name:</label>
                            <span id="view_doctor_name" class="detail-value">-</span>
                        </div>
                        <div class="detail-item">
                            <label class="detail-label">Department:</label>
                            <span id="view_doctor_department" class="detail-value">-</span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="detail-section">
                    <h4 class="detail-section-title">
                        <i class="fas fa-calendar-alt"></i> Appointment Information
                    </h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label class="detail-label">Date:</label>
                            <span id="view_appointment_date" class="detail-value">-</span>
                        </div>
                        <div class="detail-item">
                            <label class="detail-label">Time:</label>
                            <span id="view_appointment_time" class="detail-value">-</span>
                        </div>
                        <div class="detail-item">
                            <label class="detail-label">Duration:</label>
                            <span id="view_appointment_duration" class="detail-value">-</span>
                        </div>
                        <div class="detail-item">
                            <label class="detail-label">Type:</label>
                            <span id="view_appointment_type" class="detail-value">-</span>
                        </div>
                        <div class="detail-item full">
                            <label class="detail-label">Reason/Condition:</label>
                            <span id="view_appointment_reason" class="detail-value">-</span>
                        </div>
                        <div class="detail-item full">
                            <label class="detail-label">Notes:</label>
                            <span id="view_appointment_notes" class="detail-value">-</span>
                        </div>
                        <div class="detail-item">
                            <label class="detail-label">Status:</label>
                            <span id="view_appointment_status" class="detail-value">-</span>
                        </div>
                        <div class="detail-item">
                            <label class="detail-label">Created:</label>
                            <span id="view_created_at" class="detail-value">-</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="hms-modal-actions">
            <button type="button" class="btn btn-secondary" onclick="closeViewAppointmentModal()">Close</button>
            <?php if (in_array($userRole, ['admin', 'doctor', 'receptionist'])): ?>
                <button type="button" class="btn btn-primary" onclick="editAppointmentFromView()">
                    <i class="fas fa-edit"></i> Edit Appointment
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.appointment-details {
    max-width: 100%;
}

.detail-section {
    margin-bottom: 2rem;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.detail-section-title {
    margin: 0 0 1rem 0;
    color: #374151;
    font-size: 1.1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.detail-section-title i {
    color: #4f46e5;
}

.detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.detail-item.full {
    grid-column: 1 / -1;
}

.detail-label {
    font-weight: 600;
    color: #6b7280;
    font-size: 0.875rem;
}

.detail-value {
    color: #111827;
    font-size: 0.95rem;
    padding: 0.5rem;
    background: white;
    border-radius: 4px;
    border: 1px solid #e5e7eb;
}

@media (max-width: 768px) {
    .detail-grid {
        grid-template-columns: 1fr;
    }
}
</style>
