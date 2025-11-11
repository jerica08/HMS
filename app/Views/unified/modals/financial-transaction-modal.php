<!-- Financial Transaction Modal -->
<div id="financialTransactionModal" class="modal-overlay" style="display: none;">
    <div class="modal-content financial-modal">
        <div class="modal-header">
            <h3><i class="fas fa-money-bill-wave"></i> Add Financial Transaction</h3>
            <button type="button" class="modal-close" onclick="closeFinancialTransactionModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="financialTransactionForm" class="modal-form">
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
            
            <div class="modal-body">
                <div class="form-grid">
                    <!-- Transaction Type -->
                    <div class="form-group">
                        <label for="transactionType">
                            <i class="fas fa-exchange-alt"></i> Transaction Type *
                        </label>
                        <select id="transactionType" name="type" class="form-control" required onchange="updateFinancialCategories()">
                            <option value="">Select Transaction Type</option>
                            <option value="Income">Income</option>
                            <option value="Expense">Expense</option>
                        </select>
                        <small class="form-text">Choose whether this is money coming in or going out</small>
                    </div>
                    
                    <!-- Category -->
                    <div class="form-group">
                        <label for="transactionCategory">
                            <i class="fas fa-tags"></i> Category *
                        </label>
                        <select id="transactionCategory" name="category_id" class="form-control" required>
                            <option value="">First select transaction type</option>
                        </select>
                        <small class="form-text">Select the appropriate category for this transaction</small>
                    </div>
                    
                    <!-- Amount -->
                    <div class="form-group">
                        <label for="transactionAmount">
                            <i class="fas fa-peso-sign"></i> Amount (₱) *
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" id="transactionAmount" name="amount" class="form-control" 
                                   step="0.01" min="0.01" required placeholder="0.00">
                        </div>
                        <small class="form-text">Enter the exact amount in Philippine Pesos</small>
                    </div>
                    
                    <!-- Transaction Date -->
                    <div class="form-group">
                        <label for="transactionDate">
                            <i class="fas fa-calendar"></i> Transaction Date *
                        </label>
                        <input type="date" id="transactionDate" name="transaction_date" class="form-control" required>
                        <small class="form-text">Date when the transaction occurred</small>
                    </div>
                    
                    <!-- User (Owner) -->
                    <div class="form-group">
                        <label for="transactionUser">
                            <i class="fas fa-user"></i> Recorded By *
                        </label>
                        <select id="transactionUser" name="user_id" class="form-control" required>
                            <option value="">Select User</option>
                            <?php if (isset($users)): ?>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['id'] ?>"><?= $user['username'] ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <small class="form-text">Person recording this transaction</small>
                    </div>
                    
                    <!-- Description -->
                    <div class="form-group full-width">
                        <label for="transactionDescription">
                            <i class="fas fa-align-left"></i> Description
                        </label>
                        <textarea id="transactionDescription" name="description" class="form-control" 
                                  rows="3" placeholder="Enter transaction details, notes, or reference information..."></textarea>
                        <small class="form-text">Optional: Add any additional details about this transaction</small>
                    </div>
                </div>
                
                <!-- Transaction Summary Preview -->
                <div class="transaction-preview" id="transactionPreview" style="display: none;">
                    <h4><i class="fas fa-eye"></i> Transaction Preview</h4>
                    <div class="preview-content">
                        <div class="preview-row">
                            <span class="preview-label">Type:</span>
                            <span class="preview-value" id="previewType">-</span>
                        </div>
                        <div class="preview-row">
                            <span class="preview-label">Category:</span>
                            <span class="preview-value" id="previewCategory">-</span>
                        </div>
                        <div class="preview-row">
                            <span class="preview-label">Amount:</span>
                            <span class="preview-value" id="previewAmount">-</span>
                        </div>
                        <div class="preview-row">
                            <span class="preview-label">Date:</span>
                            <span class="preview-value" id="previewDate">-</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeFinancialTransactionModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-outline" onclick="resetFinancialForm()">
                    <i class="fas fa-redo"></i> Reset
                </button>
                <button type="submit" class="btn btn-primary" id="saveFinancialBtn">
                    <i class="fas fa-save"></i> Save Transaction
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Financial transaction modal functions
let financialCategories = {};

function openFinancialTransactionModal() {
    const modal = document.getElementById('financialTransactionModal');
    
    if (modal) {
        modal.style.display = 'flex';
        
        // Set default date to today
        document.getElementById('transactionDate').value = new Date().toISOString().split('T')[0];
        
        // Load categories via AJAX
        loadFinancialCategories();
        
        // Load users via AJAX
        loadFinancialUsers();
    } else {
        console.error('Financial Transaction Modal not found!');
        alert('Modal not found. Please check if the modal is properly included.');
    }
}

function closeFinancialTransactionModal() {
    document.getElementById('financialTransactionModal').style.display = 'none';
    resetFinancialForm();
}

function resetFinancialForm() {
    document.getElementById('financialTransactionForm').reset();
    document.getElementById('transactionPreview').style.display = 'none';
    updateFinancialCategories();
}

function loadFinancialCategories() {
    fetch(window.baseUrl + '/financial-management/categories?type=all')
        .then(response => response.json())
        .then(data => {
            financialCategories = data;
            updateFinancialCategories();
        })
        .catch(error => {
            console.error('Error loading categories:', error);
        });
}

function loadFinancialUsers() {
    fetch(window.baseUrl + '/api/users')
        .then(response => response.json())
        .then(data => {
            const userSelect = document.getElementById('transactionUser');
            userSelect.innerHTML = '<option value="">Select User</option>';
            
            if (data.users) {
                data.users.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.textContent = user.username;
                    userSelect.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error loading users:', error);
        });
}

function updateFinancialCategories() {
    const type = document.getElementById('transactionType').value;
    const categorySelect = document.getElementById('transactionCategory');
    
    categorySelect.innerHTML = '<option value="">First select transaction type</option>';
    
    if (type && financialCategories[type]) {
        financialCategories[type].forEach(category => {
            const option = document.createElement('option');
            option.value = category.category_id;
            option.textContent = category.name;
            categorySelect.appendChild(option);
        });
    }
    
    updateTransactionPreview();
}

function updateTransactionPreview() {
    const type = document.getElementById('transactionType').value;
    const categoryId = document.getElementById('transactionCategory').value;
    const amount = document.getElementById('transactionAmount').value;
    const date = document.getElementById('transactionDate').value;
    
    if (type || categoryId || amount || date) {
        document.getElementById('transactionPreview').style.display = 'block';
        
        document.getElementById('previewType').textContent = type || '-';
        
        const categoryOption = document.querySelector(`#transactionCategory option[value="${categoryId}"]`);
        document.getElementById('previewCategory').textContent = categoryOption ? categoryOption.textContent : '-';
        
        if (amount) {
            const formattedAmount = new Intl.NumberFormat('en-PH', {
                style: 'currency',
                currency: 'PHP'
            }).format(amount);
            document.getElementById('previewAmount').textContent = formattedAmount;
        } else {
            document.getElementById('previewAmount').textContent = '-';
        }
        
        if (date) {
            const formattedDate = new Date(date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            document.getElementById('previewDate').textContent = formattedDate;
        } else {
            document.getElementById('previewDate').textContent = '-';
        }
    } else {
        document.getElementById('transactionPreview').style.display = 'none';
    }
}

// Add event listeners for real-time preview
document.getElementById('transactionType').addEventListener('change', updateTransactionPreview);
document.getElementById('transactionCategory').addEventListener('change', updateTransactionPreview);
document.getElementById('transactionAmount').addEventListener('input', updateTransactionPreview);
document.getElementById('transactionDate').addEventListener('change', updateTransactionPreview);

// Form submission
document.getElementById('financialTransactionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = document.getElementById('saveFinancialBtn');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    
    fetch(window.baseUrl + '/financial-management/add', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            closeFinancialTransactionModal();
            showNotification('Financial transaction added successfully!', 'success');
            // Reload the page or update the transaction list
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.message || 'Failed to save transaction', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while saving the transaction', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Save Transaction';
    });
});

// Click outside modal to close
document.getElementById('financialTransactionModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeFinancialTransactionModal();
    }
});

// Notification function
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}
</script>

<style>
/* Financial Modal Styles */
.financial-modal {
    max-width: 600px;
    width: 90%;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    margin-bottom: 8px;
    color: #374151;
}

.form-group label i {
    font-size: 14px;
    color: #6b7280;
}

.form-control {
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.input-group {
    display: flex;
    align-items: stretch;
}

.input-group-text {
    display: flex;
    align-items: center;
    padding: 10px 12px;
    background-color: #f9fafb;
    border: 1px solid #d1d5db;
    border-right: none;
    border-radius: 6px 0 0 6px;
    font-weight: 600;
    color: #374151;
}

.input-group .form-control {
    border-radius: 0 6px 6px 0;
}

.form-text {
    font-size: 12px;
    color: #6b7280;
    margin-top: 4px;
}

/* Transaction Preview */
.transaction-preview {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 16px;
    margin-top: 20px;
}

.transaction-preview h4 {
    margin: 0 0 12px 0;
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 8px;
}

.preview-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.preview-row {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
}

.preview-label {
    color: #6b7280;
    font-weight: 500;
}

.preview-value {
    color: #111827;
    font-weight: 600;
}

/* Notification Styles */
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    border-left: 4px solid #10b981;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border-radius: 6px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    z-index: 9999;
    transform: translateX(100%);
    transition: transform 0.3s ease;
}

.notification.show {
    transform: translateX(0);
}

.notification-success {
    border-left-color: #10b981;
}

.notification-error {
    border-left-color: #ef4444;
}

.notification-success i {
    color: #10b981;
}

.notification-error i {
    color: #ef4444;
}

/* Modal Specific Styles */
.modal-footer {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    padding: 20px;
    border-top: 1px solid #e5e7eb;
}

.btn {
    padding: 10px 16px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #2563eb;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover:not(:disabled) {
    background: #4b5563;
}

.btn-outline {
    background: transparent;
    color: #6b7280;
    border: 1px solid #d1d5db;
}

.btn-outline:hover:not(:disabled) {
    background: #f9fafb;
    border-color: #9ca3af;
}

@media (max-width: 640px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .preview-content {
        grid-template-columns: 1fr;
    }
}
</style>
