/**
 * Unified Prescription Management JavaScript
 * Handles all prescription management functionality across different user roles
 */

class PrescriptionManager {
    constructor() {
        this.config = this.getConfig();
        this.filters = {};
        this.prescriptions = [];
        
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
                prescriptions: `${baseUrl}prescriptions/api`,
                create: `${baseUrl}prescriptions/create`,
                update: `${baseUrl}prescriptions/update`,
                delete: `${baseUrl}prescriptions/delete`,
                getPrescription: `${baseUrl}prescriptions`,
                updateStatus: `${baseUrl}prescriptions`,
                availablePatients: `${baseUrl}prescriptions/available-patients`,
                availableDoctors: `${baseUrl}prescriptions/available-doctors`
            }
        };
    }

    init() {
        this.bindEvents();
        this.loadPrescriptions();
        this.setupAutoRefresh();
    }

    bindEvents() {
        // Modal events
        this.bindModalEvents();
        
        // Filter events
        this.bindFilterEvents();
        
        // Form events
        this.bindFormEvents();
        
        // Action button events
        this.bindActionEvents();
    }

    bindModalEvents() {
        // Create prescription modal
        const createBtn = document.getElementById('createPrescriptionBtn');
        const prescriptionModal = document.getElementById('prescriptionModal');
        const closePrescriptionModal = document.getElementById('closePrescriptionModal');
        const cancelPrescriptionBtn = document.getElementById('cancelPrescriptionBtn');

        if (createBtn) {
            createBtn.addEventListener('click', () => this.openCreateModal());
        }

        if (closePrescriptionModal) {
            closePrescriptionModal.addEventListener('click', () => this.closePrescriptionModal());
        }

        if (cancelPrescriptionBtn) {
            cancelPrescriptionBtn.addEventListener('click', () => this.closePrescriptionModal());
        }

        // View prescription modal
        const viewPrescriptionModal = document.getElementById('viewPrescriptionModal');
        const closeViewPrescriptionModal = document.getElementById('closeViewPrescriptionModal');
        const closeViewPrescriptionBtn = document.getElementById('closeViewPrescriptionBtn');
        const editFromViewBtn = document.getElementById('editFromViewBtn');

        if (closeViewPrescriptionModal) {
            closeViewPrescriptionModal.addEventListener('click', () => this.closeViewPrescriptionModal());
        }

        if (closeViewPrescriptionBtn) {
            closeViewPrescriptionBtn.addEventListener('click', () => this.closeViewPrescriptionModal());
        }

        if (editFromViewBtn) {
            editFromViewBtn.addEventListener('click', () => this.editFromView());
        }

        // Click outside to close
        if (prescriptionModal) {
            prescriptionModal.addEventListener('click', (e) => {
                if (e.target === prescriptionModal) {
                    this.closePrescriptionModal();
                }
            });
        }

        if (viewPrescriptionModal) {
            viewPrescriptionModal.addEventListener('click', (e) => {
                if (e.target === viewPrescriptionModal) {
                    this.closeViewPrescriptionModal();
                }
            });
        }

        // Escape key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closePrescriptionModal();
                this.closeViewPrescriptionModal();
            }
        });
    }

    bindFilterEvents() {
        const dateFilter = document.getElementById('dateFilter');
        const statusFilter = document.getElementById('statusFilter');
        const searchFilter = document.getElementById('searchFilter');
        const clearFilters = document.getElementById('clearFilters');

        if (dateFilter) {
            dateFilter.addEventListener('change', () => this.applyFilters());
        }

        if (statusFilter) {
            statusFilter.addEventListener('change', () => this.applyFilters());
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

    bindFormEvents() {
        const prescriptionForm = document.getElementById('prescriptionForm');
        
        if (prescriptionForm) {
            prescriptionForm.addEventListener('submit', (e) => this.handleFormSubmit(e));
        }
    }

    bindActionEvents() {
        // Delegate event handling for dynamically created buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('.btn-view') || e.target.closest('.btn-view')) {
                const btn = e.target.matches('.btn-view') ? e.target : e.target.closest('.btn-view');
                const prescriptionId = btn.dataset.prescriptionId;
                if (prescriptionId) {
                    this.viewPrescription(prescriptionId);
                }
            }

            if (e.target.matches('.btn-edit') || e.target.closest('.btn-edit')) {
                const btn = e.target.matches('.btn-edit') ? e.target : e.target.closest('.btn-edit');
                const prescriptionId = btn.dataset.prescriptionId;
                if (prescriptionId) {
                    this.editPrescription(prescriptionId);
                }
            }

            if (e.target.matches('.btn-delete') || e.target.closest('.btn-delete')) {
                const btn = e.target.matches('.btn-delete') ? e.target : e.target.closest('.btn-delete');
                const prescriptionId = btn.dataset.prescriptionId;
                if (prescriptionId) {
                    this.deletePrescription(prescriptionId);
                }
            }

            if (e.target.matches('.btn-status') || e.target.closest('.btn-status')) {
                const btn = e.target.matches('.btn-status') ? e.target : e.target.closest('.btn-status');
                const prescriptionId = btn.dataset.prescriptionId;
                const status = btn.dataset.status;
                if (prescriptionId && status) {
                    this.updatePrescriptionStatus(prescriptionId, status);
                }
            }

            if (e.target.matches('.btn-dispense') || e.target.closest('.btn-dispense')) {
                const btn = e.target.matches('.btn-dispense') ? e.target : e.target.closest('.btn-dispense');
                const prescriptionId = btn.dataset.prescriptionId;
                if (prescriptionId) {
                    this.dispensePrescription(prescriptionId);
                }
            }
        });
    }

    async loadPrescriptions() {
        try {
            this.showLoading(true);
            
            const url = new URL(this.config.endpoints.prescriptions, window.location.origin);
            
            // Add filters to URL
            Object.keys(this.filters).forEach(key => {
                if (this.filters[key]) {
                    url.searchParams.append(key, this.filters[key]);
                }
            });

            const response = await fetch(url);
            const data = await response.json();

            if (data.status === 'success') {
                this.prescriptions = data.data || [];
                this.renderPrescriptions();
            } else {
                this.showError('Failed to load prescriptions: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error loading prescriptions:', error);
            this.showError('Failed to load prescriptions');
        } finally {
            this.showLoading(false);
        }
    }

    renderPrescriptions() {
        const tbody = document.getElementById('prescriptionsTableBody');
        if (!tbody) return;

        if (this.prescriptions.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="empty-state">
                        <i class="fas fa-prescription-bottle"></i>
                        <h3>No prescriptions found</h3>
                        <p>No prescriptions match your current filters.</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = this.prescriptions.map(prescription => this.renderPrescriptionRow(prescription)).join('');
    }

    renderPrescriptionRow(prescription) {
        const statusClass = prescription.status ? prescription.status.toLowerCase() : 'active';
        const canEdit = this.canEditPrescription(prescription);
        const canDelete = this.canDeletePrescription(prescription);
        const canDispense = this.canDispensePrescription(prescription);
        
        return `
            <tr class="fade-in">
                <td>
                    <div class="prescription-id">
                        <strong>${this.escapeHtml(prescription.prescription_id || 'N/A')}</strong>
                    </div>
                </td>
                <td>
                    <div class="patient-info">
                        <div class="patient-name">${this.escapeHtml(prescription.patient_name || 'Unknown')}</div>
                        <div class="patient-id">ID: ${this.escapeHtml(prescription.pat_id || 'N/A')}</div>
                    </div>
                </td>
                <td>
                    <div class="medication-info">
                        <div class="medication-name">${this.escapeHtml(prescription.medication || 'N/A')}</div>
                    </div>
                </td>
                <td>${this.escapeHtml(prescription.dosage || '-')}</td>
                <td>${this.escapeHtml(prescription.frequency || '-')}</td>
                <td>${this.formatDate(prescription.date_issued)}</td>
                <td>
                    <span class="status-badge ${statusClass}">
                        ${this.escapeHtml(prescription.status || 'Active')}
                    </span>
                </td>
                <td>
                    <div class="action-buttons">
                        <button type="button" class="btn btn-sm btn-view" data-prescription-id="${prescription.id}" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        ${canEdit ? `
                            <button type="button" class="btn btn-sm btn-edit" data-prescription-id="${prescription.id}" title="Edit Prescription">
                                <i class="fas fa-edit"></i>
                            </button>
                        ` : ''}
                        ${canDispense ? `
                            <button type="button" class="btn btn-sm btn-dispense" data-prescription-id="${prescription.id}" title="Dispense">
                                <i class="fas fa-pills"></i>
                            </button>
                        ` : ''}
                        ${this.canUpdateStatus(prescription) ? `
                            <button type="button" class="btn btn-sm btn-status" data-prescription-id="${prescription.id}" data-status="completed" title="Mark Completed">
                                <i class="fas fa-check"></i>
                            </button>
                        ` : ''}
                        ${canDelete ? `
                            <button type="button" class="btn btn-sm btn-delete" data-prescription-id="${prescription.id}" title="Delete Prescription">
                                <i class="fas fa-trash"></i>
                            </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `;
    }

    applyFilters() {
        this.filters = {
            date: document.getElementById('dateFilter')?.value || '',
            status: document.getElementById('statusFilter')?.value || '',
            search: document.getElementById('searchFilter')?.value || ''
        };

        this.loadPrescriptions();
    }

    clearFilters() {
        const dateFilter = document.getElementById('dateFilter');
        const statusFilter = document.getElementById('statusFilter');
        const searchFilter = document.getElementById('searchFilter');

        if (dateFilter) dateFilter.value = '';
        if (statusFilter) statusFilter.value = '';
        if (searchFilter) searchFilter.value = '';

        this.filters = {};
        this.loadPrescriptions();
    }

    async openCreateModal() {
        this.resetForm();
        document.getElementById('modalTitle').textContent = 'Create Prescription';
        document.getElementById('prescriptionId').value = '';
        
        const modal = document.getElementById('prescriptionModal');
        
        if (modal) {
            // Load available patients
            await this.loadAvailablePatients();
            
            // Add active class
            modal.classList.add('active');
            
            // Set inline styles for compatibility
            modal.style.display = 'flex';
            modal.style.position = 'fixed';
            modal.style.top = '0';
            modal.style.left = '0';
            modal.style.width = '100vw';
            modal.style.height = '100vh';
            modal.style.zIndex = '9999';
            modal.style.alignItems = 'center';
            modal.style.justifyContent = 'center';
            modal.style.background = 'rgba(15, 23, 42, 0.55)';
            
            document.body.style.overflow = 'hidden';
        }
    }

    async editPrescription(prescriptionId) {
        try {
            const response = await fetch(`${this.config.endpoints.getPrescription}/${prescriptionId}`);
            const data = await response.json();

            if (data.status === 'success' && data.data) {
                this.populateForm(data.data);
                document.getElementById('modalTitle').textContent = 'Edit Prescription';
                document.getElementById('prescriptionId').value = prescriptionId;
                document.getElementById('prescriptionModal').classList.add('active');
            } else {
                this.showError('Failed to load prescription details');
            }
        } catch (error) {
            console.error('Error loading prescription:', error);
            this.showError('Failed to load prescription details');
        }
    }

    async viewPrescription(prescriptionId) {
        try {
            const response = await fetch(`${this.config.endpoints.getPrescription}/${prescriptionId}`);
            const data = await response.json();

            if (data.status === 'success' && data.data) {
                this.populateViewModal(data.data);
                document.getElementById('viewPrescriptionModal').classList.add('active');
            } else {
                this.showError('Failed to load prescription details');
            }
        } catch (error) {
            console.error('Error loading prescription:', error);
            this.showError('Failed to load prescription details');
        }
    }

    async deletePrescription(prescriptionId) {
        if (!confirm('Are you sure you want to delete this prescription? This action cannot be undone.')) {
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
                    id: prescriptionId,
                    [this.config.csrfToken]: this.config.csrfHash
                })
            });

            const data = await response.json();

            if (data.status === 'success') {
                this.showSuccess('Prescription deleted successfully');
                this.loadPrescriptions();
            } else {
                this.showError(data.message || 'Failed to delete prescription');
            }

            // Update CSRF hash
            if (data.csrf) {
                this.config.csrfHash = data.csrf.value;
            }
        } catch (error) {
            console.error('Error deleting prescription:', error);
            this.showError('Failed to delete prescription');
        }
    }

    async updatePrescriptionStatus(prescriptionId, status) {
        try {
            const response = await fetch(`${this.config.endpoints.updateStatus}/${prescriptionId}/status`, {
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
                this.showSuccess(`Prescription marked as ${status.toLowerCase()}`);
                this.loadPrescriptions();
            } else {
                this.showError(data.message || 'Failed to update prescription status');
            }

            // Update CSRF hash
            if (data.csrf) {
                this.config.csrfHash = data.csrf.value;
            }
        } catch (error) {
            console.error('Error updating prescription status:', error);
            this.showError('Failed to update prescription status');
        }
    }

    async dispensePrescription(prescriptionId) {
        // For pharmacists - mark as dispensed
        await this.updatePrescriptionStatus(prescriptionId, 'completed');
    }

    async handleFormSubmit(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
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
                this.showSuccess(isEdit ? 'Prescription updated successfully' : 'Prescription created successfully');
                this.closePrescriptionModal();
                this.loadPrescriptions();
            } else {
                this.showError(result.message || 'Failed to save prescription');
                
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
            console.error('Error saving prescription:', error);
            this.showError('Failed to save prescription');
        } finally {
            this.showLoading(false, form);
        }
    }

    populateForm(prescription) {
        document.getElementById('patientSelect').value = prescription.patient_id || '';
        document.getElementById('prescriptionDate').value = prescription.date_issued || '';
        document.getElementById('medication').value = prescription.medication || '';
        document.getElementById('dosage').value = prescription.dosage || '';
        document.getElementById('frequency').value = prescription.frequency || '';
        document.getElementById('duration').value = prescription.duration || '';
        document.getElementById('prescriptionStatus').value = prescription.status || 'active';
        document.getElementById('prescriptionNotes').value = prescription.notes || '';
    }

    populateViewModal(prescription) {
        document.getElementById('viewPrescriptionId').textContent = prescription.prescription_id || 'N/A';
        document.getElementById('viewPrescriptionDate').textContent = this.formatDate(prescription.date_issued);
        document.getElementById('viewPrescriptionStatus').textContent = prescription.status || 'Active';
        document.getElementById('viewPrescriptionStatus').className = `status-badge ${(prescription.status || 'active').toLowerCase()}`;
        document.getElementById('viewDoctorName').textContent = prescription.doctor_name || 'Unknown';
        document.getElementById('viewPatientName').textContent = prescription.patient_name || 'Unknown';
        document.getElementById('viewPatientId').textContent = prescription.pat_id || 'N/A';
        document.getElementById('viewMedication').textContent = prescription.medication || 'N/A';
        document.getElementById('viewDosage').textContent = prescription.dosage || 'N/A';
        document.getElementById('viewFrequency').textContent = prescription.frequency || 'N/A';
        document.getElementById('viewDuration').textContent = prescription.duration || 'N/A';
        document.getElementById('viewPrescriptionNotes').textContent = prescription.notes || 'No notes available';

        // Store prescription data for edit functionality
        this.currentViewPrescription = prescription;
    }

    resetForm() {
        const form = document.getElementById('prescriptionForm');
        if (form) {
            form.reset();
            // Set default date to today
            document.getElementById('prescriptionDate').value = new Date().toISOString().split('T')[0];
        }
        this.clearValidationErrors();
    }

    closePrescriptionModal() {
        const modal = document.getElementById('prescriptionModal');
        if (modal) {
            modal.classList.remove('active');
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
        this.resetForm();
    }

    closeViewPrescriptionModal() {
        document.getElementById('viewPrescriptionModal').classList.remove('active');
        this.currentViewPrescription = null;
    }

    editFromView() {
        if (this.currentViewPrescription) {
            this.closeViewPrescriptionModal();
            this.populateForm(this.currentViewPrescription);
            document.getElementById('modalTitle').textContent = 'Edit Prescription';
            document.getElementById('prescriptionId').value = this.currentViewPrescription.id;
            document.getElementById('prescriptionModal').classList.add('active');
        }
    }

    setupAutoRefresh() {
        // Refresh prescriptions every 5 minutes
        setInterval(() => {
            this.loadPrescriptions();
        }, 5 * 60 * 1000);
    }

    // Permission methods
    canEditPrescription(prescription) {
        if (this.config.userRole === 'admin' || this.config.userRole === 'it_staff') {
            return true;
        }
        
        if (this.config.userRole === 'doctor') {
            // Doctors can edit their own prescriptions
            return prescription.doctor_id === this.getCurrentStaffId();
        }
        
        if (this.config.userRole === 'pharmacist') {
            // Pharmacists can update status
            return true;
        }
        
        return false;
    }

    canDeletePrescription(prescription) {
        if (this.config.userRole === 'admin') {
            return true;
        }
        
        if (this.config.userRole === 'doctor') {
            // Doctors can delete their own prescriptions if not yet dispensed
            return prescription.doctor_id === this.getCurrentStaffId() && 
                   ['active', 'pending'].includes(prescription.status);
        }
        
        return false;
    }

    canDispensePrescription(prescription) {
        return this.config.userRole === 'pharmacist' && 
               ['active', 'ready'].includes(prescription.status);
    }

    canUpdateStatus(prescription) {
        if (this.config.userRole === 'admin' || this.config.userRole === 'it_staff') {
            return true;
        }
        
        if (this.config.userRole === 'doctor') {
            return prescription.doctor_id === this.getCurrentStaffId() && 
                   prescription.status === 'active';
        }
        
        if (this.config.userRole === 'pharmacist') {
            return ['active', 'ready'].includes(prescription.status);
        }
        
        return false;
    }

    getCurrentStaffId() {
        // This would typically come from session data or user context
        return window.currentStaffId || null;
    }

    async loadAvailablePatients() {
        try {
            const patientSelect = document.getElementById('patientSelect');
            if (!patientSelect) {
                return;
            }

            // Show loading state
            patientSelect.innerHTML = '<option value="">Loading patients...</option>';
            patientSelect.disabled = true;

            const response = await fetch(this.config.endpoints.availablePatients);
            const data = await response.json();

            if (data.status === 'success' && Array.isArray(data.data)) {
                // Clear and populate dropdown
                patientSelect.innerHTML = '<option value="">Select Patient</option>';
                
                data.data.forEach(patient => {
                    const option = document.createElement('option');
                    option.value = patient.patient_id;
                    option.textContent = `${patient.first_name} ${patient.last_name} (ID: ${patient.patient_id})`;
                    patientSelect.appendChild(option);
                });
            } else {
                patientSelect.innerHTML = '<option value="">No patients available</option>';
            }

            patientSelect.disabled = false;

        } catch (error) {
            console.error('Error loading patients:', error);
            const patientSelect = document.getElementById('patientSelect');
            if (patientSelect) {
                patientSelect.innerHTML = '<option value="">Error loading patients</option>';
                patientSelect.disabled = false;
            }
            this.showError('Failed to load patients. Please refresh the page.');
        }
    }

    // Utility methods
    showLoading(show, element = null) {
        if (element) {
            element.classList.toggle('loading', show);
        } else {
            const tbody = document.getElementById('prescriptionsTableBody');
            if (tbody && show) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="loading-row">
                            <i class="fas fa-spinner fa-spin"></i> Loading prescriptions...
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

    escapeHtml(text) {
        if (typeof text !== 'string') return text;
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.prescriptionManager = new PrescriptionManager();
});

// Global functions for backward compatibility
window.viewPrescription = (id) => window.prescriptionManager?.viewPrescription(id);
window.editPrescription = (id) => window.prescriptionManager?.editPrescription(id);
window.deletePrescription = (id) => window.prescriptionManager?.deletePrescription(id);
window.updatePrescriptionStatus = (id, status) => window.prescriptionManager?.updatePrescriptionStatus(id, status);
