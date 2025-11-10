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
    <link rel="stylesheet" href="<?= base_url('assets/css/unified/prescription-management.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/unified/shift-management.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="<?= esc($userRole) ?>">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <?php 
        // Include unified sidebar component
        include APPPATH . 'Views/unified/components/sidebar.php'; 
        ?>

        <main class="content" role="main">
            <h1 class="page-title">
                <i class="fas fa-prescription-bottle-alt"></i>
                <?php
                $pageTitles = [
                    'admin' => 'Prescription Management',
                    'doctor' => 'My Prescriptions',
                    'nurse' => 'Department Prescriptions',
                    'pharmacist' => 'Pharmacy Queue',
                    'receptionist' => 'Prescription Records'
                ];
                echo esc($pageTitles[$userRole] ?? 'Prescriptions');
                ?>
            </h1>
            <div class="page-actions">
                <?php if ($permissions['canCreate']): ?>
                    <button type="button" id="createPrescriptionBtn" class="btn btn-primary" aria-label="Create New Prescription" onclick="showPrescriptionModalDirect()">
                        <i class="fas fa-plus" aria-hidden="true"></i> Add Prescription
                    </button>
                <?php endif; ?>
                <?php if (in_array($userRole ?? '', ['admin', 'it_staff', 'pharmacist'])): ?>
                    <button type="button" class="btn btn-secondary" id="exportBtn" aria-label="Export Data">
                        <i class="fas fa-download" aria-hidden="true"></i> Export
                    </button>
                <?php endif; ?>
            </div>

            <?php if (session()->getFlashdata('success') || session()->getFlashdata('error')): ?>
                <div id="flashNotice" role="alert" aria-live="polite" style="
                    margin-top: 1rem; padding: 0.75rem 1rem; border-radius: 8px;
                    border: 1px solid <?= session()->getFlashdata('success') ? '#86efac' : '#fecaca' ?>;
                    background: <?= session()->getFlashdata('success') ? '#dcfce7' : '#fee2e2' ?>;
                    color: <?= session()->getFlashdata('success') ? '#166534' : '#991b1b' ?>; display:flex; align-items:center; gap:0.5rem;">
                    <i class="fas <?= session()->getFlashdata('success') ? 'fa-check-circle' : 'fa-exclamation-triangle' ?>" aria-hidden="true"></i>
                    <span>
                        <?= esc(session()->getFlashdata('success') ?: session()->getFlashdata('error')) ?>
                    </span>
                    <button type="button" onclick="dismissFlash()" aria-label="Dismiss notification" style="margin-left:auto; background:transparent; border:none; cursor:pointer; color:inherit;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>

            <?php $errors = session()->get('errors'); ?>
            <?php if (!empty($errors) && is_array($errors)): ?>
                <div role="alert" aria-live="polite" style="margin-top:0.75rem; padding:0.75rem 1rem; border-radius:8px; border:1px solid #fecaca; background:#fee2e2; color:#991b1b;">
                    <div style="font-weight:600; margin-bottom:0.25rem;"><i class="fas fa-exclamation-circle"></i> Please fix the following errors:</div>
                    <ul style="margin:0; padding-left:1.25rem;">
                        <?php foreach ($errors as $field => $msg): ?>
                            <li><?= esc(is_array($msg) ? implode(', ', $msg) : $msg) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <br />

            <!-- Statistics Overview -->
            <div class="dashboard-overview">
                <?php if ($userRole === 'admin' || $userRole === 'it_staff'): ?>
                    <!-- Total Prescriptions Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-pills"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Total Prescriptions</h3>
                                <p class="card-subtitle">All medication orders</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $stats['total_prescriptions'] ?? 0 ?></div>
                                <div class="metric-label">Total</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value green"><?= $stats['active_prescriptions'] ?? 0 ?></div>
                                <div class="metric-label">Active</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Today's Prescriptions Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern orange"><i class="fas fa-prescription-bottle"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Today's Prescriptions</h3>
                                <p class="card-subtitle"><?= date('F j, Y') ?></p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value orange"><?= $stats['today_prescriptions'] ?? 0 ?></div>
                                <div class="metric-label">Today</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value purple"><?= $stats['pending_prescriptions'] ?? 0 ?></div>
                                <div class="metric-label">Pending</div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($userRole === 'doctor'): ?>
                    <!-- My Prescriptions Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-clipboard-list"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">My Prescriptions</h3>
                                <p class="card-subtitle">Issued prescriptions</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $stats['my_prescriptions'] ?? 0 ?></div>
                                <div class="metric-label">Total</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value orange"><?= $stats['today_prescriptions'] ?? 0 ?></div>
                                <div class="metric-label">Today</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Patient Overview Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern green"><i class="fas fa-check-circle"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Prescription Status</h3>
                                <p class="card-subtitle">Current status</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value green"><?= $stats['active_prescriptions'] ?? 0 ?></div>
                                <div class="metric-label">Active</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value purple"><?= $stats['completed_prescriptions'] ?? 0 ?></div>
                                <div class="metric-label">Completed</div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($userRole === 'pharmacist'): ?>
                    <!-- Prescription Queue Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-hourglass-half"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Prescription Queue</h3>
                                <p class="card-subtitle">Pending dispensing</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $stats['pending_prescriptions'] ?? 0 ?></div>
                                <div class="metric-label">Pending</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value red"><?= $stats['stat_prescriptions'] ?? 0 ?></div>
                                <div class="metric-label">STAT</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dispensed Today Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern green"><i class="fas fa-mortar-pestle"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Dispensed Today</h3>
                                <p class="card-subtitle"><?= date('F j, Y') ?></p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value green"><?= $stats['dispensed_today'] ?? 0 ?></div>
                                <div class="metric-label">Dispensed</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value orange"><?= $stats['ready_to_dispense'] ?? 0 ?></div>
                                <div class="metric-label">Ready</div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($userRole === 'nurse'): ?>
                    <!-- Department Prescriptions Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-hospital"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Department Prescriptions</h3>
                                <p class="card-subtitle"><?= esc($stats['department'] ?? 'Your department') ?></p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $stats['department_prescriptions'] ?? 0 ?></div>
                                <div class="metric-label">Total</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value orange"><?= $stats['today_prescriptions'] ?? 0 ?></div>
                                <div class="metric-label">Today</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status Overview Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern green"><i class="fas fa-chart-pie"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Status Overview</h3>
                                <p class="card-subtitle">Current status</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value green"><?= $stats['active_prescriptions'] ?? 0 ?></div>
                                <div class="metric-label">Active</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value purple"><?= $stats['pending_prescriptions'] ?? 0 ?></div>
                                <div class="metric-label">Pending</div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- General Prescriptions Overview -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-file-medical"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Prescriptions Overview</h3>
                                <p class="card-subtitle">General statistics</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $stats['total_prescriptions'] ?? 0 ?></div>
                                <div class="metric-label">Total</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value orange"><?= $stats['today_prescriptions'] ?? 0 ?></div>
                                <div class="metric-label">Today</div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Filters and Search -->
            <div class="controls-section">
                <div class="filters-section">
                    <div class="filter-group">
                        <label for="dateFilter">Date:</label>
                        <input type="date" id="dateFilter" class="form-input">
                    </div>
                    
                    <div class="filter-group">
                        <label for="statusFilter">Status:</label>
                        <select id="statusFilter" class="form-select">
                            <option value="">All Status</option>
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?= esc($status['status']) ?>"><?= esc(ucfirst($status['status'])) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="searchFilter">Search:</label>
                        <input type="text" id="searchFilter" class="form-input" placeholder="Search prescriptions...">
                    </div>
                    
                    <button type="button" id="clearFilters" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>
            </div>

            <!-- Prescriptions Table -->
            <div class="prescriptions-table-container">
                <table class="prescriptions-table">
                    <thead>
                        <tr>
                            <th>Prescription ID</th>
                            <th>Patient</th>
                            <th>Medication</th>
                            <th>Dosage</th>
                            <th>Frequency</th>
                            <th>Date Issued</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="prescriptionsTableBody">
                        <tr>
                            <td colspan="8" class="loading-row">
                                <i class="fas fa-spinner fa-spin"></i> Loading prescriptions...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

<!-- Modals -->
<?php 
    $modalData = [
        'statuses' => $statuses ?? [],
        'priorities' => $priorities ?? [],
        'availablePatients' => $availablePatients ?? [],
        'userRole' => $userRole ?? '',
        'permissions' => $permissions ?? []
    ];
?>
<?= view('unified/modals/add-prescription-modal', $modalData) ?>

    <!-- JavaScript -->
    <script src="<?= base_url('public/assets/js/unified/prescription-management.js') ?>"></script>
    
    <script>
    // Direct function to show prescription modal - using exact shift modal approach
    function showPrescriptionModalDirect() {
        console.log('showPrescriptionModalDirect called directly');
        const modal = document.getElementById('prescriptionModal');
        console.log('Modal element found:', !!modal);
        
        if (modal) {
            // Reset form and show modal
            const form = document.getElementById('prescriptionForm');
            if (form) {
                form.reset();
                const idField = document.getElementById('prescriptionId');
                if (idField) {
                    idField.value = '';
                }
                // Set default date to today
                const dateField = document.getElementById('prescriptionDate');
                if (dateField) {
                    dateField.value = new Date().toISOString().split('T')[0];
                }
            }
            
            // Show modal using the same approach that works for shift modal
            modal.classList.add('active');
            modal.style.display = 'flex';
            modal.style.position = 'fixed';
            modal.style.top = '0';
            modal.style.left = '0';
            modal.style.width = '100vw';
            modal.style.height = '100vh';
            modal.style.zIndex = '9999';
            modal.style.alignItems = 'center';
            modal.style.justifyContent = 'center';
            modal.style.background = 'rgba(15, 23, 42, 0.55)';
            
            document.body.style.overflow = 'hidden';
            
            console.log('Prescription modal should be visible now via direct click');
        } else {
            console.error('Prescription modal not found!');
        }
    }
    
    // Direct function to hide modal - using exact shift modal approach
    function hidePrescriptionModalDirect() {
        console.log('hidePrescriptionModalDirect called directly');
        const modal = document.getElementById('prescriptionModal');
        if (modal) {
            modal.classList.remove('active');
            modal.style.display = 'none';
            document.body.style.overflow = '';
            console.log('Prescription modal closed via direct function');
        } else {
            console.error('Prescription modal not found for closing!');
        }
    }
    
    // Make functions global
    window.showPrescriptionModalDirect = showPrescriptionModalDirect;
    window.hidePrescriptionModalDirect = hidePrescriptionModalDirect;
    
    // Add event listeners for close buttons
    document.addEventListener('DOMContentLoaded', function() {
        const closeBtn = document.getElementById('closePrescriptionModal');
        const cancelBtn = document.getElementById('cancelPrescriptionBtn');
        
        if (closeBtn) {
            closeBtn.addEventListener('click', hidePrescriptionModalDirect);
        }
        
        if (cancelBtn) {
            cancelBtn.addEventListener('click', hidePrescriptionModalDirect);
        }
        
        // Click outside to close
        const modal = document.getElementById('prescriptionModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    hidePrescriptionModalDirect();
                }
            });
        }
    });
    </script>
</body>
</html>
