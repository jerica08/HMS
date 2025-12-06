/**
 * Department Management - Main Controller
 */
(function() {
    const baseUrl = document.querySelector('meta[name="base-url"]')?.content?.replace(/\/+$/, '') || '';
    let allDepartmentRows = [];

    // Initialize add department modal button
    document.getElementById('addDepartmentBtn')?.addEventListener('click', () => {
        if (window.AddDepartmentModal && window.AddDepartmentModal.open) {
            window.AddDepartmentModal.open();
        }
    });

    // Search and filter functionality
    function initializeSearch() {
        const searchInput = document.getElementById('searchDepartment');
        const clearBtn = document.getElementById('clearFiltersDepartment');
        const tableBody = document.getElementById('departmentsTableBody');

        if (!tableBody) return;

        // Store all rows initially
        allDepartmentRows = Array.from(tableBody.querySelectorAll('tr'));

        function filterDepartments() {
            const searchTerm = (searchInput?.value || '').toLowerCase().trim();

            allDepartmentRows.forEach(row => {
                if (row.querySelector('td[colspan]')) {
                    // Skip placeholder/loading rows
                    return;
                }

                const cells = row.querySelectorAll('td');
                if (cells.length === 0) return;

                const searchableText = Array.from(cells)
                    .map(cell => cell.textContent || '')
                    .join(' ')
                    .toLowerCase();

                const matches = !searchTerm || searchableText.includes(searchTerm);
                row.style.display = matches ? '' : 'none';
            });
        }

        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(filterDepartments, 300);
            });
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                if (searchInput) searchInput.value = '';
                filterDepartments();
            });
        }
    }

    // Initialize search when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeSearch);
    } else {
        initializeSearch();
    }

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
