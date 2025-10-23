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
            if (e.key === 'Escape' && this.modal.classList.contains('active')) {
                this.close();
            }
        });
    },

    open() {
        if (this.modal) {
            this.modal.classList.add('active');
            this.modal.style.display = 'flex';
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
            
            // Load patients and doctors when modal opens
            this.loadPatients();
            this.loadDoctors();
        }
    },

    close() {
        if (this.modal) {
            this.modal.classList.remove('active');
            this.modal.style.display = 'none';
            document.body.style.overflow = ''; // Restore scrolling
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

        fetch(`${getBaseUrl()}appointments/create`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                showNotification('Appointment scheduled successfully!', 'success');
                this.close();
                // Refresh appointments if the function exists
                if (typeof window.AppointmentManager?.refreshAppointments === 'function') {
                    window.AppointmentManager.refreshAppointments();
                }
                // Refresh page if no appointment manager
                else {
                    window.location.reload();
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
    },

    loadPatients() {
        const patientSelect = document.getElementById('patientSelect');
        if (!patientSelect) return;

        patientSelect.innerHTML = '<option value="">Loading patients...</option>';

        fetch(`${getBaseUrl()}patients/api`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            patientSelect.innerHTML = '<option value="">Select Patient</option>';
            
            if (data.status === 'success' && data.data) {
                data.data.forEach(patient => {
                    const option = document.createElement('option');
                    option.value = patient.patient_id;
                    option.textContent = `${patient.full_name} - ID: ${patient.patient_id}`;
                    patientSelect.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error loading patients:', error);
            patientSelect.innerHTML = '<option value="">Error loading patients</option>';
        });
    },

    loadDoctors() {
        const doctorSelect = document.getElementById('doctorSelect');
        if (!doctorSelect) return; // Only load if doctor select exists (admin/receptionist)

        doctorSelect.innerHTML = '<option value="">Loading doctors...</option>';

        fetch(`${getBaseUrl()}staff/api?role=doctor`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            doctorSelect.innerHTML = '<option value="">Select Doctor</option>';
            
            if (data.status === 'success' && data.data) {
                data.data.forEach(doctor => {
                    const option = document.createElement('option');
                    option.value = doctor.staff_id;
                    option.textContent = `Dr. ${doctor.first_name} ${doctor.last_name} - ${doctor.department}`;
                    doctorSelect.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error loading doctors:', error);
            doctorSelect.innerHTML = '<option value="">Error loading doctors</option>';
        });
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    NewAppointmentModal.init();
});

// Export to global scope
window.NewAppointmentModal = NewAppointmentModal;