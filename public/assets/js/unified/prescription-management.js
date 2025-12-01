/**
 * Unified Prescription Management JavaScript
 * Handles all prescription management functionality across different user roles
 */

class PrescriptionManager {
    constructor() {
        this.config = this.getConfig();
        this.filters = {};
        this.prescriptions = [];
        this.medicationOptionsCache = null; // cache medications for all rows
        
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
                availableDoctors: `${baseUrl}prescriptions/available-doctors`,
                availableMedications: `${baseUrl}prescriptions/available-medications`,
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
        const completeFromViewBtn = document.getElementById('completeFromViewBtn');

        if (closeViewPrescriptionModal) {
            closeViewPrescriptionModal.addEventListener('click', () => this.closeViewPrescriptionModal());
        }

        if (closeViewPrescriptionBtn) {
            closeViewPrescriptionBtn.addEventListener('click', () => this.closeViewPrescriptionModal());
        }

        if (editFromViewBtn) {
            editFromViewBtn.addEventListener('click', () => this.editFromView());
        }

        if (completeFromViewBtn) {
            completeFromViewBtn.addEventListener('click', () => {
                if (this.currentViewPrescription && this.currentViewPrescription.id) {
                    this.completePrescription(this.currentViewPrescription.id);
                }
            });
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

    // Billing helpers for prescriptions
    openBillingModal(prescriptionId, patientName, medication) {
        const modal = document.getElementById('prescriptionBillingModal');
        const idInput = document.getElementById('billing_prescription_id');
        const amountInput = document.getElementById('billing_prescription_amount');
        const qtyInput = document.getElementById('billing_prescription_quantity');
        const info = document.getElementById('billingPrescriptionInfo');
        if (!modal || !idInput || !amountInput || !qtyInput) return;

        idInput.value = prescriptionId || '';
        amountInput.value = '';
        qtyInput.value = '1';
        if (info) {
            info.textContent = `Prescription #${prescriptionId} • ${patientName || 'Unknown patient'} • ${medication || ''}`;
        }

        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
    }

    closeBillingModal() {
        const modal = document.getElementById('prescriptionBillingModal');
        if (!modal) return;
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
    }

    async submitBillingForm(e) {
        e.preventDefault();
        const idInput = document.getElementById('billing_prescription_id');
        const amountInput = document.getElementById('billing_prescription_amount');
        const qtyInput = document.getElementById('billing_prescription_quantity');
        if (!idInput || !amountInput || !qtyInput) return;

        const prescriptionId = parseInt(idInput.value, 10);
        const unitPrice = parseFloat(amountInput.value);
        const quantity = parseInt(qtyInput.value, 10) || 1;

        if (!prescriptionId || isNaN(unitPrice) || unitPrice <= 0) {
            this.showError('Please enter a valid positive amount.');
            return;
        }

        try {
            const url = `${this.config.baseUrl}/prescriptions/${prescriptionId}/bill`;
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': this.config.csrfHash
                },
                body: JSON.stringify({
                    unit_price: unitPrice,
                    quantity: quantity
                })
            });

            const data = await res.json();
            if (data.success) {
                this.showSuccess(data.message || 'Prescription added to billing.');
                this.closeBillingModal();
            } else {
                this.showError(data.message || 'Failed to add prescription to billing.');
            }
        } catch (err) {
            console.error('Error adding prescription to billing:', err);
            this.showError('Failed to add prescription to billing. Please try again.');
        }
    }

    bindFormEvents() {
        const prescriptionForm = document.getElementById('prescriptionForm');
        
        if (prescriptionForm) {
            prescriptionForm.addEventListener('submit', (e) => this.handleFormSubmit(e));
        }

        // Add medicine row button
        const addBtn = document.getElementById('addMedicineRowBtn');
        if (addBtn) {
            addBtn.addEventListener('click', () => this.addMedicineRow());
        }

        // Remove medicine row (event delegation)
        const medicinesBody = document.getElementById('medicinesTableBody');
        if (medicinesBody) {
            medicinesBody.addEventListener('click', (e) => {
                const removeBtn = e.target.closest('.remove-medicine-row');
                if (removeBtn) {
                    const row = removeBtn.closest('.medicine-row');
                    if (row && medicinesBody.children.length > 1) {
                        row.remove();
                    }
                }
            });
        }

        const billingForm = document.getElementById('prescriptionBillingForm');
        if (billingForm) {
            billingForm.addEventListener('submit', (e) => this.submitBillingForm(e));
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
                const action = btn.dataset.action;
                if (prescriptionId && status) {
                    // Use completePrescription for complete action (includes confirmation)
                    if (action === 'complete' && status === 'completed') {
                        this.completePrescription(prescriptionId);
                    } else {
                        this.updatePrescriptionStatus(prescriptionId, status);
                    }
                }
            }

            if (e.target.matches('.btn-dispense') || e.target.closest('.btn-dispense')) {
                const btn = e.target.matches('.btn-dispense') ? e.target : e.target.closest('.btn-dispense');
                const prescriptionId = btn.dataset.prescriptionId;
                if (prescriptionId) {
                    this.dispensePrescription(prescriptionId);
                }
            }

            if (e.target.matches('.btn-bill') || e.target.closest('.btn-bill')) {
                const btn = e.target.matches('.btn-bill') ? e.target : e.target.closest('.btn-bill');
                const prescriptionId = btn.dataset.prescriptionId;
                const patientName = btn.dataset.patientName || '';
                const medication = btn.dataset.medication || '';
                if (prescriptionId) {
                    this.openBillingModal(prescriptionId, patientName, medication);
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
        
        if (!tbody) {
            return;
        }

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
        const canBillRole = ['admin', 'accountant', 'pharmacist'].includes(this.config.userRole || '');
        const statusNormalized = (prescription.status || '').toLowerCase();
        const canBillStatus = ['completed', 'dispensed'].includes(statusNormalized);
        const canBill = canBillRole && canBillStatus;
        
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
                <td>${this.formatDate(prescription.created_at)}</td>
                <td>
                    <span class="status-badge ${statusClass}">
                        ${this.escapeHtml(prescription.status || 'Active')}
                    </span>
                </td>
                <td>
                    <div class="action-buttons">
                        <button type="button" class="btn btn-sm btn-primary btn-view" data-prescription-id="${prescription.id}" data-action="view" title="View Details">
                            <i class="fas fa-eye"></i> View
                        </button>
                        ${canEdit ? `
                            <button type="button" class="btn btn-sm btn-warning btn-edit" data-prescription-id="${prescription.id}" data-action="edit" title="Edit Prescription">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                        ` : ''}
                        ${canDispense ? `
                            <button type="button" class="btn btn-sm btn-success btn-dispense" data-prescription-id="${prescription.id}" data-action="dispense" title="Dispense">
                                <i class="fas fa-pills"></i> Dispense
                            </button>
                        ` : ''}
                        ${canBill ? `
                            <button type="button" class="btn btn-sm btn-info btn-bill" data-prescription-id="${prescription.id}" data-patient-name="${this.escapeHtml(prescription.patient_name || '')}" data-medication="${this.escapeHtml(prescription.medication || '')}" title="Add to Bill">
                                <i class="fas fa-file-invoice-dollar"></i> Add to Bill
                            </button>
                        ` : ''}
                        ${this.canUpdateStatus(prescription) ? `
                            <button type="button" class="btn btn-sm btn-success btn-status" data-prescription-id="${prescription.id}" data-action="complete" data-status="completed" title="Mark Completed">
                                <i class="fas fa-check"></i> Complete
                            </button>
                        ` : ''}
                        ${canDelete ? `
                            <button type="button" class="btn btn-sm btn-danger btn-delete" data-prescription-id="${prescription.id}" data-action="delete" title="Delete Prescription">
                                <i class="fas fa-trash"></i> Delete
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

            // Load available medications from Resource Management
            await this.loadAvailableMedications();
            
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
                // Load available patients first
                await this.loadAvailablePatients();

                // Load medications so selector is populated before filling form
                await this.loadAvailableMedications();
                
                // Populate form with prescription data
                this.populateForm(data.data);
                
                // Set modal title and ID
                document.getElementById('modalTitle').textContent = 'Edit Prescription';
                document.getElementById('prescriptionId').value = prescriptionId;
                
                // Open modal with proper styling
                const modal = document.getElementById('prescriptionModal');
                if (modal) {
                    modal.classList.add('active');
                    
                    // Set inline styles for proper display
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
            } else {
                this.showError('Failed to load prescription details');
            }
        } catch (error) {
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
            this.showError('Failed to update prescription status');
        }
    }

    async dispensePrescription(prescriptionId) {
        // For pharmacists - mark as dispensed
        await this.updatePrescriptionStatus(prescriptionId, 'completed');
    }

    async completePrescription(prescriptionId) {
        if (!confirm('Are you sure you want to mark this prescription as completed?')) {
            return;
        }

        try {
            const response = await fetch(`${this.config.endpoints.updateStatus}/${prescriptionId}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    status: 'completed',
                    [this.config.csrfToken]: this.config.csrfHash
                })
            });

            const data = await response.json();

            if (data.status === 'success') {
                this.showSuccess('Prescription marked as completed');
                this.closeViewPrescriptionModal();
                this.loadPrescriptions();
            } else {
                this.showError(data.message || 'Failed to complete prescription');
            }

            // Update CSRF hash
            if (data.csrf) {
                this.config.csrfHash = data.csrf.value;
            }
        } catch (error) {
            console.error('Complete prescription error:', error);
            this.showError('Failed to complete prescription');
        }
    }

    async handleFormSubmit(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        // Build items[] from medicines table
        const rows = Array.from(document.querySelectorAll('#medicinesTableBody .medicine-row'));
        const items = [];

        rows.forEach(row => {
            const medSelect = row.querySelector('select[name="medication_resource_id[]"]');
            const nameInput = row.querySelector('input[name="medication_name[]"]');
            const dosage    = row.querySelector('input[name="dosage[]"]');
            const freq      = row.querySelector('select[name="frequency[]"]');
            const duration  = row.querySelector('select[name="duration[]"]');
            const qtyInput  = row.querySelector('input[name="quantity[]"]');

            const medicationName = (nameInput?.value || '').trim();
            const quantity       = parseInt(qtyInput?.value || '0', 10);

            // Skip completely empty rows
            if (!medicationName && !quantity) {
                return;
            }

            if (!medicationName || quantity <= 0) {
                // Basic client-side validation; backend will also validate
                return;
            }

            items.push({
                medication_resource_id: medSelect?.value || null,
                medication_name: medicationName,
                dosage: dosage?.value || '',
                frequency: freq?.value || '',
                duration: duration?.value || '',
                quantity: quantity
            });
        });

        if (!items.length) {
            this.showError('Please add at least one medicine with a name and quantity.');
            return;
        }

        data.items = items;

        // Remove legacy single-medication fields if present
        delete data.medication_resource_id;
        delete data.medication;
        delete data.dosage;
        delete data.frequency;
        delete data.duration;
        delete data.quantity;

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
            this.showError('Failed to save prescription');
        } finally {
            this.showLoading(false, form);
        }
    }

    populateForm(prescription) {
        console.log('Populating form with:', prescription);
        
        // Check if prescription object exists
        if (!prescription) {
            console.error('Cannot populate form: prescription object is null');
            this.showError('Failed to load prescription data');
            return;
        }
        
        // Helper function to safely set field values
        const setFieldValue = (fieldId, value) => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.value = value || '';
            }
        };
        
        // Ensure patient dropdown reflects the prescription's patient
        const selectedPatientId = prescription.patient_id || prescription.pat_id || '';
        setFieldValue('patientSelect', selectedPatientId);
        
        // Set doctor field if it exists (admin only)
        if (this.config.userRole === 'admin' && prescription.doctor_id) {
            setFieldValue('doctorSelect', prescription.doctor_id);
        }
        
        setFieldValue('prescriptionDate', prescription.created_at ? prescription.created_at.split(' ')[0] : '');

        // Clear existing medicine rows except the first template
        const medicinesBody = document.getElementById('medicinesTableBody');
        if (medicinesBody) {
            const rows = Array.from(medicinesBody.querySelectorAll('.medicine-row'));
            rows.slice(1).forEach(r => r.remove());
        }

        // Ensure medications are loaded
        this.loadAvailableMedications().then(() => {
            const items = Array.isArray(prescription.items) && prescription.items.length
                ? prescription.items
                : [{
                    medication_name: prescription.medication,
                    dosage: prescription.dosage,
                    frequency: prescription.frequency,
                    duration: prescription.duration,
                    quantity: prescription.quantity
                }];

            // Fill first row, then add extra rows as needed
            if (medicinesBody) {
                const firstRow = medicinesBody.querySelector('.medicine-row');
                if (firstRow) {
                    this.fillMedicineRow(firstRow, items[0]);
                }

                for (let i = 1; i < items.length; i++) {
                    this.addMedicineRow(items[i]);
                }
            }
        });
        setFieldValue('prescriptionStatus', prescription.status || 'queued');
        setFieldValue('prescriptionNotes', prescription.notes);
        
        // Store the prescription ID for update
        const idField = document.getElementById('prescriptionId');
        if (idField) {
            idField.value = prescription.id || '';
        }
    }

    populateViewModal(prescription) {
        console.log('Populating view modal with:', prescription);
        
        // Safely set text content with null checks
        const setElementText = (id, value) => {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = value || 'N/A';
            } else {
                console.warn(`Element not found: ${id}`);
            }
        };
        
        setElementText('viewPrescriptionId', prescription.prescription_id);
        setElementText('viewPrescriptionDate', this.formatDate(prescription.created_at));
        setElementText('viewDoctorName', prescription.prescriber || 'Unknown');
        setElementText('viewPatientName', prescription.patient_name || 'Unknown');
        setElementText('viewPatientId', prescription.pat_id);
        // Render medicines table
        const body = document.getElementById('viewMedicinesBody');
        if (body) {
            const items = Array.isArray(prescription.items) && prescription.items.length
                ? prescription.items
                : [{
                    medication_name: prescription.medication,
                    dosage: prescription.dosage,
                    frequency: prescription.frequency,
                    duration: prescription.duration,
                    quantity: prescription.quantity
                }];

            body.innerHTML = items.map(item => `
                <tr>
                    <td>${this.escapeHtml(item.medication_name || prescription.medication || 'N/A')}</td>
                    <td>${this.escapeHtml(item.dosage || '')}</td>
                    <td>${this.escapeHtml(item.frequency || '')}</td>
                    <td>${this.escapeHtml(item.duration || '')}</td>
                    <td>${this.escapeHtml(String(item.quantity || ''))}</td>
                </tr>
            `).join('');
        }
        setElementText('viewPrescriptionNotes', prescription.notes || 'No notes available');
        
        // Handle status badge specially
        const statusElement = document.getElementById('viewPrescriptionStatus');
        if (statusElement) {
            statusElement.textContent = prescription.status || 'Queued';
            statusElement.className = `status-badge ${(prescription.status || 'queued').toLowerCase()}`;
        }

        // Store prescription data for edit functionality
        this.currentViewPrescription = prescription;
    }

    resetForm() {
        const form = document.getElementById('prescriptionForm');
        if (form) {
            form.reset();
            // Set default date to today
            const dateField = document.getElementById('prescriptionDate');
            if (dateField) {
                dateField.value = new Date().toISOString().split('T')[0];
            }
        }

        // Reset medicines table to a single empty row
        const medicinesBody = document.getElementById('medicinesTableBody');
        if (medicinesBody) {
            const rows = Array.from(medicinesBody.querySelectorAll('.medicine-row'));
            rows.slice(1).forEach(r => r.remove());
            const firstRow = medicinesBody.querySelector('.medicine-row');
            if (firstRow) {
                firstRow.querySelectorAll('input, select').forEach(input => {
                    input.value = '';
                });
            }
        }

        this.clearValidationErrors();
    }

    /**
     * Load medications from Resource Management into all medication selectors.
     */
    async loadAvailableMedications() {
        // If already loaded, just populate all selects
        if (this.medicationOptionsCache !== null) {
            this.populateAllMedicationSelects();
            return;
        }

        try {
            const response = await fetch(this.config.endpoints.availableMedications);
            const data = await response.json();

            if (data.status === 'success' && Array.isArray(data.data)) {
                this.medicationOptionsCache = data.data;
            } else {
                this.medicationOptionsCache = [];
            }
        } catch (error) {
            console.error('Error loading medications:', error);
            this.medicationOptionsCache = [];
        }

        this.populateAllMedicationSelects();
    }

    populateAllMedicationSelects() {
        const selects = document.querySelectorAll('.medicine-medication-select');
        selects.forEach(select => this.populateMedicationSelect(select));
    }

    populateMedicationSelect(select) {
        if (!select) return;

        select.innerHTML = '<option value="">Select medication</option>';

        if (!Array.isArray(this.medicationOptionsCache) || !this.medicationOptionsCache.length) {
            select.innerHTML = '<option value="">No medications available</option>';
            return;
        }

        this.medicationOptionsCache.forEach(med => {
            const opt = document.createElement('option');
            opt.value = med.id;
            opt.textContent = `${med.equipment_name} (Stock: ${med.quantity})`;
            opt.dataset.name = med.equipment_name;
            select.appendChild(opt);
        });

        // Keep hidden medication name in sync with selected resource
        select.addEventListener('change', () => {
            const selected = select.options[select.selectedIndex];
            const hidden = select.closest('td')?.querySelector('.medicine-name-hidden');
            if (hidden) {
                hidden.value = selected && selected.dataset ? (selected.dataset.name || selected.textContent || '') : '';
            }
        });
    }

    addMedicineRow(item = null) {
        const body = document.getElementById('medicinesTableBody');
        if (!body) return;

        const template = body.querySelector('.medicine-row');
        if (!template) return;

        const row = template.cloneNode(true);
        row.querySelectorAll('input, select').forEach(input => {
            input.value = '';
        });

        body.appendChild(row);

        const select = row.querySelector('.medicine-medication-select');
        this.populateMedicationSelect(select);

        if (item) {
            this.fillMedicineRow(row, item);
        }
    }

    fillMedicineRow(row, item) {
        if (!row || !item) return;

        const select   = row.querySelector('.medicine-medication-select');
        const nameHide = row.querySelector('.medicine-name-hidden');
        const dosage   = row.querySelector('input[name="dosage[]"]');
        const freq     = row.querySelector('select[name="frequency[]"]');
        const duration = row.querySelector('select[name="duration[]"]');
        const quantity = row.querySelector('input[name="quantity[]"]');

        if (nameHide) {
            nameHide.value = item.medication_name || '';
        }
        if (dosage) dosage.value = item.dosage || '';
        if (freq) freq.value = item.frequency || '';
        if (duration) duration.value = item.duration || '';
        if (quantity) quantity.value = item.quantity || '';

        if (select && Array.isArray(this.medicationOptionsCache)) {
            // try by resource id first
            if (item.medication_resource_id) {
                select.value = item.medication_resource_id;
            } else if (item.medication_name) {
                // fallback match by name
                const target = item.medication_name.toLowerCase();
                for (let i = 0; i < select.options.length; i++) {
                    const opt = select.options[i];
                    const name = (opt.dataset?.name || opt.textContent || '').toLowerCase();
                    if (name.startsWith(target)) {
                        select.selectedIndex = i;
                        break;
                    }
                }
            }
        }
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
            const modalTitle = document.getElementById('modalTitle');
            if (modalTitle) modalTitle.textContent = 'Edit Prescription';
            const prescriptionId = document.getElementById('prescriptionId');
            if (prescriptionId) prescriptionId.value = this.currentViewPrescription.id;
            const modal = document.getElementById('prescriptionModal');
            if (modal) modal.classList.add('active');
        } else {
            console.error('No prescription data available to edit');
            this.showError('Failed to load prescription for editing');
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

    async loadAvailableDoctors() {
        try {
            const doctorSelect = document.getElementById('doctorSelect');
            if (!doctorSelect) {
                return;
            }

            // Show loading state
            doctorSelect.innerHTML = '<option value="">Loading doctors...</option>';
            doctorSelect.disabled = true;

            const response = await fetch(this.config.endpoints.availableDoctors);
            const data = await response.json();

            if (data.status === 'success' && Array.isArray(data.data)) {
                // Clear and populate dropdown
                doctorSelect.innerHTML = '<option value="">Select Doctor</option>';
                
                data.data.forEach(doctor => {
                    const option = document.createElement('option');
                    option.value = doctor.staff_id;
                    const doctorInfo = `${doctor.first_name} ${doctor.last_name}`;
                    const specialty = doctor.specialization ? ` - ${doctor.specialization}` : '';
                    option.textContent = doctorInfo + specialty;
                    doctorSelect.appendChild(option);
                });
            } else {
                doctorSelect.innerHTML = '<option value="">No doctors available</option>';
            }

            doctorSelect.disabled = false;

        } catch (error) {
            console.error('Error loading doctors:', error);
            const doctorSelect = document.getElementById('doctorSelect');
            if (doctorSelect) {
                doctorSelect.innerHTML = '<option value="">Error loading doctors</option>';
                doctorSelect.disabled = false;
            }
            this.showError('Failed to load doctors. Please refresh the page.');
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
        const container = document.getElementById('prescriptionsNotification');
        const iconEl = document.getElementById('prescriptionsNotificationIcon');
        const textEl = document.getElementById('prescriptionsNotificationText');

        // Prefer shared top bar when available
        if (container && iconEl && textEl) {
            const isError = type === 'error';
            const isSuccess = type === 'success';

            container.style.border = isError ? '1px solid #fecaca' : '1px solid #bbf7d0';
            container.style.background = isError ? '#fee2e2' : '#ecfdf5';
            container.style.color = isError ? '#991b1b' : '#166534';

            const iconClass = isError
                ? 'fa-exclamation-triangle'
                : (isSuccess ? 'fa-check-circle' : 'fa-info-circle');
            iconEl.className = 'fas ' + iconClass;

            textEl.textContent = this.escapeHtml(message || '');
            container.style.display = 'flex';

            setTimeout(() => {
                if (container.style.display !== 'none') {
                    container.style.display = 'none';
                }
            }, 4000);
            return;
        }

        // Fallback: inline alert at top of content
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} fade-in`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            ${this.escapeHtml(message)}
        `;

        const content = document.querySelector('.content');
        if (content) {
            content.insertBefore(notification, content.firstChild);
        }

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

// Helper for shared prescriptions notification bar
function dismissPrescriptionsNotification() {
    const container = document.getElementById('prescriptionsNotification');
    if (container) {
        container.style.display = 'none';
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
