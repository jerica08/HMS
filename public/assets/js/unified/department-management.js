/**
 * Department Management - Main Controller
 */
(function() {
    const baseUrl = document.querySelector('meta[name="base-url"]')?.content?.replace(/\/+$/, '') || '';

    // Initialize add department modal button
    document.getElementById('addDepartmentBtn')?.addEventListener('click', () => {
        if (window.AddDepartmentModal && window.AddDepartmentModal.open) {
            window.AddDepartmentModal.open();
        }
    });

    // Notification functions
    window.showDepartmentsNotification = function(message, type = 'success') {
        const container = document.getElementById('departmentsNotification');
        const messageEl = document.getElementById('departmentsNotificationMessage');
        if (!container || !messageEl) {
            alert(message);
            return;
        }

        if (window.departmentsNotificationTimeout) {
            clearTimeout(window.departmentsNotificationTimeout);
        }

        container.className = `notification ${type}`;
        messageEl.textContent = String(message || '');
        container.style.display = 'flex';

        window.departmentsNotificationTimeout = setTimeout(dismissDepartmentsNotification, 5000);
    };

    window.dismissDepartmentsNotification = function() {
        const container = document.getElementById('departmentsNotification');
        if (container) {
            container.style.display = 'none';
        }
    };

})();
