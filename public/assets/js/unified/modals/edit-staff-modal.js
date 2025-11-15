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
            const designationEl = document.getElementById('e_designation');
            designationEl?.addEventListener('change', () => {
                this.toggleRoleFields();
            });

            // Live DOB validation on change
            const dobEl = document.getElementById('e_date_of_birth');
            if (dobEl && !dobEl.__boundDobValidation) {
                dobEl.__boundDobValidation = true;
                dobEl.addEventListener('change', () => {
                    const dobErrors = {};
                    this.validateDob(this.collectFormData(), dobErrors);
                    const dobErrEl = document.getElementById('e_err_date_of_birth');
                    if (dobErrEl) {
                        dobErrEl.textContent = dobErrors.date_of_birth || '';
                    }
                });
            }

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
            this.toggleRoleFields();
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
        const normalizeDepartment = (dept) => {
            if (!dept) return '';
            return (String(dept).toUpperCase() === 'N/A') ? '' : dept;
        };

        const fields = {
            'e_staff_id': staff.staff_id || '',
            'e_employee_id': staff.employee_id || '',
            'e_first_name': staff.first_name || '',
            'e_last_name': staff.last_name || '',
            'e_gender': staff.gender || '',
            'e_date_of_birth': staff.date_of_birth || staff.dob || '',
            'e_contact_no': staff.contact_no || staff.phone || '',
            'e_email': staff.email || '',
            'e_department': normalizeDepartment(staff.department),
            'e_designation': staff.role || staff.designation || '',
            'e_date_joined': staff.date_joined || '',
            'e_status': staff.status || 'active',
            'e_address': staff.address || '',
            'e_doctor_specialization': staff.doctor_specialization || '',
            'e_doctor_license_no': staff.doctor_license_no || '',
            'e_doctor_consultation_fee': staff.doctor_consultation_fee || '',
            'e_nurse_license_no': staff.nurse_license_no || '',
            'e_nurse_specialization': staff.nurse_specialization || '',
            'e_accountant_license_no': staff.accountant_license_no || '',
            'e_laboratorist_license_no': staff.laboratorist_license_no || '',
            'e_laboratorist_specialization': staff.laboratorist_specialization || '',
            'e_lab_room_no': staff.lab_room_no || '',
            'e_pharmacist_license_no': staff.pharmacist_license_no || '',
            'e_pharmacist_specialization': staff.pharmacist_specialization || ''
        };
        
        for (const [fieldId, value] of Object.entries(fields)) {
            const element = document.getElementById(fieldId);
            if (element) {
                element.value = value;
            }
        }

        this.toggleRoleFields();
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

            // Basic client-side validation similar to add staff
            const clientErrors = {};
            if (!formData.employee_id || String(formData.employee_id).trim().length < 3) {
                clientErrors.employee_id = 'Employee ID is required (min 3 characters).';
            }
            if (!formData.first_name || String(formData.first_name).trim().length < 2) {
                clientErrors.first_name = 'First name is required (min 2 characters).';
            }
            this.validateDob(formData, clientErrors);
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

    toggleRoleFields() {
        const designation = document.getElementById('e_designation')?.value || '';
        const isDoctor = designation === 'doctor';
        const isNurse = designation === 'nurse';
        const isAccountant = designation === 'accountant';
        const isLaboratorist = designation === 'laboratorist';
        const isPharmacist = designation === 'pharmacist';

        const docFields = document.getElementById('e_doctorFields');
        const nurseFields = document.getElementById('e_nurseFields');
        const accountantFields = document.getElementById('e_accountantFields');
        const laboratoristFields = document.getElementById('e_laboratoristFields');
        const pharmacistFields = document.getElementById('e_pharmacistFields');

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

    validateDob(formData, errors) {
        const dobRaw = formData.date_of_birth || formData.dob || '';
        if (!dobRaw || String(dobRaw).trim().length === 0) {
            return;
        }

        const dob = new Date(dobRaw);
        if (isNaN(dob.getTime())) {
            errors.date_of_birth = 'Please enter a valid date of birth.';
            return;
        }

        const today = new Date();
        today.setHours(0, 0, 0, 0);
        dob.setHours(0, 0, 0, 0);

        if (dob > today) {
            errors.date_of_birth = 'Date of birth cannot be in the future.';
            return;
        }

        const ageDiffMs = today.getTime() - dob.getTime();
        const ageDate = new Date(ageDiffMs);
        const age = Math.abs(ageDate.getUTCFullYear() - 1970);

        if (age < 18) {
            errors.date_of_birth = 'Age not valid: staff must be at least 18 years old.';
        } else if (age > 100) {
            errors.date_of_birth = 'Age not valid: please check the date of birth.';
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
