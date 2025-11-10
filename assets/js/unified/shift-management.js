/**
 * Shift Management JavaScript
 * Handles all client-side functionality for the shift management system
 */

// Global variables
let shiftsData = [];
let currentFilters = {};

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Shift Management JS initialized');
    initializeShiftManagement();
});

/**
 * Initialize shift management functionality
 */
function initializeShiftManagement() {
    // Use initial data from PHP if available
    const initialShifts = window.initialShifts || [];
    if (initialShifts.length > 0) {
        shiftsData = initialShifts;
        renderShiftsTable(shiftsData);
    } else {
        loadShifts();
    }
    
    setupEventListeners();
    setupModals();
}

/**
 * Setup event listeners
 */
function setupEventListeners() {
    // Create shift button
    const createBtn = document.getElementById('createShiftBtn');
    if (createBtn) {
        createBtn.addEventListener('click', showCreateShiftModal);
    }

    // Export button
    const exportBtn = document.getElementById('exportBtn');
    if (exportBtn) {
        exportBtn.addEventListener('click', exportShifts);
    }

    // Form submit handlers
    const createForm = document.getElementById('shiftForm');
    if (createForm) {
        createForm.addEventListener('submit', handleCreateShift);
    }

    const editForm = document.getElementById('editShiftForm');
    if (editForm) {
        editForm.addEventListener('submit', handleEditShift);
    }

    // Filter controls (if they exist)
    setupFilterListeners();
}

/**
 * Setup modal functionality
 */
function setupModals() {
    // Close modal on overlay background click only
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-overlay')) {
            closeAllModals();
        }
    });

    // Specific close button handlers
    const closeButtons = ['closeShiftModal', 'closeViewShiftModal', 'closeEditShiftModal', 'cancelShiftBtn', 'cancelEditShiftBtn'];
    closeButtons.forEach(id => {
        const btn = document.getElementById(id);
        if (btn) {
            btn.addEventListener('click', closeAllModals);
        }
    });

    // Escape key to close modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllModals();
        }
    });
}

/**
 * Load shifts from API
 */
async function loadShifts() {
    try {
        showLoadingState();
        
        const response = await fetch(`${getBaseUrl()}shifts/api?${new URLSearchParams(currentFilters)}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const result = await response.json();
        
        if (result.status === 'success') {
            shiftsData = result.data;
            renderShiftsTable(shiftsData);
        } else {
            showError('Failed to load shifts: ' + result.message);
        }
    } catch (error) {
        console.error('Error loading shifts:', error);
        showError('Error loading shifts. Please try again.');
    }
}

/**
 * Render shifts table
 */
function renderShiftsTable(shifts) {
    console.log('Rendering shifts table with data:', shifts);
    
    const tbody = document.getElementById('shiftsTableBody');
    if (!tbody) {
        console.error('Shifts table body not found!');
        return;
    }

    if (shifts.length === 0) {
        console.log('No shifts to display');
        tbody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align: center; padding: 2rem;">
                    <i class="fas fa-calendar-times" style="font-size: 2rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                    <p style="color: #6b7280;">No shifts found</p>
                </td>
            </tr>
        `;
        return;
    }

    console.log('Rendering', shifts.length, 'shifts');
    tbody.innerHTML = shifts.map(shift => `
        <tr>
            <td>${escapeHtml(shift.doctor_name || 'N/A')}</td>
            <td>${formatDate(shift.shift_date)}</td>
            <td>${formatTime(shift.start_time)} - ${formatTime(shift.end_time)}</td>
            <td>${escapeHtml(shift.department || 'N/A')}</td>
            <td>${escapeHtml(shift.shift_type || 'N/A')}</td>
            <td>
                <span class="status-badge ${shift.status || 'scheduled'}">
                    ${escapeHtml(shift.status || 'scheduled')}
                </span>
            </td>
            <td>
                <div class="action-buttons">
                    <button class="btn-action btn-view" onclick="viewShift(${shift.id})" title="View">
                        <i class="fas fa-eye"></i>
                    </button>
                    ${canEditShift(shift) ? `
                        <button class="btn-action btn-edit" onclick="editShift(${shift.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                    ` : ''}
                    ${canDeleteShift(shift) ? `
                        <button class="btn-action btn-delete" onclick="deleteShift(${shift.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    ` : ''}
                </div>
            </td>
        </tr>
    `).join('');
    
    console.log('Table rendered successfully');
}

/**
 * Show loading state
 */
function showLoadingState() {
    const tbody = document.getElementById('shiftsTableBody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align: center; padding: 2rem;">
                    <div class="loading-spinner">
                        <i class="fas fa-spinner fa-spin"></i>
                        <p>Loading shifts...</p>
                    </div>
                </td>
            </tr>
        `;
    }
}

/**
 * Show create shift modal
 */
function showCreateShiftModal() {
    const modal = document.getElementById('shiftModal');
    if (modal) {
        resetCreateForm();
        modal.style.display = 'flex';
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        // Update modal title for create mode
        const titleElement = document.getElementById('modalTitle');
        if (titleElement) {
            titleElement.textContent = 'Create Shift';
        }
    }
}

/**
 * Show edit shift modal
 */
function editShift(shiftId) {
    const shift = shiftsData.find(s => s.id === shiftId);
    if (!shift) return;

    const modal = document.getElementById('editShiftModal');
    if (modal) {
        populateEditForm(shift);
        modal.style.display = 'flex';
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

/**
 * View shift details
 */
function viewShift(shiftId) {
    const shift = shiftsData.find(s => s.id === shiftId);
    if (!shift) return;

    const modal = document.getElementById('viewShiftModal');
    if (modal) {
        populateViewModal(shift);
        modal.style.display = 'flex';
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Delete shift
 */
async function deleteShift(shiftId) {
    if (!confirm('Are you sure you want to delete this shift?')) return;

    try {
        const response = await fetch(`${getBaseUrl()}shifts/delete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ id: shiftId })
        });

        const result = await response.json();
        
        if (result.status === 'success') {
            showSuccess('Shift deleted successfully');
            loadShifts();
        } else {
            showError('Failed to delete shift: ' + result.message);
        }
    } catch (error) {
        console.error('Error deleting shift:', error);
        showError('Error deleting shift. Please try again.');
    }
}

/**
 * Close all modals
 */
function closeAllModals() {
    const modals = document.querySelectorAll('.modal-overlay');
    modals.forEach(modal => {
        modal.style.display = 'none';
        modal.classList.remove('active');
    });
    document.body.style.overflow = '';
}

/**
 * Setup filter listeners
 */
function setupFilterListeners() {
    // Date filter
    const dateFilter = document.getElementById('dateFilter');
    if (dateFilter) {
        dateFilter.addEventListener('change', function() {
            currentFilters.date = this.value;
            loadShifts();
        });
    }

    // Status filter
    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            currentFilters.status = this.value;
            loadShifts();
        });
    }

    // Department filter
    const deptFilter = document.getElementById('departmentFilter');
    if (deptFilter) {
        deptFilter.addEventListener('change', function() {
            currentFilters.department = this.value;
            loadShifts();
        });
    }

    // Search filter
    const searchFilter = document.getElementById('searchFilter');
    if (searchFilter) {
        let searchTimeout;
        searchFilter.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentFilters.search = this.value;
                loadShifts();
            }, 300);
        });
    }
}

/**
 * Export shifts functionality
 */
function exportShifts() {
    // Simple CSV export
    const headers = ['Doctor', 'Date', 'Start Time', 'End Time', 'Department', 'Type', 'Status'];
    const rows = shiftsData.map(shift => [
        shift.doctor_name || '',
        shift.shift_date || '',
        shift.start_time || '',
        shift.end_time || '',
        shift.department || '',
        shift.shift_type || '',
        shift.status || ''
    ]);

    let csv = headers.join(',') + '\n';
    rows.forEach(row => {
        csv += row.map(cell => `"${cell}"`).join(',') + '\n';
    });

    downloadCSV(csv, 'shifts-export.csv');
}

/**
 * Download CSV file
 */
function downloadCSV(csv, filename) {
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

/**
 * Helper functions
 */
function getBaseUrl() {
    const meta = document.querySelector('meta[name="base-url"]');
    return meta ? meta.getAttribute('content') : '/';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

function formatTime(timeString) {
    if (!timeString) return 'N/A';
    const [hours, minutes] = timeString.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour % 12 || 12;
    return `${displayHour}:${minutes} ${ampm}`;
}

function calculateDuration(startTime, endTime) {
    if (!startTime || !endTime) return 'N/A';
    
    try {
        const [startHours, startMinutes] = startTime.split(':').map(Number);
        const [endHours, endMinutes] = endTime.split(':').map(Number);
        
        let startTotalMinutes = startHours * 60 + startMinutes;
        let endTotalMinutes = endHours * 60 + endMinutes;
        
        // Handle overnight shifts
        if (endTotalMinutes < startTotalMinutes) {
            endTotalMinutes += 24 * 60;
        }
        
        const durationMinutes = endTotalMinutes - startTotalMinutes;
        const hours = Math.floor(durationMinutes / 60);
        const minutes = durationMinutes % 60;
        
        if (minutes === 0) {
            return `${hours} hour${hours > 1 ? 's' : ''}`;
        } else {
            return `${hours}h ${minutes}m`;
        }
    } catch (error) {
        return 'N/A';
    }
}

function canEditShift(shift) {
    // Check user permissions here
    const userRole = window.userRole || document.querySelector('meta[name="user-role"]')?.getAttribute('content');
    return ['admin', 'it_staff'].includes(userRole);
}

function canDeleteShift(shift) {
    // Check user permissions here
    const userRole = window.userRole || document.querySelector('meta[name="user-role"]')?.getAttribute('content');
    return userRole === 'admin';
}

function showSuccess(message) {
    // Show success message (could use a toast library)
    alert('Success: ' + message);
}

function showError(message) {
    // Show error message (could use a toast library)
    alert('Error: ' + message);
}

function resetCreateForm() {
    const form = document.getElementById('shiftForm');
    if (form) {
        form.reset();
        // Reset hidden ID field
        const idField = document.getElementById('shiftId');
        if (idField) {
            idField.value = '';
        }
    }
}

function populateEditForm(shift) {
    // Populate edit form with shift data
    const form = document.getElementById('editShiftForm');
    if (form) {
        // Set form values based on shift data
        const doctorSelect = form.querySelector('#editDoctorSelect');
        const dateInput = form.querySelector('#editShiftDate');
        const startTimeInput = form.querySelector('#editShiftStart');
        const endTimeInput = form.querySelector('#editShiftEnd');
        const departmentSelect = form.querySelector('#editShiftDepartment');
        const typeSelect = form.querySelector('#editShiftType');
        const statusSelect = form.querySelector('#editShiftStatus');
        const notesInput = form.querySelector('#editShiftNotes');
        const idField = form.querySelector('#editShiftId');

        if (idField) idField.value = shift.id || '';
        if (doctorSelect) doctorSelect.value = shift.doctor_id || '';
        if (dateInput) dateInput.value = shift.shift_date || '';
        if (startTimeInput) startTimeInput.value = shift.start_time || '';
        if (endTimeInput) endTimeInput.value = shift.end_time || '';
        if (departmentSelect) departmentSelect.value = shift.department || '';
        if (typeSelect) typeSelect.value = shift.shift_type || '';
        if (statusSelect) statusSelect.value = shift.status || 'Scheduled';
        if (notesInput) notesInput.value = shift.notes || '';
    }
}

function populateViewModal(shift) {
    // Populate view modal with shift data
    const modal = document.getElementById('viewShiftModal');
    if (modal) {
        const elements = {
            'viewDoctorName': (shift.doctor_name || 'N/A') + (shift.specialization ? ' - ' + shift.specialization : ''),
            'viewShiftDate': formatDate(shift.shift_date),
            'viewShiftTime': `${formatTime(shift.start_time)} - ${formatTime(shift.end_time)}`,
            'viewShiftDuration': calculateDuration(shift.start_time, shift.end_time),
            'viewShiftDepartment': shift.department || 'N/A',
            'viewShiftType': shift.shift_type || 'N/A',
            'viewRoomWard': shift.room_ward || 'N/A',
            'viewShiftStatus': shift.status || 'Scheduled',
            'viewShiftNotes': shift.notes || 'No notes available'
        };

        Object.entries(elements).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) {
                if (id === 'viewShiftStatus') {
                    element.textContent = value;
                    element.className = `status-badge ${value.toLowerCase()}`;
                } else {
                    element.textContent = value;
                }
            }
        });
    }
}

/**
 * Handle create shift form submission
 */
async function handleCreateShift(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const response = await fetch(`${getBaseUrl()}shifts/create`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        
        if (result.status === 'success') {
            showSuccess('Shift created successfully');
            closeAllModals();
            loadShifts();
        } else {
            showError('Failed to create shift: ' + result.message);
        }
    } catch (error) {
        console.error('Error creating shift:', error);
        showError('Error creating shift. Please try again.');
    }
}

/**
 * Handle edit shift form submission
 */
async function handleEditShift(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const response = await fetch(`${getBaseUrl()}shifts/update`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        
        if (result.status === 'success') {
            showSuccess('Shift updated successfully');
            closeAllModals();
            loadShifts();
        } else {
            showError('Failed to update shift: ' + result.message);
        }
    } catch (error) {
        console.error('Error updating shift:', error);
        showError('Error updating shift. Please try again.');
    }
}
