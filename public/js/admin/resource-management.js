(function(){
  'use strict';

  // Safeguards for globals
  var HMS = window.HMS || {};
  var baseUrl = (HMS.baseUrl || '').replace(/\/+$/, '') + '/';
  var csrf = HMS.csrf || {};

  // preload data to map if provided by controller (expects window.resources injected via PHP)
  (function(){
    try {
      var list = window.__RESOURCES__ || [];
      var byId = {};
      if (Array.isArray(list)) {
        for (var i = 0; i < list.length; i++) {
          var r = list[i];
          if (r && (r.id || r.resource_id)) {
            byId[r.id || r.resource_id] = r;
          }
        }
      }
      window.resourcesById = byId;
    } catch (e) {
      window.resourcesById = {};
    }
  })();

  // Modal controls (aligned with user-management.js behavior)
  function openAddResourceModal(){ var m=document.getElementById('addResourceModal'); if(m){ m.classList.add('active'); m.setAttribute('aria-hidden','false'); } }
  function closeAddResourceModal(){ var m=document.getElementById('addResourceModal'); if(m){ m.classList.remove('active'); m.setAttribute('aria-hidden','true'); } }
  function openEditResourceModal(){ var m=document.getElementById('editResourceModal'); if(m){ m.classList.add('active'); m.setAttribute('aria-hidden','false'); } }
  function closeEditResourceModal(){ var m=document.getElementById('editResourceModal'); if(m){ m.classList.remove('active'); m.setAttribute('aria-hidden','true'); } }

  window.openAddResourceModal = openAddResourceModal;
  window.closeAddResourceModal = closeAddResourceModal;
  window.openEditResourceModal = openEditResourceModal;
  window.closeEditResourceModal = closeEditResourceModal;

  document.getElementById('addResourceBtn')?.addEventListener('click', openAddResourceModal);
  document.addEventListener('click', function(e){ var m=document.getElementById('addResourceModal'); if(m && e.target===m) closeAddResourceModal(); });
  document.addEventListener('click', function(e){ var m=document.getElementById('editResourceModal'); if(m && e.target===m) closeEditResourceModal(); });
  document.addEventListener('keydown', function(e){ if(e.key==='Escape'){ closeAddResourceModal(); closeEditResourceModal(); }});

  // Export functionality
  (function(){
    var exportBtn = document.getElementById('exportBtn');
    if (exportBtn) {
      exportBtn.addEventListener('click', function(){
        var searchInput = document.getElementById('searchResource');
        var categoryFilter = document.getElementById('filterCategory');
        var statusFilter = document.getElementById('filterStatus');
        
        var params = new URLSearchParams();
        if (searchInput && searchInput.value) params.append('search', searchInput.value);
        if (categoryFilter && categoryFilter.value) params.append('category', categoryFilter.value);
        if (statusFilter && statusFilter.value) params.append('status', statusFilter.value);
        
        var url = baseUrl + 'admin/resource-management/export';
        if (params.toString()) url += '?' + params.toString();
        
        window.location.href = url;
      });
    }
  })();

  // Helpers
  function toParams(form){
    var fd = new FormData(form);
    var p = new URLSearchParams();
    fd.forEach(function(v,k){ if(v!==undefined && v!==null) p.append(k, v); });
    try { if (csrf.token && csrf.hash) { p.append('csrf_token', csrf.hash); } } catch(e) {}
    return p;
  }
  function postForm(url, form){
    return fetch(url, {
      method:'POST',
      headers:{ 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' },
      credentials:'same-origin',
      body: toParams(form)
    }).then(async function(r){
      try { return await r.json(); }
      catch(e){
        try { var t = await r.text(); return { status:'error', message: t && t.trim() ? t : ('HTTP '+r.status) }; }
        catch(_){ return { status:'error', message: 'HTTP '+r.status }; }
      }
    });
  }

  // Add Resource submit -> POST admin/resource-management/create
  // NOTE: This handler is disabled to prevent conflicts with inline script in modal
  // The modal has its own submission handler to prevent duplicate submissions
  (function(){
    var form = document.getElementById('addResourceForm');
    if(!form) return;

    // Remove any existing submit listeners to prevent duplicates
    var newForm = form.cloneNode(true);
    form.parentNode.replaceChild(newForm, form);
    
    // Only add listener if not already handled by inline script
    // The inline script in the modal handles submission via button click
    // This prevents double submission
  })();

  // Edit button and submit (UI stub)
  window.editResource = function(id){
    var r=(window.resourcesById||{})[id];
    if(!r){ alert('Resource not found'); return; }
    var set=function(id,val){ var el=document.getElementById(id); if(!el) return; if(el.tagName==='SELECT' || el.tagName==='INPUT' || el.tagName==='TEXTAREA'){ el.value = val ?? ''; } else { el.textContent = val ?? ''; } };
    set('er_id', r.id || '');
    set('er_name', r.equipment_name || '');
    set('er_category', r.category || '');
    set('er_quantity', r.quantity || '');
    // Status removed from form - default to 'Stock In' in backend
    set('er_location', r.location || '');
    set('er_serial_number', r.serial_number || '');
    set('er_batch_number', r.batch_number || '');
    set('er_expiry_date', r.expiry_date || '');
    set('er_remarks', r.remarks || '');
    
    // Show/hide medication fields based on category
    var isMedication = r.category === 'Medications';
    var medFields = document.getElementById('editMedicationFields');
    if(medFields) medFields.style.display = isMedication ? 'flex' : 'none';
    
    openEditResourceModal();
  };

  window.deleteResource = function(id){
    if(!confirm('Delete this resource?')) return;
    var p=new URLSearchParams(); p.append('id', id);
    try { if (csrf.token && csrf.hash) { p.append('csrf_token', csrf.hash); } } catch(e) {}
    fetch(baseUrl + 'admin/resource-management/delete', { method:'POST', headers:{ 'Accept':'application/json' }, body:p })
      .then(function(r){ return r.json().catch(function(){ return {status:'error'}; }); })
      .then(function(res){
        if(res && res.status==='success'){
          showNotification('Resource deleted successfully','success');
          window.location.reload();
        } else {
          showNotification(res && res.message ? res.message : 'Failed to delete','error');
        }
      })
      .catch(function(){ showNotification('Failed to delete','error'); });
  };

  // Edit Resource -> POST admin/resource-management/update
  (function(){
    var form=document.getElementById('editResourceForm');
    if(!form) return;

    // Add form validation
    form.addEventListener('submit', function(e){
      e.preventDefault();

      // Clear previous errors
      var errorFields = ['err_er_name', 'err_er_category', 'err_er_quantity', 'err_er_location'];
      errorFields.forEach(function(fieldId) {
        var field = document.getElementById(fieldId);
        if (field) field.textContent = '';
      });

      // Get form values
      var name = document.getElementById('er_name')?.value?.trim();
      var category = document.getElementById('er_category')?.value;
      var quantity = document.getElementById('er_quantity')?.value;
      // Status removed from form - default to 'Stock In' in backend
      var location = document.getElementById('er_location')?.value?.trim();

      // Validate required fields
      var hasErrors = false;

      if (!name) {
        document.getElementById('err_er_name').textContent = 'Resource name is required.';
        hasErrors = true;
      }

      if (!category) {
        document.getElementById('err_er_category').textContent = 'Please select a category.';
        hasErrors = true;
      }

      if (!quantity || quantity < 1) {
        document.getElementById('err_er_quantity').textContent = 'Quantity must be at least 1.';
        hasErrors = true;
      }

      // Status validation removed - default to 'Stock In' in backend

      if (!location) {
        document.getElementById('err_er_location').textContent = 'Location is required.';
        hasErrors = true;
      }

      if (hasErrors) {
        return;
      }

      // Submit form if validation passes
      postForm(baseUrl + 'admin/resource-management/update', form)
        .then(function(res){
          if(res && res.status==='success'){
            alert('Resource updated successfully.');
            closeEditResourceModal();
            return;
          }
          var msg = 'Failed to update resource';
          try {
            if (res) {
              if (res.message) { msg = res.message; }
              if (res.db && res.db.message) { msg = res.db.message; }
              if (res.errors) { msg += '\n' + JSON.stringify(res.errors); }
            }
          } catch(e){}
          alert(msg);
        })
        .catch(function(){ alert('Failed to update resource'); });
    });
  })();

  // Shared notification helper using resourcesNotification bar when present
  function showNotification(message, type){
    var container = document.getElementById('resourcesNotification');
    var iconEl = document.getElementById('resourcesNotificationIcon');
    var textEl = document.getElementById('resourcesNotificationText');

    if (container && iconEl && textEl) {
      var isError = type === 'error';
      var isSuccess = type === 'success';

      container.style.border = isError ? '1px solid #fecaca' : '1px solid #bbf7d0';
      container.style.background = isError ? '#fee2e2' : '#ecfdf5';
      container.style.color = isError ? '#991b1b' : '#166534';

      var iconClass = isError ? 'fa-exclamation-triangle' : (isSuccess ? 'fa-check-circle' : 'fa-info-circle');
      iconEl.className = 'fas ' + iconClass;

      textEl.textContent = String(message || '');
      container.style.display = 'flex';

      setTimeout(function(){
        if (container.style.display !== 'none') {
          container.style.display = 'none';
        }
      }, 4000);
      return;
    }

    // Fallback: simple alert
    alert(message || (type === 'error' ? 'Error' : 'Notice'));
  }

  window.dismissResourcesNotification = function(){
    var container = document.getElementById('resourcesNotification');
    if (container) container.style.display = 'none';
  };

  // Search and Filter Functionality
  (function(){
    var searchInput = document.getElementById('searchResource');
    var categoryFilter = document.getElementById('filterCategory');
    var statusFilter = document.getElementById('filterStatus');
    var clearFiltersBtn = document.getElementById('clearFilters');
    var tableBody = document.getElementById('resourcesTableBody');

    if (!tableBody) return;

    var allResources = [];
    // Get all resources from table rows
    (function(){
      var rows = tableBody.querySelectorAll('tr');
      rows.forEach(function(row){
        var cells = row.querySelectorAll('td');
        if (cells.length > 0) {
          var name = cells[0].textContent.trim();
          var category = cells[1].textContent.trim();
          var quantity = cells[2].textContent.trim();
          var statusEl = cells[3].querySelector('.badge');
          var status = statusEl ? statusEl.textContent.trim() : '';
          var location = cells[4].textContent.trim();
          
          allResources.push({
            row: row,
            name: name.toLowerCase(),
            category: category,
            status: status,
            location: location.toLowerCase(),
            searchText: (name + ' ' + location).toLowerCase()
          });
        }
      });
    })();

    function filterResources(){
      var searchTerm = (searchInput?.value || '').toLowerCase().trim();
      var selectedCategory = categoryFilter?.value || '';
      var selectedStatus = statusFilter?.value || '';

      allResources.forEach(function(resource){
        var matchesSearch = !searchTerm || resource.searchText.includes(searchTerm);
        var matchesCategory = !selectedCategory || resource.category === selectedCategory;
        var matchesStatus = !selectedStatus || resource.status === selectedStatus;

        if (matchesSearch && matchesCategory && matchesStatus) {
          resource.row.style.display = '';
        } else {
          resource.row.style.display = 'none';
        }
      });
    }

    function clearFilters(){
      if (searchInput) searchInput.value = '';
      if (categoryFilter) categoryFilter.value = '';
      if (statusFilter) statusFilter.value = '';
      filterResources();
    }

    // Event listeners
    if (searchInput) {
      searchInput.addEventListener('input', function(){
        clearTimeout(window.searchTimeout);
        window.searchTimeout = setTimeout(filterResources, 300);
      });
    }
    if (categoryFilter) {
      categoryFilter.addEventListener('change', filterResources);
    }
    if (statusFilter) {
      statusFilter.addEventListener('change', filterResources);
    }
    if (clearFiltersBtn) {
      clearFiltersBtn.addEventListener('click', clearFilters);
    }
  })();

})();
