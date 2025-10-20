/**
 * View Patient Modal JavaScript
 * Handles view patient modal functionality
 */

const PatientView = {
    modal: null,
    content: null,
    
    init() {
        this.modal = document.getElementById('viewPatientModal');
        this.content = document.getElementById('viewPatientContent');
        this.setupModalEvents();
    },
    
    async open(patientId) {
        if (!this.modal || !this.content) return;
        
        // Show modal and loading state
        this.modal.style.display = 'flex';
        this.content.innerHTML = '<div style="text-align:center; padding:2rem; color:#6b7280;">Loading patient details...</div>';
        
        try {
            const response = await fetch(`${window.CONFIG.baseUrl}${window.CONFIG.endpoints.patient}${patientId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) throw new Error('Failed to fetch patient');
            
            const result = await response.json();
            if (result.status === 'success' && result.patient) {
                this.displayPatientDetails(result.patient);
            } else {
                throw new Error(result.message || 'Patient not found');
            }
        } catch (error) {
            console.error('Error loading patient:', error);
            this.content.innerHTML = `<div style="text-align:center; padding:2rem; color:#ef4444;">Error: ${error.message}</div>`;
        }
    },
    
    close() {
        if (this.modal) {
            this.modal.style.display = 'none';
        }
    },
    
    displayPatientDetails(patient) {
        const age = patient.age || window.PatientUtils.calculateAge(patient.date_of_birth);
        
        this.content.innerHTML = `
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap:1.5rem;">
                <div style="background:#f8fafc; padding:1rem; border-radius:6px;">
                    <h4 style="margin:0 0 0.5rem 0; color:#374151; font-size:0.9rem; text-transform:uppercase; letter-spacing:0.05em;">Personal Information</h4>
                    <div style="space-y:0.5rem;">
                        <p><strong>Name:</strong> ${window.PatientUtils.escapeHtml((patient.first_name || '') + ' ' + (patient.middle_name || '') + ' ' + (patient.last_name || ''))}</p>
                        <p><strong>Date of Birth:</strong> ${patient.date_of_birth || 'N/A'}</p>
                        <p><strong>Age:</strong> ${age}</p>
                        <p><strong>Gender:</strong> ${patient.gender || 'N/A'}</p>
                        <p><strong>Civil Status:</strong> ${patient.civil_status || 'N/A'}</p>
                    </div>
                </div>
                
                <div style="background:#f8fafc; padding:1rem; border-radius:6px;">
                    <h4 style="margin:0 0 0.5rem 0; color:#374151; font-size:0.9rem; text-transform:uppercase; letter-spacing:0.05em;">Contact Information</h4>
                    <div style="space-y:0.5rem;">
                        <p><strong>Phone:</strong> ${patient.contact_no || 'N/A'}</p>
                        <p><strong>Email:</strong> ${patient.email || 'N/A'}</p>
                        <p><strong>Address:</strong> ${patient.address || 'N/A'}</p>
                        <p><strong>City:</strong> ${patient.city || 'N/A'}</p>
                        <p><strong>Province:</strong> ${patient.province || 'N/A'}</p>
                        <p><strong>ZIP Code:</strong> ${patient.zip_code || 'N/A'}</p>
                    </div>
                </div>
                
                <div style="background:#f8fafc; padding:1rem; border-radius:6px;">
                    <h4 style="margin:0 0 0.5rem 0; color:#374151; font-size:0.9rem; text-transform:uppercase; letter-spacing:0.05em;">Medical Information</h4>
                    <div style="space-y:0.5rem;">
                        <p><strong>Patient Type:</strong> ${patient.patient_type || 'N/A'}</p>
                        <p><strong>Status:</strong> <span style="color:${patient.status === 'active' ? '#059669' : '#dc2626'}">${patient.status || 'N/A'}</span></p>
                        <p><strong>Blood Group:</strong> ${patient.blood_group || 'N/A'}</p>
                        <p><strong>Insurance Provider:</strong> ${patient.insurance_provider || 'N/A'}</p>
                        <p><strong>Insurance Number:</strong> ${patient.insurance_number || 'N/A'}</p>
                    </div>
                </div>
                
                <div style="background:#f8fafc; padding:1rem; border-radius:6px;">
                    <h4 style="margin:0 0 0.5rem 0; color:#374151; font-size:0.9rem; text-transform:uppercase; letter-spacing:0.05em;">Emergency Contact</h4>
                    <div style="space-y:0.5rem;">
                        <p><strong>Name:</strong> ${patient.emergency_contact || 'N/A'}</p>
                        <p><strong>Phone:</strong> ${patient.emergency_phone || 'N/A'}</p>
                    </div>
                </div>
                
                ${patient.medical_notes ? `
                <div style="grid-column: 1 / -1; background:#f8fafc; padding:1rem; border-radius:6px;">
                    <h4 style="margin:0 0 0.5rem 0; color:#374151; font-size:0.9rem; text-transform:uppercase; letter-spacing:0.05em;">Medical Notes</h4>
                    <p>${window.PatientUtils.escapeHtml(patient.medical_notes)}</p>
                </div>
                ` : ''}
            </div>
        `;
    },
    
    setupModalEvents() {
        // Close when clicking outside
        document.addEventListener('click', (e) => {
            if (this.modal && e.target === this.modal) {
                this.close();
            }
        });
        
        // Close on Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.close();
            }
        });
    }
};

// Export for global access
window.PatientView = PatientView;