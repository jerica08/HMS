<!-- Process Payment Modal -->
<div id="paymentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-credit-card"></i> Process Payment</h3>
            <button class="modal-close" id="closePaymentModal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="paymentForm">
                <div class="modal-form-group">
                    <label for="pay_patient_identifier" class="modal-form-label">Patient Name / ID</label>
                    <input id="pay_patient_identifier" name="patient_identifier" type="text" class="form-input modal-form-input" required autocomplete="off">
                </div>
                <div class="modal-form-group">
                    <label for="pay_bill_ref" class="modal-form-label">Bill Reference</label>
                    <input id="pay_bill_ref" name="bill_ref" type="text" class="form-input modal-form-input" placeholder="Bill ID / Number" required autocomplete="off">
                </div>
                <div class="modal-form-group">
                    <label for="pay_amount" class="modal-form-label">Amount</label>
                    <input id="pay_amount" name="amount" type="number" class="form-input modal-form-input" min="0" step="0.01" required>
                </div>
                <div class="modal-form-group">
                    <label for="pay_method" class="modal-form-label">Payment Method</label>
                    <select id="pay_method" name="method" class="form-select modal-form-input" required>
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="check">Check</option>
                    </select>
                </div>
                <div class="modal-form-group">
                    <label for="pay_date" class="modal-form-label">Payment Date</label>
                    <input id="pay_date" name="date" type="date" class="form-input modal-form-input">
                </div>
                <div class="modal-form-group">
                    <label for="pay_notes" class="modal-form-label">Notes</label>
                    <textarea id="pay_notes" name="notes" rows="3" class="form-textarea modal-form-textarea" autocomplete="off"></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closePaymentModal()">Cancel</button>
            <button type="button" class="btn btn-success" onclick="savePayment()">
                <i class="fas fa-save"></i> Process Payment
            </button>
        </div>
    </div>
</div>
