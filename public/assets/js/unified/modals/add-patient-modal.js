/**
 * Add Patient Modal Controller
 * Handles the add patient modal functionality
 */

const AddPatientModal = {
    modal: null,
    form: null,
    doctorsCache: null,

    /**
     * Initialize the modal
     */
    init() {
        this.modal = document.getElementById('addPatientModal');
        this.form = document.getElementById('addPatientForm');
        
        if (!this.modal || !this.form) return;
        
        this.bindEvents();
    },

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Form submission
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        
        // Close modal when clicking outside
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.close();
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal.style.display === 'flex') {
                this.close();
            }
        });
    },

    /**
     * Open the modal
     */
    async open() {
        if (this.modal) {
            this.modal.style.display = 'flex';
            this.resetForm();
            await this.loadDoctors();
        }
    },

    /**
     * Close the modal
     */
    close() {
        if (this.modal) {
            this.modal.style.display = 'none';
            this.resetForm();
        }
    },

    /**
     * Reset form to initial state
     */
    resetForm() {
        if (this.form) {
            this.form.reset();
            
            // Clear validation states
            this.form.querySelectorAll('.is-invalid').forEach(el => {
                el.classList.remove('is-invalid');
            });
            this.form.querySelectorAll('.invalid-feedback').forEach(el => {
                el.textContent = '';
            });
        }
    },

    /**
     * Load available doctors
     */
    async loadDoctors() {
        const doctorSelect = document.getElementById('assigned_doctor');
        if (!doctorSelect) return;

        // Only load doctors for roles that can assign them
        if (!['admin', 'receptionist', 'it_staff'].includes(PatientConfig.userRole)) {
            return;
        }

        try {
            // Always load via AJAX to ensure fresh data
            doctorSelect.innerHTML = '<option value="">Loading doctors...</option>';

            console.log('Loading doctors from:', PatientConfig.getUrl(PatientConfig.endpoints.doctorsApi));

            const response = await PatientUtils.makeRequest(
                PatientConfig.getUrl(PatientConfig.endpoints.doctorsApi)
            );

            console.log('Doctors response:', response);

            if (response.status === 'success') {
                this.doctorsCache = response.data || [];
                console.log('Doctors loaded:', this.doctorsCache);
                this.populateDoctorsSelect(this.doctorsCache);
            } else {
                throw new Error(response.message || 'Failed to load doctors');
            }
        } catch (error) {
            console.error('Error loading doctors:', error);
            doctorSelect.innerHTML = '<option value="">Failed to load doctors</option>';
        }
    },

    /**
     * Populate doctors select dropdown
     */
    populateDoctorsSelect(doctors) {
        const doctorSelect = document.getElementById('assigned_doctor');
        if (!doctorSelect) return;

        console.log('Populating doctors dropdown with:', doctors);

        doctorSelect.innerHTML = '<option value="">Select Doctor (Optional)</option>';
        
        if (!doctors || doctors.length === 0) {
            console.log('No doctors to populate');
            doctorSelect.innerHTML = '<option value="">No doctors available</option>';
            return;
        }
        
        doctors.forEach((doctor, index) => {
            console.log(`Adding doctor ${index}:`, doctor);
            const option = document.createElement('option');
            option.value = doctor.staff_id || doctor.id;
            option.textContent = `${doctor.first_name} ${doctor.last_name}${doctor.department ? ' - ' + doctor.department : ''}`;
            doctorSelect.appendChild(option);
        });

        console.log('Doctors dropdown populated with', doctorSelect.options.length - 1, 'doctors');
    },

    /**
     * Handle form submission
     */
    async handleSubmit(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('savePatientBtn');
        const originalText = submitBtn.innerHTML;
        
        try {
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            
            // Collect form data
            const formData = this.collectFormData();
            
            // Validate form data
            const errors = this.validateFormData(formData);
            if (Object.keys(errors).length > 0) {
                PatientUtils.displayFormErrors(errors, this.form);
                return;
            }
            
            // Submit form
            const response = await PatientUtils.makeRequest(
                PatientConfig.getUrl(PatientConfig.endpoints.patientCreate),
                {
                    method: 'POST',
                    body: JSON.stringify(formData)
                }
            );
            
            if (response.status === 'success') {
                PatientUtils.showNotification('Patient added successfully!', 'success');
                this.close();
                
                // Refresh patient list
                if (window.patientManager) {
                    window.patientManager.refresh();
                }
            } else {
                throw new Error(response.message || 'Failed to add patient');
            }
            
        } catch (error) {
            console.error('Error adding patient:', error);
            PatientUtils.showNotification('Failed to add patient: ' + error.message, 'error');
        } finally {
            // Restore button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    },

    /**
     * Collect form data
     */
    collectFormData() {
        const formData = new FormData(this.form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        return data;
    },

    /**
     * Validate form data
     */
    validateFormData(data) {
        const rules = {
            first_name: { required: true, label: 'First Name' },
            last_name: { required: true, label: 'Last Name' },
            gender: { required: true, label: 'Gender' },
            date_of_birth: { required: true, label: 'Date of Birth' },
            civil_status: { required: true, label: 'Civil Status' },
            phone: { required: true, label: 'Phone Number' },
            address: { required: true, label: 'Address' },
            province: { required: true, label: 'Province' },
            city: { required: true, label: 'City' },
            barangay: { required: true, label: 'Barangay' },
            zip_code: { required: true, label: 'ZIP Code' },
            emergency_contact_name: { required: true, label: 'Emergency Contact Name' },
            emergency_contact_phone: { required: true, label: 'Emergency Contact Phone' },
            email: { email: true, label: 'Email' }
        };
        
        return PatientUtils.validateForm(data, rules);
    }
};

// Export to global scope
window.AddPatientModal = AddPatientModal;

// Global function for close button
window.closeAddPatientModal = function() {
    if (window.AddPatientModal) {
        window.AddPatientModal.close();
    }
};
