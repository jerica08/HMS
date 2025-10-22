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
    try { if (csrf.token && csrf.hash) { p.append(csrf.token, csrf.hash); } } catch(e) {}
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

  // Add Resource submit -> POST admin/resources/create
  (function(){
    var form = document.getElementById('addResourceForm');
    if(!form) return;
    form.addEventListener('submit', function(e){
      e.preventDefault();
      postForm(baseUrl + 'admin/resources/create', form)
        .then(function(res){
          if(res && res.status==='success'){ alert('Resource added successfully.'); window.location.reload(); return; }
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
    set('er_id', r.id || r.resource_id || '');
    set('er_name', r.name || '');
    set('er_category', r.category || '');
    set('er_quantity', r.quantity || '');
    set('er_status', r.status || '');
    set('er_location', r.location || '');
    set('er_notes', r.notes || '');
    openEditResourceModal();
  };

  window.deleteResource = function(id){
    if(!confirm('Delete this resource?')) return;
    var p=new URLSearchParams(); p.append('id', id);
    try { if (csrf.token && csrf.hash) { p.append(csrf.token, csrf.hash); } } catch(e) {}
    fetch(baseUrl + 'admin/resources/delete', { method:'POST', headers:{ 'Accept':'application/json' }, body:p })
      .then(function(r){ return r.json().catch(function(){ return {status:'error'}; }); })
      .then(function(res){ if(res && res.status==='success'){ window.location.reload(); } else { alert('Failed to delete'); } })
      .catch(function(){ alert('Failed to delete'); });
  };

  // Edit Resource -> POST admin/resources/update
  (function(){
    var form=document.getElementById('editResourceForm');
    if(!form) return;
    form.addEventListener('submit', function(e){
      e.preventDefault();
      postForm(baseUrl + 'admin/resources/update', form)
        .then(function(res){
          if(res && res.status==='success'){ window.location.reload(); return; }
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

})();
