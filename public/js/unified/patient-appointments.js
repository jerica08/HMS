// Patient-Appointment Connection JavaScript
// Handles patient appointment interactions

const PatientAppointments = {
    init() {
        this.bindPatientLinks();
    },

    bindPatientLinks() {
        // Handle patient link clicks to show patient appointments
        document.addEventListener('click', (e) => {
            if (e.target.closest('.patient-link')) {
                e.preventDefault();
                const link = e.target.closest('.patient-link');
                const url = new URL(link.href);
                const patientId = url.searchParams.get('patient_id');
                
                if (patientId) {
                    this.showPatientAppointments(patientId);
                }
            }
        });
    },

    showPatientAppointments(patientId) {
        // Create modal to show patient appointments
        const modal = this.createAppointmentModal();
        const modalBody = modal.querySelector('.modal-body');
        
        modalBody.innerHTML = `
            <div class="loading-state" style="text-align: center; padding: 2rem;">
                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #6366f1; margin-bottom: 1rem;"></i>
                <p style="color: #6b7280;">Loading patient appointments...</p>
            </div>
        `;
        
        document.body.appendChild(modal);
        modal.classList.add('active');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Fetch patient appointments
        this.fetchPatientAppointments(patientId, modalBody);
    },

    fetchPatientAppointments(patientId, container) {
        fetch(`${getBaseUrl()}appointments/patient/${patientId}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                this.displayPatientAppointments(data.data, container);
            } else {
                this.showError(data.message || 'Failed to load appointments', container);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showError('Network error occurred', container);
        });
    },

    displayPatientAppointments(appointments, container) {
        if (!appointments || appointments.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; padding: 2rem; color: #6b7280;">
                    <i class="fas fa-calendar-times" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                    <p>No appointments found for this patient.</p>
                </div>
            `;
            return;
        }

        const patientInfo = appointments[0]; // Get patient info from first appointment
        
        container.innerHTML = `
            <div style="margin-bottom: 1.5rem; padding: 1rem; background: #f8fafc; border-radius: 8px; border-left: 4px solid #3b82f6;">
                <h4 style="margin: 0 0 0.5rem 0; color: #1e40af;">
                    <i class="fas fa-user"></i> ${patientInfo.patient_full_name}
                </h4>
                <p style="margin: 0; color: #6b7280;">
                    ID: ${patientInfo.patient_id} | Age: ${patientInfo.patient_age} | Phone: ${patientInfo.patient_phone || 'N/A'}
                </p>
            </div>
            
            <div style="max-height: 400px; overflow-y: auto;">
                <table class="table" style="margin: 0;">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Doctor</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${appointments.map(appointment => `
                            <tr>
                                <td>
                                    <strong>${appointment.formatted_date || 'N/A'}</strong><br>
                                    <small>${appointment.formatted_time || 'N/A'}</small>
                                </td>
                                <td>
                                    <strong>Dr. ${appointment.doctor_name || 'N/A'}</strong><br>
                                    <small>${appointment.doctor_department || 'N/A'}</small>
                                </td>
                                <td>${appointment.appointment_type || 'N/A'}</td>
                                <td>
                                    <span class="badge ${this.getBadgeClass(appointment.status)}">
                                        ${(appointment.status || 'scheduled').charAt(0).toUpperCase() + (appointment.status || 'scheduled').slice(1)}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-primary btn-sm" onclick="viewAppointment(${appointment.appointment_id})">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    },

    showError(message, container) {
        container.innerHTML = `
            <div style="text-align: center; padding: 2rem; color: #ef4444;">
                <i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                <p>Error: ${message}</p>
            </div>
        `;
    },

    getBadgeClass(status) {
        switch((status || '').toLowerCase()) {
            case 'completed': return 'badge-success';
            case 'in-progress': return 'badge-info';
            case 'cancelled': return 'badge-danger';
            case 'no-show': return 'badge-warning';
            default: return 'badge-info';
        }
    },

    createAppointmentModal() {
        const modal = document.createElement('div');
        modal.className = 'hms-modal-overlay';
        modal.innerHTML = `
            <div class="hms-modal" style="max-width: 800px;">
                <div class="hms-modal-header">
                    <h3 class="hms-modal-title">
                        <i class="fas fa-calendar-alt"></i>
                        Patient Appointments
                    </h3>
                    <button class="close-btn">&times;</button>
                </div>
                <div class="hms-modal-body modal-body">
                    <!-- Content will be loaded here -->
                </div>
                <div class="hms-modal-actions">
                    <button type="button" class="action-btn secondary close-modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
            </div>
        `;

        // Bind close events
        modal.querySelector('.close-btn').addEventListener('click', () => {
            this.closeModal(modal);
        });
        
        modal.querySelector('.close-modal').addEventListener('click', () => {
            this.closeModal(modal);
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                this.closeModal(modal);
            }
        });

        return modal;
    },

    closeModal(modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
        document.body.style.overflow = '';
        setTimeout(() => {
            if (modal.parentNode) {
                modal.parentNode.removeChild(modal);
            }
        }, 300);
    }
};

// Helper function for base URL
function getBaseUrl() {
    const baseUrlMeta = document.querySelector('meta[name="base-url"]');
    return baseUrlMeta ? baseUrlMeta.getAttribute('content') : '';
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    PatientAppointments.init();
});

// Export to global scope
window.PatientAppointments = PatientAppointments;
