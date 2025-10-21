<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Financial Management - HMS Admin</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Modal and form styles (aligned with other admin modals) */
        .hms-modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.55); display: none; align-items: center; justify-content: center; padding: 1rem; z-index: 9990; }
        .hms-modal-overlay.active { display: flex; }
        .hms-modal { width: 100%; max-width: 900px; background: #fff; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); overflow: hidden; border: 1px solid #f1f5f9; position: fixed; left: 50%; top: 50%; transform: translate(-50%, -50%); max-height: 90vh; overflow: auto; box-sizing: border-box; }
        .hms-modal-header { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; border-bottom: 1px solid #e5e7eb; background: #f8f9ff; }  
        .hms-modal-title { font-weight: 600; color: #1e293b; display: flex; align-items: center; gap: 0.5rem; }
        .hms-modal-body { padding: 1rem 1.25rem; color: #475569; }
        .hms-modal-actions { display: flex; gap: 0.5rem; justify-content: flex-end; padding: 0.75rem 1.25rem 1.25rem; background: #fff; }
        .form-input, .form-select, .form-textarea { width: 100%; border: 1px solid #e5e7eb; border-radius: 8px; padding: 0.6rem 0.75rem; font-size: 0.95rem; background: #fff; transition: border-color 0.2s; }
        .form-input:focus, .form-select:focus, .form-textarea:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
        .form-label { font-size: 0.9rem; color: #374151; margin-bottom: 0.25rem; display: block; font-weight: 500; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
        .form-grid .full { grid-column: 1 / -1; }
        @media (max-width: 640px) { .form-grid { grid-template-columns: 1fr; } }

         /* Scoped styles for this page's quick stats cards */
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .quick-stats .stat-card {
            background: #fff;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            border: 1px solid #f1f5f9;
        }
        .quick-stats .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        .quick-stats .stat-label {
            font-size: 0.9rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }
        .quick-stats .stat-change {
            font-size: 0.8rem;
            font-weight: 500;
        }
        .quick-stats .change-positive { color: #22c55e; }
        .quick-stats .change-negative { color: #ef4444; }

        /* Stat-cards */       
        .quick-stats .stat-card.revenue .stat-number { color: #10b981; }       
        .quick-stats .stat-card.expenses .stat-number { color: #eab308; }     
        .quick-stats .stat-card.profit .stat-number { color: #4f46e5; }
    </style>
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
                            <i class="fas fa-receipt" style="color:#eab308"></i>
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
                            <i class="fas fa-plus-circle" style="color:#4f46e5"></i>
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
                                    <div style="overflow:auto;">
                                        <table class="bill-table" style="width:100%;">
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
                                    <div style="display:flex;justify-content:flex-end;margin-top:0.5rem;">
                                        <button type="button" class="btn btn-secondary" onclick="addServiceRow()"><i class="fas fa-plus"></i> Add Service</button>
                                    </div>
                                </div>
                                <div class="full">
                                    <div style="display:flex;justify-content:flex-end;align-items:center;gap:0.75rem;">
                                        <span class="form-label" style="margin:0;">Total</span>
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
                            <i class="fas fa-credit-card" style="color:#10b981"></i>
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

    <script>
    // Modal helpers
    const billingModal = document.getElementById('billingModal');
    function openBillingModal(){
        if(billingModal){
            billingModal.classList.add('active');
            billingModal.style.display = 'flex';
            billingModal.setAttribute('aria-hidden','false');
            try { document.body.style.overflow = 'hidden'; } catch(e){}
            try { console.log('[Billing] Modal opened'); } catch(e){}
        }
    }
    function closeBillingModal(){
        if(billingModal){
            billingModal.classList.remove('active');
            billingModal.style.display = '';
            billingModal.setAttribute('aria-hidden','true');
            try { document.body.style.overflow = ''; } catch(e){}
            try { console.log('[Billing] Modal closed'); } catch(e){}
        }
    }
    function addServiceRow(){
        const tbody = document.getElementById('servicesBody');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <select name="service_type[]" class="form-select" required>
                    <option value="Consultation">Consultation</option>
                    <option value="Lab Test">Lab Test</option>
                    <option value="Medicine">Medicine</option>
                    <option value="Room Fee">Room Fee</option>
                    <option value="Other">Other</option>
                </select>
            </td>
            <td><input name="service_desc[]" type="text" class="form-input" required autocomplete="off"></td>
            <td><input name="service_qty[]" type="number" class="form-input svc-qty" min="1" value="1" required></td>
            <td><input name="service_price[]" type="number" class="form-input svc-price" min="0" step="0.01" value="0" required></td>
            <td><input name="service_line_total[]" type="number" class="form-input svc-line" readonly value="0"></td>
            <td style="text-align:right;"><button type="button" class="btn btn-secondary" onclick="this.closest('tr').remove(); recalcTotals();"><i class="fas fa-trash"></i></button></td>
        `;
        tbody.appendChild(tr);
        recalcTotals();
    }
    function recalcTotals(){
        const rows = document.querySelectorAll('#servicesBody tr');
        let total = 0;
        rows.forEach(function(r){
            var qtyEl = r.querySelector('.svc-qty');
            var priceEl = r.querySelector('.svc-price');
            var qty = parseFloat(qtyEl && qtyEl.value ? qtyEl.value : '0');
            var price = parseFloat(priceEl && priceEl.value ? priceEl.value : '0');
            var line = (isNaN(qty)?0:qty) * (isNaN(price)?0:price);
            var lineInput = r.querySelector('.svc-line');
            if(lineInput){ lineInput.value = line.toFixed(2); }
            total += line;
        });
        var totalInput = document.getElementById('bill_total');
        if(totalInput){ totalInput.value = total.toFixed(2); }
    }
    document.addEventListener('input', function(e){
        var servicesBody = document.getElementById('servicesBody');
        if(servicesBody && servicesBody.contains(e.target)){
            if(e.target.classList.contains('svc-qty') || e.target.classList.contains('svc-price')){
                recalcTotals();
            }
        }
    });
    // Wire button immediately (script is at end of body, elements exist now)
    var btn = document.getElementById('openBillingBtn');
    if(btn){
        btn.addEventListener('click', function(){
            try { console.log('[Billing] Button clicked'); } catch(e){}
            openBillingModal();
        });
    }
    // Wire Process Payment button
    var payBtn = document.getElementById('openPaymentBtn');
    if(payBtn){
        payBtn.addEventListener('click', function(){
            try { console.log('[Payment] Button clicked'); } catch(e){}
            openPaymentModal();
        });
    }
    // Wire Expenses button
    var expBtn = document.getElementById('openExpenseBtn');
    if(expBtn){
        expBtn.addEventListener('click', function(){
            try { console.log('[Expense] Button clicked'); } catch(e){}
            openExpenseModal();
        });
    }
    // Delegated binding in case button is re-rendered
    document.addEventListener('click', function(e){
        var t = e.target;
        if(!t) return;
        if (t.id === 'openBillingBtn' || (t.closest && t.closest('#openBillingBtn'))){
            openBillingModal();
        }
        if (t.id === 'openPaymentBtn' || (t.closest && t.closest('#openPaymentBtn'))){
            openPaymentModal();
        }
        if (t.id === 'openExpenseBtn' || (t.closest && t.closest('#openExpenseBtn'))){
            openExpenseModal();
        }
    });
    // Close when clicking outside modal content
    if(billingModal){
        billingModal.addEventListener('click', function(e){
            if(e.target === billingModal){ closeBillingModal(); }
        });
    }
    const paymentModal = document.getElementById('paymentModal');
    function openPaymentModal(){
        if(paymentModal){
            paymentModal.classList.add('active');
            paymentModal.style.display = 'flex';
            paymentModal.setAttribute('aria-hidden','false');
            try { document.body.style.overflow = 'hidden'; } catch(e){}
            try { console.log('[Payment] Modal opened'); } catch(e){}
            // default date to today if empty
            try {
                var d = document.getElementById('pay_date');
                if(d && !d.value){
                    var now = new Date();
                    var m = String(now.getMonth()+1).padStart(2,'0');
                    var day = String(now.getDate()).padStart(2,'0');
                    d.value = now.getFullYear()+'-'+m+'-'+day;
                }
            } catch(e){}
        }
    }
    function closePaymentModal(){
        if(paymentModal){
            paymentModal.classList.remove('active');
            paymentModal.style.display = '';
            paymentModal.setAttribute('aria-hidden','true');
            try { document.body.style.overflow = ''; } catch(e){}
            try { console.log('[Payment] Modal closed'); } catch(e){}
        }
    }
    if(paymentModal){
        paymentModal.addEventListener('click', function(e){ if(e.target === paymentModal){ closePaymentModal(); } });
    }
    // Expense modal controls
    const expenseModal = document.getElementById('expenseModal');
    function openExpenseModal(){
        if(expenseModal){
            expenseModal.classList.add('active');
            expenseModal.style.display = 'flex';
            expenseModal.setAttribute('aria-hidden','false');
            try { document.body.style.overflow = 'hidden'; } catch(e){}
            try { console.log('[Expense] Modal opened'); } catch(e){}
            // default date to today if empty
            try {
                var d = document.getElementById('exp_date');
                if(d && !d.value){
                    var now = new Date();
                    var m = String(now.getMonth()+1).padStart(2,'0');
                    var day = String(now.getDate()).padStart(2,'0');
                    d.value = now.getFullYear()+'-'+m+'-'+day;
                }
            } catch(e){}
        }
    }
    function closeExpenseModal(){
        if(expenseModal){
            expenseModal.classList.remove('active');
            expenseModal.style.display = '';
            expenseModal.setAttribute('aria-hidden','true');
            try { document.body.style.overflow = ''; } catch(e){}
            try { console.log('[Expense] Modal closed'); } catch(e){}
        }
    }
    if(expenseModal){
        expenseModal.addEventListener('click', function(e){ if(e.target === expenseModal){ closeExpenseModal(); } });
    }
    // Escape to close
    document.addEventListener('keydown', function(e){ if(e.key === 'Escape'){ closeBillingModal(); closePaymentModal(); closeExpenseModal(); } });
    // Start with one service row on load
    document.addEventListener('DOMContentLoaded', function(){ addServiceRow(); });
    </script>
    <script src="<?= base_url('js/logout.js') ?>"></script>
</body>
</html>
