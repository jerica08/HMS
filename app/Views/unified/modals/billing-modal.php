<!-- Create Bill Modal -->
<div id="billingModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-plus-circle"></i> Create Bill</h3>
            <button class="modal-close" id="closeBillingModal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="billingForm">
                <div class="modal-form-group">
                    <label for="patient_identifier" class="modal-form-label">Patient Name / ID</label>
                    <input id="patient_identifier" name="patient_identifier" type="text" class="form-input modal-form-input" required autocomplete="off">
                </div>
                <div class="modal-form-group">
                    <label for="doctor" class="modal-form-label">Doctor</label>
                    <input id="doctor" name="doctor" type="text" class="form-input modal-form-input" required autocomplete="off">
                </div>
                <div class="modal-form-group">
                    <label for="department" class="modal-form-label">Department</label>
                    <input id="department" name="department" type="text" class="form-input modal-form-input" required autocomplete="off">
                </div>
                <div class="modal-form-group">
                    <label for="payment_status" class="modal-form-label">Status</label>
                    <select id="payment_status" name="payment_status" class="form-select modal-form-input" required>
                        <option value="pending">Pending</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>
                <div class="modal-form-group">
                    <label class="modal-form-label">Services</label>
                    <div class="services-table-container">
                        <table class="services-table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Line Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="servicesBody"></tbody>
                        </table>
                    </div>
                    <div class="add-service-container">
                        <button type="button" class="btn btn-secondary" onclick="addServiceRow()">
                            <i class="fas fa-plus"></i> Add Service
                        </button>
                    </div>
                </div>
                <div class="modal-form-group">
                    <div class="total-row">
                        <label class="modal-form-label">Total</label>
                        <input id="bill_total" name="bill_total" type="number" class="form-input modal-form-input" readonly value="0">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeBillingModal()">Cancel</button>
            <button type="button" class="btn btn-success" onclick="saveBill()">
                <i class="fas fa-save"></i> Save Bill
            </button>
        </div>
    </div>
</div>
