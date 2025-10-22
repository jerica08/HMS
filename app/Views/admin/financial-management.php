<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Financial Management - HMS Admin</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="admin">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <?php include APPPATH . 'Views/admin/components/sidebar.php'; ?>

        <main class="content">
            <h1 class="page-title">Financial Management</h1>
           <div class="page-actions">
                        <button type="button" id="openBillingBtn" class="btn btn-primary" onclick="openBillingModal()">
                            <i class="fas fa-plus"></i> Billing
                        </button>
                        <button type="button" id="openPaymentBtn" class="btn btn-success" onclick="openPaymentModal()">
                            <i class="fas fa-plus"></i> Process Payment
                        </button>
                        <button type="button" id="openExpenseBtn" class="btn btn-warning" onclick="openExpenseModal()">
                            <i class="fas fa-plus"></i> Expenses
                        </button>
                </div><br>
            <!-- Financial Stats -->
            <div class="quick-stats">
                <div class="stat-card revenue">
                    <div class="stat-number">₱0</div>
                    <div class="stat-label">Total Income</div>   
                </div>
                <div class="stat-card expenses">
                    <div class="stat-number">₱0</div>
                    <div class="stat-label">Total Expenses</div>
                </div>
                <div class="stat-card profit">
                    <div class="stat-number">₱0</div>
                    <div class="stat-label">Net Balance</div>
                </div>
            </div>

            <!-- Expenses Modal -->
            <div id="expenseModal" class="hms-modal-overlay" aria-hidden="true">
                <div class="hms-modal" role="dialog" aria-modal="true" aria-labelledby="expenseTitle">
                    <div class="hms-modal-header">
                        <div class="hms-modal-title" id="expenseTitle">
                            <i class="fas fa-receipt text-warning"></i>
                            Add Expense
                        </div>
                        <button type="button" class="btn btn-secondary btn-small" onclick="closeExpenseModal()" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form id="expenseForm">
                        <div class="hms-modal-body">
                            <div class="form-grid">
                                <div>
                                    <label class="form-label" for="exp_name">Expense Name</label>
                                    <input id="exp_name" name="name" type="text" class="form-input" required autocomplete="off">
                                </div>
                                <div>
                                    <label class="form-label" for="exp_amount">Amount</label>
                                    <input id="exp_amount" name="amount" type="number" class="form-input" min="0" step="0.01" required>
                                </div>
                                <div>
                                    <label class="form-label" for="exp_category">Category</label>
                                    <select id="exp_category" name="category" class="form-select" required>
                                        <option value="supplies">Supplies</option>
                                        <option value="utilities">Utilities</option>
                                        <option value="salary">Salary</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label" for="exp_date">Date</label>
                                    <input id="exp_date" name="date" type="date" class="form-input">
                                </div>
                                <div class="full">
                                    <label class="form-label" for="exp_notes">Notes</label>
                                    <textarea id="exp_notes" name="notes" rows="3" class="form-textarea" autocomplete="off"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="hms-modal-actions">
                            <button type="button" class="btn btn-secondary" onclick="closeExpenseModal()">Cancel</button>
                            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save</button>
                        </div>
                    </form>
                </div>
            </div>

             <!-- Billing Modal -->
            <div id="billingModal" class="hms-modal-overlay" aria-hidden="true">
                <div class="hms-modal" role="dialog" aria-modal="true" aria-labelledby="billingTitle">
                    <div class="hms-modal-header">
                        <div class="hms-modal-title" id="billingTitle">
                            <i class="fas fa-plus-circle text-primary"></i>
                            Create Bill
                        </div>
                        <button type="button" class="btn btn-secondary btn-small" onclick="closeBillingModal()" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form id="billingForm">
                        <div class="hms-modal-body">
                            <div class="form-grid">
                                <div>
                                    <label class="form-label" for="patient_identifier">Patient Name / ID</label>
                                    <input id="patient_identifier" name="patient_identifier" type="text" class="form-input" required autocomplete="off">
                                </div>
                                <div>
                                    <label class="form-label" for="doctor">Doctor</label>
                                    <input id="doctor" name="doctor" type="text" class="form-input" required autocomplete="off">
                                </div>
                                <div>
                                    <label class="form-label" for="department">Department</label>
                                    <input id="department" name="department" type="text" class="form-input" required autocomplete="off">
                                </div>
                                <div>
                                    <label class="form-label" for="payment_status">Status</label>
                                    <select id="payment_status" name="payment_status" class="form-select" required>
                                        <option value="unpaid">Unpaid</option>
                                        <option value="paid">Paid</option>
                                    </select>
                                </div>
                                <div class="full">
                                    <label class="form-label">Services</label>
                                    <div class="overflow-auto">
                                        <table class="bill-table">
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
                                    <div class="justify-end-row">
                                        <button type="button" class="btn btn-secondary" onclick="addServiceRow()"><i class="fas fa-plus"></i> Add Service</button>
                                    </div>
                                </div>
                                <div class="full">
                                    <div class="totals-row">
                                        <span class="form-label m-0">Total</span>
                                        <input id="bill_total" name="bill_total" type="number" class="form-input" readonly value="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="hms-modal-actions">
                            <button type="button" class="btn btn-secondary" onclick="closeBillingModal()">Cancel</button>
                            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Process Payment Modal -->
            <div id="paymentModal" class="hms-modal-overlay" aria-hidden="true">
                <div class="hms-modal" role="dialog" aria-modal="true" aria-labelledby="paymentTitle">
                    <div class="hms-modal-header">
                        <div class="hms-modal-title" id="paymentTitle">
                            <i class="fas fa-credit-card text-success"></i>
                            Process Payment
                        </div>
                        <button type="button" class="btn btn-secondary btn-small" onclick="closePaymentModal()" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form id="paymentForm">
                        <div class="hms-modal-body">
                            <div class="form-grid">
                                <div>
                                    <label class="form-label" for="pay_patient_identifier">Patient Name / ID</label>
                                    <input id="pay_patient_identifier" name="patient_identifier" type="text" class="form-input" required autocomplete="off">
                                </div>
                                <div>
                                    <label class="form-label" for="pay_bill_ref">Bill Reference</label>
                                    <input id="pay_bill_ref" name="bill_ref" type="text" class="form-input" placeholder="Bill ID / Number" required autocomplete="off">
                                </div>
                                <div>
                                    <label class="form-label" for="pay_amount">Amount</label>
                                    <input id="pay_amount" name="amount" type="number" class="form-input" min="0" step="0.01" required>
                                </div>
                                <div>
                                    <label class="form-label" for="pay_method">Payment Method</label>
                                    <select id="pay_method" name="method" class="form-select" required>
                                        <option value="cash">Cash</option>
                                        <option value="card">Card</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label" for="pay_date">Payment Date</label>
                                    <input id="pay_date" name="date" type="date" class="form-input">
                                </div>
                                <div class="full">
                                    <label class="form-label" for="pay_notes">Notes</label>
                                    <textarea id="pay_notes" name="notes" rows="3" class="form-textarea" autocomplete="off"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="hms-modal-actions">
                            <button type="button" class="btn btn-secondary" onclick="closePaymentModal()">Cancel</button>
                            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save</button>
                        </div>
                    </form>
                </div>
            </div>


        </main>
    </div>

    <script src="<?= base_url('js/admin/financial-management.js') ?>"></script>
    <script src="<?= base_url('js/logout.js') ?>"></script>
</body>
</html>
