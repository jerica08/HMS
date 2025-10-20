// New Appointment Modal Management
// Handles schedule appointment modal functionality

const NewAppointmentModal = {
    modal: null,
    form: null,
    scheduleBtn: null,
    closeModal: null,
    cancelBtn: null,
    saveBtn: null,

    init() {
        // Get modal elements
        this.modal = document.getElementById('scheduleModal');
        this.form = document.getElementById('scheduleForm');
        this.scheduleBtn = document.getElementById('scheduleAppointmentBtn');
        this.closeModal = document.getElementById('closeModal');
        this.cancelBtn = document.getElementById('cancelBtn');
        this.saveBtn = document.getElementById('saveBtn');

        // Bind event listeners
        this.bindEvents();
    },

    bindEvents() {
        if (!this.modal || !this.form) return;

        // Open modal
        this.scheduleBtn?.addEventListener('click', () => {
            this.open();
        });

        // Close modal events
        this.closeModal?.addEventListener('click', () => {
            this.close();
        });

        this.cancelBtn?.addEventListener('click', () => {
            this.close();
        });

        // Click outside to close
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.close();
            }
        });

        // Handle form submission
        this.saveBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            this.handleSubmit();
        });

        // Escape key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal.classList.contains('show')) {
                this.close();
            }
        });
    },

    open() {
        if (this.modal) {
            this.modal.classList.add('show');
        }
    },

    close() {
        if (this.modal) {
            this.modal.classList.remove('show');
            this.form?.reset();
        }
    },

    handleSubmit() {
        if (!this.form || !this.form.checkValidity()) {
            showNotification('Please fill in all required fields.', 'error');
            return;
        }

        const formData = new FormData(this.form);
        const data = {
            patient_id: formData.get('patient_id'),
            date: formData.get('appointmentDate'),
            time: formData.get('appointmentTime'),
            type: formData.get('appointmentType'),
            reason: formData.get('appointmentReason'),
            duration: formData.get('appointmentDuration'),
            csrf_token: getCsrfToken()
        };

        // Show loading state
        const originalText = this.saveBtn.textContent;
        this.saveBtn.textContent = 'Scheduling...';
        this.saveBtn.disabled = true;

        fetch(`${getBaseUrl()}doctor/schedule-appointment`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showNotification('Appointment scheduled successfully!', 'success');
                this.close();
                // Refresh appointments if the function exists
                if (typeof window.AppointmentManager?.refreshAppointments === 'function') {
                    window.AppointmentManager.refreshAppointments();
                }
            } else {
                showNotification('Error scheduling appointment: ' + (result.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while scheduling the appointment.', 'error');
        })
        .finally(() => {
            // Reset button state
            this.saveBtn.textContent = originalText;
            this.saveBtn.disabled = false;
        });
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    NewAppointmentModal.init();
});

// Export to global scope
window.NewAppointmentModal = NewAppointmentModal;