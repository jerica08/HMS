<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Resource Management - HMS Admin</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
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

            <!-- Add Resource Modal -->
            <div id="addResourceModal" class="modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:9999; align-items:center; justify-content:center;">
                <div style="background:#fff; padding:1.5rem; border-radius:8px; max-width:720px; width:96%; margin:auto; position:relative; max-height:92vh; overflow:auto; box-sizing:border-box;">
                    <div class="hms-modal-header">
                        <div class="hms-modal-title">
                            <i class="fas fa-plus-circle" style="color:#4f46e5"></i>
                            <h2 style="margin:0; font-size:1.1rem;">Add Resource</h2>
                        </div>
                    </div>
                    <form id="addResourceForm">
                        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:1rem;">
                            <div>
                                <label for="res_name">Name</label>
                                <input id="res_name" name="name" type="text" class="form-input" required>
                            </div>
                            <div>
                                <label for="res_category">Category</label>
                                <input id="res_category" name="category" type="text" class="form-input" required>
                            </div>
                            <div>
                                <label for="res_quantity">Quantity</label>
                                <input id="res_quantity" name="quantity" type="number" class="form-input" min="0" required>
                            </div>
                            <div>
                                <label for="res_status">Status</label>
                                <select id="res_status" name="status" class="form-select" required>
                                    <option value="available">Available</option>
                                    <option value="in_use">In Use</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="retired">Retired</option>
                                </select>
                            </div>
                            <div>
                                <label for="res_location">Location</label>
                                <input id="res_location" name="location" type="text" class="form-input">
                            </div>
                            <div class="full">
                                <label for="res_notes">Notes</label>
                                <textarea id="res_notes" name="notes" rows="3" class="form-textarea"></textarea>
                            </div>
                        </div>
                        <div class="hms-modal-actions">
                            <button type="button" class="btn btn-secondary" onclick="closeAddResourceModal()">Cancel</button>
                            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save</button>
                        </div>
                    </form>
                    <button aria-label="Close" onclick="closeAddResourceModal()" style="position:absolute; top:10px; right:10px; background:transparent; border:none; font-size:1.25rem; color:#6b7280; cursor:pointer;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Edit Resource Modal -->
            <div id="editResourceModal" class="modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:9999; align-items:center; justify-content:center;">
                <div style="background:#fff; padding:1.5rem; border-radius:8px; max-width:720px; width:96%; margin:auto; position:relative; max-height:92vh; overflow:auto; box-sizing:border-box;">
                    <div class="hms-modal-header">
                        <div class="hms-modal-title">
                            <i class="fas fa-edit" style="color:#4f46e5"></i>
                            <h2 style="margin:0; font-size:1.1rem;">Edit Resource</h2>
                        </div>
                    </div>
                    <form id="editResourceForm">
                        <input type="hidden" id="er_id" name="id">
                        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:1rem;">
                            <div>
                                <label for="er_name">Name</label>
                                <input id="er_name" name="name" type="text" class="form-input" required>
                            </div>
                            <div>
                                <label for="er_category">Category</label>
                                <input id="er_category" name="category" type="text" class="form-input" required>
                            </div>
                            <div>
                                <label for="er_quantity">Quantity</label>
                                <input id="er_quantity" name="quantity" type="number" class="form-input" min="0" required>
                            </div>
                            <div>
                                <label for="er_status">Status</label>
                                <select id="er_status" name="status" class="form-select" required>
                                    <option value="available">Available</option>
                                    <option value="in_use">In Use</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="retired">Retired</option>
                                </select>
                            </div>
                            <div>
                                <label for="er_location">Location</label>
                                <input id="er_location" name="location" type="text" class="form-input">
                            </div>
                            <div class="full">
                                <label for="er_notes">Notes</label>
                                <textarea id="er_notes" name="notes" rows="3" class="form-textarea"></textarea>
                            </div>
                        </div>
                        <div class="hms-modal-actions">
                            <button type="button" class="btn btn-secondary" onclick="closeEditResourceModal()">Cancel</button>
                            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save</button>
                        </div>
                    </form>
                    <button aria-label="Close" onclick="closeEditResourceModal()" style="position:absolute; top:10px; right:10px; background:transparent; border:none; font-size:1.25rem; color:#6b7280; cursor:pointer;">
                        <i class="fas fa-times"></i>
                    </button>
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

                // Modal controls
                function openAddResourceModal(){ var m=document.getElementById('addResourceModal'); if(m) m.style.display='flex'; }
                function closeAddResourceModal(){ var m=document.getElementById('addResourceModal'); if(m) m.style.display='none'; }
                function openEditResourceModal(){ var m=document.getElementById('editResourceModal'); if(m) m.style.display='flex'; }
                function closeEditResourceModal(){ var m=document.getElementById('editResourceModal'); if(m) m.style.display='none'; }

                document.getElementById('addResourceBtn')?.addEventListener('click', openAddResourceModal);
                document.addEventListener('click', function(e){ var m=document.getElementById('addResourceModal'); if(m && e.target===m) closeAddResourceModal(); });
                document.addEventListener('click', function(e){ var m=document.getElementById('editResourceModal'); if(m && e.target===m) closeEditResourceModal(); });
                document.addEventListener('keydown', function(e){ if(e.key==='Escape'){ closeAddResourceModal(); closeEditResourceModal(); }});

                // Add Resource submit (UI stub)
                (function(){
                    var form=document.getElementById('addResourceForm');
                    if(!form) return;
                    form.addEventListener('submit', function(e){
                        e.preventDefault();
                        alert('Resource saved (UI only). Hook this to a backend endpoint to persist.');
                        closeAddResourceModal();
                        // Optionally refresh
                        // window.location.reload();
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
                    alert('Deleted (UI only). Hook to backend to persist.');
                }

                (function(){
                    var form=document.getElementById('editResourceForm');
                    if(!form) return;
                    form.addEventListener('submit', function(e){
                        e.preventDefault();
                        alert('Resource updated (UI only). Hook this to a backend endpoint to persist.');
                        closeEditResourceModal();
                        // window.location.reload();
                    });
                })();
            </script>
        </main>
    </div>

    <script src="<?= base_url('js/logout.js') ?>"></script>
</body>
</html>
