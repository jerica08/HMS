/**
 * Financial Management - Main Controller
 */
(function() {
    const baseUrl = document.querySelector('meta[name="base-url"]')?.content?.replace(/\/+$/, '') || '';
    const utils = new BillingModalUtils(baseUrl);
    const tableBody = document.getElementById('financialTableBody');

    // Notification functions
    window.showFinancialNotification = function(message, type = 'success') {
        const container = document.getElementById('financialNotification');
        const messageEl = document.getElementById('departmentsNotificationMessage') || container?.querySelector('span');
        if (!container || !messageEl) {
            alert(message);
            return;
        }

        if (window.financialNotificationTimeout) {
            clearTimeout(window.financialNotificationTimeout);
        }

        container.className = `notification ${type}`;
        messageEl.textContent = String(message || '');
        container.style.display = 'flex';

        window.financialNotificationTimeout = setTimeout(dismissFinancialNotification, 5000);
    };

    window.dismissFinancialNotification = function() {
        const container = document.getElementById('financialNotification');
        if (container) {
            container.style.display = 'none';
        }
    };

    function handleTableClick(event) {
        const btn = event.target.closest('button[data-action]');
        if (!btn) return;

        const action = btn.dataset.action;
        const billingId = btn.dataset.billingId;

        if (action === 'view') {
            const patientName = btn.dataset.patientName;
            if (window.ViewBillingAccountModal && window.ViewBillingAccountModal.open) {
                window.ViewBillingAccountModal.open(billingId, patientName);
            }
        } else if (action === 'mark-paid') {
            markBillingAccountPaid(billingId);
        } else if (action === 'delete') {
            deleteBillingAccount(billingId);
        }
    }

    function markBillingAccountPaid(billingId) {
        if (!billingId || !confirm('Mark this billing account as PAID?')) return;

        fetch(`${baseUrl}/financial/billing-accounts/${billingId}/paid`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ billing_id: billingId })
        })
            .then(resp => resp.json())
            .then(result => {
                const ok = result && (result.success === true || result.status === 'success');
                utils.showNotification(
                    result.message || (ok ? 'Billing account marked as paid.' : 'Failed to mark billing account as paid.'),
                    ok ? 'success' : 'error'
                );
                if (ok) window.location.reload();
            })
            .catch(err => {
                console.error('Failed to mark billing account paid', err);
                utils.showNotification('Failed to mark billing account as paid.', 'error');
            });
    }

    function deleteBillingAccount(billingId) {
        if (!billingId || !confirm('Delete this billing account and all its items? This action cannot be undone.')) return;

        fetch(`${baseUrl}/financial/billing-accounts/${billingId}/delete`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ billing_id: billingId })
        })
            .then(resp => resp.json())
            .then(result => {
                const ok = result && (result.success === true || result.status === 'success');
                utils.showNotification(
                    result.message || (ok ? 'Billing account deleted successfully.' : 'Failed to delete billing account.'),
                    ok ? 'success' : 'error'
                );
                if (ok) window.location.reload();
            })
            .catch(err => {
                console.error('Failed to delete billing account', err);
                utils.showNotification('Failed to delete billing account.', 'error');
            });
    }

    // Initialize
    if (tableBody) {
        tableBody.addEventListener('click', handleTableClick);
    }
})();
