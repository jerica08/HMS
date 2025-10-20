/**
 * Patient Management JavaScript - Main Controller
 * Handles patient list and coordinates with modal modules
 */

// Configuration - Get base URL from meta tag
const CONFIG = {
    baseUrl: document.querySelector('meta[name="base-url"]')?.getAttribute('content') || '',
    endpoints: {
        patientsApi: 'doctor/patients/api',
        patients: 'doctor/patients',
        doctorsApi: 'doctor/doctors/api',
        assignDoctor: 'doctor/assign-doctor',
        patient: 'doctor/patient/'
    }
};

// Patient List Management
const PatientList = {
    tbody: null,
    
    init() {
        this.tbody = document.getElementById('doctorPatientsBody');
        if (this.tbody && !this.tbody.__bound) {
            this.tbody.__bound = true;
            this.tbody.addEventListener('click', this.handleButtonClick.bind(this));
        }
        // Load patients immediately if DOM is already loaded, otherwise wait
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.loadPatients());
        } else {
            this.loadPatients();
        }
    },
    
    async loadPatients() {
        if (!this.tbody) return;
        
        this.tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; color:#6b7280; padding:1rem;">Loading patients...</td></tr>';
        
        try {
            const response = await fetch(`${CONFIG.baseUrl}${CONFIG.endpoints.patientsApi}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            
            const json = await response.json();
            const list = Array.isArray(json?.data) ? json.data : (Array.isArray(json) ? json : []);
            
            if (!list.length) {
                this.tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; color:#6b7280; padding:1rem;">No patients found</td></tr>';
                return;
            }
            
            this.tbody.innerHTML = list.map(p => this.renderPatientRow(p)).join('');
        } catch (e) {
            console.error('Failed to load patients', e);
            this.tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; color:#ef4444; padding:1rem;">Failed to load patients</td></tr>';
        }
    },
    
    renderPatientRow(patient) {
        const name = window.PatientUtils.escapeHtml((patient.first_name || '') + ' ' + (patient.last_name || ''));
        const email = window.PatientUtils.escapeHtml(patient.email || '');
        const age = window.PatientUtils.calcAge(patient.date_of_birth);
        const id = window.PatientUtils.escapeHtml(patient.patient_id);
        const patientType = window.PatientUtils.escapeHtml(patient.patient_type || 'N/A');
        const assigned = window.PatientUtils.escapeHtml(patient.assigned_doctor_name || '');
        const status = window.PatientUtils.escapeHtml(patient.status || 'N/A');
        const init = window.PatientUtils.initials(patient.first_name, patient.last_name);
        
        return `
            <tr>
                <td>
                    <div style="display:flex; align-items:center; gap:0.75rem;">
                        <div class="patient-avatar" aria-label="Patient initials" title="Patient initials">${init}</div>
                        <div>
                            <div style="font-weight:500;">${name}</div>
                            <div style="font-size:0.8rem; color:#6b7280;">${email}</div>
                        </div>
                    </div>
                </td>
                <td>${id}</td>
                <td>${age}</td>
                <td><span style="text-transform:capitalize;">${patientType}</span></td>
                <td>${assigned}</td>
                <td>${status}</td>
                <td>
                    <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                        <button class="btn btn-secondary btn-small" data-action="view" data-id="${id}">View</button>
                        <button class="btn btn-primary btn-small" data-action="edit" data-id="${id}">Edit</button>
                        <button class="btn btn-success btn-small" data-action="assign" data-id="${id}">Assign Doctor</button>
                    </div>
                </td>
            </tr>`;
    },
    
    handleButtonClick(e) {
        const btn = e.target.closest('button[data-action]');
        if (!btn) return;
        
        const id = btn.getAttribute('data-id');
        const action = btn.getAttribute('data-action');
        
        switch (action) {
            case 'view':
                if (window.PatientView) window.PatientView.open(id);
                break;
            case 'edit':
                if (window.PatientEdit) window.PatientEdit.open(id);
                break;
            case 'assign':
                if (window.DoctorAssignment) window.DoctorAssignment.open(id);
                break;
        }
    }
};

// Global functions for backward compatibility
window.openAddPatientsModal = () => window.PatientAdd?.open();
window.closeAddPatientsModal = () => window.PatientAdd?.close();
window.closeAssignDoctorModal = () => window.DoctorAssignment?.close();
window.closeViewPatientModal = () => window.PatientView?.close();
window.closeEditPatientModal = () => window.PatientEdit?.close();
window.viewPatient = (id) => window.PatientView?.open(id);
window.editPatient = (id) => window.PatientEdit?.open(id);
window.openAssignDoctorModal = (id) => window.DoctorAssignment?.open(id);
window.loadPatients = () => PatientList.loadPatients();

// Export global objects
window.CONFIG = CONFIG;
window.PatientList = PatientList;

// Initialize all modules when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize main patient list
    PatientList.init();
    
    // Initialize modal modules if they exist
    if (window.PatientAdd) window.PatientAdd.init();
    if (window.DoctorAssignment) window.DoctorAssignment.init();
    if (window.PatientView) window.PatientView.init();
    if (window.PatientEdit) window.PatientEdit.init();
});