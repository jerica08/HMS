// New Prescription Modal Management
// Handles create prescription modal functionality

const NewPrescriptionModal = {
    modal: null,
    form: null,
    addBtn: null,
    closeBtn: null,
    cancelBtn: null,

    init() {
        // Get modal elements
        this.modal = document.getElementById('newPrescriptionModal');
        this.form = document.getElementById('newPrescriptionForm');
        this.addBtn = document.getElementById('addPrescriptionBtn');
        this.closeBtn = document.getElementById('closeNewRx');
        this.cancelBtn = document.getElementById('cancelNewRx');

        // Bind event listeners
        this.bindEvents();
    },

    bindEvents() {
        if (!this.modal || !this.form) return;

        // Open modal
        this.addBtn?.addEventListener('click', () => {
            this.open();
        });

        // Close modal events
        this.closeBtn?.addEventListener('click', () => {
            this.close();
        });

        this.cancelBtn?.addEventListener('click', () => {
            this.close();
        });

        // Form submission
        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleSubmission();
        });

        // Click outside to close
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.close();
            }
        });

        // Escape key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal.classList.contains('show')) {
                this.close();
            }
        });
    },

    open() {
        if (this.modal) {
            this.modal.classList.add('show');
        }
    },

    close() {
        if (this.modal) {
            this.modal.classList.remove('show');
        }
    },

    handleSubmission() {
        const formData = new FormData(this.form);
        const submitButton = this.form.querySelector('button[type="submit"]');
        const originalText = submitButton ? submitButton.textContent : '';

        // Show loading state
        if (submitButton) {
            submitButton.textContent = 'Creating...';
            submitButton.disabled = true;
        }

        fetch(`${window.PrescriptionUtils.getBaseUrl()}doctor/create-prescription`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.PrescriptionUtils.showNotification('Prescription created successfully! ID: ' + data.prescription_id, 'success');
                this.close();
                this.form.reset();
                // Refresh the page to show the new prescription
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                window.PrescriptionUtils.showNotification('Error: ' + (data.message || 'Failed to create prescription'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.PrescriptionUtils.showNotification('An error occurred while creating the prescription.', 'error');
        })
        .finally(() => {
            // Reset button state
            if (submitButton) {
                submitButton.textContent = originalText;
                submitButton.disabled = false;
            }
        });
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    NewPrescriptionModal.init();
});

// Export to global scope
window.NewPrescriptionModal = NewPrescriptionModal;