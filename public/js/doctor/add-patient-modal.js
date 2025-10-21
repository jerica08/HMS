/**
 * Add Patient Modal JavaScript
 * Handles add patient modal functionality
 */

const PatientAdd = {
    modal: null,
    form: null,
    
    init() {
        this.modal = document.getElementById('patientModal');
        this.form = document.getElementById('patientForm');
        
        // Setup event listeners
        const addBtn = document.getElementById('addPatientBtn');
        if (addBtn) addBtn.addEventListener('click', () => this.open());
        
        if (this.form) {
            this.form.addEventListener('submit', this.handleSubmit.bind(this));
        }
        
        // Setup age calculation
        this.setupAgeCalculation();
        
        // Setup modal close events
        this.setupModalEvents();
    },
    
    open() {
        if (this.modal) {
            this.modal.style.display = 'flex';
        }
    },
    
    close() {
        if (this.modal) {
            this.modal.style.display = 'none';
            if (this.form) this.form.reset();
        }
    },
    
    setupAgeCalculation() {
        const dob = document.getElementById('date_of_birth');
        const age = document.getElementById('age');
        
        if (dob && age) {
            dob.addEventListener('change', function() {
                const calculatedAge = window.PatientUtils.calculateAge(this.value);
                age.value = calculatedAge || '';
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
        
        const btn = document.getElementById('savePatientBtn');
        if (btn) {
            btn.disabled = true;
            btn.textContent = 'Saving...';
        }
        
        const payload = this.collectFormData();
        
        try {
            const response = await fetch(`${window.CONFIG.baseUrl}${window.CONFIG.endpoints.patients}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload),
                credentials: 'same-origin'
            });
            
            const result = await response.json().catch(() => ({}));
            
            if (response.ok && result.status === 'success') {
                alert('Patient saved successfully');
                this.close();
                window.location.reload();
            } else {
                let msg = result.message || 'Failed to save patient';
                if (result.errors) {
                    const details = Object.values(result.errors).join('\n');
                    msg += '\n\n' + details;
                }
                alert(msg);
            }
        } catch (err) {
            console.error('Error saving patient', err);
            alert('Network error. Please try again.');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.textContent = 'Save Patient';
            }
        }
    },
    
    collectFormData() {
        const getVal = (id) => {
            const el = document.getElementById(id);
            return el ? el.value : null;
        };
        
        return {
            first_name: getVal('first_name'),
            middle_name: getVal('middle_name'),
            last_name: getVal('last_name'),
            date_of_birth: getVal('date_of_birth'),
            age: getVal('age'),
            civil_status: getVal('civil_status'),
            phone: getVal('phone'),
            email: getVal('email'),
            address: getVal('address'),
            province: getVal('province'),
            city: getVal('city'),
            barangay: getVal('barangay'),
            zip_code: getVal('zip_code'),
            insurance_provider: getVal('insurance_provider'),
            insurance_number: getVal('insurance_number'),
            emergency_contact_name: getVal('emergency_contact_name'),
            emergency_contact_phone: getVal('emergency_contact_phone'),
            patient_type: getVal('patient_type'),
            status: getVal('status'),
            medical_notes: getVal('medical_notes')
        };
    }
};

// Export for global access
window.PatientAdd = PatientAdd;