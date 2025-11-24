// Initialize PrescriptionManager and expose closePrescriptionBillingModal globally
(function() {
    const manager = new PrescriptionManager();
    window.prescriptionManager = manager;
    window.closePrescriptionBillingModal = function() {
        if (window.prescriptionManager && typeof window.prescriptionManager.closeBillingModal === 'function') {
            window.prescriptionManager.closeBillingModal();
        }
    };
})();
