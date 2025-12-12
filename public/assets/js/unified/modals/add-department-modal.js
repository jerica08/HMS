/**
 * Add Department Modal
 */
(function() {
    const baseUrl = document.querySelector('meta[name="base-url"]')?.content?.replace(/\/+$/, '') || '';
    const utils = new DepartmentModalUtils(baseUrl);
    const modalId = 'addDepartmentModal';
    const formId = 'addDepartmentForm';

    const modal = document.getElementById(modalId);
    const form = document.getElementById(formId);
    const submitBtn = document.getElementById('saveDepartmentBtn');

    function init() {
        if (!modal || !form) return;

        utils.setupModalCloseHandlers(modalId);
        form.addEventListener('submit', handleSubmit);
    }

    function open() {
        if (!modal || !form) return;

        form.reset();
        utils.clearErrors('err_');
        utils.open(modalId);
    }

    function close() {
        utils.close(modalId);
    }

    async function handleSubmit(e) {
        e.preventDefault();
        if (!form) return;

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        }

        try {
            const formData = new FormData(form);
            const payload = Object.fromEntries(formData.entries());

<<<<<<< HEAD
            const response = await fetch(`${baseUrl}/departments/create`, {
=======
            // Validate required fields
            const errors = {};
            if (!payload.name || payload.name.trim() === '') {
                errors.name = 'Department name is required.';
            }
            if (!payload.department_category) {
                errors.department_category = 'Department category is required.';
            }
            if (payload.department_category === 'non_medical' && !payload.non_medical_function) {
                errors.non_medical_function = 'Function is required for non-medical departments.';
            }

            // Validate contact number if provided
            if (payload.contact_number && payload.contact_number.trim() !== '') {
                const contactPattern = /^09\d{9}$/;
                if (!contactPattern.test(payload.contact_number.trim())) {
                    errors.contact_number = payload.contact_number.startsWith('09') 
                        ? 'Contact number must be exactly 11 digits.'
                        : 'Contact number must start with 09.';
                }
            }

            if (Object.keys(errors).length > 0) {
                utils.displayErrors(errors, 'err_');
                return;
            }

            // Determine which endpoint to use based on category
            const endpoint = payload.department_category === 'medical' 
                ? `${baseUrl}/test/departments/create-medical`  // Temporary test endpoint without auth
                : `${baseUrl}/test/departments/create-non-medical`;  // Temporary test endpoint without auth

            const response = await fetch(endpoint, {
>>>>>>> 03d4e70 (COMMITenter the commit message for your changes. Lines starting)
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(payload),
            });

            const result = await response.json().catch(() => ({ status: 'error', message: 'Invalid response' }));

            if (response.ok && result.status === 'success') {
                utils.showNotification('Department created successfully', 'success');
                close();
                setTimeout(() => window.location.reload(), 1000);
            } else {
                const message = result.message || 'Failed to create department';
                if (result.errors) {
                    utils.displayErrors(result.errors, 'err_');
                } else {
                    utils.showNotification(message, 'error');
                }
            }
        } catch (error) {
            console.error('Failed to create department', error);
            utils.showNotification('Server error while creating department', 'error');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Save Department';
            }
        }
    }

    // Export to global scope
    window.AddDepartmentModal = { init, open, close };

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

