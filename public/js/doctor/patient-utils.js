/**
 * Patient Utilities
 * Shared utility functions for patient management
 */

const PatientUtils = {
    escapeHtml(str) {
        return (str || '').toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    },

    calculateAge(dateOfBirth) {
        if (!dateOfBirth) return '';
        const dob = new Date(dateOfBirth);
        if (isNaN(dob.getTime())) return '';
        const today = new Date();
        let age = today.getFullYear() - dob.getFullYear();
        const monthDiff = today.getMonth() - dob.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
            age--;
        }
        return age >= 0 ? age : '';
    },

    initials(first, last) {
        const a = (first || '').trim();
        const b = (last || '').trim();
        return ((a[0] || 'P') + (b[0] || 'P')).toUpperCase();
    },

    calcAge(dob) {
        try {
            if (!dob) return 'N/A';
            const d = new Date(dob);
            if (isNaN(d)) return 'N/A';
            const t = new Date();
            let a = t.getFullYear() - d.getFullYear();
            const m = t.getMonth() - d.getMonth();
            if (m < 0 || (m === 0 && t.getDate() < d.getDate())) a--;
            return a >= 0 ? a : 'N/A';
        } catch (_) {
            return 'N/A';
        }
    }
};

// Export for global access
window.PatientUtils = PatientUtils;