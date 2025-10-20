// Prescription Management Utilities
// Shared functions used across prescription modals

// Get base URL from meta tag
const getBaseUrl = () => {
    const baseUrlMeta = document.querySelector('meta[name="base-url"]');
    return baseUrlMeta ? baseUrlMeta.getAttribute('content') : '';
};

// Get CSRF token from meta tag
const getCsrfToken = () => {
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    return csrfMeta ? csrfMeta.getAttribute('content') : '';
};

// Notification system
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem;
        border-radius: 4px;
        color: white;
        background: ${type === 'success' ? '#10b981' : '#ef4444'};
        z-index: 10000;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Filter functionality
function applyFilters() {
    const searchInput = document.getElementById('prescriptionSearch');
    const statusFilter = document.getElementById('conditionsFilter');
    const dateFilter = document.getElementById('roleFilter');
    
    // Get filter values
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    const selectedStatus = statusFilter ? statusFilter.value.toLowerCase() : '';
    const selectedDate = dateFilter ? dateFilter.value : '';
    
    // Get all table rows
    const tableRows = document.querySelectorAll('.table tbody tr');
    
    tableRows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length === 0) return; // Skip if no cells (like "No prescriptions found" row)
        
        const prescriptionId = cells[0] ? cells[0].textContent.toLowerCase() : '';
        const patientName = cells[1] ? cells[1].textContent.toLowerCase() : '';
        const medication = cells[2] ? cells[2].textContent.toLowerCase() : '';
        const status = cells[6] ? cells[6].textContent.toLowerCase() : '';
        
        // Check if row matches filters
        const matchesSearch = !searchTerm || 
            prescriptionId.includes(searchTerm) || 
            patientName.includes(searchTerm) || 
            medication.includes(searchTerm);
        
        const matchesStatus = !selectedStatus || status.includes(selectedStatus);
        
        // Show/hide row based on filters
        if (matchesSearch && matchesStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Export utilities to global scope
window.PrescriptionUtils = {
    getBaseUrl,
    getCsrfToken,
    showNotification,
    applyFilters
};

// Make applyFilters available globally for backward compatibility
window.applyFilters = applyFilters;