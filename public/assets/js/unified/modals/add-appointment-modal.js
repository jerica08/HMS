/**
 * Add Appointment Modal Controller
 */

window.AddAppointmentModal = {
    modal: null,
    form: null,
    config: null,
    
    init() {
        this.modal = document.getElementById('newAppointmentModal');
        this.form = document.getElementById('newAppointmentForm');
        this.config = this.getConfig();
        
        if (this.form) {
            this.form.addEventListener('submit', (e) => this.handleSubmit(e));
            
            const dateInput = document.getElementById('appointment_date');
            if (dateInput) {
                dateInput.addEventListener('change', () => {
                    if (dateInput.value) {
                        this.loadAvailableDoctors(dateInput.value);
                    }
                });
            }
        }
        
        AppointmentModalUtils.setupModalCloseHandlers(this.modal, () => this.close());
        
        const scheduleBtn = document.getElementById('scheduleAppointmentBtn');
        if (scheduleBtn) {
            scheduleBtn.addEventListener('click', () => this.open());
        }
    },
    
    getConfig() {
        const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        return { baseUrl: baseUrl.replace(/\/$/, ''), csrfToken, endpoints: { create: `${baseUrl}appointments/create`, update: `${baseUrl}appointments` } };
    },
    
    open() {
        if (this.modal) {
            this.resetForm();
            AppointmentModalUtils.openModal('newAppointmentModal');
            this.loadPatients();
            
            const dateInput = document.getElementById('appointment_date');
            const dateValue = dateInput?.value || new Date().toISOString().split('T')[0];
            if (dateInput && !dateInput.value) dateInput.value = dateValue;
            this.loadAvailableDoctors(dateValue);
        }
    },
    
    close() {
        if (this.modal) {
            AppointmentModalUtils.closeModal('newAppointmentModal');
            this.resetForm();
        }
    },
    
    resetForm() {
        if (this.form) {
            this.form.reset();
            AppointmentModalUtils.clearErrors(this.form, 'appointment_');
        }
    },
    
    async loadPatients() {
        const patientSelect = document.getElementById('appointment_patient');
        if (!patientSelect) return;
        
        try {
            const response = await fetch(`${this.config.baseUrl}/appointments/patients`);
            const data = await response.json();
            if (data.status === 'success') {
                patientSelect.innerHTML = '<option value="">Select Patient...</option>';
                data.data.forEach(patient => {
                    const option = document.createElement('option');
                    option.value = patient.patient_id;
                    option.textContent = `${patient.first_name} ${patient.last_name}`;
                    patientSelect.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error loading patients:', error);
        }
    },
    
    loadAvailableDoctors(date) {
        const doctorSelect = document.getElementById('appointment_doctor');
        const dateHelp = document.getElementById('appointment_date_help');
        if (!doctorSelect) return;
        
        doctorSelect.innerHTML = '<option value="">Loading available doctors...</option>';
        if (dateHelp) dateHelp.textContent = '';
        
        const weekday = this.getWeekdayName(date);
        const url = `${this.config.baseUrl}/appointments/available-doctors?date=${encodeURIComponent(date)}&weekday=${encodeURIComponent(weekday)}`;
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                doctorSelect.innerHTML = '<option value="">Select Doctor...</option>';
                if (data.status === 'success' && Array.isArray(data.data) && data.data.length) {
                    data.data.forEach(doctor => {
                        const option = document.createElement('option');
                        option.value = doctor.staff_id;
                        option.textContent = `${doctor.first_name} ${doctor.last_name}${doctor.specialization ? ' - ' + doctor.specialization : ''}`;
                        doctorSelect.appendChild(option);
                    });
                    if (dateHelp) dateHelp.textContent = 'Doctors listed are available on this date.';
                } else {
                    if (dateHelp) dateHelp.textContent = 'No doctors are available on this date.';
                }
            })
            .catch(error => {
                console.error('Error loading available doctors:', error);
                doctorSelect.innerHTML = '<option value="">Error loading doctors</option>';
                if (dateHelp) dateHelp.textContent = 'Failed to load doctor availability.';
            });
    },
    
    getWeekdayName(dateStr) {
        const d = new Date(dateStr);
        const days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        return isNaN(d.getTime()) ? '' : days[d.getDay()];
    },
    
    async handleSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const data = AppointmentModalUtils.collectFormData(form);
        
        try {
            this.showLoading(true);
            const response = await fetch(this.config.endpoints.create, {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-Token': this.config.csrfToken},
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            const success = !!result.success;
            
            if (success) {
                this.showNotification(result.message || 'Appointment scheduled successfully', 'success');
                this.close();
                if (window.appointmentManager) {
                    window.appointmentManager.refreshAppointments();
                } else {
                    setTimeout(() => location.reload(), 800);
                }
            } else {
                this.showNotification(result.message || 'Failed to save appointment', 'error');
                if (result.errors) {
                    AppointmentModalUtils.displayErrors(result.errors, 'appointment_');
                }
            }
        } catch (error) {
            console.error('Error saving appointment:', error);
            this.showNotification('Failed to save appointment. Please try again.', 'error');
        } finally {
            this.showLoading(false);
        }
    },
    
    showLoading(show) {
        const submitBtn = document.getElementById('saveAppointmentBtn');
        if (submitBtn) {
            submitBtn.disabled = show;
            submitBtn.innerHTML = show 
                ? '<i class="fas fa-spinner fa-spin"></i> Saving...' 
                : '<i class="fas fa-calendar-check"></i> Schedule Appointment';
        }
    },
    
    showNotification(message, type) {
        if (window.appointmentManager) {
            window.appointmentManager.showNotification(message, type);
        } else if (typeof showAppointmentsNotification === 'function') {
            showAppointmentsNotification(message, type);
        } else {
            alert(message);
        }
    }
};

// Global functions for backward compatibility
window.openNewAppointmentModal = () => window.AddAppointmentModal?.open();
window.closeNewAppointmentModal = () => window.AddAppointmentModal?.close();

