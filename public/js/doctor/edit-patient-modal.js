/**
 * Edit Patient Modal JavaScript
 * Handles edit patient modal functionality
 */

const PatientEdit = {
    modal: null,
    form: null,
    
    init() {
        this.modal = document.getElementById('editPatientModal');
        this.form = document.getElementById('editPatientForm');
        
        if (this.form) {
            this.form.addEventListener('submit', this.handleSubmit.bind(this));
        }
        
        this.setupAgeCalculation();
        this.setupModalEvents();
    },
    
    async open(patientId) {
        if (!this.modal || !this.form) return;
        
        // Show modal
        this.modal.style.display = 'flex';
        
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
                this.populateForm(result.patient);
            } else {
                throw new Error(result.message || 'Patient not found');
            }
        } catch (error) {
            console.error('Error loading patient for edit:', error);
            alert('Error loading patient data: ' + error.message);
            this.close();
        }
    },
    
    close() {
        if (this.modal) {
            this.modal.style.display = 'none';
            if (this.form) this.form.reset();
            // Clear error messages
            document.querySelectorAll('[id^="edit_err_"]').forEach(el => el.textContent = '');
        }
    },
    
    populateForm(patient) {
        // Clear all error messages
        document.querySelectorAll('[id^="edit_err_"]').forEach(el => el.textContent = '');
        
        // Populate form fields
        const fields = {
            'editPatientId': patient.patient_id || '',
            'edit_first_name': patient.first_name || '',
            'edit_middle_name': patient.middle_name || '',
            'edit_last_name': patient.last_name || '',
            'edit_date_of_birth': patient.date_of_birth || '',
            'edit_age': patient.age || window.PatientUtils.calculateAge(patient.date_of_birth),
            'edit_gender': (patient.gender || '').toLowerCase(),
            'edit_civil_status': patient.civil_status || '',
            'edit_phone': patient.contact_no || '',
            'edit_email': patient.email || '',
            'edit_address': patient.address || '',
            'edit_province': patient.province || '',
            'edit_city': patient.city || '',
            'edit_barangay': patient.barangay || '',
            'edit_zip_code': patient.zip_code || '',
            'edit_insurance_provider': patient.insurance_provider || '',
            'edit_insurance_number': patient.insurance_number || '',
            'edit_emergency_contact_name': patient.emergency_contact || '',
            'edit_emergency_contact_phone': patient.emergency_phone || '',
            'edit_patient_type': patient.patient_type || '',
            'edit_status': patient.status || 'active',
            'edit_medical_notes': patient.medical_notes || ''
        };
        
        Object.entries(fields).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) element.value = value;
        });
    },
    
    setupAgeCalculation() {
        const dob = document.getElementById('edit_date_of_birth');
        const age = document.getElementById('edit_age');
        
        if (dob && age) {
            dob.addEventListener('change', function() {
                age.value = window.PatientUtils.calculateAge(this.value) || '';
            });
        }
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
    },
    
    async handleSubmit(e) {
        e.preventDefault();
        
        const btn = document.getElementById('updatePatientBtn');
        const patientId = document.getElementById('editPatientId').value;
        
        if (btn) {
            btn.disabled = true;
            btn.textContent = 'Updating...';
        }
        
        // Clear previous errors
        document.querySelectorAll('[id^="edit_err_"]').forEach(el => el.textContent = '');
        
        const formData = new FormData(this.form);
        const payload = {
            first_name: formData.get('first_name'),
            middle_name: formData.get('middle_name'),
            last_name: formData.get('last_name'),
            date_of_birth: formData.get('date_of_birth'),
            age: formData.get('age'),
            gender: formData.get('gender'),
            civil_status: formData.get('civil_status'),
            phone: formData.get('phone'),
            email: formData.get('email'),
            address: formData.get('address'),
            province: formData.get('province'),
            city: formData.get('city'),
            barangay: formData.get('barangay'),
            zip_code: formData.get('zip_code'),
            insurance_provider: formData.get('insurance_provider'),
            insurance_number: formData.get('insurance_number'),
            emergency_contact_name: formData.get('emergency_contact_name'),
            emergency_contact_phone: formData.get('emergency_contact_phone'),
            patient_type: formData.get('patient_type'),
            status: formData.get('status'),
            medical_notes: formData.get('medical_notes')
        };
        
        try {
            const response = await fetch(`${window.CONFIG.baseUrl}${window.CONFIG.endpoints.patient}${patientId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            });
            
            const result = await response.json();
            
            if (response.ok && result.status === 'success') {
                alert('Patient updated successfully!');
                this.close();
                if (window.PatientList) {
                    window.PatientList.loadPatients();
                }
            } else {
                if (result.errors) {
                    // Display field-specific errors
                    Object.keys(result.errors).forEach(field => {
                        const errorEl = document.getElementById(`edit_err_${field}`);
                        if (errorEl) {
                            errorEl.textContent = result.errors[field];
                        }
                    });
                } else {
                    alert(result.message || 'Failed to update patient');
                }
            }
        } catch (error) {
            console.error('Error updating patient:', error);
            alert('Network error. Please try again.');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.textContent = 'Update Patient';
            }
        }
    }
};

// Export for global access
window.PatientEdit = PatientEdit;