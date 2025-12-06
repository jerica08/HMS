/**
 * View Shift Modal Controller
 */

window.ViewShiftModal = {
    modal: null,
    currentShift: null,
    
    init() {
        this.modal = document.getElementById('viewShiftModal');
        ShiftModalUtils.setupModalCloseHandlers(this.modal, () => this.close());
    },
    
    async open(shiftId) {
        if (!shiftId) {
            this.showNotification('Shift ID is required', 'error');
            return;
        }
        
        if (this.modal) {
            ShiftModalUtils.openModal('viewShiftModal');
            
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
            ShiftModalUtils.closeModal('viewShiftModal');
            this.currentShift = null;
            this.clearForm();
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
        const formatDate = (d) => d ? new Date(d).toLocaleDateString() : '';
        
        const fields = {
            'viewDoctorName': (shift.doctor_name || 'N/A') + (shift.specialization ? ' - ' + shift.specialization : ''),
            'viewScheduleWeekday': shift.weekday ? ShiftModalUtils.formatWeekday(shift.weekday) : (shift.date ? formatDate(shift.date) : 'N/A'),
            'viewScheduleSlot': (() => {
                const startVal = shift.start_time || shift.start || '';
                const endVal = shift.end_time || shift.end || '';
                return startVal && endVal ? `${startVal.slice(0,5)} - ${endVal.slice(0,5)}` : (shift.shift_type || 'N/A');
            })(),
            'viewShiftStatus': ShiftModalUtils.normalizeStatus(shift.status)
        };

        for (const [id, value] of Object.entries(fields)) {
            const el = document.getElementById(id);
            if (el) el.value = value;
        }
    },
    
    clearForm() {
        ['viewDoctorName', 'viewScheduleWeekday', 'viewScheduleSlot', 'viewShiftStatus'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
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
window.openViewShiftModal = (shiftId) => window.ViewShiftModal?.open(shiftId);
window.closeViewShiftModal = () => window.ViewShiftModal?.close();

