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
        <?php include APPPATH . 'Views/unified/components/sidebar.php'; ?>

        <main class="content">
            <h1 class="page-title">Resource Management</h1>
            <div class="toolbar-row">
                <button id="addResourceBtn" class="btn btn-primary"><i class="fas fa-plus"></i> Add Resource</button>
                <button id="addDepartmentBtn" class="btn btn-primary" style="margin-left:8px" onclick="openAddDepartmentModal()"><i class="fas fa-building"></i> Add Department</button>
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
            <div class="hms-card mt-6">
                <div class="hms-card-header">
                    <h2 class="hms-card-title">Resources</h2>
                </div>
                <div class="hms-card-body">
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
                                            <td class="text-capitalize"><?= esc($r['status'] ?? '-') ?></td>
                                            <td><?= esc($r['location'] ?? '-') ?></td>
                                            <td>
                                                <div class="inline-actions">
                                                    <button class="btn btn-secondary btn-small" onclick="editResource(<?= esc($r['id'] ?? 0) ?>)"><i class="fas fa-edit"></i> Edit</button>
                                                    <button class="btn btn-danger btn-small" onclick="deleteResource(<?= esc($r['id'] ?? 0) ?>)"><i class="fas fa-trash"></i> Delete</button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="empty-state-cell">No resources found.</td>
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
                            <i class="fas fa-plus-circle text-primary"></i>
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
                                    <input id="res_name" name="name" type="text" class="form-input" required autocomplete="off">
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
                                    <input id="res_quantity" name="quantity" type="number" class="form-input" min="0" required autocomplete="off">
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
                                    <input id="res_location" name="location" type="text" class="form-input" autocomplete="off">
                                </div>
                                <div>
                                    <label class="form-label" for="res_date_acquired">Date Acquired</label>
                                    <input id="res_date_acquired" name="date_acquired" type="date" class="form-input" autocomplete="off">
                                </div>
                                <div>
                                    <label class="form-label" for="res_supplier">Supplier</label>
                                    <input id="res_supplier" name="supplier" type="text" class="form-input" autocomplete="off">
                                </div>
                                <div>
                                    <label class="form-label" for="res_maintenance">Maintenance Schedule</label>
                                    <input id="res_maintenance" name="maintenance_schedule" type="date" class="form-input" autocomplete="off">
                                </div>
                                <div class="full">
                                    <label class="form-label" for="res_notes">Remarks</label>
                                    <textarea id="res_notes" name="notes" rows="3" class="form-textarea" autocomplete="off"></textarea>
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

            <!-- Add Department Modal -->
            <div id="addDepartmentModal" class="hms-modal-overlay" aria-hidden="true">
                <div class="hms-modal" role="dialog" aria-modal="true" aria-labelledby="addDepartmentTitle">
                    <div class="hms-modal-header">
                        <div class="hms-modal-title" id="addDepartmentTitle">
                            <i class="fas fa-building text-primary"></i>
                            Add Department
                        </div>
                        <button type="button" class="btn btn-secondary btn-small" onclick="closeAddDepartmentModal()" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>   
                    </div>
                    <form id="addDepartmentForm">
                        <div class="hms-modal-body">
                            <div class="form-grid">
                                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                                <div class="full">
                                    <label class="form-label" for="dept_name">Department Name*</label>
                                    <input id="dept_name" name="name" type="text" class="form-input" required autocomplete="off" placeholder="e.g., Radiology">
                                    <small id="err_dept_name" style="color:#dc2626"></small>
                                </div>
                                <div class="full">
                                    <label class="form-label" for="dept_description">Description</label>
                                    <textarea id="dept_description" name="description" rows="3" class="form-textarea" autocomplete="off" placeholder="Optional notes..."></textarea>
                                    <small id="err_dept_description" style="color:#dc2626"></small>
                                </div>
                            </div>
                        </div>
                        <div class="hms-modal-actions">
                            <button type="button" class="btn btn-secondary" onclick="closeAddDepartmentModal()">Cancel</button>
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
                            <i class="fas fa-edit text-primary"></i>
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
                                    <input id="er_name" name="name" type="text" class="form-input" required autocomplete="off">
                                </div>
                                <div>
                                    <label class="form-label" for="er_category">Category</label>
                                    <input id="er_category" name="category" type="text" class="form-input" required>
                                </div>
                                <div>
                                    <label class="form-label" for="er_quantity">Quantity</label>
                                    <input id="er_quantity" name="quantity" type="number" class="form-input" min="0" required autocomplete="off">
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
                                    <input id="er_location" name="location" type="text" class="form-input" autocomplete="off">
                                </div>
                                <div class="full">
                                    <label class="form-label" for="er_notes">Notes</label>
                                    <textarea id="er_notes" name="notes" rows="3" class="form-textarea" autocomplete="off"></textarea>
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
                window.HMS = {
                    baseUrl: '<?= rtrim(base_url(), '/') ?>',
                    csrf: { token: '<?= csrf_token() ?>', hash: '<?= csrf_hash() ?>' }
                };
                window.__RESOURCES__ = <?php echo json_encode($resources ?? []); ?>;
            </script>
            <script src="<?= base_url('js/admin/resource-management.js') ?>"></script>
            <script>
                // Department modal open/close
                const addDepartmentBtn = document.getElementById('addDepartmentBtn');
                const addDepartmentModal = document.getElementById('addDepartmentModal');
                const addDepartmentForm = document.getElementById('addDepartmentForm');

                function openAddDepartmentModal() {
                    if (addDepartmentModal) {
                        addDepartmentModal.setAttribute('aria-hidden', 'false');
                        addDepartmentModal.style.display = 'block';
                    }
                }
                function closeAddDepartmentModal() {
                    if (addDepartmentModal) {
                        addDepartmentModal.setAttribute('aria-hidden', 'true');
                        addDepartmentModal.style.display = 'none';
                    }
                    if (addDepartmentForm) addDepartmentForm.reset();
                    const errs = addDepartmentForm?.querySelectorAll('[id^="err_"]');
                    errs?.forEach(e => e.textContent = '');
                }
                if (addDepartmentBtn) {
                    addDepartmentBtn.addEventListener('click', openAddDepartmentModal);
                }

                // Submit department to API
                if (addDepartmentForm) {
                    addDepartmentForm.addEventListener('submit', async (e) => {
                        e.preventDefault();
                        const name = document.getElementById('dept_name')?.value?.trim();
                        const description = document.getElementById('dept_description')?.value?.trim();
                        document.getElementById('err_dept_name').textContent = '';
                        if (!name) {
                            document.getElementById('err_dept_name').textContent = 'Department name is required.';
                            return;
                        }
                        try {
                            const formData = new FormData(addDepartmentForm);
                            // Manually ensure trimmed values
                            formData.set('name', name || '');
                            formData.set('description', description || '');
                            const res = await fetch('<?= site_url('departments/create') ?>', {
                                method: 'POST',
                                body: formData
                            });
                            let data = null;
                            let raw = '';
                            try {
                                raw = await res.text();
                                data = raw ? JSON.parse(raw) : null;
                            } catch (e) { /* non-JSON response */ }
                            if (!res.ok || (data && data.status === 'error')) {
                                const detail = data?.db_error?.message || data?.message || raw || 'Failed to save department';
                                alert(detail);
                                return;
                            }
                            // Success
                            closeAddDepartmentModal();
                            alert('Department saved successfully');
                            // Optional: reload to reflect in any lists on page
                            // location.reload();
                        } catch (err) {
                            alert('Failed to save department');
                        }
                    });
                }
            </script>
        </main>
    </div>

    <script src="<?= base_url('js/logout.js') ?>"></script>
</body>
</html>
