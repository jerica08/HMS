(function(){
  // Staff Management – Doctor Shifts module
  // Config bootstrap: prefer JSON from #staff-config, fallback to window.STAFF_CFG
  const cfg = (function(){
    try {
      var tag = document.getElementById('staff-config');
      if (tag) {
        return JSON.parse(tag.textContent || '{}');
      }
    } catch (_) {}
    return (window.STAFF_CFG || {});
  })();
  // API endpoints
  const apiUrl = cfg.apiUrl; // legacy/simple list
  const deleteUrl = cfg.deleteUrl;
  const doctorsUrl = cfg.doctorsUrl;
  const shiftsUrl = cfg.shiftsUrl || apiUrl;
  const createShiftUrl = cfg.createShiftUrl;
  const shiftShowBase = cfg.shiftShowBase; // e.g., base + '/:id'
  const shiftUpdateUrl = cfg.shiftUpdateUrl;
  // Staff endpoints
  const staffApiUrl = cfg.staffApiUrl; // list all staff
  const staffGetBase = cfg.staffGetBase; // base + '/:id'
  const staffCreateUrl = cfg.staffCreateUrl; // POST create
  const staffUpdateUrl = cfg.staffUpdateUrl; // POST update
  // CSRF fields
  const csrfToken = cfg.csrfToken;
  const csrfHash = cfg.csrfHash;

  // DOM cache: shifts table body element
  const body = document.getElementById('doctorShiftsBody');
  // Do not abort: other helpers (e.g., role fields) may still need to run on this page

  // ---------------------------------------------------------
  // Modal helpers & public API used by inline HTML handlers
  // ---------------------------------------------------------
  // showOverlay: activates a modal overlay by id
  function showOverlay(id){
    var el = document.getElementById(id);
    if (el) { el.classList.add('active'); el.setAttribute('aria-hidden','false'); }
  }

  // ---------------------------------------------------------
  // Assign Shift (Doctors) modal open/close + submit
  // ---------------------------------------------------------
  // prefillAssignDate: sets today's date on assign shift form
  function prefillAssignDate(){
    try{
      var dateEl = document.getElementById('shift_date');
      if (!dateEl) return;
      var today = new Date();
      var yyyy = today.getFullYear();
      var mm = String(today.getMonth()+1).padStart(2,'0');
      var dd = String(today.getDate()).padStart(2,'0');
      dateEl.value = yyyy + '-' + mm + '-' + dd;
    }catch(_){ /* noop */ }
  }
  // openAssignShiftModal: prepares and opens the Assign Shift modal
  window.openAssignShiftModal = function(){
    prefillAssignDate();
    if (doctorsUrl) loadDoctors();
    showOverlay('assignShiftModal');
  };
  // closeAssignShiftModal: closes Assign Shift modal and resets form
  window.closeAssignShiftModal = function(){
    hideOverlay('assignShiftModal');
    var f = document.getElementById('assignShiftForm');
    if (f) f.reset();
  };
  // bindAssignShiftForm: wires submit/close behaviors for Assign Shift form
  function bindAssignShiftForm(){
    var form = document.getElementById('assignShiftForm');
    if (!form || form.__boundSubmit) return;
    form.__boundSubmit = true;
    form.addEventListener('submit', async function(e){
      e.preventDefault();
      if (!createShiftUrl) return;
      var fd = new FormData(form);
      try{
        var res = await fetch(createShiftUrl, { method:'POST', headers:{ 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' }, body: fd, credentials:'same-origin' });
        var bodyText = await res.text().catch(function(){ return ''; });
        var json = {};
        try { json = bodyText ? JSON.parse(bodyText) : {}; } catch(_) { json = {}; }
        if (!res.ok || !(json && json.status === 'success')){
          var dbErr = json && json.db_error ? ('\nDB: '+(JSON.stringify(json.db_error))) : '';
          var serverMsg = (json && json.message) ? json.message : '';
          alert((serverMsg || ('Failed to create shift (HTTP '+res.status+')')) + dbErr);
          return;
        }
        alert('Shift created successfully.');
        form.reset();
        hideOverlay('assignShiftModal');
        if (typeof loadDoctorShifts === 'function') await loadDoctorShifts();
      }catch(err){
        console.error('Create shift failed', err);
        alert('Failed to create shift.');
      }
    });
    // Overlay background click to close
    var overlay = document.getElementById('assignShiftModal');
    if (overlay && !overlay.__boundClick){
      overlay.__boundClick = true;
      overlay.addEventListener('click', function(e){ if (e.target === overlay) window.closeAssignShiftModal(); });
    }
    // Fallback open button
    var btn = document.getElementById('openAssignShiftBtn');
    if (btn && !btn.__boundClick){ btn.__boundClick = true; btn.addEventListener('click', window.openAssignShiftModal); }
  }

  // ---------------------------------------------------------
  // Staff list (table), Add staff, View/Edit staff modals
  // ---------------------------------------------------------
  // loadStaffTable: fetches and renders staff rows in table
  async function loadStaffTable(){
    const tbody = document.getElementById('staffTableBody');
    if (!tbody || !staffApiUrl) return;
    try{
      const res = await fetch(staffApiUrl, { headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' } });
      if (!res.ok) throw new Error('Failed to load staff');
      const staff = await res.json();
      if (!Array.isArray(staff) || staff.length === 0){
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:#6b7280; padding:1rem;">No staff found.</td></tr>';
        return;
      }
      tbody.innerHTML = staff.map(function(s){
        const id = (s.id != null ? s.id : (s.staff_id != null ? s.staff_id : ''));
        const first = s.first_name || '';
        const last = s.last_name || '';
        const name = (s.full_name || (first + ' ' + last)).trim();
        const role = (s.role || '').toString().toLowerCase();
        const roleDisplay = role ? role.replace('_',' ') : '';
        const dept = s.department || '';
        const email = s.email || '';
        return (
          '<tr>'+
            '<td style="padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">'+(name || '-')+'</td>'+
            '<td style="padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb; text-transform:capitalize;">'+(roleDisplay || '-')+'</td>'+
            '<td style="padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">'+(dept || '-')+'</td>'+
            '<td style="padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">'+(email || '-')+'</td>'+
            '<td style="padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">'+
              '<button type="button" class="btn btn-primary btn-small" onclick="openStaffViewModal('+id+')"><i class="fas fa-eye"></i> View</button> '+
              '<button type="button" class="btn btn-warning btn-small" onclick="openStaffEditModal('+id+')"><i class="fas fa-edit"></i> Edit</button>'+
            '</td>'+
          '</tr>'
        );
      }).join('');
    } catch(err){
      console.error(err);
      tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:#ef4444; padding:1rem;">Failed to load staff.</td></tr>';
    }
  }

  // populateViewStaffFields: clears or sets values for view-staff modal inputs
  function populateViewStaffFields(clear){
    const setVal = function(id, val){ var el = document.getElementById(id); if (el) el.value = val; };
    if (clear){
      setVal('v_full_name',''); setVal('v_role_input',''); setVal('v_department_input',''); setVal('v_email_input','');
      setVal('v_contact_input',''); setVal('v_gender_input',''); setVal('v_dob_input',''); setVal('v_address_input','');
    }
  }

  // openStaffViewModal: loads staff details and opens the view modal
  window.openStaffViewModal = function(id){
    const overlay = document.getElementById('viewStaffModal');
    if (!overlay || !staffGetBase) return;
    populateViewStaffFields(true);
    const hiddenId = document.getElementById('v_staff_id');
    if (hiddenId) hiddenId.value = id || '';
    fetch(staffGetBase + '/' + id, { headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' }})
      .then(function(r){ return r.json(); })
      .then(function(s){
        const first = s.first_name || '';
        const last = s.last_name || '';
        const name = (s.full_name || (first + ' ' + last)).trim();
        const role = (s.role || '').toString().toLowerCase();
        const roleDisplay = role ? role.replace('_',' ') : '';
        const set = function(id, val){ var el = document.getElementById(id); if (el) el.value = (val || ''); };
        set('v_full_name', name);
        set('v_role_input', roleDisplay);
        set('v_department_input', s.department);
        set('v_email_input', s.email);
        set('v_contact_input', s.contact_no);
        set('v_gender_input', s.gender);
        set('v_dob_input', s.dob);
        var addr = document.getElementById('v_address_input'); if (addr) addr.value = (s.address || '');
      })
      .catch(function(err){ console.error(err); });
    showOverlay('viewStaffModal');
  };

  // openStaffEditModal: loads staff details and opens the edit modal
  window.openStaffEditModal = function(id){
    const overlay = document.getElementById('editStaffModal');
    const form = document.getElementById('editStaffForm');
    if (!overlay || !form || !staffGetBase) return;
    // Clear id first
    var hid = document.getElementById('e_staff_id'); if (hid) hid.value = ''+ (id || '');
    fetch(staffGetBase + '/' + id, { headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' }})
      .then(function(r){ return r.json(); })
      .then(function(s){
        form.querySelector('#e_first_name').value = s.first_name || '';
        form.querySelector('#e_last_name').value = s.last_name || '';
        form.querySelector('#e_gender').value = s.gender || '';
        form.querySelector('#e_dob').value = s.dob || '';
        form.querySelector('#e_contact_no').value = s.contact_no || '';
        form.querySelector('#e_email').value = s.email || '';
        form.querySelector('#e_address').value = s.address || '';
        form.querySelector('#e_department').value = s.department || '';
        form.querySelector('#e_designation').value = s.designation || '';
      })
      .catch(function(err){ console.error(err); });
    showOverlay('editStaffModal');
  };

  // bindAddStaffForm: wires submit for Add Staff form and fallback open button
  function bindAddStaffForm(){
    const form = document.getElementById('addStaffForm');
    if (!form || form.__boundSubmit) return;
    form.__boundSubmit = true;
    // also wire open button fallback
    var openBtn = document.getElementById('openAddStaffBtn');
    if (openBtn && !openBtn.__boundClick){ openBtn.__boundClick = true; openBtn.addEventListener('click', function(){ showOverlay('addStaffModal'); }); }
    form.addEventListener('submit', async function(e){
      e.preventDefault();
      const action = staffCreateUrl || form.getAttribute('action');
      const formData = new FormData(form);
      try{
        const res = await fetch(action, { method:'POST', headers:{ 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' }, body: formData });
        const data = await res.json();
        if (res.ok && data && data.status === 'success'){
          alert('Staff member created successfully');
          form.reset();
          hideOverlay('addStaffModal');
          loadStaffTable();
        } else {
          const errs = data && data.errors ? Object.values(data.errors).join('\n- ') : null;
          const msg = (data && data.message) || 'Failed to create staff';
          alert(errs ? (msg+':\n- '+errs) : msg);
        }
      } catch(err){
        console.error(err);
        alert('An error occurred while creating staff');
      }
    });
  }

  // bindEditStaffForm: wires submit for Edit Staff form
  function bindEditStaffForm(){
    const form = document.getElementById('editStaffForm');
    if (!form || form.__boundSubmit) return;
    form.__boundSubmit = true;
    form.addEventListener('submit', async function(e){
      e.preventDefault();
      if (!staffUpdateUrl) return;
      const fd = new FormData(form);
      try{
        const res = await fetch(staffUpdateUrl, { method:'POST', headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' }, body: fd });
        const data = await res.json().catch(function(){ return {}; });
        if (res.ok && data && data.status === 'success'){
          hideOverlay('editStaffModal');
          loadStaffTable();
        } else {
          alert((data && data.message) || 'Failed to update staff');
        }
      } catch(err){
        console.error(err);
        alert('An error occurred while updating staff');
      }
    });
  }
  // hideOverlay: deactivates a modal overlay by id
  function hideOverlay(id){
    var el = document.getElementById(id);
    if (el) { el.classList.remove('active'); el.setAttribute('aria-hidden','true'); }
  }

  // Doctor Shift admin modal (view/edit existing)
  // closeDoctorShiftAdminModal: closes the doctor shift admin modal
  window.closeDoctorShiftAdminModal = function(){ hideOverlay('doctorShiftAdminModal'); };

  // View staff modal
  // closeStaffViewModal: closes the view staff modal
  window.closeStaffViewModal = function(){ hideOverlay('viewStaffModal'); };

  // Edit staff modal
  // closeStaffEditModal: closes the edit staff modal
  window.closeStaffEditModal = function(){ hideOverlay('editStaffModal'); };

  // Add staff modal
  // openAddStaffModal: opens the add staff modal
  window.openAddStaffModal = function(){ showOverlay('addStaffModal'); };
  // closeAddStaffModal: closes the add staff modal
  window.closeAddStaffModal = function(){ hideOverlay('addStaffModal'); };

  // Close on overlay background click for Add Staff
  document.addEventListener('click', function(e){
    const overlay = document.getElementById('addStaffModal');
    if (overlay && e.target === overlay){ hideOverlay('addStaffModal'); }
  });

  // ---------------------------------------------------------
  // Role-based dynamic fields (Add/Edit Staff)
  // ---------------------------------------------------------
  // getRoleBlocks: returns map of role -> container element
  function getRoleBlocks(){
    return {
      'doctor': document.getElementById('role-fields-doctor'),
      'nurse': document.getElementById('role-fields-nurse'),
      'pharmacist': document.getElementById('role-fields-pharmacist'),
      'laboratorist': document.getElementById('role-fields-laboratorist'),
      'accountant': document.getElementById('role-fields-accountant'),
      'receptionist': document.getElementById('role-fields-receptionist'),
      'it_staff': document.getElementById('role-fields-it_staff')
    };
  }
  // updateRoleFieldsForSelect: toggles role-specific blocks based on selection
  function updateRoleFieldsForSelect(selectEl){
    if (!selectEl) return;
    const roleBlocks = getRoleBlocks();
    const v = selectEl.value;
    Object.values(roleBlocks).forEach(function(b){ if (b) b.style.display = 'none'; });
    if (roleBlocks[v]) roleBlocks[v].style.display = 'block';
  }
  // bindRoleSelect: attaches change handler and sets initial visibility
  function bindRoleSelect(id){
    const sel = document.getElementById(id);
    if (!sel) return;
    if (!sel.__boundRoleFields){
      sel.__boundRoleFields = true;
      sel.addEventListener('change', function(){ updateRoleFieldsForSelect(sel); });
    }
    updateRoleFieldsForSelect(sel);
  }

  // Helpers: formatting utilities
  // fmt: formats empty values to '-'
  function fmt(t){ return t==null || t==='' ? '-' : t; }

  // Renderer: build a table row's HTML for a shift
  // rowHtml: renders one shift row HTML string
  function rowHtml(r){
    const idNum = Number(r.id) || 0;
    return (
      '<tr>'+
        '<td style="padding:0.75rem 1rem;">'+fmt(r.doctor_name)+'</td>'+
        '<td style="padding:0.75rem 1rem;">'+fmt(r.date)+'</td>'+
        '<td style="padding:0.75rem 1rem;">'+fmt(r.start)+'</td>'+
        '<td style="padding:0.75rem 1rem;">'+fmt(r.end)+'</td>'+
        '<td style="padding:0.75rem 1rem;">'+fmt(r.department)+'</td>'+
        '<td style="padding:0.5rem 1rem;">'+
          '<button class="btn btn-primary btn-small" onclick="alert(\'Edit not implemented yet\')"><i class="fas fa-pen"></i> Edit</button> '+
          '<button class="btn btn-danger btn-small" onclick="deleteDoctorShift('+idNum+')"><i class="fas fa-trash"></i> Delete</button>'+
        '</td>'+
      '</tr>'
    );
  }

  // Render controller: inject rows into the table body
  // render: replaces shifts table body with provided list
  function render(list){
    if(!Array.isArray(list) || list.length===0){
      body.innerHTML = '<tr><td colspan="6" style="text-align:center; color:#6b7280; padding:1rem;">No doctor shifts found.</td></tr>';
      return;
    }
    body.innerHTML = list.map(rowHtml).join('');
  }

  // Data loading (simple): fetch shifts from API and render
  // load: loads shifts using legacy apiUrl (fallback)
  function load(){
    if (!apiUrl) { render([]); return; }
    fetch(apiUrl, { headers:{ 'Accept':'application/json' }})
      .then(function(r){ return r.json(); })
      .then(function(res){
        if(res && res.status==='success'){
          render(res.data||[]);
        } else {
          render([]);
        }
      })
      .catch(function(){ render([]); });
  }

  // Actions: delete shift (uses CSRF)
  // deleteDoctorShift: deletes a shift by id with CSRF support
  window.deleteDoctorShift = function(id){
    if(!id || !confirm('Delete this shift?')) return;
    var p = new URLSearchParams();
    p.append('id', id);
    if (csrfToken && csrfHash) { p.append(csrfToken, csrfHash); }
    fetch(deleteUrl, { method:'POST', headers:{ 'Accept':'application/json' }, body:p })
      .then(function(r){ return r.json().catch(function(){ return {status:'error'}; }); })
      .then(function(res){
        if(res && res.status==='success'){
          load();
        } else {
          alert('Failed to delete shift');
        }
      })
      .catch(function(){ alert('Failed to delete shift'); });
  };

  // ---------------------------------------------------------
  // Advanced doctor shifts handling (externalized from view)
  // ---------------------------------------------------------
  // loadDoctors: populates doctor select with options from API
  async function loadDoctors(){
    const select = document.getElementById('doctor_id');
    if (!select || !doctorsUrl) return;
    try {
      select.innerHTML = '<option value>Loading doctors...</option>';
      const res = await fetch(doctorsUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }});
      const ctype = res.headers.get('content-type') || '';
      if (!res.ok || !ctype.includes('application/json')){
        const msg = !res.ok ? ('Error '+res.status+'. Please reload the page.') : 'Session expired. Please reload the page.';
        select.innerHTML = '<option value>Failed to load doctors. '+msg+'</option>';
        return;
      }
      const json = await res.json();
      const list = Array.isArray(json && json.data) ? json.data : (Array.isArray(json) ? json : []);
      if (!Array.isArray(list) || list.length === 0){
        select.innerHTML = '<option value>None available</option>';
        return;
      }
      select.innerHTML = '<option value="" selected>Select a doctor</option>' + list.map(function(d){
        const dept = (d.department||'').replace(/"/g,'&quot;');
        const name = (d.name || 'Doctor');
        const spec = d.specialization ? ('('+d.specialization+')') : '';
        return '<option value="'+d.doctor_id+'" data-department="'+dept+'">'+name+' '+spec+'</option>';
      }).join('');
    } catch(e){
      console.error('loadDoctors() failed', e);
      select.innerHTML = '<option value>Failed to load doctors. Please reload the page.</option>';
    }
  }

  // getAssignDepartment: returns the department select from Assign Shift form
  function getAssignDepartment(){
    const form = document.getElementById('assignShiftForm');
    return form ? form.querySelector('select[name="department"]') : null;
  }
  // syncDepartmentFromDoctor: sets department based on selected doctor's department
  function syncDepartmentFromDoctor(){
    try{
      const docSel = document.getElementById('doctor_id');
      const depSel = getAssignDepartment();
      if (!docSel || !depSel) return;
      const opt = docSel.options[docSel.selectedIndex];
      if (!opt) return;
      const dept = (opt.getAttribute('data-department') || '').trim();
      if (!dept) return;
      let match = Array.from(depSel.options).some(function(o){ return (o.value||'') === dept; });
      if (!match){
        const o = document.createElement('option');
        o.value = dept; o.textContent = dept; depSel.appendChild(o);
      }
      depSel.value = dept;
    } catch(_){}
  }

  // Advanced load of shifts list with delegated actions
  // loadDoctorShifts: fetches and renders advanced shifts list
  async function loadDoctorShifts(){
    if (!body || !shiftsUrl) return;
    try{
      body.innerHTML = '<tr><td colspan="6" style="text-align:center; color:#6b7280; padding:1rem;">Loading doctor shifts...</td></tr>';
      const res = await fetch(shiftsUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }});
      if (!res.ok){
        console.error('doctor-shifts/api HTTP', res.status);
        throw new Error('HTTP '+res.status);
      }
      const json = await res.json();
      const rows = Array.isArray(json && json.data) ? json.data : (Array.isArray(json) ? json : []);
      if (!Array.isArray(rows) || rows.length === 0){
        body.innerHTML = '<tr><td colspan="6" style="text-align:center; color:#6b7280; padding:1rem;">No shifts found</td></tr>';
        return;
      }
      body.innerHTML = rows.map(function(r){
        return (
          '<tr>'+
            '<td style="padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">'+(r.doctor_name||'')+'</td>'+
            '<td style="padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">'+(r.date||'')+'</td>'+
            '<td style="padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">'+(r.start||'')+'</td>'+
            '<td style="padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">'+(r.end||'')+'</td>'+
            '<td style="padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">'+(r.department||'')+'</td>'+
            '<td style="padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb;">'+
              '<button class="btn btn-primary btn-small btn-edit-shift" data-id="'+(r.id||'')+'"><i class="fas fa-pen"></i> Edit</button> '+
              '<button class="btn btn-danger btn-small btn-delete-shift" data-id="'+(r.id||'')+'"><i class="fas fa-trash"></i> Delete</button>'+
            '</td>'+
          '</tr>'
        );
      }).join('');
    } catch(e){
      console.error('Failed to load doctor shifts', e);
      body.innerHTML = '<tr><td colspan="6" style="text-align:center; color:#ef4444; padding:1rem;">Failed to load shifts</td></tr>';
    }
  }

  // openDoctorShiftAdminModal: loads one shift and opens admin modal
  async function openDoctorShiftAdminModal(id){
    if (!shiftShowBase) return;
    try{
      const res = await fetch(shiftShowBase + '/' + id, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }});
      if (!res.ok){
        console.error('Load shift failed', res.status);
        alert('Failed to load shift (HTTP ' + res.status + ').');
        return;
      }
      const json = await res.json();
      const d = (json && json.data) || {};
      const form = document.getElementById('doctorShiftAdminForm');
      if (!form) return;
      form.querySelector('#doctor_shift_id').value = d.id || '';
      form.querySelector('#adm_shift_date').value = d.date || '';
      form.querySelector('#adm_shift_start').value = d.start || '';
      form.querySelector('#adm_shift_end').value = d.end || '';
      form.querySelector('#adm_department').value = d.department || '';
      showOverlay('doctorShiftAdminModal');
    } catch(err){
      console.error('Exception loading shift', err);
      alert('Failed to load shift');
    }
  }

  // bindShiftActions: delegates edit/delete actions in shifts table
  function bindShiftActions(){
    if (!body || body.__boundShiftActions) return;
    body.__boundShiftActions = true;
    body.addEventListener('click', async function(e){
      const btn = e.target.closest('button');
      if (!btn) return;
      const id = btn.getAttribute('data-id');
      if (!id) return;
      if (btn.classList.contains('btn-edit-shift')){
        await openDoctorShiftAdminModal(id);
      } else if (btn.classList.contains('btn-delete-shift')){
        if (!confirm('Delete this shift?')) return;
        const fd = new FormData();
        fd.append('id', id);
        if (csrfToken && csrfHash) fd.append(csrfToken, csrfHash);
        try{
          const res = await fetch(deleteUrl, { method:'POST', headers: { 'Accept':'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: fd });
          const json = await res.json().catch(function(){ return {}; });
          if (json && json.csrf && json.csrf.name && json.csrf.value){
            // optional: update any CSRF inputs if present
          }
          if (json && json.status === 'success'){
            await loadDoctorShifts();
          } else {
            alert((json && json.message) || 'Failed to delete shift');
          }
        } catch(err){
          alert('Failed to delete shift');
        }
      }
    });
  }

  // bindAdminForm: wires submit for doctor shift admin form
  function bindAdminForm(){
    const form = document.getElementById('doctorShiftAdminForm');
    if (!form || form.__boundSubmit) return;
    form.__boundSubmit = true;
    form.addEventListener('submit', async function(e){
      e.preventDefault();
      if (!shiftUpdateUrl){ return; }
      const fd = new FormData(form);
      try{
        const res = await fetch(shiftUpdateUrl, { method:'POST', headers: { 'Accept':'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: fd });
        if (!res.ok){
          console.error('Update shift failed', res.status);
          alert('Failed to update shift (HTTP ' + res.status + ').');
          return;
        }
        const json = await res.json();
        if (json && json.status === 'success'){
          hideOverlay('doctorShiftAdminModal');
          await loadDoctorShifts();
        } else {
          alert((json && json.message) || 'Failed to update shift');
        }
      } catch(err){
        console.error('Exception updating shift', err);
        alert('Failed to update shift');
      }
    });
  }

  // Init: bind role fields, doctor change sync, actions, and load lists
  // DOMContentLoaded: entry point to initialize bindings and load data
  document.addEventListener('DOMContentLoaded', function(){
    bindRoleSelect('designation');
    bindRoleSelect('e_designation');
    const docSel = document.getElementById('doctor_id');
    if (docSel && !docSel.__boundSyncDept){
      docSel.__boundSyncDept = true;
      docSel.addEventListener('change', syncDepartmentFromDoctor);
    }
    bindShiftActions();
    bindAdminForm();
    bindAssignShiftForm();
    bindAddStaffForm();
    bindEditStaffForm();
    if (staffApiUrl) loadStaffTable();
    if (doctorsUrl) loadDoctors();
    if (shiftsUrl) loadDoctorShifts(); else if (apiUrl) load();
  });
})();
