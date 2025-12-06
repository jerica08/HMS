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

    <!-- Expiry Notifications -->
    <?php if (!empty($expiredMedications) || !empty($expiringMedications)): ?>
        <div class="expiry-notifications" style="margin: 1rem auto; max-width: 1180px;">
            <?php if (!empty($expiredMedications)): ?>
                <div class="expiry-alert expired" style="
                    padding: 1rem;
                    margin-bottom: 0.75rem;
                    border-radius: 6px;
                    border: 1px solid #fecaca;
                    background: #fee2e2;
                    color: #991b1b;
                    display: flex;
                    align-items: center;
                    gap: 0.75rem;
                ">
                    <i class="fas fa-exclamation-triangle" style="font-size: 1.25rem;"></i>
                    <div style="flex: 1;">
                        <strong>Expired Medications Alert</strong>
                        <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem;">
                            <?= count($expiredMedications) ?> medication(s) have expired:
                            <?php 
                            $expiredNames = array_slice(array_map(function($m) { 
                                return esc($m['equipment_name'] ?? 'Unknown'); 
                            }, $expiredMedications), 0, 5);
                            echo implode(', ', $expiredNames);
                            if (count($expiredMedications) > 5) echo ' and ' . (count($expiredMedications) - 5) . ' more';
                            ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($expiringMedications)): ?>
                <div class="expiry-alert expiring" style="
                    padding: 1rem;
                    margin-bottom: 0.75rem;
                    border-radius: 6px;
                    border: 1px solid #fef3c7;
                    background: #fef9c3;
                    color: #92400e;
                    display: flex;
                    align-items: center;
                    gap: 0.75rem;
                ">
                    <i class="fas fa-exclamation-circle" style="font-size: 1.25rem;"></i>
                    <div style="flex: 1;">
                        <strong>Expiring Medications Warning</strong>
                        <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem;">
                            <?= count($expiringMedications) ?> medication(s) will expire within 30 days:
                            <?php 
                            $expiringNames = array_slice(array_map(function($m) { 
                                $name = esc($m['equipment_name'] ?? 'Unknown');
                                $expiry = !empty($m['expiry_date']) ? date('M d, Y', strtotime($m['expiry_date'])) : 'Unknown';
                                return $name . ' (expires: ' . $expiry . ')';
                            }, $expiringMedications), 0, 3);
                            echo implode(', ', $expiringNames);
                            if (count($expiringMedications) > 3) echo ' and ' . (count($expiringMedications) - 3) . ' more';
                            ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

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
                                <div class="metric-value green"><?= $stats['stock_in'] ?? 0 ?></div>
                                <div class="metric-label">Stock In</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Resource Status Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern orange"><i class="fas fa-tools"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Resource Status</h3>
                                <p class="card-subtitle">Current stock status</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value green"><?= $stats['stock_in'] ?? 0 ?></div>
                                <div class="metric-label">Stock In</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value red"><?= $stats['stock_out'] ?? 0 ?></div>
                                <div class="metric-label">Stock Out</div>
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
                                <div class="metric-value orange"><?= $stats['low_quantity'] ?? 0 ?></div>
                                <div class="metric-label">Low Stock</div>
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
                                <div class="metric-value green"><?= $stats['stock_in'] ?? 0 ?></div>
                                <div class="metric-label">Stock In</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Equipment Status Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern orange"><i class="fas fa-heartbeat"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Equipment Status</h3>
                                <p class="card-subtitle">Current stock status</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value green"><?= $stats['stock_in'] ?? 0 ?></div>
                                <div class="metric-label">Stock In</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value red"><?= $stats['stock_out'] ?? 0 ?></div>
                                <div class="metric-label">Stock Out</div>
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
                                <div class="metric-value green"><?= $stats['stock_in'] ?? 0 ?></div>
                                <div class="metric-label">Stock In</div>
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
                                <div class="metric-value green"><?= $stats['stock_in'] ?? 0 ?></div>
                                <div class="metric-label">Stock In</div>
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
                                <div class="metric-value green"><?= $stats['stock_in'] ?? 0 ?></div>
                                <div class="metric-label">Stock In</div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Search and Filter Section -->
            <div class="filters-section" style="background: white; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                    <div class="form-group" style="margin: 0;">
                        <label for="searchResource" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                            <i class="fas fa-search"></i> Search
                        </label>
                        <input type="text" id="searchResource" class="form-control" placeholder="Search by name, location..." autocomplete="off">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label for="filterCategory" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                            <i class="fas fa-filter"></i> Category
                        </label>
                        <select id="filterCategory" class="form-control">
                            <option value="">All Categories</option>
                            <?php foreach ($categories ?? [] as $cat): ?>
                                <option value="<?= esc($cat) ?>"><?= esc($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label for="filterStatus" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                            <i class="fas fa-info-circle"></i> Status
                        </label>
                        <select id="filterStatus" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="Stock In">Stock In</option>
                            <option value="Stock Out">Stock Out</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <button type="button" id="clearFilters" class="btn btn-secondary" style="width: 100%;">
                            <i class="fas fa-times"></i> Clear Filters
                        </button>
                    </div>
                </div>
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
                                        $status = $r['status'] ?? 'Stock In';
                                        $badgeClass = match($status) {
                                            'Stock In' => 'badge-success',
                                            'Stock Out' => 'badge-danger',
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
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="res_location">Location*</label>
                                        <input id="res_location" name="location" type="text" class="form-control" required autocomplete="off" placeholder="Enter location">
                                        <small id="err_res_location" style="color:#dc2626"></small>
                                    </div>
                                    <div class="form-group">
                                        <label for="res_serial_number">
                                            <i class="fas fa-hashtag"></i> Serial Number
                                            <small style="color: #666;">(Optional)</small>
                                        </label>
                                        <input id="res_serial_number" name="serial_number" type="text" class="form-control" autocomplete="off" placeholder="Enter serial number">
                                        <small id="err_res_serial_number" style="color:#dc2626"></small>
                                    </div>
                                </div>
                                <!-- Medication-specific fields (shown when category is Medications) -->
                                <div class="form-row" id="medicationFields" style="display: none;">
                                    <div class="form-group">
                                        <label for="res_batch_number">
                                            <i class="fas fa-barcode"></i> Batch Number
                                            <small style="color: #666;">(Required for medications)</small>
                                        </label>
                                        <input id="res_batch_number" name="batch_number" type="text" class="form-control" autocomplete="off" placeholder="Enter batch/lot number">
                                        <small id="err_res_batch_number" style="color:#dc2626"></small>
                                    </div>
                                    <div class="form-group">
                                        <label for="res_expiry_date">
                                            <i class="fas fa-calendar-times"></i> Expiry Date
                                            <small style="color: #666;">(Required for medications)</small>
                                        </label>
                                        <input id="res_expiry_date" name="expiry_date" type="date" class="form-control" autocomplete="off">
                                        <small id="err_res_expiry_date" style="color:#dc2626"></small>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group full-width">
                                        <label for="res_remarks">Remarks/Notes</label>
                                        <textarea id="res_remarks" name="remarks" rows="3" class="form-control" autocomplete="off" placeholder="Additional notes..."></textarea>
                                        <small id="err_res_remarks" style="color:#dc2626"></small>
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
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="er_location">Location*</label>
                                        <input id="er_location" name="location" type="text" class="form-control" required autocomplete="off" placeholder="Enter location">
                                        <small id="err_er_location" style="color:#dc2626"></small>
                                    </div>
                                    <div class="form-group">
                                        <label for="er_serial_number">
                                            <i class="fas fa-hashtag"></i> Serial Number
                                            <small style="color: #666;">(Optional)</small>
                                        </label>
                                        <input id="er_serial_number" name="serial_number" type="text" class="form-control" autocomplete="off" placeholder="Enter serial number">
                                        <small id="err_er_serial_number" style="color:#dc2626"></small>
                                    </div>
                                </div>
                                <!-- Medication-specific fields (shown when category is Medications) -->
                                <div class="form-row" id="editMedicationFields" style="display: none;">
                                    <div class="form-group">
                                        <label for="er_batch_number">
                                            <i class="fas fa-barcode"></i> Batch Number
                                            <small style="color: #666;">(Required for medications)</small>
                                        </label>
                                        <input id="er_batch_number" name="batch_number" type="text" class="form-control" autocomplete="off" placeholder="Enter batch/lot number">
                                        <small id="err_er_batch_number" style="color:#dc2626"></small>
                                    </div>
                                    <div class="form-group">
                                        <label for="er_expiry_date">
                                            <i class="fas fa-calendar-times"></i> Expiry Date
                                            <small style="color: #666;">(Required for medications)</small>
                                        </label>
                                        <input id="er_expiry_date" name="expiry_date" type="date" class="form-control" autocomplete="off">
                                        <small id="err_er_expiry_date" style="color:#dc2626"></small>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group full-width">
                                        <label for="er_remarks">Remarks/Notes</label>
                                        <textarea id="er_remarks" name="remarks" rows="3" class="form-control" autocomplete="off" placeholder="Additional notes..."></textarea>
                                        <small id="err_er_remarks" style="color:#dc2626"></small>
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
                function closeAddResourceModal(reload = true) {
                    const modal = document.getElementById('addResourceModal');
                    const form = document.getElementById('addResourceForm');
                    const wasOpen = modal && (modal.style.display === 'block' || modal.getAttribute('aria-hidden') === 'false');
                    
                    if (modal) {
                        modal.setAttribute('aria-hidden', 'true');
                        modal.style.display = 'none';
                    }
                    if (form) form.reset();
                    // Clear errors
                    const errors = form?.querySelectorAll('[id^="err_res_"]');
                    errors?.forEach(e => e.textContent = '');
                    
                    // Reload page so resources table is updated after closing the modal
                    // Only reload if modal was actually open and reload is requested
                    // Delay reload to allow notification to be visible
                    if (reload && wasOpen) {
                        setTimeout(() => window.location.reload(), 1500);
                    }
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
                        // Status is no longer in form - default to 'Stock In' in backend
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
                        // Status validation removed - default to 'Stock In' in backend
                        if (!location) {
                            document.getElementById('err_res_location').textContent = 'Location is required.';
                            hasErrors = true;
                        }

                        if (hasErrors) return;

                        try {
                            const formData = new FormData(addResourceForm);
                            const res = await fetch(window.HMS.baseUrl + '/admin/resource-management/create', {
                                method: 'POST',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                },
                                body: formData
                            });

                            let data = null;
                            try {
                                const contentType = res.headers.get('content-type');
                                if (contentType && contentType.includes('application/json')) {
                                    data = await res.json();
                                } else {
                                    const raw = await res.text();
                                    data = raw ? JSON.parse(raw) : null;
                                }
                            } catch (e) {
                                console.error('Error parsing response:', e);
                            }

                            console.log('Response data:', data); // Debug log

                            // Check for success - the response should have 'success' property set to true
                            if (data && data.success === true) {
                                // Show success notification
                                showResourcesNotification(data.message || 'Resource added successfully!', 'success');
                                
                                // Close modal and reload after a short delay to show notification
                                setTimeout(() => {
                                    closeAddResourceModal();
                                }, 500);
                            } else {
                                // Show error message
                                const errorMsg = data?.message || data?.db_error?.message || 'Failed to save resource';
                                alert(errorMsg);
                                console.error('Failed response:', data);
                            }
                        } catch (err) {
                            console.error('Error:', err);
                            alert('An error occurred while saving the resource. Please check the console for details.');
                        }
                    });
                }

                // Export button handler
                document.getElementById('exportBtn')?.addEventListener('click', function() {
                    const searchInput = document.getElementById('searchResource');
                    const categoryFilter = document.getElementById('filterCategory');
                    const statusFilter = document.getElementById('filterStatus');
                    
                    const params = new URLSearchParams();
                    if (searchInput?.value) params.append('search', searchInput.value);
                    if (categoryFilter?.value) params.append('category', categoryFilter.value);
                    if (statusFilter?.value) params.append('status', statusFilter.value);
                    
                    let url = window.HMS.baseUrl + '/admin/resource-management/export';
                    if (params.toString()) url += '?' + params.toString();
                    
                    window.location.href = url;
                });

                // Show/hide medication fields based on category selection
                function toggleMedicationFields(categorySelectId, fieldsContainerId, serialContainerId) {
                    const categorySelect = document.getElementById(categorySelectId);
                    const fieldsContainer = document.getElementById(fieldsContainerId);
                    
                    if (!categorySelect || !fieldsContainer) return;
                    
                    const toggleFields = () => {
                        const isMedication = categorySelect.value === 'Medications';
                        fieldsContainer.style.display = isMedication ? 'flex' : 'none';
                        
                        // Make batch number and expiry date required for medications
                        const batchNumber = fieldsContainer.querySelector('[name="batch_number"]');
                        const expiryDate = fieldsContainer.querySelector('[name="expiry_date"]');
                        
                        if (batchNumber) {
                            batchNumber.required = isMedication;
                            if (!isMedication) batchNumber.value = '';
                        }
                        if (expiryDate) {
                            expiryDate.required = isMedication;
                            if (!isMedication) expiryDate.value = '';
                        }
                    };
                    
                    categorySelect.addEventListener('change', toggleFields);
                    toggleFields(); // Initial check
                }

                // Initialize medication field toggling for add form
                toggleMedicationFields('res_category', 'medicationFields', null);
                
                // Initialize medication field toggling for edit form
                toggleMedicationFields('er_category', 'editMedicationFields', null);

                // Notification functions
                function showResourcesNotification(message, type = 'info') {
                    const container = document.getElementById('resourcesNotification');
                    const iconEl = document.getElementById('resourcesNotificationIcon');
                    const textEl = document.getElementById('resourcesNotificationText');

                    if (!container || !iconEl || !textEl) {
                        // Fallback to alert if notification component not found
                        alert(message);
                        return;
                    }

                    const isError = type === 'error';
                    const isSuccess = type === 'success';

                    // Set styling based on type
                    container.style.border = isError ? '1px solid #fecaca' : '1px solid #bbf7d0';
                    container.style.background = isError ? '#fee2e2' : '#ecfdf5';
                    container.style.color = isError ? '#991b1b' : '#166534';

                    // Set icon
                    const iconClass = isError
                        ? 'fa-exclamation-triangle'
                        : (isSuccess ? 'fa-check-circle' : 'fa-info-circle');
                    iconEl.className = 'fas ' + iconClass;

                    // Set message
                    textEl.textContent = message || '';
                    container.style.display = 'flex';

                    // Auto-hide after 4 seconds
                    setTimeout(() => {
                        if (container && container.style.display !== 'none') {
                            container.style.display = 'none';
                        }
                    }, 4000);
                }

                function dismissResourcesNotification() {
                    const container = document.getElementById('resourcesNotification');
                    if (container) {
                        container.style.display = 'none';
                    }
                }
            </script>
        </main>
    </div>
</body>
</html>
