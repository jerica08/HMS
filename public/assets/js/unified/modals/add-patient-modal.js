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

        // Initialize inpatient/outpatient view based on default patient type
        this.updateInpatientVisibility();
    },

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Form submission
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));

        // Patient type change - toggle inpatient-only fields
        const patientTypeSelect = document.getElementById('patient_type');
        if (patientTypeSelect) {
            patientTypeSelect.addEventListener('change', () => this.updateInpatientVisibility());
        }

        // Date of birth change - update age display and pediatric logic
        const dobInput = document.getElementById('date_of_birth');
        if (dobInput) {
            dobInput.addEventListener('change', () => this.handleDobChange());
        }

        // Weight/height change - update BMI
        const weightInput = document.getElementById('weight_kg');
        const heightInput = document.getElementById('height_cm');
        if (weightInput) {
            weightInput.addEventListener('input', () => this.updateBmi());
        }
        if (heightInput) {
            heightInput.addEventListener('input', () => this.updateBmi());
        }
        
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

            // Reset inpatient visibility to match default patient type
            this.updateInpatientVisibility();
        }
    },

    /**
     * Show/hide inpatient-only sections based on selected patient type
     */
    updateInpatientVisibility() {
        const patientTypeSelect = document.getElementById('patient_type');
        const typeValue = (patientTypeSelect ? patientTypeSelect.value : 'Outpatient') || 'Outpatient';
        const normalizedType = typeValue.toLowerCase();

        const inpatientSections = this.form ? this.form.querySelectorAll('[data-section="inpatient-only"]') : [];
        const isInpatientLike = normalizedType === 'inpatient' || normalizedType === 'emergency';

        inpatientSections.forEach(section => {
            const inputs = section.querySelectorAll('input, select, textarea');
            if (isInpatientLike) {
                section.style.display = '';
                // For inpatient, keep required attributes as defined in HTML
                inputs.forEach(input => {
                    if (input.dataset.originalRequired === 'true') {
                        input.required = true;
                    }
                });
            } else {
                section.style.display = 'none';
                // For outpatient, remove required from inpatient-only fields and clear errors
                inputs.forEach(input => {
                    if (input.required) {
                        // Remember which fields were originally required so we can restore later
                        if (!input.dataset.originalRequired) {
                            input.dataset.originalRequired = 'true';
                        }
                        input.required = false;
                    }
                    // Clear values for hidden inpatient fields
                    input.value = '';
                    const errEl = this.form.querySelector(`#err_${input.name}`);
                    if (errEl) {
                        errEl.textContent = '';
                    }
                    input.classList.remove('is-invalid');
                });
            }
        });
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
     * Handle DOB change: update age display and filter doctors for newborns
     */
    handleDobChange() {
        const dobInput = document.getElementById('date_of_birth');
        const ageDisplay = document.getElementById('age_display');
        if (!dobInput || !ageDisplay) return;

        const dobValue = dobInput.value;
        if (!dobValue) {
            ageDisplay.value = '';
            this.applyPediatricLogic(null);
            return;
        }

        const ageYears = this.calculateAgeYears(dobValue);
        if (ageYears === null) {
            ageDisplay.value = '';
        } else if (ageYears < 1) {
            ageDisplay.value = 'Newborn / < 1 year';
        } else {
            ageDisplay.value = `${ageYears} year${ageYears !== 1 ? 's' : ''}`;
        }

        this.applyPediatricLogic(ageYears);
    },

    /**
     * Calculate age in years from a date string (YYYY-MM-DD)
     */
    calculateAgeYears(dob) {
        try {
            const dobDate = new Date(dob);
            if (!dobDate || isNaN(dobDate.getTime())) return null;
            const today = new Date();
            let age = today.getFullYear() - dobDate.getFullYear();
            const m = today.getMonth() - dobDate.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < dobDate.getDate())) {
                age--;
            }
            if (age < 0) return null;
            return age;
        } catch (e) {
            console.error('Invalid DOB for age calculation', e);
            return null;
        }
    },

    /**
     * Update BMI when weight or height changes
     */
    updateBmi() {
        const weightInput = document.getElementById('weight_kg');
        const heightInput = document.getElementById('height_cm');
        const bmiInput = document.getElementById('bmi');
        if (!weightInput || !heightInput || !bmiInput) return;

        const weight = parseFloat(weightInput.value);
        const heightCm = parseFloat(heightInput.value);
        if (!weight || !heightCm || weight <= 0 || heightCm <= 0) {
            bmiInput.value = '';
            return;
        }

        const heightM = heightCm / 100.0;
        const bmi = weight / (heightM * heightM);
        if (!isFinite(bmi)) {
            bmiInput.value = '';
            return;
        }
        bmiInput.value = bmi.toFixed(2);
    },

    /**
     * Apply pediatric logic: for newborns, filter doctors to pediatricians and show previous pediatrician field
     */
    applyPediatricLogic(ageYears) {
        const doctorSelect = document.getElementById('assigned_doctor');
        const pastPedWrapper = document.getElementById('pastPediatricianWrapper');

        const isNewborn = ageYears !== null && ageYears < 1;
        const isPediatricAge = ageYears !== null && ageYears < 18;

        // Toggle previous pediatrician field visibility only for newborns
        if (pastPedWrapper) {
            pastPedWrapper.style.display = isNewborn ? '' : 'none';
        }

        if (!doctorSelect) return;

        // Cache all doctor options the first time (including department from data attribute)
        if (!this.doctorsCache) {
            this.doctorsCache = Array.from(doctorSelect.options).map(opt => ({
                value: opt.value,
                text: opt.textContent,
                department: opt.getAttribute('data-department') || ''
            }));
        }

        // If not pediatric age, restore full list
        if (!isPediatricAge) {
            this.restoreDoctorOptions();
            return;
        }

        // Filter to pediatric doctors for pediatric-age patients (< 18 years)
        const pediatricKeywords = ['pedia', 'pediatric', 'pediatrics', 'neonatal', 'neonatology'];
        const filtered = this.doctorsCache.filter(opt => {
            const text = (opt.text || '').toLowerCase();

            // Expect labels like "Name - Specialization"
            const parts = text.split('-');
            const specialization = parts[1] ? parts[1].trim() : '';
            const isPediatricSpecialization = ['pediatrics', 'pediatrician', 'pediatric'].includes(specialization);

            return (
                opt.value === '' ||
                isPediatricSpecialization ||
                pediatricKeywords.some(k => text.includes(k))
            );
        });

        // If no pediatric doctors found
        if (filtered.length <= 1) {
            // For newborns, do NOT fall back to adult specialists
            if (isNewborn) {
                doctorSelect.innerHTML = '';
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = 'No pediatric doctors available';
                opt.disabled = true;
                opt.selected = true;
                doctorSelect.appendChild(opt);
                return;
            }

            // For older pediatric-age patients, fall back to full list
            this.restoreDoctorOptions();
            return;
        }

        doctorSelect.innerHTML = '';
        filtered.forEach(optData => {
            const opt = document.createElement('option');
            opt.value = optData.value;
            opt.textContent = optData.text;
            doctorSelect.appendChild(opt);
        });
    },

    restoreDoctorOptions() {
        const doctorSelect = document.getElementById('assigned_doctor');
        if (!doctorSelect || !this.doctorsCache) return;

        doctorSelect.innerHTML = '';
        this.doctorsCache.forEach(optData => {
            const opt = document.createElement('option');
            opt.value = optData.value;
            opt.textContent = optData.text;
            if (optData.department) {
                opt.setAttribute('data-department', optData.department);
            }
            doctorSelect.appendChild(opt);
        });
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
        const typeValue = (data.patient_type || 'Outpatient').toLowerCase();

        // Base rules for all patients (outpatient + inpatient)
        const rules = {
            first_name: { required: true, label: 'First Name' },
            last_name: { required: true, label: 'Last Name' },
            gender: { required: true, label: 'Gender' },
            date_of_birth: { required: true, label: 'Date of Birth' },
            civil_status: { required: true, label: 'Civil Status' },
            phone: { required: true, label: 'Phone Number' },
            address: { required: true, label: 'Address' },
            email: { email: true, label: 'Email' }
        };

        // Inpatient/Emergency require full details
        if (typeValue === 'inpatient' || typeValue === 'emergency') {
            Object.assign(rules, {
                province: { required: true, label: 'Province' },
                city: { required: true, label: 'City' },
                barangay: { required: true, label: 'Barangay' },
                zip_code: { required: true, label: 'ZIP Code' },
                emergency_contact_name: { required: true, label: 'Emergency Contact Name' },
                emergency_contact_phone: { required: true, label: 'Emergency Contact Phone' }
            });
        }

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
