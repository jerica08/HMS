(function(){
  'use strict';

  var baseUrl = '';
  try {
    var meta = document.querySelector('meta[name="base-url"]');
    baseUrl = (meta && meta.getAttribute('content')) ? meta.getAttribute('content') : '';
    if (baseUrl && !baseUrl.endsWith('/')) baseUrl += '/';
  } catch (e) { baseUrl = ''; }

  function buildPatientsMap() {
    try {
      var dataTag = document.getElementById('patients-json');
      if (!dataTag) return {};
      var list = JSON.parse(dataTag.textContent || '[]');
      var map = {};
      if (Array.isArray(list)) {
        for (var i = 0; i < list.length; i++) {
          var p = list[i];
          if (p && p.patient_id != null) {
            map[p.patient_id] = p;
          }
        }
      }
      return map;
    } catch (e) {
      return {};
    }
  }

  window.patientsById = buildPatientsMap();

  function openModal(id){ var m=document.getElementById(id); if(m){ m.style.display='flex'; } }
  function closeModal(id){ var m=document.getElementById(id); if(m){ m.style.display='none'; } }

  window.openViewPatientModal = function(){ openModal('viewPatientModal'); };
  window.closeViewPatientModal = function(){ closeModal('viewPatientModal'); };

  window.viewPatient = function(id){
    var p = (window.patientsById||{})[id];
    if(!p){ alert('Patient not found'); return; }
    var age='';
    if(p.date_of_birth){
      try {
        var d=new Date(p.date_of_birth);
        var t=new Date();
        var a=t.getFullYear()-d.getFullYear();
        var m=t.getMonth()-d.getMonth();
        if(m<0 || (m===0 && t.getDate()<d.getDate())) a--;
        age = a>=0? a : '';
      } catch(e){}
    }
    var setVal = function(i,v){ var el=document.getElementById(i); if(!el) return; if(el.tagName==='SELECT' || el.tagName==='INPUT' || el.tagName==='TEXTAREA'){ el.value = v ?? ''; } else { el.textContent = (v==null||v==='')? '-' : v; } };
    setVal('vp_id', p.patient_id || '');
    setVal('vp_first_name', p.first_name || '');
    setVal('vp_last_name', p.last_name || '');
    setVal('vp_gender', (p.gender||'').toLowerCase());
    setVal('vp_dob', p.date_of_birth || '');
    setVal('vp_age', age || '');
    setVal('vp_phone', p.contact_no || p.phone || '');
    setVal('vp_email', p.email || '');
    setVal('vp_address', p.address || '');
    setVal('vp_type', (p.patient_type||'').toLowerCase());
    setVal('vp_status', (p.status||'').toLowerCase());
    setVal('vp_emergency_name', p.emergency_contact || '');
    setVal('vp_emergency_phone', p.emergency_phone || '');
    setVal('vp_notes', p.medical_notes || '');
    var docLabel = '';
    if (p.primary_doctor_name) { docLabel = p.primary_doctor_name; }
    else if (p.doctor_name) { docLabel = p.doctor_name; }
    else if (p.primary_doctor_id) { docLabel = 'Doctor #' + p.primary_doctor_id; }
    setVal('vp_doctor', docLabel);
    openModal('viewPatientModal');
  };

  window.openEditPatientModal = function(){ openModal('editPatientModal'); };
  window.closeEditPatientModal = function(){ closeModal('editPatientModal'); };

  window.editPatient = function(id){
    var p = (window.patientsById||{})[id];
    if(!p){ alert('Patient not found'); return; }
    var set = function(i,val){ var el=document.getElementById(i); if(el){ if(el.tagName==='INPUT' || el.tagName==='TEXTAREA' || el.tagName==='SELECT'){ el.value = val ?? ''; } else { el.textContent = val ?? ''; } } };
    set('ep_patient_id', p.patient_id || '');
    set('ep_first_name', p.first_name || '');
    set('ep_middle_name', p.middle_name || '');
    set('ep_last_name', p.last_name || '');
    set('ep_date_of_birth', p.date_of_birth || '');
    set('ep_gender', (p.gender||'').toLowerCase());
    set('ep_civil_status', (p.civil_status||''));
    set('ep_phone', p.contact_no || p.phone || '');
    set('ep_email', p.email || '');
    set('ep_address', p.address || '');
    set('ep_province', p.province || '');
    set('ep_city', p.city || '');
    set('ep_barangay', p.barangay || '');
    set('ep_zip_code', p.zip_code || '');
    set('ep_insurance_provider', p.insurance_provider || '');
    set('ep_insurance_number', p.insurance_number || '');
    set('ep_patient_type', p.patient_type || '');
    set('ep_status', (p.status||'').toLowerCase());
    set('ep_emergency_contact_name', p.emergency_contact || '');
    set('ep_emergency_contact_phone', p.emergency_phone || '');
    set('ep_medical_notes', p.medical_notes || '');
    loadDoctors('ep_primary_doctor_id', p.primary_doctor_id || '');
    openModal('editPatientModal');
  };

  window.openAddPatientsModal = function(){
    var m = document.getElementById('patientModal');
    if (m) { m.style.display = 'flex'; }
    loadDoctors('primary_doctor_id');
  };
  window.closeAddPatientsModal = function(){
    var m = document.getElementById('patientModal');
    if (m) { m.style.display = 'none'; }
  };
  window.addPatient = function(){ window.openAddPatientsModal(); };

  document.addEventListener('click', function(e){
    var m = document.getElementById('patientModal');
    if (!m) return;
    if (e.target === m) window.closeAddPatientsModal();
  });
  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape') window.closeAddPatientsModal();
  });

  (function(){
    var addBtn = document.getElementById('addPatientBtn');
    if (addBtn) addBtn.addEventListener('click', window.addPatient);
  })();

  (function(){
    var dob = document.getElementById('date_of_birth');
    var age = document.getElementById('age');
    function calcAge(value){
      if (!value) { age && (age.value = ''); return; }
      var d = new Date(value);
      if (isNaN(d.getTime())) { age && (age.value = ''); return; }
      var today = new Date();
      var a = today.getFullYear() - d.getFullYear();
      var m = today.getMonth() - d.getMonth();
      if (m < 0 || (m === 0 && today.getDate() < d.getDate())) a--;
      if (age) age.value = a >= 0 ? a : '';
    }
    if (dob) {
      dob.addEventListener('change', function(){ calcAge(this.value); });
    }
  })();

  window.loadDoctors = async function(selectId, selectedValue){
    var sel = document.getElementById(selectId);
    if (!sel) return;
    sel.innerHTML = '<option value="">Loading doctors...</option>';
    try {
      var res = await fetch(baseUrl + 'admin/doctors/api', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
      var result = await res.json().catch(function(){ return {}; });
      sel.innerHTML = '';
      if (res.ok && result.status === 'success' && Array.isArray(result.data) && result.data.length){
        var opt = document.createElement('option');
        opt.value = '';
        opt.textContent = '— Select doctor —';
        sel.appendChild(opt);
        result.data.forEach(function(d){
          var o = document.createElement('option');
          o.value = d.doctor_id;
          var label = (d.name || ('Doctor #' + d.doctor_id));
          if (d.department) { label += ' — ' + d.department; }
          if (d.specialization) { label += ' (' + d.specialization + ')'; }
          o.textContent = label;
          if (selectedValue && String(selectedValue) === String(d.doctor_id)) o.selected = true;
          sel.appendChild(o);
        });
      } else {
        var none = document.createElement('option');
        none.value = '';
        none.textContent = 'No doctors found';
        sel.appendChild(none);
      }
    } catch (e){
      sel.innerHTML = '<option value="">Failed to load</option>';
    }
  };

  (async function(){
    var form = document.getElementById('patientForm');
    if (!form) return;
    form.addEventListener('submit', async function(e){
      e.preventDefault();
      var btn = document.getElementById('savePatientBtn');
      if (btn) { btn.disabled = true; btn.textContent = 'Saving...'; }
      var getVal = function(id){ var el = document.getElementById(id); return el ? el.value : null; };
      var payload = {
        first_name: getVal('first_name'),
        middle_name: getVal('middle_name'),
        last_name: getVal('last_name'),
        date_of_birth: getVal('date_of_birth'),
        age: getVal('age'),
        gender: getVal('gender'),
        civil_status: getVal('civil_status'),
        phone: getVal('phone'),
        email: getVal('email'),
        address: getVal('address'),
        province: getVal('province'),
        city: getVal('city'),
        barangay: getVal('barangay'),
        zip_code: getVal('zip_code'),
        insurance_provider: getVal('insurance_provider'),
        insurance_number: getVal('insurance_number'),
        emergency_contact_name: getVal('emergency_contact_name'),
        emergency_contact_phone: getVal('emergency_contact_phone'),
        primary_doctor_id: getVal('primary_doctor_id'),
        patient_type: getVal('patient_type'),
        status: getVal('status'),
        medical_notes: getVal('medical_notes')
      };
      try {
        var res = await fetch(baseUrl + 'admin/patients', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify(payload),
          credentials: 'same-origin'
        });
        var result = await res.json().catch(function(){ return {}; });
        if (res.ok && result.status === 'success'){
          alert('Patient saved successfully');
          window.closeAddPatientsModal();
          window.location.reload();
        } else {
          var msg = result.message || 'Failed to save patient';
          if (result.errors){
            var details = Object.values(result.errors).join('\n');
            msg += '\n\n' + details;
          }
          alert(msg);
        }
      } catch (err){
        console.error('Error saving patient', err);
        alert('Network error. Please try again.');
      } finally {
        if (btn) { btn.disabled = false; btn.textContent = 'Save Patient'; }
      }
    });
  })();
})();
