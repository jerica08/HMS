(function(){
  'use strict';

  // Elements cache
  var billingModal = document.getElementById('billingModal');
  var paymentModal = document.getElementById('paymentModal');
  var expenseModal = document.getElementById('expenseModal');

  // ======================== Billing ========================
  // Open the Billing modal and lock body scroll
  function openBillingModal(){
    if(!billingModal) return;
    billingModal.classList.add('active');
    billingModal.style.display = 'flex';
    billingModal.setAttribute('aria-hidden','false');
    try { document.body.style.overflow = 'hidden'; } catch(e){}
    try { console.log('[Billing] Modal opened'); } catch(e){}
  }
  // Close the Billing modal and restore body scroll
  function closeBillingModal(){
    if(!billingModal) return;
    billingModal.classList.remove('active');
    billingModal.style.display = '';
    billingModal.setAttribute('aria-hidden','true');
    try { document.body.style.overflow = ''; } catch(e){}
    try { console.log('[Billing] Modal closed'); } catch(e){}
  }
  // Add a service row to the billing table and recalc totals
  function addServiceRow(){
    var tbody = document.getElementById('servicesBody');
    if(!tbody) return;
    var tr = document.createElement('tr');
    tr.innerHTML = '\
      <td>\
        <select name="service_type[]" class="form-select" required>\
          <option value="Consultation">Consultation</option>\
          <option value="Lab Test">Lab Test</option>\
          <option value="Medicine">Medicine</option>\
          <option value="Room Fee">Room Fee</option>\
          <option value="Other">Other</option>\
        </select>\
      </td>\
      <td><input name="service_desc[]" type="text" class="form-input" required autocomplete="off"></td>\
      <td><input name="service_qty[]" type="number" class="form-input svc-qty" min="1" value="1" required></td>\
      <td><input name="service_price[]" type="number" class="form-input svc-price" min="0" step="0.01" value="0" required></td>\
      <td><input name="service_line_total[]" type="number" class="form-input svc-line" readonly value="0"></td>\
      <td class="text-right"><button type="button" class="btn btn-secondary" onclick="this.closest(\'tr\').remove(); recalcTotals();"><i class="fas fa-trash"></i></button></td>';
    tbody.appendChild(tr);
    recalcTotals();
  }
  // Recalculate the total bill amount based on service rows
  function recalcTotals(){
    var rows = document.querySelectorAll('#servicesBody tr');
    var total = 0;
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

  // ======================== Payment ========================
  // Open the Payment modal and prefill date
  function openPaymentModal(){
    if(!paymentModal) return;
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
  // Close the Payment modal
  function closePaymentModal(){
    if(!paymentModal) return;
    paymentModal.classList.remove('active');
    paymentModal.style.display = '';
    paymentModal.setAttribute('aria-hidden','true');
    try { document.body.style.overflow = ''; } catch(e){}
    try { console.log('[Payment] Modal closed'); } catch(e){}
  }

  // ======================== Expense ========================
  // Open the Expense modal and prefill date
  function openExpenseModal(){
    if(!expenseModal) return;
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
  // Close the Expense modal
  function closeExpenseModal(){
    if(!expenseModal) return;
    expenseModal.classList.remove('active');
    expenseModal.style.display = '';
    expenseModal.setAttribute('aria-hidden','true');
    try { document.body.style.overflow = ''; } catch(e){}
    try { console.log('[Expense] Modal closed'); } catch(e){}
  }

  // ======================== Event Wiring ========================
  // Recalculate totals when quantity or price changes in the services table
  document.addEventListener('input', function(e){
    var servicesBody = document.getElementById('servicesBody');
    if(servicesBody && servicesBody.contains(e.target)){
      if(e.target.classList.contains('svc-qty') || e.target.classList.contains('svc-price')){
        recalcTotals();
      }
    }
  });

  // Wire top-page action buttons
  var btn = document.getElementById('openBillingBtn');
  if(btn){
    btn.addEventListener('click', function(){
      try { console.log('[Billing] Button clicked'); } catch(e){}
      openBillingModal();
    });
  }
  var payBtn = document.getElementById('openPaymentBtn');
  if(payBtn){
    payBtn.addEventListener('click', function(){
      try { console.log('[Payment] Button clicked'); } catch(e){}
      openPaymentModal();
    });
  }
  var expBtn = document.getElementById('openExpenseBtn');
  if(expBtn){
    expBtn.addEventListener('click', function(){
      try { console.log('[Expense] Button clicked'); } catch(e){}
      openExpenseModal();
    });
  }

  // Delegated bindings for clicks on overlay backgrounds to close modals
  if(billingModal){ billingModal.addEventListener('click', function(e){ if(e.target === billingModal){ closeBillingModal(); } }); }
  if(paymentModal){ paymentModal.addEventListener('click', function(e){ if(e.target === paymentModal){ closePaymentModal(); } }); }
  if(expenseModal){ expenseModal.addEventListener('click', function(e){ if(e.target === expenseModal){ closeExpenseModal(); } }); }

  // ESC key closes any open modal
  document.addEventListener('keydown', function(e){ if(e.key === 'Escape'){ closeBillingModal(); closePaymentModal(); closeExpenseModal(); } });

  // Start with one service row on initial load
  document.addEventListener('DOMContentLoaded', function(){ addServiceRow(); });

  // Expose functions used by inline onclick attributes
  window.openBillingModal = openBillingModal;
  window.closeBillingModal = closeBillingModal;
  window.openPaymentModal = openPaymentModal;
  window.closePaymentModal = closePaymentModal;
  window.openExpenseModal = openExpenseModal;
  window.closeExpenseModal = closeExpenseModal;
  window.addServiceRow = addServiceRow;
  window.recalcTotals = recalcTotals;

})();
