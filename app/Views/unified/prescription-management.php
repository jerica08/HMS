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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="<?= esc($userRole) ?>">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <?php if ($pageConfig['showSidebar']): ?>
            <?php 
            $sidebarPath = match($pageConfig['sidebarType']) {
                'admin' => 'admin/components/sidebar.php',
                'doctor' => 'doctor/components/sidebar.php',
                'nurse' => 'nurse/components/sidebar.php',
                'receptionist' => 'receptionist/components/sidebar.php',
                'pharmacist' => 'pharmacist/components/sidebar.php',
                default => 'admin/components/sidebar.php'
            };
            include APPPATH . 'Views/' . $sidebarPath; 
            ?>
        <?php endif; ?>

        <main class="content" role="main">
            <h1 class="page-title"><?= esc($pageConfig['title']) ?></h1>
            <div class="page-actions">
                <?php if ($permissions['canCreate']): ?>
                    <button type="button" id="createPrescriptionBtn" class="btn btn-primary" aria-label="Create New Prescription">
                        <i class="fas fa-plus" aria-hidden="true"></i> Add Prescription
                    </button>
                <?php endif; ?>
                <?php if (in_array($userRole ?? '', ['admin', 'it_staff', 'pharmacist'])): ?>
                    <button type="button" class="btn btn-secondary" id="exportBtn" aria-label="Export Data">
                        <i class="fas fa-download" aria-hidden="true"></i> Export
                    </button>
                <?php endif; ?>
            </div>

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

    <!-- Create/Edit Prescription Modal -->
    <div id="prescriptionModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-prescription-bottle"></i>
                    <span id="modalTitle">Create Prescription</span>
                </h3>
                <button type="button" class="modal-close" id="closePrescriptionModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="prescriptionForm" class="modal-form">
                <?= csrf_field() ?>
                <input type="hidden" id="prescriptionId" name="id">
                
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="patientSelect" class="form-label">Patient *</label>
                            <select id="patientSelect" name="patient_id" class="form-select" required>
                                <option value="">Select Patient</option>
                                <?php foreach ($availablePatients as $patient): ?>
                                    <option value="<?= esc($patient['patient_id']) ?>">
                                        <?= esc($patient['first_name'] . ' ' . $patient['last_name']) ?> 
                                        (ID: <?= esc($patient['patient_id']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="prescriptionDate" class="form-label">Date Issued *</label>
                            <input type="date" id="prescriptionDate" name="date_issued" class="form-input" required value="<?= date('Y-m-d') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="medication" class="form-label">Medication *</label>
                            <input type="text" id="medication" name="medication" class="form-input" required placeholder="Enter medication name">
                        </div>
                        
                        <div class="form-group">
                            <label for="dosage" class="form-label">Dosage *</label>
                            <input type="text" id="dosage" name="dosage" class="form-input" required placeholder="e.g., 500mg">
                        </div>
                        
                        <div class="form-group">
                            <label for="frequency" class="form-label">Frequency *</label>
                            <input type="text" id="frequency" name="frequency" class="form-input" required placeholder="e.g., Twice daily">
                        </div>
                        
                        <div class="form-group">
                            <label for="duration" class="form-label">Duration *</label>
                            <input type="text" id="duration" name="duration" class="form-input" required placeholder="e.g., 7 days">
                        </div>
                        
                        <div class="form-group">
                            <label for="prescriptionStatus" class="form-label">Status</label>
                            <select id="prescriptionStatus" name="status" class="form-select">
                                <?php foreach ($statuses as $status): ?>
                                    <option value="<?= esc($status['status']) ?>" <?= $status['status'] === 'active' ? 'selected' : '' ?>>
                                        <?= esc(ucfirst($status['status'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="prescriptionNotes" class="form-label">Notes</label>
                            <textarea id="prescriptionNotes" name="notes" class="form-textarea" rows="3" placeholder="Additional instructions or notes..."></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="cancelPrescriptionBtn">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="savePrescriptionBtn">
                        <i class="fas fa-save"></i> Save Prescription
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Prescription Modal -->
    <div id="viewPrescriptionModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-eye"></i>
                    Prescription Details
                </h3>
                <button type="button" class="modal-close" id="closeViewPrescriptionModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="modal-body">
                <div class="prescription-details">
                    <div class="detail-section">
                        <h4>Prescription Information</h4>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <label>Prescription ID:</label>
                                <span id="viewPrescriptionId">-</span>
                            </div>
                            <div class="detail-item">
                                <label>Date Issued:</label>
                                <span id="viewPrescriptionDate">-</span>
                            </div>
                            <div class="detail-item">
                                <label>Status:</label>
                                <span id="viewPrescriptionStatus" class="status-badge">-</span>
                            </div>
                            <div class="detail-item">
                                <label>Doctor:</label>
                                <span id="viewDoctorName">-</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h4>Patient Information</h4>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <label>Patient Name:</label>
                                <span id="viewPatientName">-</span>
                            </div>
                            <div class="detail-item">
                                <label>Patient ID:</label>
                                <span id="viewPatientId">-</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h4>Medication Details</h4>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <label>Medication:</label>
                                <span id="viewMedication">-</span>
                            </div>
                            <div class="detail-item">
                                <label>Dosage:</label>
                                <span id="viewDosage">-</span>
                            </div>
                            <div class="detail-item">
                                <label>Frequency:</label>
                                <span id="viewFrequency">-</span>
                            </div>
                            <div class="detail-item">
                                <label>Duration:</label>
                                <span id="viewDuration">-</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h4>Notes</h4>
                        <div class="notes-content" id="viewPrescriptionNotes">
                            No notes available
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="closeViewPrescriptionBtn">Close</button>
                <?php if ($permissions['canEdit']): ?>
                <button type="button" class="btn btn-primary" id="editFromViewBtn">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="<?= base_url('assets/js/unified/prescription-management.js') ?>"></script>
</body>
</html>
