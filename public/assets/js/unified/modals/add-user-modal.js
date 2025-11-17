/**
 * Add User Modal Controller
 */

window.AddUserModal = {
    modal: null,
    form: null,
    staffCache: [],
    
    init() {
        this.modal = document.getElementById('addUserModal');
        this.form = document.getElementById('addUserForm');
        
        if (this.form) {
            this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        }
        
        // Staff selection change handler
        const staffSelect = document.getElementById('staff_id');
        if (staffSelect) {
            staffSelect.addEventListener('change', (e) => this.handleStaffSelection(e));
        }
        
        // Password confirmation validation
        const confirmPassword = document.getElementById('confirm_password');
        if (confirmPassword) {
            confirmPassword.addEventListener('input', () => this.validatePasswordMatch());
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
    
    async open() {
        if (this.modal) {
            this.modal.classList.add('active');
            this.modal.setAttribute('aria-hidden', 'false');
            this.resetForm();
            
            try {
                await this.loadAvailableStaff();
            } catch (error) {
                console.error('Error loading staff:', error);
                UserUtils.showNotification('Failed to load available staff', 'error');
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
            
            // Reset staff dropdown
            const staffSelect = document.getElementById('staff_id');
            if (staffSelect) {
                // Keep server-rendered options; just reset selection
                staffSelect.value = '';
            }
        }
    },
    
    clearErrors() {
        const errorElements = this.form?.querySelectorAll('[id^="err_"]');
        errorElements?.forEach(el => el.textContent = '');
    },
    
    async loadAvailableStaff() {
        const staffSelect = document.getElementById('staff_id');
        if (!staffSelect) return;
        
        try {
            // Only show loading placeholder if there are no existing options
            if (staffSelect.options.length <= 1) {
                staffSelect.innerHTML = '<option value="">Loading staff...</option>';
            }
            
            const response = await UserUtils.makeRequest(
                UserConfig.getUrl(UserConfig.endpoints.availableStaff)
            );
            
            if (response.status === 'success') {
                this.staffCache = response.data || [];
                this.populateStaffSelect();
            } else {
                throw new Error(response.message || 'Failed to load staff');
            }
        } catch (error) {
            console.error('Error loading staff:', error);
            // Preserve existing options if present; only show error if empty
            if (staffSelect.options.length <= 1) {
                staffSelect.innerHTML = '<option value="">Error loading staff</option>';
            }
            throw error;
        }
    },
    
    populateStaffSelect() {
        const staffSelect = document.getElementById('staff_id');
        if (!staffSelect) return;
        
        if (this.staffCache.length === 0) {
            staffSelect.innerHTML = '<option value="">No available staff members</option>';
            return;
        }
        
        const options = ['<option value="">Select staff member...</option>'];
        
        this.staffCache.forEach(staff => {
            const displayName = `${staff.full_name} - ${staff.department || 'No Department'} (${staff.employee_id || staff.staff_id})`;
            options.push(`<option value="${staff.staff_id}" data-email="${staff.email || ''}" data-role="${staff.role || staff.designation || ''}">${UserUtils.escapeHtml(displayName)}</option>`);
        });
        
        staffSelect.innerHTML = options.join('');
    },
    
    handleStaffSelection(e) {
        const selectedOption = e.target.selectedOptions[0];
        if (!selectedOption || !selectedOption.value) {
            // Clear dependent fields
            document.getElementById('email').value = '';
            return;
        }
        
        // Populate email from selected staff
        const email = selectedOption.dataset.email || '';
        
        document.getElementById('email').value = email;
        
        // Generate username suggestion
        const staff = this.staffCache.find(s => s.staff_id == selectedOption.value);
        if (staff) {
            const usernameSuggestion = this.generateUsername(staff);
            document.getElementById('username').value = usernameSuggestion;
        }
    },
    
    generateUsername(staff) {
        // Generate username from first name + last name initial
        const firstName = (staff.first_name || '').toLowerCase().replace(/[^a-z]/g, '');
        const lastInitial = (staff.last_name || '').charAt(0).toLowerCase();
        return firstName + lastInitial;
    },
    
    validatePasswordMatch() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const errorElement = document.getElementById('err_confirm_password');
        
        if (confirmPassword && password !== confirmPassword) {
            errorElement.textContent = 'Passwords do not match';
            return false;
        } else {
            errorElement.textContent = '';
            return true;
        }
    },
    
    async handleSubmit(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('saveUserBtn');
        const originalText = submitBtn?.innerHTML;
        
        try {
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
            }
            
            this.clearErrors();
            
            // Validate password match
            if (!this.validatePasswordMatch()) {
                return;
            }
            
            const formData = this.collectFormData();
            
            const response = await UserUtils.makeRequest(
                UserConfig.getUrl(UserConfig.endpoints.userCreate),
                {
                    method: 'POST',
                    body: JSON.stringify(formData)
                }
            );

            if (response.status === 'success' || response.success === true || response.ok === true) {
                UserUtils.showNotification('User created successfully', 'success');
                this.close();
                
                // Refresh user list
                if (window.userManager) {
                    window.userManager.refresh();
                }
            } else {
                if (response.errors) {
                    this.displayErrors(response.errors);
                    UserUtils.showNotification(response.message || 'Please fix the highlighted errors.', 'warning');
                    return;
                }
                throw new Error(response.message || `Failed to create user (status ${response.statusCode || 'unknown'})`);
            }
        } catch (error) {
            console.error('Error creating user:', error);
            UserUtils.showNotification('Failed to create user: ' + error.message, 'error');
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
            const errorElement = document.getElementById(`err_${field}`);
            if (errorElement) {
                errorElement.textContent = Array.isArray(message) ? message[0] : message;
            }
        }
    }
};

// Global functions for backward compatibility
window.openAddUserModal = function() {
    if (window.AddUserModal) {
        window.AddUserModal.open();
    }
};

window.closeAddUserModal = function() {
    if (window.AddUserModal) {
        window.AddUserModal.close();
    }
};
