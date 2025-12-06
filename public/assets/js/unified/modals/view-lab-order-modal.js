/**
 * View Lab Order Modal
 */
(function() {
    const baseUrl = document.querySelector('meta[name="base-url"]').content.replace(/\/$/, '') + '/';
    const utils = new LabModalUtils(baseUrl);
    const modalId = 'viewLabOrderModal';

    const modal = document.getElementById(modalId);
    const orderIdEl = document.getElementById('viewLabOrderId');
    const patientEl = document.getElementById('viewLabPatient');
    const testEl = document.getElementById('viewLabTest');
    const priorityEl = document.getElementById('viewLabPriority');
    const statusEl = document.getElementById('viewLabStatus');
    const orderedAtEl = document.getElementById('viewLabOrderedAt');

    function init() {
        if (!modal) return;
        utils.setupModalCloseHandlers(modalId);
    }

    function open(labOrderId) {
        if (!modal) return;

        utils.open(modalId);
        loadLabOrder(labOrderId);
    }

    function close() {
        utils.close(modalId);
    }

    async function loadLabOrder(labOrderId) {
        try {
            const res = await fetch(baseUrl + 'labs/' + labOrderId, { credentials: 'same-origin' });
            const data = await res.json();

            if (data.success && data.data) {
                const order = data.data;
                if (orderIdEl) orderIdEl.textContent = order.lab_order_id || 'N/A';
                if (patientEl) patientEl.textContent = order.patient_name || 'N/A';
                if (testEl) testEl.textContent = (order.test_name || order.test_code) || 'N/A';
                if (priorityEl) priorityEl.textContent = (order.priority || 'routine').charAt(0).toUpperCase() + (order.priority || 'routine').slice(1);
                if (statusEl) statusEl.textContent = (order.status || 'ordered').replace('_', ' ');
                if (orderedAtEl) orderedAtEl.textContent = order.ordered_at || order.created_at || 'N/A';
            } else {
                alert(data.message || 'Failed to load lab order');
                close();
            }
        } catch (e) {
            console.error('Failed to load lab order', e);
            alert('Failed to load lab order');
            close();
        }
    }

    // Export to global scope
    window.ViewLabOrderModal = { init, open, close };

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

