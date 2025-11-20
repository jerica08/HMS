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
  (function(){
    var form = document.getElementById('addResourceForm');
    if(!form) return;

    // Add form validation
    form.addEventListener('submit', function(e){
      e.preventDefault();

      // Clear previous errors
      var errorFields = ['err_res_name', 'err_res_category', 'err_res_quantity', 'err_res_status', 'err_res_location'];
      errorFields.forEach(function(fieldId) {
        var field = document.getElementById(fieldId);
        if (field) field.textContent = '';
      });

      // Get form values
      var name = document.getElementById('res_name')?.value?.trim();
      var category = document.getElementById('res_category')?.value;
      var quantity = document.getElementById('res_quantity')?.value;
      var status = document.getElementById('res_status')?.value;
      var location = document.getElementById('res_location')?.value?.trim();

      // Validate required fields
      var hasErrors = false;

      if (!name) {
        document.getElementById('err_res_name').textContent = 'Resource name is required.';
        hasErrors = true;
      }

      if (!category) {
        document.getElementById('err_res_category').textContent = 'Please select a category.';
        hasErrors = true;
      }

      if (!quantity || quantity < 1) {
        document.getElementById('err_res_quantity').textContent = 'Quantity must be at least 1.';
        hasErrors = true;
      }

      if (!status) {
        document.getElementById('err_res_status').textContent = 'Please select a status.';
        hasErrors = true;
      }

      if (!location) {
        document.getElementById('err_res_location').textContent = 'Location is required.';
        hasErrors = true;
      }

      if (hasErrors) {
        return;
      }

      // Submit form if validation passes
      postForm(baseUrl + 'admin/resource-management/create', form)
        .then(function(res){
          if(res && res.status==='success'){
            alert('Resource added successfully.');
            closeAddResourceModal();
            form.reset();
            window.location.reload();
            return;
          }
          var msg = 'Failed to save resource';
          try {
            if (res) {
              if (res.message) { msg = res.message; }
              if (res.db && res.db.message) { msg = res.db.message; }
              if (res.errors) { msg += '\n' + JSON.stringify(res.errors); }
            }
          } catch(e){}
          alert(msg);
        })
        .catch(function(){ alert('Failed to save resource'); });
    });
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
    set('er_status', r.status || 'Available');
    set('er_location', r.location || '');
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
      var errorFields = ['err_er_name', 'err_er_category', 'err_er_quantity', 'err_er_status', 'err_er_location'];
      errorFields.forEach(function(fieldId) {
        var field = document.getElementById(fieldId);
        if (field) field.textContent = '';
      });

      // Get form values
      var name = document.getElementById('er_name')?.value?.trim();
      var category = document.getElementById('er_category')?.value;
      var quantity = document.getElementById('er_quantity')?.value;
      var status = document.getElementById('er_status')?.value;
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

      if (!status) {
        document.getElementById('err_er_status').textContent = 'Please select a status.';
        hasErrors = true;
      }

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
            window.location.reload();
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

})();
