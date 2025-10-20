// View Prescription Modal Management
// Handles prescription details display modal

const ViewPrescriptionModal = {
    modal: null,
    closeBtn: null,
    closeViewBtn: null,
    editFromViewBtn: null,

    init() {
        // Get modal elements
        this.modal = document.getElementById('viewPrescriptionModal');
        this.closeBtn = document.getElementById('closeViewRx');
        this.closeViewBtn = document.getElementById('closeViewRxBtn');
        this.editFromViewBtn = document.getElementById('editFromViewBtn');

        // Bind event listeners
        this.bindEvents();
    },

    bindEvents() {
        if (!this.modal) return;

        // View prescription buttons (dynamically added)
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('view-rx-btn')) {
                this.open();
            }
        });

        // Close modal events
        this.closeBtn?.addEventListener('click', () => {
            this.close();
        });

        this.closeViewBtn?.addEventListener('click', () => {
            this.close();
        });

        // Edit from view modal
        this.editFromViewBtn?.addEventListener('click', () => {
            this.close();
            // Open new prescription modal
            if (window.NewPrescriptionModal) {
                window.NewPrescriptionModal.open();
            }
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
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    ViewPrescriptionModal.init();
});

// Export to global scope
window.ViewPrescriptionModal = ViewPrescriptionModal;