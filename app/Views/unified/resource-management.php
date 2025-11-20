<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> - HMS</title>
    <meta name="base-url" content="<?= base_url() ?>">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta name="csrf-hash" content="<?= csrf_hash() ?>">
    <meta name="user-role" content="<?= esc($userRole) ?>">
    
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/unified/resource-management.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="<?= esc($userRole) ?>">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <?= $this->include('unified/components/notification', [
        'id' => 'resourcesNotification',
        'dismissFn' => 'dismissResourcesNotification()'
    ]) ?>

    <div class="main-container">
        <?php include APPPATH . 'Views/unified/components/sidebar.php'; ?>

        <main class="content" role="main">
            <h1 class="page-title">
                <i class="fas fa-boxes"></i>
                <?php
                $pageTitles = [
                    'admin' => 'Resource Management',
                    'doctor' => 'Medical Resources',
                    'nurse' => 'Medical Resources',
                    'pharmacist' => 'Pharmacy Resources',
                    'laboratorist' => 'Lab Resources',
                    'receptionist' => 'Office Resources',
                    'it_staff' => 'IT Resource Management'
                ];
                echo esc($pageTitles[$userRole] ?? 'Resources');
                ?>
            </h1>
            <div class="page-actions">
                <?php if (in_array('create', $permissions['resources'] ?? [])): ?>
                    <button type="button" id="addResourceBtn" class="btn btn-primary" aria-label="Add New Resource">
                        <i class="fas fa-plus" aria-hidden="true"></i> Add Resource
                    </button>
                <?php endif; ?>
                <?php if (in_array($userRole ?? '', ['admin', 'it_staff'])): ?>
                    <button type="button" class="btn btn-secondary" id="exportBtn" aria-label="Export Data">
                        <i class="fas fa-download" aria-hidden="true"></i> Export
                    </button>
                <?php endif; ?>
            </div>

            <br />

            <!-- Statistics Overview -->
            <div class="dashboard-overview">
                <?php if ($userRole === 'admin' || $userRole === 'it_staff'): ?>
                    <!-- Total Resources Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-boxes"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Total Resources</h3>
                                <p class="card-subtitle">All equipment & supplies</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $stats['total_resources'] ?? 0 ?></div>
                                <div class="metric-label">Total</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value green"><?= $stats['available'] ?? 0 ?></div>
                                <div class="metric-label">Available</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Resource Status Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern orange"><i class="fas fa-tools"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Resource Status</h3>
                                <p class="card-subtitle">Current utilization</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value orange"><?= $stats['in_use'] ?? 0 ?></div>
                                <div class="metric-label">In Use</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value purple"><?= $stats['maintenance'] ?? 0 ?></div>
                                <div class="metric-label">Maintenance</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Categories Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern purple"><i class="fas fa-layer-group"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Categories</h3>
                                <p class="card-subtitle">Resource types</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value purple"><?= $stats['categories'] ?? 0 ?></div>
                                <div class="metric-label">Types</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value red"><?= $stats['out_of_order'] ?? 0 ?></div>
                                <div class="metric-label">Out of Order</div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($userRole === 'doctor' || $userRole === 'nurse'): ?>
                    <!-- Medical Resources Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-stethoscope"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Medical Resources</h3>
                                <p class="card-subtitle">Equipment & supplies</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $stats['total_resources'] ?? 0 ?></div>
                                <div class="metric-label">Total</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value green"><?= $stats['available'] ?? 0 ?></div>
                                <div class="metric-label">Available</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Equipment Status Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern orange"><i class="fas fa-heartbeat"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Equipment Status</h3>
                                <p class="card-subtitle">Current status</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value orange"><?= $stats['in_use'] ?? 0 ?></div>
                                <div class="metric-label">In Use</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value purple"><?= $stats['maintenance'] ?? 0 ?></div>
                                <div class="metric-label">Maintenance</div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($userRole === 'pharmacist'): ?>
                    <!-- Pharmacy Resources Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-pills"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Pharmacy Resources</h3>
                                <p class="card-subtitle">Equipment & supplies</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $stats['total_resources'] ?? 0 ?></div>
                                <div class="metric-label">Total</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value green"><?= $stats['available'] ?? 0 ?></div>
                                <div class="metric-label">Available</div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($userRole === 'laboratorist'): ?>
                    <!-- Lab Resources Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-microscope"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Lab Resources</h3>
                                <p class="card-subtitle">Equipment & supplies</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $stats['total_resources'] ?? 0 ?></div>
                                <div class="metric-label">Total</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value green"><?= $stats['available'] ?? 0 ?></div>
                                <div class="metric-label">Available</div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($userRole === 'receptionist'): ?>
                    <!-- Office Resources Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-desktop"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Office Resources</h3>
                                <p class="card-subtitle">Equipment & supplies</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $stats['total_resources'] ?? 0 ?></div>
                                <div class="metric-label">Total</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value green"><?= $stats['available'] ?? 0 ?></div>
                                <div class="metric-label">Available</div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Resources Table -->
            <div class="resources-table-container">
                <table class="resources-table">
                    <thead>
                        <tr>
                            <th>Resource Name</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Location</th>
                            <?php if (in_array('edit', $permissions['resources'] ?? []) || in_array('delete', $permissions['resources'] ?? [])): ?>
                                <th>Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody id="resourcesTableBody">
                        <?php if (!empty($resources) && is_array($resources)): ?>
                            <?php foreach ($resources as $r): ?>
                                <tr>
                                    <td><?= esc($r['equipment_name'] ?? '-') ?></td>
                                    <td><?= esc($r['category'] ?? '-') ?></td>
                                    <td><?= esc($r['quantity'] ?? '-') ?></td>
                                    <td>
                                        <?php
                                        $status = $r['status'] ?? 'Available';
                                        $badgeClass = match($status) {
                                            'Available' => 'badge-success',
                                            'In Use' => 'badge-info',
                                            'Maintenance' => 'badge-warning',
                                            'Out of Order' => 'badge-danger',
                                            default => 'badge-info'
                                        };
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= esc($status) ?></span>
                                    </td>
                                    <td><?= esc($r['location'] ?? '-') ?></td>
                                    <?php if (in_array('edit', $permissions['resources'] ?? []) || in_array('delete', $permissions['resources'] ?? [])): ?>
                                        <td>
                                            <div style="display: flex; gap: 0.5rem;">
                                                <?php if (in_array('edit', $permissions['resources'] ?? [])): ?>
                                                    <button class="btn btn-warning btn-small" onclick="editResource(<?= esc($r['id'] ?? 0) ?>)" aria-label="Edit Resource">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                <?php endif; ?>
                                                <?php if (in_array('delete', $permissions['resources'] ?? [])): ?>
                                                    <button class="btn btn-danger btn-small" onclick="deleteResource(<?= esc($r['id'] ?? 0) ?>)" aria-label="Delete Resource">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= (in_array('edit', $permissions['resources'] ?? []) || in_array('delete', $permissions['resources'] ?? [])) ? '6' : '5' ?>" class="empty-state">
                                    <i class="fas fa-box-open"></i>
                                    <h3>No Resources Found</h3>
                                    <p>Start by adding your first resource</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Add Resource Modal -->
            <div id="addResourceModal" class="modal" aria-hidden="true">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>
                            <i class="fas fa-plus-circle"></i>
                            Add New Resource
                        </h3>
                        <button type="button" class="modal-close" onclick="closeAddResourceModal()" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form id="addResourceForm">
                        <div class="modal-body">
                            <div class="form-section">
                                <h4><i class="fas fa-info-circle"></i> Resource Information</h4>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="res_name">Resource Name*</label>
                                        <input id="res_name" name="equipment_name" type="text" class="form-control" required autocomplete="off" placeholder="Enter resource name">
                                        <small id="err_res_name" style="color:#dc2626"></small>
                                    </div>
                                    <div class="form-group">
                                        <label for="res_category">Category*</label>
                                        <select id="res_category" name="category" class="form-control" required>
                                            <option value="">Select category</option>
                                            <?php foreach ($categories ?? [] as $cat): ?>
                                                <option value="<?= esc($cat) ?>"><?= esc($cat) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small id="err_res_category" style="color:#dc2626"></small>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="res_quantity">Quantity*</label>
                                        <input id="res_quantity" name="quantity" type="number" class="form-control" min="1" required autocomplete="off" placeholder="Enter quantity">
                                        <small id="err_res_quantity" style="color:#dc2626"></small>
                                    </div>
                                    <div class="form-group">
                                        <label for="res_status">Status*</label>
                                        <select id="res_status" name="status" class="form-control" required>
                                            <option value="Available">Available</option>
                                            <option value="In Use">In Use</option>
                                            <option value="Maintenance">Maintenance</option>
                                            <option value="Out of Order">Out of Order</option>
                                        </select>
                                        <small id="err_res_status" style="color:#dc2626"></small>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group full-width">
                                        <label for="res_location">Location*</label>
                                        <input id="res_location" name="location" type="text" class="form-control" required autocomplete="off" placeholder="Enter location">
                                        <small id="err_res_location" style="color:#dc2626"></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
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
            <div id="editResourceModal" class="modal" aria-hidden="true">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>
                            <i class="fas fa-edit"></i>
                            Edit Resource
                        </h3>
                        <button type="button" class="modal-close" onclick="closeEditResourceModal()" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form id="editResourceForm">
                        <input type="hidden" id="er_id" name="id">
                        <div class="modal-body">
                            <div class="form-section">
                                <h4><i class="fas fa-info-circle"></i> Resource Information</h4>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="er_name">Resource Name*</label>
                                        <input id="er_name" name="equipment_name" type="text" class="form-control" required autocomplete="off" placeholder="Enter resource name">
                                        <small id="err_er_name" style="color:#dc2626"></small>
                                    </div>
                                    <div class="form-group">
                                        <label for="er_category">Category*</label>
                                        <select id="er_category" name="category" class="form-control" required>
                                            <option value="">Select category</option>
                                            <?php foreach ($categories ?? [] as $cat): ?>
                                                <option value="<?= esc($cat) ?>"><?= esc($cat) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small id="err_er_category" style="color:#dc2626"></small>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="er_quantity">Quantity*</label>
                                        <input id="er_quantity" name="quantity" type="number" class="form-control" min="1" required autocomplete="off" placeholder="Enter quantity">
                                        <small id="err_er_quantity" style="color:#dc2626"></small>
                                    </div>
                                    <div class="form-group">
                                        <label for="er_status">Status*</label>
                                        <select id="er_status" name="status" class="form-control" required>
                                            <option value="Available">Available</option>
                                            <option value="In Use">In Use</option>
                                            <option value="Maintenance">Maintenance</option>
                                            <option value="Out of Order">Out of Order</option>
                                        </select>
                                        <small id="err_er_status" style="color:#dc2626"></small>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group full-width">
                                        <label for="er_location">Location*</label>
                                        <input id="er_location" name="location" type="text" class="form-control" required autocomplete="off" placeholder="Enter location">
                                        <small id="err_er_location" style="color:#dc2626"></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
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
                    // Reload page so resources table is updated after closing the modal
                    window.location.reload();
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
                    // Reload page so resources table is updated after closing the edit modal
                    window.location.reload();
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
</body>
</html>
