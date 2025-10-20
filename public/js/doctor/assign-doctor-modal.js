/**
 * Assign Doctor Modal JavaScript
 * Handles doctor assignment modal functionality
 */

const DoctorAssignment = {
    modal: null,
    form: null,
    doctorsCache: null,
    
    init() {
        this.modal = document.getElementById('assignDoctorModal');
        this.form = document.getElementById('assignDoctorForm');
        
        if (this.form) {
            this.form.addEventListener('submit', this.handleSubmit.bind(this));
        }
        
        this.setupModalEvents();
    },
    
    async open(patientId) {
        const patientIdInput = document.getElementById('assignPatientId');
        const doctorSelect = document.getElementById('doctorSelect');
        
        if (!this.modal || !patientIdInput || !doctorSelect) return;
        
        // Set patient ID
        patientIdInput.value = patientId;
        
        // Load doctors
        doctorSelect.innerHTML = '<option value="">Loading doctors...</option>';
        const doctors = await this.loadDoctors();
        
        // Populate doctor dropdown
        doctorSelect.innerHTML = '<option value="">Select a doctor...</option>';
        doctors.forEach(doctor => {
            const option = document.createElement('option');
            option.value = doctor.staff_id;
            option.textContent = `${doctor.full_name} - ${doctor.department || 'N/A'}`;
            doctorSelect.appendChild(option);
        });
        
        // Show modal
        this.modal.style.display = 'flex';
    },
    
    close() {
        if (this.modal) {
            this.modal.style.display = 'none';
            if (this.form) this.form.reset();
            const errorEl = document.getElementById('err_doctor');
            if (errorEl) errorEl.textContent = '';
        }
    },
    
    async loadDoctors() {
        if (this.doctorsCache) return this.doctorsCache;
        
        try {
            const response = await fetch(`${window.CONFIG.baseUrl}${window.CONFIG.endpoints.doctorsApi}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) throw new Error('Failed to fetch doctors');
            
            const result = await response.json();
            if (result.success && result.data) {
                this.doctorsCache = result.data;
                return this.doctorsCache;
            }
            throw new Error('Invalid response format');
        } catch (error) {
            console.error('Error loading doctors:', error);
            return [];
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
        
        const btn = document.getElementById('assignDoctorBtn');
        const errorEl = document.getElementById('err_doctor');
        
        if (btn) {
            btn.disabled = true;
            btn.textContent = 'Assigning...';
        }
        
        if (errorEl) errorEl.textContent = '';
        
        const formData = new FormData(this.form);
        const payload = {
            patient_id: formData.get('patient_id'),
            doctor_id: formData.get('doctor_id')
        };
        
        try {
            const response = await fetch(`${window.CONFIG.baseUrl}${window.CONFIG.endpoints.assignDoctor}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            });
            
            const result = await response.json();
            
            if (response.ok && result.success) {
                alert('Doctor assigned successfully!');
                this.close();
                if (window.PatientList) {
                    window.PatientList.loadPatients();
                }
            } else {
                if (errorEl) errorEl.textContent = result.message || 'Failed to assign doctor';
            }
        } catch (error) {
            console.error('Error assigning doctor:', error);
            if (errorEl) errorEl.textContent = 'Network error. Please try again.';
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.textContent = 'Assign Doctor';
            }
        }
    }
};

// Export for global access
window.DoctorAssignment = DoctorAssignment;