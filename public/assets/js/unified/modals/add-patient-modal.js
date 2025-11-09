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
            this.modal.removeAttribute('hidden');
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
            this.modal.setAttribute('hidden', '');
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
        if (!doctorSelect) {
            console.log('Doctor select element not found');
            return;
        }

        // Only load doctors for roles that can assign them
        if (!['admin', 'receptionist', 'it_staff'].includes(PatientConfig.userRole)) {
            console.log('User role does not allow doctor assignment:', PatientConfig.userRole);
            return;
        }

        console.log('Loading doctors for user role:', PatientConfig.userRole);

        // Check if we already have doctors from PHP
        const existingOptions = doctorSelect.querySelectorAll('option');
        console.log('Existing options:', Array.from(existingOptions).map(opt => ({ value: opt.value, text: opt.textContent })));
        
        const hasRealDoctors = Array.from(existingOptions).some(option => 
            option.value !== "" && 
            option.textContent !== "No doctors available" &&
            option.textContent !== "Loading doctors..."
        );

        if (hasRealDoctors) {
            console.log('Doctors already loaded from PHP');
            // Update the first option text if needed
            const firstOption = doctorSelect.querySelector('option[value=""]');
            if (firstOption && firstOption.textContent.includes('Loading')) {
                firstOption.textContent = "Select Doctor (Optional)";
            }
            return;
        }

        console.log('No doctors found in PHP, showing no doctors available');
        doctorSelect.innerHTML = '<option value="">No doctors available</option>';
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
