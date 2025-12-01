/**
 * Unified Shift Management JavaScript
 * Handles all shift management functionality across different user roles
 */

class ShiftManager {
    constructor() {
        this.config = this.getConfig();
        this.calendar = null;
        this.currentView = 'list';
        this.filters = {};
        this.shifts = [];
        
        this.init();
    }

    getConfig() {
        const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const csrfHash = document.querySelector('meta[name="csrf-hash"]')?.content || '';
        const userRole = document.querySelector('meta[name="user-role"]')?.content || '';

        return {
            baseUrl: baseUrl.replace(/\/$/, ''),
            csrfToken,
            csrfHash,
            userRole,
            endpoints: {
                shifts: `${baseUrl}shifts/api`,
                create: `${baseUrl}shifts/create`,
                update: `${baseUrl}shifts/update`,
                delete: `${baseUrl}shifts/delete`,
                getShift: `${baseUrl}shifts`,
                updateStatus: `${baseUrl}shifts`,
                availableStaff: `${baseUrl}shifts/available-staff`
            }
        };
    }

    init() {
        this.bindEvents();
        this.loadShifts();
        this.initCalendar();
        this.setupAutoRefresh();
    }

    bindEvents() {
        // Modal events
        this.bindModalEvents();
        
        // Filter events
        this.bindFilterEvents();
        
        // View toggle events
        this.bindViewToggleEvents();
        
        // Form events
        this.bindFormEvents();
        
        // Action button events
        this.bindActionEvents();
    }

    bindModalEvents() {
        // Create shift modal
        const createBtn = document.getElementById('createShiftBtn');
        const shiftModal = document.getElementById('shiftModal');
        const closeShiftModal = document.getElementById('closeShiftModal');
        const cancelShiftBtn = document.getElementById('cancelShiftBtn');

        if (createBtn) {
            createBtn.addEventListener('click', () => this.openCreateModal());
        }

        if (closeShiftModal) {
            closeShiftModal.addEventListener('click', () => this.closeShiftModal());
        }

        if (cancelShiftBtn) {
            cancelShiftBtn.addEventListener('click', () => this.closeShiftModal());
        }

        // Click outside to close create/edit modal
        if (shiftModal) {
            shiftModal.addEventListener('click', (e) => {
                if (e.target === shiftModal) {
                    this.closeShiftModal();
                }
            });
        }

        // Escape key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeShiftModal();
            }
        });
    }

    bindFilterEvents() {
        const dateFilter = document.getElementById('dateFilter');
        const statusFilter = document.getElementById('statusFilter');
        const departmentFilter = document.getElementById('departmentFilter');
        const searchFilter = document.getElementById('searchFilter');
        const clearFilters = document.getElementById('clearFilters');

        if (dateFilter) {
            dateFilter.addEventListener('change', () => this.applyFilters());
        }

        if (statusFilter) {
            statusFilter.addEventListener('change', () => this.applyFilters());
        }

        if (departmentFilter) {
            departmentFilter.addEventListener('change', () => this.applyFilters());
        }

        if (searchFilter) {
            let searchTimeout;
            searchFilter.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => this.applyFilters(), 300);
            });
        }

        if (clearFilters) {
            clearFilters.addEventListener('click', () => this.clearFilters());
        }
    }

    bindViewToggleEvents() {
        const listViewBtn = document.getElementById('listViewBtn');
        const calendarViewBtn = document.getElementById('calendarViewBtn');

        if (listViewBtn) {
            listViewBtn.addEventListener('click', () => this.switchView('list'));
        }

        if (calendarViewBtn) {
            calendarViewBtn.addEventListener('click', () => this.switchView('calendar'));
        }
    }

    bindFormEvents() {
        const shiftForm = document.getElementById('shiftForm');
        
        if (shiftForm) {
            shiftForm.addEventListener('submit', (e) => this.handleFormSubmit(e));
        }

        // Doctor selection change
        const doctorSelect = document.getElementById('doctorSelect');
        if (doctorSelect) {
            doctorSelect.addEventListener('change', () => this.onDoctorChange());
        }
    }

    bindActionEvents() {
        // Delegate event handling for dynamically created buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('.btn-view') || e.target.closest('.btn-view')) {
                const btn = e.target.matches('.btn-view') ? e.target : e.target.closest('.btn-view');
                const shiftId = btn.dataset.shiftId;
                if (shiftId) {
                    this.viewShift(shiftId);
                }
            }

            if (e.target.matches('.btn-edit') || e.target.closest('.btn-edit')) {
                const btn = e.target.matches('.btn-edit') ? e.target : e.target.closest('.btn-edit');
                const shiftId = btn.dataset.shiftId;
                if (shiftId) {
                    this.editShift(shiftId);
                }
            }

            if (e.target.matches('.btn-delete') || e.target.closest('.btn-delete')) {
                const btn = e.target.matches('.btn-delete') ? e.target : e.target.closest('.btn-delete');
                const shiftId = btn.dataset.shiftId;
                if (shiftId) {
                    this.deleteShift(shiftId);
                }
            }

            if (e.target.matches('.btn-status') || e.target.closest('.btn-status')) {
                const btn = e.target.matches('.btn-status') ? e.target : e.target.closest('.btn-status');
                const shiftId = btn.dataset.shiftId;
                const status = btn.dataset.status;
                if (shiftId && status) {
                    this.updateShiftStatus(shiftId, status);
                }
            }
        });
    }

    async loadShifts() {
        try {
            this.showLoading(true);
            
            const url = new URL(this.config.endpoints.shifts, window.location.origin);
            
            // Add filters to URL
            Object.keys(this.filters).forEach(key => {
                if (this.filters[key]) {
                    url.searchParams.append(key, this.filters[key]);
                }
            });

            const response = await fetch(url);
            const data = await response.json();

            if (data.status === 'success') {
                this.shifts = data.data || [];
                this.renderShifts();
                this.updateCalendar();
            } else {
                this.showError('Failed to load shifts: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error loading shifts:', error);
            this.showError('Failed to load shifts');
        } finally {
            this.showLoading(false);
        }
    }

    renderShifts() {
        const tbody = document.getElementById('shiftsTableBody');
        if (!tbody) return;

        if (this.shifts.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h3>No shifts found</h3>
                        <p>No shifts match your current filters.</p>
                    </td>
                </tr>
            `;
            return;
        }

        // Group shifts by doctor + time range + status so multiple weekdays
        // with the same time range appear as a single row in the UI.
        const groupsMap = new Map();

        this.shifts.forEach(shift => {
            const staffId   = shift.staff_id || shift.doctor_id || '';
            const startVal  = shift.start_time || shift.start || '';
            const endVal    = shift.end_time || shift.end || '';
            const rawStatus = (shift.status || 'active').toString().toLowerCase();

            const key = `${staffId}|${startVal}|${endVal}|${rawStatus}`;

            if (!groupsMap.has(key)) {
                const base = { ...shift };
                base.weekdays = [];
                base.ids = [];
                base.primaryId = shift.id;
                groupsMap.set(key, base);
            }

            const group = groupsMap.get(key);
            if (typeof shift.weekday !== 'undefined' && shift.weekday !== null) {
                group.weekdays.push(shift.weekday);
            }
            group.ids.push(shift.id);
        });

        const groupedShifts = Array.from(groupsMap.values());

        tbody.innerHTML = groupedShifts.map(shift => this.renderShiftRow(shift)).join('');
    }

    renderShiftRow(shift) {
        // Map schedule data (weekday/time/status) to the table row

        // If this is a grouped shift, we may have multiple weekdays.
        let weekdayLabel = '-';
        if (Array.isArray(shift.weekdays) && shift.weekdays.length) {
            const uniqueWeekdays = Array.from(new Set(shift.weekdays))
                .filter(wd => typeof wd !== 'undefined' && wd !== null)
                .sort();
            weekdayLabel = uniqueWeekdays
                .map(wd => this.formatWeekday(wd))
                .join(', ');
        } else if (shift.weekday) {
            weekdayLabel = this.formatWeekday(shift.weekday);
        }
        const timeLabel = (shift.start_time || shift.start || '') && (shift.end_time || shift.end || '')
            ? `${(shift.start_time || shift.start || '').slice(0,5)} - ${(shift.end_time || shift.end || '').slice(0,5)}`
            : '-';

        const rawStatus = (shift.status || 'active').toString().toLowerCase();
        let displayStatus = 'Scheduled';
        let statusClass = 'scheduled';

        if (rawStatus === 'active' || rawStatus === 'scheduled') {
            displayStatus = 'Scheduled';
            statusClass = 'scheduled';
        } else if (rawStatus === 'completed' || rawStatus === 'done' || rawStatus === 'finished') {
            displayStatus = 'Completed';
            statusClass = 'completed';
        } else if (rawStatus === 'cancelled' || rawStatus === 'canceled' || rawStatus === 'inactive') {
            displayStatus = 'Cancelled';
            statusClass = 'cancelled';
        } else {
            // Fallback: title-case any other status value
            displayStatus = rawStatus.charAt(0).toUpperCase() + rawStatus.slice(1);
        }

        const canEdit = this.canEditShift(shift);
        const canDelete = this.canDeleteShift(shift);

        // For grouped rows use a primary id for actions. This id represents
        // one underlying schedule entry; edit/delete will operate on that
        // specific entry while the UI shows the combined weekdays.
        const primaryId = shift.primaryId || shift.id;

        return `
            <tr class="fade-in">
                <td>
                    <div class="doctor-info">
                        <div class="doctor-name">${this.escapeHtml(shift.doctor_name || 'Unknown')}</div>
                    </div>
                </td>
                <td>${this.escapeHtml(weekdayLabel)}</td>
                <td>${this.escapeHtml(timeLabel)}</td>
                <td>
                    <span class="status-badge ${statusClass}">
                        ${this.escapeHtml(displayStatus)}
                    </span>
                </td>
                <td>
                    <div class="action-buttons">
                        <button type="button" class="btn btn-sm btn-view" data-shift-id="${primaryId}" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        ${canEdit ? `
                            <button type="button" class="btn btn-sm btn-edit" data-shift-id="${primaryId}" title="Edit Shift">
                                <i class="fas fa-edit"></i>
                            </button>
                        ` : ''}
                        ${canDelete ? `
                            <button type="button" class="btn btn-sm btn-delete" data-shift-id="${primaryId}" title="Delete Shift">
                                <i class="fas fa-trash"></i>
                            </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `;
    }

    initCalendar() {
        const calendarEl = document.getElementById('calendar');
        if (!calendarEl) return;

        this.calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: [],
            eventClick: (info) => {
                const shiftId = info.event.id;
                if (shiftId) {
                    this.viewShift(shiftId);
                }
            },
            eventDidMount: (info) => {
                const shift = this.shifts.find(s => s.id == info.event.id);
                if (shift) {
                    info.el.classList.add(`shift-${shift.status?.toLowerCase() || 'scheduled'}`);
                }
            }
        });

        this.calendar.render();
    }

    updateCalendar() {
        if (!this.calendar) return;

        const events = this.shifts.map(shift => ({
            id: shift.id,
            title: `${shift.doctor_name || 'Unknown'} - ${shift.department || ''}`,
            start: `${shift.date}T${shift.start}`,
            end: `${shift.date}T${shift.end}`,
            backgroundColor: this.getEventColor(shift.status),
            borderColor: this.getEventColor(shift.status),
            extendedProps: {
                shift: shift
            }
        }));

        this.calendar.removeAllEvents();
        this.calendar.addEventSource(events);
    }

    getEventColor(status) {
        const colors = {
            'Scheduled': '#3b82f6',
            'Completed': '#10b981',
            'Cancelled': '#ef4444'
        };
        return colors[status] || colors['Scheduled'];
    }

    switchView(view) {
        this.currentView = view;
        
        const listView = document.getElementById('listView');
        const calendarView = document.getElementById('calendarView');
        const listViewBtn = document.getElementById('listViewBtn');
        const calendarViewBtn = document.getElementById('calendarViewBtn');

        if (view === 'list') {
            if (listView) listView.style.display = 'block';
            if (calendarView) calendarView.style.display = 'none';
            if (listViewBtn) listViewBtn.classList.add('active');
            if (calendarViewBtn) calendarViewBtn.classList.remove('active');
        } else {
            if (listView) listView.style.display = 'none';
            if (calendarView) calendarView.style.display = 'block';
            if (listViewBtn) listViewBtn.classList.remove('active');
            if (calendarViewBtn) calendarViewBtn.classList.add('active');
            
            // Refresh calendar when switching to calendar view
            if (this.calendar) {
                setTimeout(() => this.calendar.updateSize(), 100);
            }
        }
    }

    applyFilters() {
        this.filters = {
            date: document.getElementById('dateFilter')?.value || '',
            status: document.getElementById('statusFilter')?.value || '',
            department: document.getElementById('departmentFilter')?.value || '',
            search: document.getElementById('searchFilter')?.value || ''
        };

        this.loadShifts();
    }

    clearFilters() {
        const dateFilter = document.getElementById('dateFilter');
        const statusFilter = document.getElementById('statusFilter');
        const departmentFilter = document.getElementById('departmentFilter');
        const searchFilter = document.getElementById('searchFilter');

        if (dateFilter) dateFilter.value = '';
        if (statusFilter) statusFilter.value = '';
        if (departmentFilter) departmentFilter.value = '';
        if (searchFilter) searchFilter.value = '';

        this.filters = {};
        this.loadShifts();
    }

    openCreateModal() {
        this.resetForm();
        document.getElementById('modalTitle').textContent = 'Create Shift';
        document.getElementById('shiftId').value = '';
        document.getElementById('shiftModal').classList.add('active');
    }

    async editShift(shiftId) {
        // Use the already-loaded schedule list (this.shifts) to populate the edit modal
        const shift = this.shifts.find(s => String(s.id) === String(shiftId));

        if (!shift) {
            this.showError('Failed to load shift details');
            return;
        }

        this.populateForm(shift);

        const modalTitleEl = document.getElementById('modalTitle');
        if (modalTitleEl) {
            modalTitleEl.textContent = 'Edit Shift';
        }

        const idInput = document.getElementById('shiftId');
        if (idInput) {
            idInput.value = shiftId;
        }

        const modal = document.getElementById('shiftModal');
        if (modal) {
            modal.classList.add('active');
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }
    }

    async viewShift(shiftId) {
        // Use already-loaded shifts list instead of calling backend again.
        const shift = this.shifts.find(s => String(s.id) === String(shiftId));

        if (!shift) {
            this.showError('Failed to load shift details');
            return;
        }

        // Simple read-only alert using available fields (supports old shift or new schedule data).
        const parts = [];
        if (shift.doctor_name) parts.push(`Doctor: ${shift.doctor_name}`);
        if (shift.weekday) parts.push(`Weekday: ${shift.weekday}`);
        if (shift.slot) parts.push(`Slot: ${shift.slot}`);
        if (shift.date) parts.push(`Date: ${shift.date}`);
        const startVal = shift.start_time || shift.start || '';
        const endVal   = shift.end_time || shift.end || '';
        if (startVal || endVal) {
            parts.push(`Time: ${startVal}${endVal ? ' - ' + endVal : ''}`);
        }
        if (shift.department) parts.push(`Department: ${shift.department}`);
        if (shift.status) parts.push(`Status: ${shift.status}`);

        alert(parts.join('\n') || 'No details available for this schedule.');
    }

    async deleteShift(shiftId) {
        if (!confirm('Are you sure you want to delete this shift? This action cannot be undone.')) {
            return;
        }

        try {
            const response = await fetch(this.config.endpoints.delete, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    id: shiftId,
                    [this.config.csrfToken]: this.config.csrfHash
                })
            });

            const data = await response.json();

            if (data.status === 'success') {
                this.showSuccess('Shift deleted successfully');
                this.loadShifts();
            } else {
                this.showError(data.message || 'Failed to delete shift');
            }

            // Update CSRF hash
            if (data.csrf) {
                this.config.csrfHash = data.csrf.value;
            }
        } catch (error) {
            console.error('Error deleting shift:', error);
            this.showError('Failed to delete shift');
        }
    }

    async updateShiftStatus(shiftId, status) {
        try {
            const response = await fetch(`${this.config.endpoints.updateStatus}/${shiftId}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    status: status,
                    [this.config.csrfToken]: this.config.csrfHash
                })
            });

            const data = await response.json();

            if (data.status === 'success') {
                this.showSuccess(`Shift marked as ${status.toLowerCase()}`);
                this.loadShifts();
            } else {
                this.showError(data.message || 'Failed to update shift status');
            }

            // Update CSRF hash
            if (data.csrf) {
                this.config.csrfHash = data.csrf.value;
            }
        } catch (error) {
            console.error('Error updating shift status:', error);
            this.showError('Failed to update shift status');
        }
    }

    async handleFormSubmit(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);

        // Build data object manually so that multiple weekday checkboxes
        // (weekdays[]) are captured as an array instead of a single value.
        const data = {};
        formData.forEach((value, key) => {
            if (key === 'weekdays[]') {
                if (!data.weekdays) {
                    data.weekdays = [];
                }
                data.weekdays.push(value);
            } else {
                data[key] = value;
            }
        });

        // Add CSRF token
        data[this.config.csrfToken] = this.config.csrfHash;

        const isEdit = !!data.id;
        const endpoint = isEdit ? this.config.endpoints.update : this.config.endpoints.create;

        try {
            this.showLoading(true, form);

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.status === 'success') {
                this.showSuccess(isEdit ? 'Shift updated successfully' : 'Shift created successfully');
                this.closeShiftModal();
                this.loadShifts();
            } else {
                this.showError(result.message || 'Failed to save shift');
                
                // Show validation errors
                if (result.errors) {
                    this.showValidationErrors(result.errors);
                }
            }

            // Update CSRF hash
            if (result.csrf) {
                this.config.csrfHash = result.csrf.value;
            }
        } catch (error) {
            console.error('Error saving shift:', error);
            this.showError('Failed to save shift');
        } finally {
            this.showLoading(false, form);
        }
    }

    populateForm(shift) {
        // Updated to match the new schedule form (doctor, weekdays[], start_time/end_time, status, notes)
        const doctorSelect   = document.getElementById('doctorSelect');
        const startTimeInput = document.getElementById('startTime');
        const endTimeInput   = document.getElementById('endTime');
        const statusSelect   = document.getElementById('shiftStatus');
        const notesTextarea  = document.getElementById('shiftNotes');

        if (doctorSelect) {
            const value = shift.staff_id || shift.doctor_id || '';
            doctorSelect.value = value;
        }

        // Clear all weekday checkboxes, then check the one matching this shift
        const weekdayCheckboxes = document.querySelectorAll('input[name="weekdays[]"]');
        if (weekdayCheckboxes && typeof shift.weekday !== 'undefined') {
            const weekdayValue = String(shift.weekday || '');
            weekdayCheckboxes.forEach(cb => {
                cb.checked = cb.value === weekdayValue;
            });
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
            const raw = (shift.status || 'Scheduled').toString().toLowerCase();
            let mapped = 'Scheduled';

            if (raw === 'active' || raw === 'scheduled') {
                mapped = 'Scheduled';
            } else if (raw === 'completed' || raw === 'done' || raw === 'finished') {
                mapped = 'Completed';
            } else if (raw === 'cancelled' || raw === 'canceled' || raw === 'inactive') {
                mapped = 'Cancelled';
            }

            statusSelect.value = mapped;
        }

        if (notesTextarea) {
            notesTextarea.value = shift.notes || '';
        }
    }

    populateViewModal(shift) {
        // If the view modal is not present on this page, do nothing safely
        const modal = document.getElementById('viewShiftModal');
        if (!modal) {
            console.warn('View shift modal not found; skipping populateViewModal');
            return;
        }

        const doctorInput = document.getElementById('viewDoctorName');
        const weekdayInput = document.getElementById('viewScheduleWeekday');
        const slotInput = document.getElementById('viewScheduleSlot');
        const statusInput = document.getElementById('viewShiftStatus');

        if (doctorInput) {
            const name = (shift.doctor_name || 'N/A') + (shift.specialization ? ' - ' + shift.specialization : '');
            doctorInput.value = name;
        }

        if (weekdayInput) {
            const weekdayLabel = shift.weekday
                ? this.formatWeekday(shift.weekday)
                : (shift.date ? this.formatDate(shift.date) : 'N/A');
            weekdayInput.value = weekdayLabel;
        }

        if (slotInput) {
            const startVal = shift.start_time || shift.start || '';
            const endVal   = shift.end_time || shift.end || '';
            const timeLabel = startVal && endVal
                ? `${startVal.slice(0,5)} - ${endVal.slice(0,5)}`
                : (shift.shift_type || 'N/A');
            slotInput.value = timeLabel;
        }

        if (statusInput) {
            const rawStatus = (shift.status || 'scheduled').toString().toLowerCase();
            let label = 'Scheduled';

            if (rawStatus === 'active' || rawStatus === 'scheduled') {
                label = 'Scheduled';
            } else if (rawStatus === 'completed' || rawStatus === 'done' || rawStatus === 'finished') {
                label = 'Completed';
            } else if (rawStatus === 'cancelled' || rawStatus === 'canceled' || rawStatus === 'inactive') {
                label = 'Cancelled';
            } else {
                label = rawStatus.charAt(0).toUpperCase() + rawStatus.slice(1);
            }

            statusInput.value = label;
        }

        // Store shift data for edit functionality
        this.currentViewShift = shift;
    }

    resetForm() {
        const form = document.getElementById('shiftForm');
        if (form) {
            form.reset();
        }
        this.clearValidationErrors();
    }

    closeShiftModal() {
        document.getElementById('shiftModal').classList.remove('active');
        this.resetForm();
    }

    closeViewShiftModal() {
        const modal = document.getElementById('viewShiftModal');
        if (modal) {
            modal.classList.remove('active');
            modal.style.display = 'none';
        }
        document.body.style.overflow = '';
        this.currentViewShift = null;
    }

    editFromView() {
        if (this.currentViewShift) {
            this.closeViewShiftModal();
            this.populateForm(this.currentViewShift);
            document.getElementById('modalTitle').textContent = 'Edit Shift';
            document.getElementById('shiftId').value = this.currentViewShift.id;
            document.getElementById('shiftModal').classList.add('active');
        }
    }

    onDoctorChange() {
        // Auto-populate department based on doctor selection
        const doctorSelect = document.getElementById('doctorSelect');
        const departmentSelect = document.getElementById('shiftDepartment');
        
        if (doctorSelect && departmentSelect) {
            const selectedOption = doctorSelect.options[doctorSelect.selectedIndex];
            if (selectedOption && selectedOption.textContent.includes(' - ')) {
                const department = selectedOption.textContent.split(' - ')[1];
                departmentSelect.value = department;
            }
        }
    }

    setupAutoRefresh() {
        // Refresh shifts every 5 minutes
        setInterval(() => {
            this.loadShifts();
        }, 5 * 60 * 1000);
    }

    // Permission methods
    canEditShift(shift) {
        if (this.config.userRole === 'admin' || this.config.userRole === 'it_staff') {
            return true;
        }
        
        if (this.config.userRole === 'doctor') {
            // Doctors can edit their own shifts
            return shift.staff_id === this.getCurrentStaffId();
        }
        
        return false;
    }

    canDeleteShift(shift) {
        // Only admin can delete shifts
        return this.config.userRole === 'admin';
    }

    canUpdateStatus(shift) {
        if (this.config.userRole === 'admin' || this.config.userRole === 'it_staff') {
            return true;
        }
        
        if (this.config.userRole === 'doctor') {
            // Doctors can update status of their own shifts
            return shift.staff_id === this.getCurrentStaffId() && shift.status === 'Scheduled';
        }
        
        return false;
    }

    getCurrentStaffId() {
        // This would typically come from session data or user context
        // For now, we'll assume it's available in the page context
        return window.currentStaffId || null;
    }

    // Utility methods
    showLoading(show, element = null) {
        if (element) {
            element.classList.toggle('loading', show);
        } else {
            const tbody = document.getElementById('shiftsTableBody');
            if (tbody && show) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="loading-row">
                            <i class="fas fa-spinner fa-spin"></i> Loading shifts...
                        </td>
                    </tr>
                `;
            }
        }
    }

    showSuccess(message) {
        this.showNotification(message, 'success');
    }

    showError(message) {
        this.showNotification(message, 'error');
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} fade-in`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            ${this.escapeHtml(message)}
        `;

        // Add to page
        const content = document.querySelector('.content');
        if (content) {
            content.insertBefore(notification, content.firstChild);
        }

        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    showValidationErrors(errors) {
        Object.keys(errors).forEach(field => {
            const input = document.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('error');
                
                // Add error message
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message';
                errorDiv.textContent = errors[field];
                input.parentNode.appendChild(errorDiv);
            }
        });
    }

    clearValidationErrors() {
        document.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
        document.querySelectorAll('.error-message').forEach(el => el.remove());
    }

    formatDate(dateString) {
        if (!dateString) return '-';
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        } catch (e) {
            return dateString;
        }
    }

    formatTime(timeString) {
        if (!timeString) return '-';
        try {
            const [hours, minutes] = timeString.split(':');
            const date = new Date();
            date.setHours(parseInt(hours), parseInt(minutes));
            return date.toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
        } catch (e) {
            return timeString;
        }
    }

    formatWeekday(weekday) {
        const labels = {
            1: 'Monday',
            2: 'Tuesday',
            3: 'Wednesday',
            4: 'Thursday',
            5: 'Friday',
            6: 'Saturday',
            7: 'Sunday'
        };
        const w = parseInt(weekday, 10);
        return labels[w] || 'N/A';
    }

    formatSlot(slot) {
        if (!slot) return 'N/A';
        const map = {
            'morning': 'Morning',
            'afternoon': 'Afternoon',
            'night': 'Night',
            'all_day': 'All Day'
        };
        return map[slot] || slot;
    }

    escapeHtml(text) {
        if (typeof text !== 'string') return text;
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.shiftManager = new ShiftManager();
});

// Global functions for backward compatibility
window.viewShift = (id) => window.shiftManager?.viewShift(id);
window.editShift = (id) => window.shiftManager?.editShift(id);
window.deleteShift = (id) => window.shiftManager?.deleteShift(id);
window.updateShiftStatus = (id, status) => window.shiftManager?.updateShiftStatus(id, status);
