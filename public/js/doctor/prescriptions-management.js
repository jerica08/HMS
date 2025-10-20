// Main Prescription Management Controller
// Handles prescription list and coordination between modals

const PrescriptionManager = {
    init() {
        this.setupEditButtons();
        this.setupRefreshButton();
        this.setupExportButton();
    },

    setupEditButtons() {
        // Edit prescription buttons (dynamically added)
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('edit-rx-btn')) {
                // Open new prescription modal for editing
                if (window.NewPrescriptionModal) {
                    window.NewPrescriptionModal.open();
                }
            }
        });
    },

    setupRefreshButton() {
        const refreshBtn = document.getElementById('exportBtn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                location.reload();
            });
        }
    },

    setupExportButton() {
        const exportBtn = document.getElementById('printBtn');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => {
                // Export functionality can be added here
                window.PrescriptionUtils.showNotification('Export functionality coming soon!', 'info');
            });
        }
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    PrescriptionManager.init();
});

// Export to global scope
window.PrescriptionManager = PrescriptionManager;