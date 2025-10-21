(function(){
  // User Management – externalized scripts
  const CFG = (window.USER_MGMT_CFG || {});
  const getUserBase = CFG.getUserBase;         // base + id
  const resetUserBase = CFG.resetUserBase;     // base + id (GET)
  const deleteUserBase = CFG.deleteUserBase;   // base + id (GET)
  const hasErrors = !!CFG.hasErrors;           // open Add User on errors

  function qs(id){ return document.getElementById(id); }

  // Flash notice helpers
  function dismissFlash(){ var n = qs('flashNotice'); if(n){ n.style.display='none'; } }
  window.dismissFlash = dismissFlash;
  setTimeout(dismissFlash, 4000);

  // Populate add-user form fields from selected staff option
  function populateFromStaff(){
    var sel = qs('staff_id'); if (!sel) return;
    var opt = sel.options[sel.selectedIndex];
    var first = qs('first_name'); var last = qs('last_name'); var email = qs('email'); var roleSel = qs('role');
    if (!opt || !sel.value){ if(first)first.value=''; if(last)last.value=''; if(email)email.value=''; if(roleSel)roleSel.value=''; return; }
    if (first) first.value = opt.getAttribute('data-first-name') || '';
    if (last) last.value = opt.getAttribute('data-last-name') || '';
    if (email) email.value = opt.getAttribute('data-email') || '';
    if (roleSel){ var autoRole = (opt.getAttribute('data-role') || opt.getAttribute('data-designation') || '').toLowerCase(); if (autoRole) roleSel.value = autoRole; }
  }
  window.populateFromStaff = populateFromStaff;

  // Add User modal
  function openAddUserModal(){ var overlay = qs('addUserModal'); if (overlay) overlay.classList.add('active'); populateFromStaff(); }
  function closeAddUserModal(){ var overlay = qs('addUserModal'); if (overlay) overlay.classList.remove('active'); }
  window.openAddUserModal = openAddUserModal;
  window.closeAddUserModal = closeAddUserModal;

  // Edit User modal
  function closeEditUserModal(){ var overlay = qs('editUserModal'); if (overlay) overlay.classList.remove('active'); }
  window.closeEditUserModal = closeEditUserModal;

  // CRUD actions
  window.editUser = function(id){
    if (!id || !getUserBase) return;
    fetch(getUserBase + id)
      .then(function(r){ return r.json(); })
      .then(function(data){
        if (data && data.error){ alert('Error: ' + data.error); return; }
        qs('edit_user_id').value = data.user_id;
        qs('edit_username').value = data.username;
        qs('edit_role').value = data.role;
        qs('edit_status').value = data.status;
        var overlay = qs('editUserModal'); if (overlay) overlay.classList.add('active');
      })
      .catch(function(){ alert('Failed to load user data.'); });
  };

  window.resetPassword = function(id){
    if (!id || !resetUserBase) return;
    if (!confirm("Reset this user's password? A temporary password will be generated.")) return;
    var form = document.createElement('form'); form.method='GET'; form.action = resetUserBase + id; document.body.appendChild(form); form.submit();
  };

  window.deleteUser = function(id){
    if (!id || !deleteUserBase) return;
    if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) return;
    var form = document.createElement('form'); form.method='GET'; form.action = deleteUserBase + id; document.body.appendChild(form); form.submit();
  };

  // Global overlay click-to-close for add/edit modals
  window.addEventListener('click', function(event){
    var addOv = qs('addUserModal'); if (addOv && event.target === addOv) addOv.classList.remove('active');
    var editOv = qs('editUserModal'); if (editOv && event.target === editOv) editOv.classList.remove('active');
  });

  // Bind staff change populate
  document.addEventListener('DOMContentLoaded', function(){
    var sel = qs('staff_id'); if (sel && !sel.__bound){ sel.__bound = true; sel.addEventListener('change', populateFromStaff); }
    if (hasErrors){ try{ openAddUserModal(); populateFromStaff(); } catch(_){} }
  });
})();
