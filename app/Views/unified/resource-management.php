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
                                            <td><?= esc($r['equipment_name'] ?? '-') ?></td>
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
                            <i class="fas fa-plus-circle" style="color:#4f46e5"></i>
                            Add New Resource
                        </div>
                        <button type="button" class="btn btn-secondary btn-small" onclick="closeAddResourceModal()" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form id="addResourceForm">
                        <div class="hms-modal-body">
                            <div class="form-grid">
                                <div>
                                    <label class="form-label" for="res_name">Resource Name*</label>
                                    <input id="res_name" name="name" type="text" class="form-input" required autocomplete="off" placeholder="Enter resource name">
                                    <small id="err_res_name" style="color:#dc2626"></small>
                                </div>
                                <div>
                                    <label class="form-label" for="res_category">Category*</label>
                                    <select id="res_category" name="category" class="form-select" required>
                                        <option value="">Select category</option>
                                        <option value="Equipment">Equipment</option>
                                        <option value="Facility">Facility</option>
                                        <option value="Personnel">Personnel</option>
                                    </select>
                                    <small id="err_res_category" style="color:#dc2626"></small>
                                </div>
                                <div>
                                    <label class="form-label" for="res_quantity">Quantity*</label>
                                    <input id="res_quantity" name="quantity" type="number" class="form-input" min="1" required autocomplete="off" placeholder="Enter quantity">
                                    <small id="err_res_quantity" style="color:#dc2626"></small>
                                </div>
                                <div>
                                    <label class="form-label" for="res_status">Status*</label>
                                    <select id="res_status" name="status" class="form-select" required>
                                        <option value="Available">Available</option>
                                        <option value="In Use">In Use</option>
                                        <option value="Maintenance">Maintenance</option>
                                        <option value="Out of Order">Out of Order</option>
                                    </select>
                                    <small id="err_res_status" style="color:#dc2626"></small>
                                </div>
                                <div>
                                    <label class="form-label" for="res_location">Location*</label>
                                    <input id="res_location" name="location" type="text" class="form-input" required autocomplete="off" placeholder="Enter location">
                                    <small id="err_res_location" style="color:#dc2626"></small>
                                </div>
                            </div>
                        </div>
                        <div class="hms-modal-actions">
                            <button type="button" class="btn btn-secondary" onclick="closeAddResourceModal()">Cancel</button>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Resource</button>
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
                        <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                        <div class="hms-modal-body">
                            <div class="form-grid">
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

            <!-- Edit Resource Modal -->
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
                                    <label class="form-label" for="er_name">Resource Name*</label>
                                    <input id="er_name" name="name" type="text" class="form-input" required autocomplete="off" placeholder="Enter resource name">
                                    <small id="err_er_name" style="color:#dc2626"></small>
                                </div>
                                <div>
                                    <label class="form-label" for="er_category">Category*</label>
                                    <select id="er_category" name="category" class="form-select" required>
                                        <option value="">Select category</option>
                                        <option value="Equipment">Equipment</option>
                                        <option value="Facility">Facility</option>
                                        <option value="Personnel">Personnel</option>
                                    </select>
                                    <small id="err_er_category" style="color:#dc2626"></small>
                                </div>
                                <div>
                                    <label class="form-label" for="er_quantity">Quantity*</label>
                                    <input id="er_quantity" name="quantity" type="number" class="form-input" min="1" required autocomplete="off" placeholder="Enter quantity">
                                    <small id="err_er_quantity" style="color:#dc2626"></small>
                                </div>
                                <div>
                                    <label class="form-label" for="er_status">Status*</label>
                                    <select id="er_status" name="status" class="form-select" required>
                                        <option value="Available">Available</option>
                                        <option value="In Use">In Use</option>
                                        <option value="Maintenance">Maintenance</option>
                                        <option value="Out of Order">Out of Order</option>
                                    </select>
                                    <small id="err_er_status" style="color:#dc2626"></small>
                                </div>
                                <div>
                                    <label class="form-label" for="er_location">Location*</label>
                                    <input id="er_location" name="location" type="text" class="form-input" required autocomplete="off" placeholder="Enter location">
                                    <small id="err_er_location" style="color:#dc2626"></small>
                                </div>
                            </div>
                        </div>
                        <div class="hms-modal-actions">
                            <button type="button" class="btn btn-secondary" onclick="closeEditResourceModal()">Cancel</button>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Resource</button>
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
                // Add Resource modal functions
                function openAddResourceModal() {
                    const modal = document.getElementById('addResourceModal');
                    if (modal) {
                        modal.setAttribute('aria-hidden', 'false');
                        modal.style.display = 'block';
                    }
                }
                function closeAddResourceModal() {
                    const modal = document.getElementById('addResourceModal');
                    const form = document.getElementById('addResourceForm');
                    if (modal) {
                        modal.setAttribute('aria-hidden', 'true');
                        modal.style.display = 'none';
                    }
                    if (form) form.reset();
                    // Clear errors
                    const errors = form?.querySelectorAll('[id^="err_res_"]');
                    errors?.forEach(e => e.textContent = '');
                }
                function openEditResourceModal() {
                    const modal = document.getElementById('editResourceModal');
                    if (modal) {
                        modal.setAttribute('aria-hidden', 'false');
                        modal.style.display = 'block';
                    }
                }
                function closeEditResourceModal() {
                    const modal = document.getElementById('editResourceModal');
                    const form = document.getElementById('editResourceForm');
                    if (modal) {
                        modal.setAttribute('aria-hidden', 'true');
                        modal.style.display = 'none';
                    }
                    if (form) form.reset();
                    // Clear errors
                    const errors = form?.querySelectorAll('[id^="err_er_"]');
                    errors?.forEach(e => e.textContent = '');
                }

                // Add Department modal functions
                function openAddDepartmentModal() {
                    const modal = document.getElementById('addDepartmentModal');
                    if (modal) {
                        modal.setAttribute('aria-hidden', 'false');
                        modal.style.display = 'block';
                    }
                }
                function closeAddDepartmentModal() {
                    const modal = document.getElementById('addDepartmentModal');
                    const form = document.getElementById('addDepartmentForm');
                    if (modal) {
                        modal.setAttribute('aria-hidden', 'true');
                        modal.style.display = 'none';
                    }
                    if (form) form.reset();
                    // Clear errors
                    const errors = form?.querySelectorAll('[id^="err_dept_"]');
                    errors?.forEach(e => e.textContent = '');
                }

                // Add Department button event listener
                document.getElementById('addDepartmentBtn')?.addEventListener('click', openAddDepartmentModal);

                // Add Department form submission
                const addDepartmentForm = document.getElementById('addDepartmentForm');
                if (addDepartmentForm) {
                    addDepartmentForm.addEventListener('submit', async (e) => {
                        e.preventDefault();

                        // Clear previous errors
                        const errors = addDepartmentForm.querySelectorAll('[id^="err_dept_"]');
                        errors.forEach(error => error.textContent = '');

                        // Get form values
                        const name = document.getElementById('dept_name')?.value?.trim();
                        const description = document.getElementById('dept_description')?.value?.trim();

                        // Validate
                        let hasErrors = false;
                        if (!name) {
                            document.getElementById('err_dept_name').textContent = 'Department name is required.';
                            hasErrors = true;
                        }

                        if (hasErrors) return;

                        try {
                            const formData = new FormData(addDepartmentForm);
                            const res = await fetch(window.HMS.baseUrl + '/departments/create', {
                                method: 'POST',
                                body: formData
                            });

                            let data = null;
                            try {
                                const raw = await res.text();
                                data = raw ? JSON.parse(raw) : null;
                            } catch (e) {}

                            if (!res.ok || (data && data.status === 'error')) {
                                const detail = data?.message || 'Failed to save department';
                                alert('Error: ' + detail);
                                return;
                            }

                            closeAddDepartmentModal();
                            alert('Department saved successfully');
                            location.reload();
                        } catch (err) {
                            alert('Failed to save department');
                        }
                    });
                }

                // Add Resource form submission
                const addResourceForm = document.getElementById('addResourceForm');
                if (addResourceForm) {
                    addResourceForm.addEventListener('submit', async (e) => {
                        e.preventDefault();

                        // Clear previous errors
                        const errors = addResourceForm.querySelectorAll('[id^="err_res_"]');
                        errors.forEach(error => error.textContent = '');

                        // Get form values
                        const name = document.getElementById('res_name')?.value?.trim();
                        const category = document.getElementById('res_category')?.value;
                        const quantity = document.getElementById('res_quantity')?.value;
                        const status = document.getElementById('res_status')?.value;
                        const location = document.getElementById('res_location')?.value?.trim();

                        // Validate
                        let hasErrors = false;
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

                        if (hasErrors) return;

                        try {
                            const formData = new FormData(addResourceForm);
                            const res = await fetch(window.HMS.baseUrl + '/admin/resource-management/create', {
                                method: 'POST',
                                body: formData
                            });

                            let data = null;
                            try {
                                const raw = await res.text();
                                data = raw ? JSON.parse(raw) : null;
                            } catch (e) {}

                            if (!res.ok || (data && data.status === 'error')) {
                                const detail = data?.db_error?.message || data?.message || 'Failed to save resource';
                                alert(detail);
                                return;
                            }

                            closeAddResourceModal();
                            alert('Resource saved successfully');
                            location.reload();
                        } catch (err) {
                            alert('Failed to save resource');
                        }
                    });
                }
            </script>
        </main>
    </div>

    <script src="<?= base_url('js/logout.js') ?>"></script>
</body>
</html>
