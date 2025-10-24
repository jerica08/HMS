<!-- Add Expense Modal -->
<div id="expenseModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-receipt"></i> Add Expense</h3>
            <button class="modal-close" id="closeExpenseModal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="expenseForm">
                <div class="modal-form-group">
                    <label for="exp_name" class="modal-form-label">Expense Name</label>
                    <input id="exp_name" name="name" type="text" class="form-input modal-form-input" required autocomplete="off">
                </div>
                <div class="modal-form-group">
                    <label for="exp_amount" class="modal-form-label">Amount</label>
                    <input id="exp_amount" name="amount" type="number" class="form-input modal-form-input" min="0" step="0.01" required>
                </div>
                <div class="modal-form-group">
                    <label for="exp_category" class="modal-form-label">Category</label>
                    <select id="exp_category" name="category" class="form-select modal-form-input" required>
                        <option value="supplies">Medical Supplies</option>
                        <option value="utilities">Utilities</option>
                        <option value="salary">Staff Salary</option>
                        <option value="maintenance">Equipment Maintenance</option>
                        <option value="rent">Rent & Facilities</option>
                        <option value="insurance">Insurance</option>
                        <option value="marketing">Marketing</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="modal-form-group">
                    <label for="exp_date" class="modal-form-label">Date</label>
                    <input id="exp_date" name="date" type="date" class="form-input modal-form-input">
                </div>
                <div class="modal-form-group">
                    <label for="exp_notes" class="modal-form-label">Notes</label>
                    <textarea id="exp_notes" name="notes" rows="3" class="form-textarea modal-form-textarea" autocomplete="off"></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeExpenseModal()">Cancel</button>
            <button type="button" class="btn btn-success" onclick="saveExpense()">
                <i class="fas fa-save"></i> Save Expense
            </button>
        </div>
    </div>
</div>
