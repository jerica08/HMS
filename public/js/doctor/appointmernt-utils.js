// Appointment Management Utilities
// Shared functions used across appointment modals

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

// Escape HTML function for XSS protection
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text ? text.replace(/[&<>"']/g, function(m) { return map[m]; }) : '';
}

// Format time string to readable format
function formatTime(timeString) {
    if (!timeString) return 'N/A';
    const time = new Date('2000-01-01 ' + timeString);
    return time.toLocaleTimeString('en-US', { 
        hour: 'numeric', 
        minute: '2-digit', 
        hour12: true 
    });
}

// Format date to ISO string
function formatDate(date) {
    return date.toISOString().split('T')[0];
}

// Format date for display
function formatDateDisplay(date) {
    return date.toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
}

// Get badge class for appointment status
function getBadgeClass(status) {
    switch(status.toLowerCase()) {
        case 'completed': return 'badge-success';
        case 'in-progress': return 'badge-info';
        case 'cancelled': return 'badge-danger';
        case 'no-show': return 'badge-warning';
        default: return 'badge-info';
    }
}

// Get week range string
function getWeekRange(date) {
    const startOfWeek = new Date(date);
    startOfWeek.setDate(date.getDate() - date.getDay());
    const endOfWeek = new Date(startOfWeek);
    endOfWeek.setDate(startOfWeek.getDate() + 6);
    
    return `${startOfWeek.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${endOfWeek.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}`;
}

// Update current time display
function updateCurrentTime() {
    const now = new Date();
    const timeElement = document.querySelector('.overview-card:nth-child(3) .card-subtitle');
    if (timeElement) {
        timeElement.textContent = `Current time: ${now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })}`;
    }
}

// Show notification system
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

// Export utilities to global scope
window.AppointmentUtils = {
    getBaseUrl,
    getCsrfToken,
    escapeHtml,
    formatTime,
    formatDate,
    formatDateDisplay,
    getBadgeClass,
    getWeekRange,
    updateCurrentTime,
    showNotification
};