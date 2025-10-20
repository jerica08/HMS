// View Appointment Modal Management
// Handles appointment details display modal

const ViewAppointmentModal = {
    modal: null,
    closeViewModal: null,
    closeViewAppointmentBtn: null,
    currentAppointmentId: null,

    init() {
        // Get modal elements
        this.modal = document.getElementById('viewAppointmentModal');
        this.closeViewModal = document.getElementById('closeViewModal');
        this.closeViewAppointmentBtn = document.getElementById('closeViewAppointmentBtn');

        // Bind event listeners
        this.bindEvents();
    },

    bindEvents() {
        if (!this.modal) return;

        // Close modal events
        this.closeViewModal?.addEventListener('click', () => {
            this.close();
        });

        this.closeViewAppointmentBtn?.addEventListener('click', () => {
            this.close();
        });

        // Click outside to close
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.close();
            }
        });

        // Escape key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal.classList.contains('show')) {
                this.close();
            }
        });
    },

    open(appointmentId) {
        this.currentAppointmentId = appointmentId;
        const detailsBody = document.getElementById('appointmentDetailsBody');
        
        // Show loading state
        if (detailsBody) {
            detailsBody.innerHTML = `
                <div style="text-align: center; padding: 2rem; color: #6b7280;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                    <p>Loading appointment details...</p>
                </div>
            `;
        }
        
        // Show modal
        this.modal.classList.add('show');
        
        // Fetch appointment details
        this.fetchAppointmentDetails(appointmentId);
    },

    close() {
        if (this.modal) {
            this.modal.classList.remove('show');
            this.currentAppointmentId = null;
        }
    },

    fetchAppointmentDetails(appointmentId) {
        fetch(`${getBaseUrl()}doctor/appointment/details/${appointmentId}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.displayAppointmentDetails(data.appointment);
            } else {
                this.showError(data.message || 'Unknown error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showError('Failed to load appointment details. Please try again.');
        });
    },

    displayAppointmentDetails(appointment) {
        const detailsBody = document.getElementById('appointmentDetailsBody');
        if (!detailsBody) return;
        
        detailsBody.innerHTML = `
            <div style="display: grid; gap: 1.5rem;">
                <!-- Appointment Information -->
                <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; border-left: 4px solid #3b82f6;">
                    <h4 style="margin: 0 0 0.75rem 0; color: #1e40af; font-size: 1.1rem;">
                        <i class="fas fa-calendar-check"></i> Appointment Information
                    </h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem;">
                        <div>
                            <strong>Date:</strong><br>
                            <span>${appointment.formatted_date || 'N/A'}</span>
                        </div>
                        <div>
                            <strong>Time:</strong><br>
                            <span>${appointment.formatted_time || 'N/A'}</span>
                        </div>
                        <div>
                            <strong>Type:</strong><br>
                            <span>${appointment.appointment_type || 'N/A'}</span>
                        </div>
                        <div>
                            <strong>Duration:</strong><br>
                            <span>${appointment.duration || '30'} minutes</span>
                        </div>
                        <div>
                            <strong>Status:</strong><br>
                            <span class="badge ${getBadgeClass(appointment.status || 'scheduled')}">
                                ${(appointment.status || 'scheduled').charAt(0).toUpperCase() + (appointment.status || 'scheduled').slice(1)}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Patient Information -->
                <div style="background: #f0fdf4; padding: 1rem; border-radius: 8px; border-left: 4px solid #10b981;">
                    <h4 style="margin: 0 0 0.75rem 0; color: #047857; font-size: 1.1rem;">
                        <i class="fas fa-user"></i> Patient Information
                    </h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem;">
                        <div>
                            <strong>Name:</strong><br>
                            <span>${(appointment.patient_first_name || '') + ' ' + (appointment.patient_last_name || '')}</span>
                        </div>
                        <div>
                            <strong>Patient ID:</strong><br>
                            <span>${appointment.patient_id || 'N/A'}</span>
                        </div>
                        <div>
                            <strong>Age:</strong><br>
                            <span>${appointment.patient_age || 'N/A'}</span>
                        </div>
                        <div>
                            <strong>Gender:</strong><br>
                            <span>${appointment.gender || 'N/A'}</span>
                        </div>
                        <div>
                            <strong>Phone:</strong><br>
                            <span>${appointment.patient_phone || 'N/A'}</span>
                        </div>
                        <div>
                            <strong>Email:</strong><br>
                            <span>${appointment.patient_email || 'N/A'}</span>
                        </div>
                    </div>
                </div>

                <!-- Reason for Visit -->
                <div style="background: #fefce8; padding: 1rem; border-radius: 8px; border-left: 4px solid #eab308;">
                    <h4 style="margin: 0 0 0.75rem 0; color: #a16207; font-size: 1.1rem;">
                        <i class="fas fa-stethoscope"></i> Reason for Visit
                    </h4>
                    <p style="margin: 0; line-height: 1.5;">${appointment.reason || 'General consultation'}</p>
                </div>

                ${appointment.medical_history ? `
                <!-- Medical History -->
                <div style="background: #fdf2f8; padding: 1rem; border-radius: 8px; border-left: 4px solid #ec4899;">
                    <h4 style="margin: 0 0 0.75rem 0; color: #be185d; font-size: 1.1rem;">
                        <i class="fas fa-file-medical"></i> Medical History
                    </h4>
                    <p style="margin: 0; line-height: 1.5;">${appointment.medical_history}</p>
                </div>
                ` : ''}

                ${appointment.emergency_contact_name ? `
                <!-- Emergency Contact -->
                <div style="background: #fef2f2; padding: 1rem; border-radius: 8px; border-left: 4px solid #ef4444;">
                    <h4 style="margin: 0 0 0.75rem 0; color: #dc2626; font-size: 1.1rem;">
                        <i class="fas fa-phone-alt"></i> Emergency Contact
                    </h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem;">
                        <div>
                            <strong>Name:</strong><br>
                            <span>${appointment.emergency_contact_name}</span>
                        </div>
                        <div>
                            <strong>Phone:</strong><br>
                            <span>${appointment.emergency_contact_phone || 'N/A'}</span>
                        </div>
                    </div>
                </div>
                ` : ''}
            </div>
        `;
    },

    showError(message) {
        const detailsBody = document.getElementById('appointmentDetailsBody');
        if (detailsBody) {
            detailsBody.innerHTML = `
                <div style="text-align: center; padding: 2rem; color: #ef4444;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                    <p>Error loading appointment details: ${message}</p>
                </div>
            `;
        }
    }
};

// Global function for backward compatibility
function viewAppointment(appointmentId) {
    ViewAppointmentModal.open(appointmentId);
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    ViewAppointmentModal.init();
});

// Export to global scope
window.ViewAppointmentModal = ViewAppointmentModal;