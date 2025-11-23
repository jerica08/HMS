/**
 * Financial Management Main Controller
 */

const FinancialManager = {
    userRole: null,
    refreshInterval: null,

    init() {
        this.userRole = FinancialUtils.getUserRole();
        this.bindEvents();
        this.startAutoRefresh();
        
        console.log('Financial Management initialized for role:', this.userRole);
    },

    bindEvents() {
        // Auto-refresh every 5 minutes
        this.refreshInterval = setInterval(() => {
            this.refreshFinancialData();
        }, 5 * 60 * 1000);

        // Manual refresh button if exists
        const refreshBtn = document.getElementById('refreshFinancialData');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => this.refreshFinancialData());
        }
    },

    startAutoRefresh() {
        // Initial load
        this.refreshFinancialData();
    },

    async refreshFinancialData() {
        // Stats are currently rendered server-side; skip background API call
        return;
    },

    updateStatistics(stats) {
        // Update income
        const totalIncomeEl = document.getElementById('totalIncome');
        if (totalIncomeEl && stats.total_income !== undefined) {
            totalIncomeEl.textContent = FinancialUtils.formatCurrency(stats.total_income);
        }

        // Update expenses (if visible for role)
        const totalExpensesEl = document.getElementById('totalExpenses');
        if (totalExpensesEl && stats.total_expenses !== undefined) {
            totalExpensesEl.textContent = FinancialUtils.formatCurrency(stats.total_expenses);
        }

        // Update net balance
        const netBalanceEl = document.getElementById('netBalance');
        if (netBalanceEl && stats.net_balance !== undefined) {
            netBalanceEl.textContent = FinancialUtils.formatCurrency(stats.net_balance);
            
            // Add color coding for positive/negative balance
            netBalanceEl.className = stats.net_balance >= 0 ? 'stat-number positive' : 'stat-number negative';
        }

        // Update pending bills
        const pendingBillsEl = document.getElementById('pendingBills');
        if (pendingBillsEl && stats.pending_bills !== undefined) {
            pendingBillsEl.textContent = stats.pending_bills;
        }

        // Update paid bills
        const paidBillsEl = document.getElementById('paidBills');
        if (paidBillsEl && stats.paid_bills !== undefined) {
            paidBillsEl.textContent = stats.paid_bills;
        }

        // Update recent transactions if container exists
        if (stats.recent_transactions) {
            this.updateRecentTransactions(stats.recent_transactions);
        }
    },

    updateRecentTransactions(transactions) {
        const container = document.getElementById('recentTransactions');
        if (!container) return;

        if (transactions.length === 0) {
            container.innerHTML = '<p class="no-data">No recent transactions</p>';
            return;
        }

        const transactionsList = transactions.map(transaction => `
            <div class="transaction-item">
                <div class="transaction-info">
                    <div class="transaction-patient">
                        ${FinancialUtils.escapeHtml(transaction.first_name || '')} 
                        ${FinancialUtils.escapeHtml(transaction.last_name || '')}
                    </div>
                    <div class="transaction-details">
                        ${FinancialUtils.escapeHtml(transaction.bill_number || 'N/A')} • 
                        ${FinancialUtils.escapeHtml(transaction.payment_method || 'N/A')}
                    </div>
                </div>
                <div class="transaction-amount">
                    ${FinancialUtils.formatCurrency(transaction.amount)}
                </div>
                <div class="transaction-date">
                    ${FinancialUtils.formatDate(transaction.payment_date)}
                </div>
            </div>
        `).join('');

        container.innerHTML = transactionsList;
    },

    // Export/Print functionality
    exportFinancialReport() {
        // Implementation for exporting financial reports
        FinancialUtils.showNotification('Export functionality coming soon', 'info');
    },

    printFinancialReport() {
        // Implementation for printing financial reports
        window.print();
    },

    // Cleanup
    destroy() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }
    }
};

// Global function for refreshing data (used by modals)
function refreshFinancialData() {
    FinancialManager.refreshFinancialData();
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    FinancialManager.init();
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    FinancialManager.destroy();
});

// Export to global scope
window.FinancialManager = FinancialManager;
