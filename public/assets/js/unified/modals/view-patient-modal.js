/**
 * View Patient Modal Controller
 * Handles the view patient modal functionality
 */

const ViewPatientModal = {
    modal: null,
    currentPatientId: null,

    /**
     * Initialize the modal
     */
    init() {
        this.modal = document.getElementById('viewPatientModal');
        
        if (!this.modal) return;
        
        this.bindEvents();
    },

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Edit from view button
        const editFromViewBtn = document.getElementById('editFromViewBtn');
        if (editFromViewBtn) {
            editFromViewBtn.addEventListener('click', () => this.editFromView());
        }
        
        // Close modal when clicking outside
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.close();
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal.style.display === 'flex') {
                this.close();
            }
        });
    },

    /**
     * Open the modal
     */
    async open(patientId) {
        if (!this.modal || !patientId) return;
        
        this.currentPatientId = patientId;
        this.modal.style.display = 'flex';
        
        await this.loadPatientDetails(patientId);
    },

    /**
     * Close the modal
     */
    close() {
        if (this.modal) {
            this.modal.style.display = 'none';
            this.currentPatientId = null;
        }
    },

    /**
     * Load patient details
     */
    async loadPatientDetails(patientId) {
        const contentDiv = document.getElementById('viewPatientContent');
        
        try {
            PatientUtils.showLoading(contentDiv, 'Loading patient details...');
            
            const response = await PatientUtils.makeRequest(
                PatientConfig.getUrl(`${PatientConfig.endpoints.patientGet}/${patientId}`)
            );
            
            if (response.status === 'success') {
                this.displayPatientDetails(response.data);
            } else {
                throw new Error(response.message || 'Failed to load patient details');
            }
        } catch (error) {
            console.error('Error loading patient details:', error);
            PatientUtils.showError(contentDiv, 'Failed to load patient details');
        }
    },

    /**
     * Display patient details
     */
    displayPatientDetails(patient) {
        const contentDiv = document.getElementById('viewPatientContent');
        
        const fullName = PatientUtils.formatFullName(patient.first_name, patient.middle_name, patient.last_name);
        const age = PatientUtils.calculateAge(patient.date_of_birth);
        const statusBadge = PatientUtils.getStatusBadge(patient.status);
        const typeBadge = PatientUtils.getTypeBadge(patient.patient_type);
        
        contentDiv.innerHTML = `
            <!-- Personal Information Section -->
            <div style="margin-bottom: 2rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #4f46e5;">
                    <i class="fas fa-user" style="color: #4f46e5;"></i>
                    <h3 style="margin: 0; color: #4f46e5; font-size: 1.1rem; font-weight: 600;">Personal Information</h3>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <div>
                        <strong>Full Name:</strong><br>
                        <span style="color: #666;">${PatientUtils.escapeHtml(fullName)}</span>
                    </div>
                    <div>
                        <strong>Patient ID:</strong><br>
                        <span style="color: #666;">${PatientUtils.escapeHtml(patient.patient_id)}</span>
                    </div>
                    <div>
                        <strong>Gender:</strong><br>
                        <span style="color: #666;">${PatientUtils.escapeHtml(patient.gender || 'N/A')}</span>
                    </div>
                    <div>
                        <strong>Date of Birth:</strong><br>
                        <span style="color: #666;">${PatientUtils.formatDate(patient.date_of_birth)}</span>
                    </div>
                    <div>
                        <strong>Age:</strong><br>
                        <span style="color: #666;">${PatientUtils.escapeHtml(age)} years old</span>
                    </div>
                    <div>
                        <strong>Civil Status:</strong><br>
                        <span style="color: #666;">${PatientUtils.escapeHtml(patient.civil_status || 'N/A')}</span>
                    </div>
                </div>
            </div>

            <!-- Contact Information Section -->
            <div style="margin-bottom: 2rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #0ea5e9;">
                    <i class="fas fa-phone" style="color: #0ea5e9;"></i>
                    <h3 style="margin: 0; color: #0ea5e9; font-size: 1.1rem; font-weight: 600;">Contact Information</h3>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <div>
                        <strong>Phone:</strong><br>
                        <span style="color: #666;">${PatientUtils.escapeHtml(patient.contact_no || 'N/A')}</span>
                    </div>
                    <div>
                        <strong>Email:</strong><br>
                        <span style="color: #666;">${PatientUtils.escapeHtml(patient.email || 'N/A')}</span>
                    </div>
                    <div>
                        <strong>Address:</strong><br>
                        <span style="color: #666;">${PatientUtils.escapeHtml(patient.address || 'N/A')}</span>
                    </div>
                    <div>
                        <strong>Province:</strong><br>
                        <span style="color: #666;">${PatientUtils.escapeHtml(patient.province || 'N/A')}</span>
                    </div>
                    <div>
                        <strong>City:</strong><br>
                        <span style="color: #666;">${PatientUtils.escapeHtml(patient.city || 'N/A')}</span>
                    </div>
                    <div>
                        <strong>Barangay:</strong><br>
                        <span style="color: #666;">${PatientUtils.escapeHtml(patient.barangay || 'N/A')}</span>
                    </div>
                    <div>
                        <strong>ZIP Code:</strong><br>
                        <span style="color: #666;">${PatientUtils.escapeHtml(patient.zip_code || 'N/A')}</span>
                    </div>
                </div>
            </div>

            <!-- Medical Information Section -->
            <div style="margin-bottom: 2rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #10b981;">
                    <i class="fas fa-heartbeat" style="color: #10b981;"></i>
                    <h3 style="margin: 0; color: #10b981; font-size: 1.1rem; font-weight: 600;">Medical Information</h3>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <div>
                        <strong>Patient Type:</strong><br>
                        <span style="color: #666;">${typeBadge}</span>
                    </div>
                    <div>
                        <strong>Blood Group:</strong><br>
                        <span style="color: #666;">${PatientUtils.escapeHtml(patient.blood_group || 'N/A')}</span>
                    </div>
                    <div>
                        <strong>Status:</strong><br>
                        <span style="color: #666;">${statusBadge}</span>
                    </div>
                    <div>
                        <strong>Date Registered:</strong><br>
                        <span style="color: #666;">${PatientUtils.formatDate(patient.date_registered)}</span>
                    </div>
                    ${patient.medical_notes ? `
                        <div style="grid-column: 1 / -1;">
                            <strong>Medical Notes:</strong><br>
                            <span style="color: #666;">${PatientUtils.escapeHtml(patient.medical_notes)}</span>
                        </div>
                    ` : ''}
                </div>
            </div>

            <!-- Insurance & Emergency Contact Section -->
            <div style="margin-bottom: 2rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #f59e0b;">
                    <i class="fas fa-shield-alt" style="color: #f59e0b;"></i>
                    <h3 style="margin: 0; color: #f59e0b; font-size: 1.1rem; font-weight: 600;">Insurance & Emergency Contact</h3>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <div>
                        <strong>Insurance Provider:</strong><br>
                        <span style="color: #666;">${PatientUtils.escapeHtml(patient.insurance_provider || 'N/A')}</span>
                    </div>
                    <div>
                        <strong>Insurance Number:</strong><br>
                        <span style="color: #666;">${PatientUtils.escapeHtml(patient.insurance_number || 'N/A')}</span>
                    </div>
                    <div>
                        <strong>Emergency Contact:</strong><br>
                        <span style="color: #666;">${PatientUtils.escapeHtml(patient.emergency_contact || 'N/A')}</span>
                    </div>
                    <div>
                        <strong>Emergency Phone:</strong><br>
                        <span style="color: #666;">${PatientUtils.escapeHtml(patient.emergency_phone || 'N/A')}</span>
                    </div>
                </div>
            </div>
        `;
    },

    /**
     * Edit patient from view modal
     */
    editFromView() {
        if (this.currentPatientId && window.EditPatientModal) {
            this.close();
            window.EditPatientModal.open(this.currentPatientId);
        }
    }
};

// Export to global scope
window.ViewPatientModal = ViewPatientModal;

// Global function for close button
window.closeViewPatientModal = function() {
    if (window.ViewPatientModal) {
        window.ViewPatientModal.close();
    }
};
