/**
 * View Staff Modal Controller
 */

window.ViewStaffModal = {
    modal: null,
    
    init() {
        this.modal = document.getElementById('viewStaffModal');
        
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
            this.clearForm();
        }
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
            'v_employee_id': staff.employee_id || staff.staff_id || '',
            'v_first_name': staff.first_name || '',
            'v_last_name': staff.last_name || '',
            'v_gender': staff.gender ? staff.gender.charAt(0).toUpperCase() + staff.gender.slice(1) : '',
            'v_date_of_birth': staff.date_of_birth ? new Date(staff.date_of_birth).toLocaleDateString() : '',
            'v_contact_no': staff.contact_no || staff.phone || '',
            'v_email': staff.email || '',
            'v_role': staff.role ? staff.role.charAt(0).toUpperCase() + staff.role.slice(1).replace('_', ' ') : '',
            'v_department': staff.department || '',
            'v_date_joined': staff.date_joined ? new Date(staff.date_joined).toLocaleDateString() : '',
            'v_status': staff.status ? staff.status.charAt(0).toUpperCase() + staff.status.slice(1) : '',
            'v_address': staff.address || ''
        };
        
        for (const [fieldId, value] of Object.entries(fields)) {
            const element = document.getElementById(fieldId);
            if (element) {
                element.value = value;
            }
        }
    },
    
    clearForm() {
        const fields = [
            'v_employee_id', 'v_first_name', 'v_last_name', 'v_gender',
            'v_date_of_birth', 'v_contact_no', 'v_email', 'v_role',
            'v_department', 'v_date_joined', 'v_status', 'v_address'
        ];
        
        fields.forEach(fieldId => {
            const element = document.getElementById(fieldId);
            if (element) {
                element.value = '';
            }
        });
    }
};

// Global functions for backward compatibility
window.openStaffViewModal = function(staffId) {
    if (window.ViewStaffModal) {
        window.ViewStaffModal.open(staffId);
    }
};

window.closeViewStaffModal = function() {
    if (window.ViewStaffModal) {
        window.ViewStaffModal.close();
    }
};
