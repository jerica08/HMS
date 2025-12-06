/**
 * View Billing Account Modal
 */
(function() {
    const baseUrl = document.querySelector('meta[name="base-url"]')?.content?.replace(/\/+$/, '') || '';
    const utils = new BillingModalUtils(baseUrl);
    const modalId = 'billingAccountModal';

    const modal = document.getElementById(modalId);
    const header = document.getElementById('billingAccountHeader');
    const body = document.getElementById('billingItemsBody');
    const totalEl = document.getElementById('billingAccountTotal');

    function init() {
        if (!modal) return;
        utils.setupModalCloseHandlers(modalId);
    }

    function open(billingId, patientName) {
        if (!modal || !header || !body || !totalEl) return;

        utils.open(modalId);
        header.innerHTML = '';
        body.innerHTML = `<tr><td colspan="4" class="loading-row"><i class="fas fa-spinner fa-spin"></i> Loading billing details...</td></tr>`;

        fetch(`${baseUrl}/billing/accounts/${billingId}`)
            .then(resp => resp.json())
            .then(result => {
                if (!result || !result.success) {
                    body.innerHTML = `<tr><td colspan="4" class="loading-row">Failed to load billing account.</td></tr>`;
                    return;
                }

                const acc = result.data;
                const patientNameDisplay = patientName || acc.patient_name || 'Unknown Patient';

                header.innerHTML = `
                    <div><strong>Billing ID:</strong> ${acc.billing_id}</div>
                    <div><strong>Patient:</strong> ${utils.escapeHtml(patientNameDisplay)}</div>
                `;

                const items = Array.isArray(acc.items) ? acc.items : [];

                if (!items.length) {
                    body.innerHTML = `<tr><td colspan="4" class="loading-row"><i class="fas fa-info-circle"></i> No billing items found.</td></tr>`;
                } else {
                    body.innerHTML = '';
                    items.forEach(item => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${utils.escapeHtml(item.description)}</td>
                            <td>${item.quantity}</td>
                            <td>₱${parseFloat(item.unit_price).toFixed(2)}</td>
                            <td>₱${parseFloat(item.line_total).toFixed(2)}</td>
                        `;
                        body.appendChild(tr);
                    });
                }

                totalEl.textContent = "₱" + parseFloat(acc.total_amount).toFixed(2);
            })
            .catch(() => {
                body.innerHTML = `<tr><td colspan="4">Failed to load billing account.</td></tr>`;
            });
    }

    function close() {
        utils.close(modalId);
    }

    // Export to global scope
    window.ViewBillingAccountModal = { init, open, close };

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

