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
                dob: 'date_of_birth'
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
