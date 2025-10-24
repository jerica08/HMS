/**
 * Add Staff Modal Controller
 */

window.AddStaffModal = {
    modal: null,
    form: null,
    
    init() {
        this.modal = document.getElementById('addStaffModal');
        this.form = document.getElementById('addStaffForm');
        
        if (this.form) {
            this.form.addEventListener('submit', (e) => this.handleSubmit(e));
            // Toggle role-specific fields
            const designationEl = document.getElementById('designation');
            designationEl?.addEventListener('change', () => this.toggleRoleFields());
            // Initialize visibility once
            this.toggleRoleFields();
        }
        
        // Close modal on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal && !this.modal.getAttribute('aria-hidden')) {
                this.close();
            }
        });
        
        // Close modal on background click
        if (this.modal) {
            this.modal.addEventListener('click', (e) => {
                if (e.target === this.modal) {
                    this.close();
                }
            });
        }
    },
    
    open() {
        if (this.modal) {
            this.modal.classList.add('active');
            this.modal.setAttribute('aria-hidden', 'false');
            this.resetForm();
            
            // Set default date joined to today
            const dateJoinedField = document.getElementById('date_joined');
            if (dateJoinedField && !dateJoinedField.value) {
                dateJoinedField.value = new Date().toISOString().split('T')[0];
            }
        }
    },
    
    close() {
        if (this.modal) {
            this.modal.classList.remove('active');
            this.modal.setAttribute('aria-hidden', 'true');
            this.resetForm();
        }
    },
    
    resetForm() {
        if (this.form) {
            this.form.reset();
            this.clearErrors();
            this.toggleRoleFields();
        }
    },
    
    toggleRoleFields() {
        const designation = document.getElementById('designation')?.value || '';
        const isDoctor = designation === 'doctor';
        const isNurse = designation === 'nurse';
        const isAccountant = designation === 'accountant';
        const isLaboratorist = designation === 'laboratorist';
        const isPharmacist = designation === 'pharmacist';
        const docFields = document.getElementById('doctorFields');
        const nurseFields = document.getElementById('nurseFields');
        const accountantFields = document.getElementById('accountantFields');
        const laboratoristFields = document.getElementById('laboratoristFields');
        const pharmacistFields = document.getElementById('pharmacistFields');
        if (docFields) {
            docFields.style.display = isDoctor ? 'block' : 'none';
        }
        if (nurseFields) {
            nurseFields.style.display = isNurse ? 'block' : 'none';
        }
        if (accountantFields) {
            accountantFields.style.display = isAccountant ? 'block' : 'none';
        }
        if (laboratoristFields) {
            laboratoristFields.style.display = isLaboratorist ? 'block' : 'none';
        }
        if (pharmacistFields) {
            pharmacistFields.style.display = isPharmacist ? 'block' : 'none';
        }
    },

    clearErrors() {
        const errorElements = this.form?.querySelectorAll('[id^="err_"]');
        errorElements?.forEach(el => el.textContent = '');
    },
    
    async handleSubmit(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('saveStaffBtn');
        const originalText = submitBtn?.innerHTML;
        
        try {
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            }
            
            this.clearErrors();
            
            const formData = this.collectFormData();

            // Quick client-side validation for required fields
            const clientErrors = {};
            if (!formData.employee_id || String(formData.employee_id).trim().length < 3) {
                clientErrors.employee_id = 'Employee ID is required (min 3 characters).';
            }
            if (!formData.first_name || String(formData.first_name).trim().length < 2) {
                clientErrors.first_name = 'First name is required (min 2 characters).';
            }
            if (!formData.designation) {
                clientErrors.designation = 'Designation is required.';
            }
            if (formData.designation === 'doctor') {
                if (!formData.doctor_specialization || String(formData.doctor_specialization).trim().length < 2) {
                    clientErrors.doctor_specialization = 'Doctor specialization is required.';
                }
            }
            if (formData.designation === 'nurse') {
                if (!formData.nurse_license_no || String(formData.nurse_license_no).trim().length < 2) {
                    clientErrors.nurse_license_no = 'Nurse license number is required.';
                }
            }
            if (formData.designation === 'accountant') {
                if (!formData.accountant_license_no || String(formData.accountant_license_no).trim().length < 2) {
                    clientErrors.accountant_license_no = 'Accountant license number is required.';
                }
            }
            if (formData.designation === 'laboratorist') {
                if (!formData.laboratorist_license_no || String(formData.laboratorist_license_no).trim().length < 2) {
                    clientErrors.laboratorist_license_no = 'Laboratorist license number is required.';
                }
            }
            if (formData.designation === 'pharmacist') {
                if (!formData.pharmacist_license_no || String(formData.pharmacist_license_no).trim().length < 2) {
                    clientErrors.pharmacist_license_no = 'Pharmacist license number is required.';
                }
            }
            if (Object.keys(clientErrors).length) {
                this.displayErrors(clientErrors);
                StaffUtils.showNotification('Please fix the highlighted errors.', 'warning');
                return;
            }
            
            const response = await StaffUtils.makeRequest(
                StaffConfig.getUrl(StaffConfig.endpoints.staffCreate),
                {
                    method: 'POST',
                    body: JSON.stringify(formData)
                }
            );
            
            if (response.status === 'success') {
                StaffUtils.showNotification('Staff member added successfully', 'success');
                this.close();
                
                // Refresh staff list
                if (window.staffManager) {
                    window.staffManager.refresh();
                }
            } else {
                // Gracefully handle validation and known server errors
                if (response.errors) {
                    this.displayErrors(response.errors);
                    StaffUtils.showNotification(response.message || 'Please fix the highlighted errors.', 'warning');
                    return; // Don't throw for validation; let user correct
                }
                // Unknown error payload
                throw new Error(response.message || `Request failed (status ${response.statusCode || 'unknown'})`);
            }
        } catch (error) {
            console.error('Error adding staff:', error);
            StaffUtils.showNotification('Failed to add staff: ' + error.message, 'error');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }
    },
    
    collectFormData() {
        const formData = new FormData(this.form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        return data;
    },
    
    displayErrors(errors) {
        for (const [rawField, message] of Object.entries(errors)) {
            // Map backend field names to UI field IDs
            const fieldMap = {
                dob: 'date_of_birth',
                doctor_specialization: 'doctor_specialization',
                doctor_license_no: 'doctor_license_no',
                doctor_consultation_fee: 'doctor_consultation_fee',
                nurse_license_no: 'nurse_license_no',
                nurse_specialization: 'nurse_specialization',
                accountant_license_no: 'accountant_license_no',
                laboratorist_license_no: 'laboratorist_license_no',
                laboratorist_specialization: 'laboratorist_specialization',
                lab_room_no: 'lab_room_no',
                pharmacist_license_no: 'pharmacist_license_no',
                pharmacist_specialization: 'pharmacist_specialization'
            };
            const field = fieldMap[rawField] || rawField;
            const errorElement = document.getElementById(`err_${field}`);
            if (errorElement) {
                errorElement.textContent = Array.isArray(message) ? message[0] : message;
            }
        }
    }
};

// Global functions for backward compatibility
window.openAddStaffModal = function() {
    if (window.AddStaffModal) {
        window.AddStaffModal.open();
    }
};

window.closeAddStaffModal = function() {
    if (window.AddStaffModal) {
        window.AddStaffModal.close();
    }
};
