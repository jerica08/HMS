/**
 * Edit Staff Modal Controller
 */

window.EditStaffModal = {
    modal: null,
    form: null,
    
    init() {
        this.modal = document.getElementById('editStaffModal');
        this.form = document.getElementById('editStaffForm');
        
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
    
    async open(staffId) {
        if (!staffId) {
            StaffUtils.showNotification('Staff ID is required', 'error');
            return;
        }
        
        if (this.modal) {
            this.modal.classList.add('active');
            this.modal.setAttribute('aria-hidden', 'false');
            this.clearErrors();
            
            try {
                await this.loadStaffDetails(staffId);
            } catch (error) {
                console.error('Error loading staff details:', error);
                StaffUtils.showNotification('Failed to load staff details', 'error');
                this.close();
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
        const errorElements = this.form?.querySelectorAll('[id^="e_err_"]');
        errorElements?.forEach(el => el.textContent = '');
    },
    
    async loadStaffDetails(staffId) {
        try {
            const response = await StaffUtils.makeRequest(
                StaffConfig.getUrl(`${StaffConfig.endpoints.staffGet}/${staffId}`)
            );
            
            if (response.status === 'success' && response.data) {
                this.populateForm(response.data);
            } else {
                throw new Error(response.message || 'Failed to load staff details');
            }
        } catch (error) {
            console.error('Error loading staff details:', error);
            throw error;
        }
    },
    
    populateForm(staff) {
        const fields = {
            'e_staff_id': staff.staff_id || '',
            'e_employee_id': staff.employee_id || '',
            'e_first_name': staff.first_name || '',
            'e_last_name': staff.last_name || '',
            'e_gender': staff.gender || '',
            'e_date_of_birth': staff.date_of_birth || '',
            'e_contact_no': staff.contact_no || staff.phone || '',
            'e_email': staff.email || '',
            'e_department': staff.department || '',
            'e_designation': staff.role || staff.designation || '',
            'e_date_joined': staff.date_joined || '',
            'e_status': staff.status || 'active',
            'e_address': staff.address || ''
        };
        
        for (const [fieldId, value] of Object.entries(fields)) {
            const element = document.getElementById(fieldId);
            if (element) {
                element.value = value;
            }
        }
    },
    
    async handleSubmit(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('updateStaffBtn');
        const originalText = submitBtn?.innerHTML;
        
        try {
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
            }
            
            this.clearErrors();
            
            const formData = this.collectFormData();
            
            const response = await StaffUtils.makeRequest(
                StaffConfig.getUrl(StaffConfig.endpoints.staffUpdate),
                {
                    method: 'POST',
                    body: JSON.stringify(formData)
                }
            );
            
            if (response.status === 'success' || response.success === true || response.ok === true) {
                StaffUtils.showNotification('Staff member updated successfully', 'success');
                this.close();
                
                // Refresh staff list
                if (window.staffManager) {
                    window.staffManager.refresh();
                }
            } else {
                if (response.errors) {
                    this.displayErrors(response.errors);
                }
                throw new Error(response.message || 'Failed to update staff member');
            }
        } catch (error) {
            console.error('Error updating staff:', error);
            StaffUtils.showNotification('Failed to update staff: ' + error.message, 'error');
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
        for (const [field, message] of Object.entries(errors)) {
            const errorElement = document.getElementById(`e_err_${field}`);
            if (errorElement) {
                errorElement.textContent = Array.isArray(message) ? message[0] : message;
            }
        }
    }
};

// Global functions for backward compatibility
window.openStaffEditModal = function(staffId) {
    if (window.EditStaffModal) {
        window.EditStaffModal.open(staffId);
    }
};

window.closeEditStaffModal = function() {
    if (window.EditStaffModal) {
        window.EditStaffModal.close();
    }
};
