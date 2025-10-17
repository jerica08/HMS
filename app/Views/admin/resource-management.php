<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Resource Management - HMS Admin</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
      /* Modal and form styles (aligned with user-management.php) */
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
    </style>
</head>
<body class="admin">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <?php include APPPATH . 'Views/admin/components/sidebar.php'; ?>

        <main class="content">
            <h1 class="page-title">Resource Management</h1>
            <div style="margin:0.75rem 0 1rem; display:flex; justify-content:flex-start;">
                <button id="addResourceBtn" class="btn btn-primary"><i class="fas fa-plus"></i> Add Resource</button>
            </div>

            <div class="dashboard-overview">
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue">
                            <i class="fas fa-bed"></i>
                        </div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Bed Occopuation </h3>
                            <p class="card-subtitle">Current  bed utilization</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value blue">0%</div>
                        </div>
                    </div>
                </div>

                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern purple">
                            <i class="fas fa-tools"></i>
                        </div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Equipment Status</h3>
                            <p class="card-subtitle">Operational equipment</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value purple">0%</div>
                        </div>
                    </div>
                </div>

                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern purple">
                            <i class="fas fa-user-times"></i>
                        </div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Inventroy Alerts</h3>
                            <p class="card-subtitle">Low stock Items</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value purple">0</div>
                        </div>
                    </div>
                </div>

                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern purple">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Departments</h3>
                            <p class="card-subtitle">Active administrators</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value purple">0</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Basic Resource Management -->
            <div class="hms-card" style="margin-top:1.5rem;">
                <div class="hms-card-header" style="display:flex; justify-content:space-between; align-items:center;">
                    <h2 style="margin:0; font-size:1.1rem;">Resources</h2>
                </div>
                <div class="hms-card-body" style="margin-top:0.75rem;">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Qty</th>
                                    <th>Status</th>
                                    <th>Location</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($resources) && is_array($resources)): ?>
                                    <?php foreach ($resources as $r): ?>
                                        <tr>
                                            <td><?= esc($r['name'] ?? '-') ?></td>
                                            <td><?= esc($r['category'] ?? '-') ?></td>
                                            <td><?= esc($r['quantity'] ?? '-') ?></td>
                                            <td style="text-transform:capitalize;"><?= esc($r['status'] ?? '-') ?></td>
                                            <td><?= esc($r['location'] ?? '-') ?></td>
                                            <td>
                                                <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                                                    <button class="btn btn-secondary btn-small" onclick="editResource(<?= esc($r['id'] ?? 0) ?>)"><i class="fas fa-edit"></i> Edit</button>
                                                    <button class="btn btn-danger btn-small" onclick="deleteResource(<?= esc($r['id'] ?? 0) ?>)"><i class="fas fa-trash"></i> Delete</button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align:center; padding:1.5rem; color:#6b7280;">No resources found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Add Resource Modal (styled like Add User modal) -->
            <div id="addResourceModal" class="hms-modal-overlay" aria-hidden="true">
                <div class="hms-modal" role="dialog" aria-modal="true" aria-labelledby="addResourceTitle">
                    <div class="hms-modal-header">
                        <div class="hms-modal-title" id="addResourceTitle">
                            <i class="fas fa-plus-circle" style="color:#4f46e5"></i>
                            Add Resource
                        </div>
                        <button type="button" class="btn btn-secondary btn-small" onclick="closeAddResourceModal()" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form id="addResourceForm">
                        <div class="hms-modal-body">
                            <div class="form-grid">
                                <div>
                                    <label class="form-label" for="res_name">Equipment Name</label>
                                    <input id="res_name" name="name" type="text" class="form-input" required>
                                </div>
                                <div>
                                    <label class="form-label" for="res_category">Category</label>
                                    <select id="res_category" name="category" class="form-select" required>
                                        <option value="">Select category</option>
                                        <option value="Diagnostic">Diagnostic</option>
                                        <option value="Surgical">Surgical</option>
                                        <option value="Furniture">Furniture</option>
                                        <option value="IT">IT</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label" for="res_quantity">Quantity</label>
                                    <input id="res_quantity" name="quantity" type="number" class="form-input" min="0" required>
                                </div>
                                <div>
                                    <label class="form-label" for="res_status">Status</label>
                                    <select id="res_status" name="status" class="form-select" required>
                                        <option value="available">Available</option>
                                        <option value="in_use">In Use</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="retired">Retired</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label" for="res_location">Location</label>
                                    <input id="res_location" name="location" type="text" class="form-input">
                                </div>
                                <div>
                                    <label class="form-label" for="res_date_acquired">Date Acquired</label>
                                    <input id="res_date_acquired" name="date_acquired" type="date" class="form-input">
                                </div>
                                <div>
                                    <label class="form-label" for="res_supplier">Supplier</label>
                                    <input id="res_supplier" name="supplier" type="text" class="form-input">
                                </div>
                                <div>
                                    <label class="form-label" for="res_maintenance">Maintenance Schedule</label>
                                    <input id="res_maintenance" name="maintenance_schedule" type="date" class="form-input">
                                </div>
                                <div class="full">
                                    <label class="form-label" for="res_notes">Remarks</label>
                                    <textarea id="res_notes" name="notes" rows="3" class="form-textarea"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="hms-modal-actions">
                            <button type="button" class="btn btn-secondary" onclick="closeAddResourceModal()">Cancel</button>
                            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Edit Resource Modal (styled like Add User modal) -->
            <div id="editResourceModal" class="hms-modal-overlay" aria-hidden="true">
                <div class="hms-modal" role="dialog" aria-modal="true" aria-labelledby="editResourceTitle">
                    <div class="hms-modal-header">
                        <div class="hms-modal-title" id="editResourceTitle">
                            <i class="fas fa-edit" style="color:#4f46e5"></i>
                            Edit Resource
                        </div>
                        <button type="button" class="btn btn-secondary btn-small" onclick="closeEditResourceModal()" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form id="editResourceForm">
                        <input type="hidden" id="er_id" name="id">
                        <div class="hms-modal-body">
                            <div class="form-grid">
                                <div>
                                    <label class="form-label" for="er_name">Name</label>
                                    <input id="er_name" name="name" type="text" class="form-input" required>
                                </div>
                                <div>
                                    <label class="form-label" for="er_category">Category</label>
                                    <input id="er_category" name="category" type="text" class="form-input" required>
                                </div>
                                <div>
                                    <label class="form-label" for="er_quantity">Quantity</label>
                                    <input id="er_quantity" name="quantity" type="number" class="form-input" min="0" required>
                                </div>
                                <div>
                                    <label class="form-label" for="er_status">Status</label>
                                    <select id="er_status" name="status" class="form-select" required>
                                        <option value="available">Available</option>
                                        <option value="in_use">In Use</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="retired">Retired</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label" for="er_location">Location</label>
                                    <input id="er_location" name="location" type="text" class="form-input">
                                </div>
                                <div class="full">
                                    <label class="form-label" for="er_notes">Notes</label>
                                    <textarea id="er_notes" name="notes" rows="3" class="form-textarea"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="hms-modal-actions">
                            <button type="button" class="btn btn-secondary" onclick="closeEditResourceModal()">Cancel</button>
                            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                // preload data to map if provided by controller
                (function(){
                    try {
                        var list = <?php echo json_encode($resources ?? []); ?>;
                        var byId = {};
                        if (Array.isArray(list)) { for (var i=0;i<list.length;i++){ var r=list[i]; if(r && (r.id||r.resource_id)){ byId[r.id||r.resource_id]=r; } } }
                        window.resourcesById = byId;
                    } catch(e){ window.resourcesById = {}; }
                })();

                // Modal controls (aligned with user-management.php)
                function openAddResourceModal(){ var m=document.getElementById('addResourceModal'); if(m) m.classList.add('active'); }
                function closeAddResourceModal(){ var m=document.getElementById('addResourceModal'); if(m) m.classList.remove('active'); }
                function openEditResourceModal(){ var m=document.getElementById('editResourceModal'); if(m) m.classList.add('active'); }
                function closeEditResourceModal(){ var m=document.getElementById('editResourceModal'); if(m) m.classList.remove('active'); }

                document.getElementById('addResourceBtn')?.addEventListener('click', openAddResourceModal);
                document.addEventListener('click', function(e){ var m=document.getElementById('addResourceModal'); if(m && e.target===m) closeAddResourceModal(); });
                document.addEventListener('click', function(e){ var m=document.getElementById('editResourceModal'); if(m && e.target===m) closeEditResourceModal(); });
                document.addEventListener('keydown', function(e){ if(e.key==='Escape'){ closeAddResourceModal(); closeEditResourceModal(); }});

                // Helpers
                function toParams(form){
                    var fd=new FormData(form); var p=new URLSearchParams();
                    fd.forEach((v,k)=>{ if(v!==undefined && v!==null) p.append(k, v); });
                    try { p.append('<?= csrf_token() ?>','<?= csrf_hash() ?>'); } catch(e) {}
                    return p;
                }
                function postForm(url, form){
                    return fetch(url, { method:'POST', headers:{ 'Accept':'application/json' }, body: toParams(form) })
                        .then(r=>r.json().catch(()=>({status:'error'})));
                }

                // Add Resource submit -> POST admin/resources/create
                (function(){
                    var form=document.getElementById('addResourceForm');
                    if(!form) return;
                    form.addEventListener('submit', function(e){
                        e.preventDefault();
                        postForm('<?= base_url('admin/resources/create') ?>', form)
                            .then(function(res){
                                if(res && res.status==='success'){ window.location.reload(); return; }
                                alert('Failed to save resource');
                            })
                            .catch(function(){ alert('Failed to save resource'); });
                    });
                })();

                // Edit button and submit (UI stub)
                function editResource(id){
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
                }

                function deleteResource(id){
                    if(!confirm('Delete this resource?')) return;
                    var p=new URLSearchParams(); p.append('id', id);
                    try { p.append('<?= csrf_token() ?>','<?= csrf_hash() ?>'); } catch(e) {}
                    fetch('<?= base_url('admin/resources/delete') ?>', { method:'POST', headers:{ 'Accept':'application/json' }, body:p })
                        .then(r=>r.json().catch(()=>({status:'error'})))
                        .then(function(res){ if(res && res.status==='success'){ window.location.reload(); } else { alert('Failed to delete'); } })
                        .catch(function(){ alert('Failed to delete'); });
                }

                // Edit Resource -> POST admin/resources/update
                (function(){
                    var form=document.getElementById('editResourceForm');
                    if(!form) return;
                    form.addEventListener('submit', function(e){
                        e.preventDefault();
                        postForm('<?= base_url('admin/resources/update') ?>', form)
                            .then(function(res){
                                if(res && res.status==='success'){ window.location.reload(); return; }
                                alert('Failed to update resource');
                            })
                            .catch(function(){ alert('Failed to update resource'); });
                    });
                })();
            </script>
        </main>
    </div>

    <script src="<?= base_url('js/logout.js') ?>"></script>
</body>
</html>
