/**
 * Edit Shift Modal Controller
 */

window.EditShiftModal = {
    modal: null,
    form: null,
    config: null,
    currentShift: null,
    
    init() {
        this.modal = document.getElementById('editShiftModal');
        this.form = document.getElementById('editShiftForm');
        this.config = this.getConfig();
        
        if (this.form) {
            this.form.addEventListener('submit', (e) => this.handleSubmit(e));
            
            const doctorSelect = document.getElementById('editDoctorSelect');
            if (doctorSelect) {
                doctorSelect.addEventListener('change', () => this.onDoctorChange());
            }
        }
        
        ShiftModalUtils.setupModalCloseHandlers(this.modal, () => this.close());
    },
    
    getConfig() {
        const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const csrfHash = document.querySelector('meta[name="csrf-hash"]')?.content || '';
        
        return {
            baseUrl: baseUrl.replace(/\/$/, ''),
            csrfToken,
            csrfHash,
            endpoints: {
                update: `${baseUrl}shifts/update`
            }
        };
    },
    
    async open(shiftId) {
        if (!shiftId) {
            this.showNotification('Shift ID is required', 'error');
            return;
        }
        
        if (this.modal) {
            ShiftModalUtils.clearErrors(this.form);
            ShiftModalUtils.openModal('editShiftModal');
            
            try {
                await this.loadShiftDetails(shiftId);
            } catch (error) {
                console.error('Error loading shift details:', error);
                this.showNotification('Failed to load shift details', 'error');
                this.close();
            }
        }
    },
    
    close() {
        if (this.modal) {
            ShiftModalUtils.closeModal('editShiftModal');
            this.currentShift = null;
        }
    },
    
    async loadShiftDetails(shiftId) {
        if (!window.shiftManager) {
            throw new Error('ShiftManager not available');
        }
        
        const shift = window.shiftManager.shifts.find(s => String(s.id) === String(shiftId));
        if (!shift) {
            throw new Error('Shift not found');
        }
        
        this.currentShift = shift;
        this.populateForm(shift);
    },
    
    populateForm(shift) {
        const doctorSelect = document.getElementById('editDoctorSelect');
        const weekdaySelect = document.getElementById('editWeekday');
        const startTimeInput = document.getElementById('editShiftStart');
        const endTimeInput = document.getElementById('editShiftEnd');
        const statusSelect = document.getElementById('editShiftStatus');
        const idInput = document.getElementById('editShiftId');

        if (doctorSelect) {
            doctorSelect.value = shift.staff_id || shift.doctor_id || '';
        }
        
        if (weekdaySelect) {
            weekdaySelect.value = shift.weekday || '';
        }

        if (startTimeInput) {
            const startVal = shift.start_time || shift.start || '';
            startTimeInput.value = startVal ? startVal.slice(0,5) : '';
        }

        if (endTimeInput) {
            const endVal = shift.end_time || shift.end || '';
            endTimeInput.value = endVal ? endVal.slice(0,5) : '';
        }

        if (statusSelect) {
            statusSelect.value = ShiftModalUtils.normalizeStatus(shift.status);
        }
        
        if (idInput) {
            idInput.value = shift.id || '';
        }
    },
    
    async handleSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const data = ShiftModalUtils.collectFormData(form);
        data[this.config.csrfToken] = this.config.csrfHash;

        try {
            this.showLoading(true);
            const response = await fetch(this.config.endpoints.update, {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.status === 'success') {
                this.showNotification('Shift updated successfully', 'success');
                this.close();
                if (window.shiftManager) {
                    window.shiftManager.loadShifts();
                }
            } else {
                this.showNotification(result.message || 'Failed to update shift', 'error');
                if (result.errors) {
                    ShiftModalUtils.displayErrors(result.errors, 'edit');
                }
            }

            if (result.csrf) {
                this.config.csrfHash = result.csrf.value;
            }
        } catch (error) {
            console.error('Error updating shift:', error);
            this.showNotification('Failed to update shift', 'error');
        } finally {
            this.showLoading(false);
        }
    },
    
    onDoctorChange() {
        const doctorSelect = document.getElementById('editDoctorSelect');
        const departmentSelect = document.getElementById('editShiftDepartment');
        
        if (doctorSelect && departmentSelect) {
            const selectedOption = doctorSelect.options[doctorSelect.selectedIndex];
            if (selectedOption && selectedOption.textContent.includes(' - ')) {
                const department = selectedOption.textContent.split(' - ')[1];
                departmentSelect.value = department;
            }
        }
    },
    
    showLoading(show) {
        const submitBtn = document.getElementById('saveEditShiftBtn');
        if (submitBtn) {
            submitBtn.disabled = show;
            submitBtn.innerHTML = show 
                ? '<i class="fas fa-spinner fa-spin"></i> Updating...' 
                : '<i class="fas fa-save"></i> Update Shift';
        }
    },
    
    showNotification(message, type) {
        if (window.shiftManager) {
            window.shiftManager.showNotification(message, type);
        } else {
            alert(message);
        }
    }
};

// Global functions for backward compatibility
window.openEditShiftModal = (shiftId) => window.EditShiftModal?.open(shiftId);
window.closeEditShiftModal = () => window.EditShiftModal?.close();

