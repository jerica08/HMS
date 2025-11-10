<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= base_url() ?>">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta name="csrf-hash" content="<?= csrf_hash() ?>">
    <meta name="user-role" content="<?= esc($userRole) ?>">
    <title><?= esc($title ?? 'Shift Management') ?> - HMS</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
     <link rel="stylesheet" href="<?= base_url('assets/css/unified/shift-management.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>

<?php include APPPATH . 'Views/template/header.php'; ?> 
<div class="main-container">
    <!-- Unified Sidebar -->
     <?php include APPPATH . 'Views/unified/components/sidebar.php'; ?>

    <main class="content" role="main">
        <h1 class="page-title">
            <i class="fas fa-calendar-alt"></i>
            <?= esc($title ?? 'Shift Management') ?>
        </h1>
        <div class="page-actions">
            <button type="button" class="btn btn-primary" id="createShiftBtn" aria-label="Create New Shift" onclick="handleAddShiftClick()">
                <i class="fas fa-plus" aria-hidden="true"></i> Add Shift
            </button>
            <?php if (in_array($userRole ?? '', ['admin', 'it_staff'])): ?>
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

        <div class="dashboard-overview" role="region" aria-label="Dashboard Overview Cards">
                <?php if ($userRole === 'admin' || $userRole === 'it_staff'): ?>
                    <!-- Total Shifts Card -->
                    <div class="overview-card" tabindex="0">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-calendar-alt"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Total Shifts</h3>
                                <p class="card-subtitle">All scheduled shifts</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $stats['total_shifts'] ?? 0 ?></div>
                                <div class="metric-label">Total</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value green"><?= $stats['scheduled_shifts'] ?? 0 ?></div>
                                <div class="metric-label">Scheduled</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Today's Shifts Card -->
                    <div class="overview-card" tabindex="0">
                        <div class="card-header-modern">
                            <div class="card-icon-modern orange"><i class="fas fa-calendar-day"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Today's Shifts</h3>
                                <p class="card-subtitle"><?= date('F j, Y') ?></p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value orange"><?= $stats['today_shifts'] ?? 0 ?></div>
                                <div class="metric-label">Today</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value purple"><?= $stats['active_doctors'] ?? 0 ?></div>
                                <div class="metric-label">Active Doctors</div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($userRole === 'doctor'): ?>
                    <!-- My Shifts Card -->
                    <div class="overview-card" tabindex="0">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-user-clock"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">My Shifts</h3>
                                <p class="card-subtitle">Personal schedule</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $stats['my_shifts'] ?? 0 ?></div>
                                <div class="metric-label">Total</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value orange"><?= $stats['today_shifts'] ?? 0 ?></div>
                                <div class="metric-label">Today</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Weekly Overview Card -->
                    <div class="overview-card" tabindex="0">
                        <div class="card-header-modern">
                            <div class="card-icon-modern green"><i class="fas fa-calendar-week"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Weekly Overview</h3>
                                <p class="card-subtitle">This week's schedule</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value green"><?= $stats['week_shifts'] ?? 0 ?></div>
                                <div class="metric-label">This Week</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value purple"><?= $stats['upcoming_shifts'] ?? 0 ?></div>
                                <div class="metric-label">Upcoming</div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($userRole === 'nurse'): ?>
                    <!-- Department Shifts Card -->
                    <div class="overview-card" tabindex="0">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-hospital"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Department Shifts</h3>
                                <p class="card-subtitle"><?= esc($stats['department'] ?? 'Your department') ?></p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $stats['department_shifts'] ?? 0 ?></div>
                                <div class="metric-label">Total</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value orange"><?= $stats['today_shifts'] ?? 0 ?></div>
                                <div class="metric-label">Today</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Schedule Status Card -->
                    <div class="overview-card" tabindex="0">
                        <div class="card-header-modern">
                            <div class="card-icon-modern green"><i class="fas fa-chart-line"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Schedule Status</h3>
                                <p class="card-subtitle">Current status</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue">0</div>
                                <div class="metric-label">Total</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value green">0</div>
                                <div class="metric-label">Scheduled</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value purple">0</div>
                                <div class="metric-label">Department</div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- General Shifts Overview -->
                    <div class="overview-card" tabindex="0">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-clock"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Shifts Overview</h3>
                                <p class="card-subtitle">General statistics</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue">0</div>
                                <div class="metric-label">Total</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value orange">0</div>
                                <div class="metric-label">Today</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Schedule Status -->
                    <div class="overview-card" tabindex="0">
                        <div class="card-header-modern">
                            <div class="card-icon-modern green"><i class="fas fa-tasks"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Schedule Status</h3>
                                <p class="card-subtitle">Current status</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value green">0</div>
                                <div class="metric-label">Scheduled</div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
        </div>

        <div class="shift-table-container">
                <div class="table-header">
                    <h3>Shifts</h3>
                </div>
                <div class="table-responsive">
                    <table class="table" id="shiftsTable" aria-describedby="shiftsTableCaption">
                        <thead>
                            <tr>
                                <th scope="col">Doctor</th>
                                <th scope="col">Date</th>
                                <th scope="col">Time</th>
                                <th scope="col">Department</th>
                                <th scope="col">Type</th>
                                <th scope="col">Status</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="shiftsTableBody">
                        </tbody>
                    </table>
                </div>
            </div>  

    </main>
</div>

<!-- Add Shift Modal -->
<?= $this->include('unified/modals/add-shift-modal', [
    'availableStaff' => $availableStaff ?? [],  
    'departments' => $departments ?? [],
    'shiftTypes' => $shiftTypes ?? [],
    'roomsWards' => $roomsWards ?? []
]) ?>

<!-- Simple Test Modal -->
<div id="testModal" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.5); z-index: 9999;">
    <div style="background: white; padding: 2rem; border-radius: 8px; margin: auto;">
        <h3>Test Modal Works!</h3>
        <p>This is a simple test to verify modal functionality.</p>
        <button onclick="closeTestModal()" style="background: #dc3545; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer;">Close</button>
    </div>
</div>

<!-- Shift Management Scripts -->
<script>
// Immediately clear any shifts and show empty state
document.addEventListener('DOMContentLoaded', function() {
    console.log('Clearing shifts table...');
    const tbody = document.getElementById('shiftsTableBody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align: center; padding: 2rem;">
                    <i class="fas fa-calendar-times" style="font-size: 2rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                    <p style="color: #6b7280;">No shifts found</p>
                </td>
            </tr>
        `;
        console.log('Table cleared and showing no shifts message');
    }
    
    // Continuously clear table every 500ms to prevent any data from loading
    setInterval(() => {
        const tbody = document.getElementById('shiftsTableBody');
        if (tbody && tbody.innerHTML.trim() !== '' && !tbody.innerHTML.includes('No shifts found')) {
            console.log('Detected unwanted shifts data, clearing...');
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem;">
                        <i class="fas fa-calendar-times" style="font-size: 2rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                        <p style="color: #6b7280;">No shifts found</p>
                    </td>
                </tr>
            `;
        }
    }, 500);
});

// Pass initial data to JavaScript
window.initialShifts = []; // Clear example data
window.userRole = <?= json_encode($userRole ?? 'admin') ?>;

function dismissFlash() {
    const flashNotice = document.getElementById('flashNotice');
    if (flashNotice) {
        flashNotice.style.display = 'none';
    }
}

// Direct onclick handler for Add Shift button
window.handleAddShiftClick = function() {
    console.log('handleAddShiftClick called directly');
    const modal = document.getElementById('shiftModal');
    console.log('Modal element found:', !!modal);
    
    if (modal) {
        // Reset form and show modal
        const form = document.getElementById('shiftForm');
        if (form) {
            form.reset();
            const idField = document.getElementById('shiftId');
            if (idField) {
                idField.value = '';
            }
        }
        
        // Load doctors from database
        loadDoctors();
        
        // Show modal using the same approach that works
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
        
        console.log('Modal should be visible now via direct click');
    } else {
        console.error('Modal not found!');
    }
};

// Load doctors from database
window.loadDoctors = function() {
    console.log('Loading doctors from database...');
    const doctorSelect = document.getElementById('doctorSelect');
    if (!doctorSelect) {
        console.error('Doctor select not found');
        return;
    }
    
    // Show loading state
    doctorSelect.innerHTML = '<option value="">Loading doctors...</option>';
    
    // Try different possible API endpoints
    const possibleEndpoints = [
        `${getBaseUrl()}doctors/api`,
        `${getBaseUrl()}api/doctors`,
        `${getBaseUrl()}staff/api`,
        `${getBaseUrl()}doctors`,
        `${getBaseUrl()}unified/doctors/api`
    ];
    
    // Try each endpoint until one works
    let endpointIndex = 0;
    
    function tryNextEndpoint() {
        if (endpointIndex >= possibleEndpoints.length) {
            // All endpoints failed, show error
            doctorSelect.innerHTML = '<option value="">No doctors available</option>';
            console.error('All endpoints failed');
            return;
        }
        
        const endpoint = possibleEndpoints[endpointIndex];
        console.log(`Trying endpoint: ${endpoint}`);
        
        fetch(endpoint, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log(`Response from ${endpoint}:`, response.status);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
        .then(result => {
            console.log(`Success from ${endpoint}:`, result);
            
            if (result && (result.data || result.doctors || result.length > 0)) {
                // We got data! Use this endpoint
                const doctors = result.data || result.doctors || result;
                
                // Clear and populate doctor dropdown
                doctorSelect.innerHTML = '<option value="">Select Doctor</option>';
                
                doctors.forEach(doctor => {
                    const option = document.createElement('option');
                    option.value = doctor.id || doctor.doctor_id || doctor.user_id;
                    option.textContent = `${doctor.first_name || doctor.fname} ${doctor.last_name || doctor.lname}${doctor.specialization ? ' - ' + doctor.specialization : ''}`;
                    doctorSelect.appendChild(option);
                });
                
                console.log('Doctors loaded successfully:', doctors.length);
            } else {
                // Try next endpoint
                endpointIndex++;
                tryNextEndpoint();
            }
        })
        .catch(error => {
            console.log(`Failed ${endpoint}:`, error);
            // Try next endpoint
            endpointIndex++;
            tryNextEndpoint();
        });
    }
    
    tryNextEndpoint();
};

// Helper function to get base URL
window.getBaseUrl = function() {
    const basePath = window.location.pathname.substring(0, window.location.pathname.indexOf('/', 1));
    return window.location.origin + basePath + '/';
};

// Direct close function for Add Shift modal
window.closeAddShiftModal = function() {
    console.log('closeAddShiftModal called directly');
    const modal = document.getElementById('shiftModal');
    if (modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
        document.body.style.overflow = '';
        console.log('Modal closed via direct function');
    } else {
        console.error('Modal not found for closing!');
    }
};

// Test function to check if modal works
window.testModalDirect = function() {
    console.log('Direct test called');
    const modal = document.getElementById('shiftModal');
    if (modal) {
        modal.classList.add('active');
        modal.style.setProperty('display', 'flex', 'important');
        modal.style.setProperty('position', 'fixed', 'important');
        modal.style.setProperty('top', '0', 'important');
        modal.style.setProperty('left', '0', 'important');
        modal.style.setProperty('width', '100vw', 'important');
        modal.style.setProperty('height', '100vh', 'important');
        modal.style.setProperty('z-index', '9999', 'important');
        document.body.style.overflow = 'hidden';
        console.log('Modal should be visible now');
    } else {
        console.error('Modal not found!');
    }
};

// Simple test modal function
window.showSimpleTest = function() {
    console.log('Simple test called');
    const testModal = document.getElementById('testModal');
    if (testModal) {
        testModal.style.display = 'flex';
        testModal.style.alignItems = 'center';
        testModal.style.justifyContent = 'center';
        document.body.style.overflow = 'hidden';
        console.log('Simple test modal should be visible');
    } else {
        console.error('Test modal not found!');
    }
};

window.closeTestModal = function() {
    const testModal = document.getElementById('testModal');
    if (testModal) {
        testModal.style.display = 'none';
        document.body.style.overflow = '';
    }
};

// Check if button exists on page load
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        const createBtn = document.getElementById('createShiftBtn');
        console.log('Button check after delay - Create shift button found:', !!createBtn);
        if (!createBtn) {
            console.warn('Add Shift button not found - permissions issue?');
        }
    }, 1000);
});
</script>
<script src="<?= base_url('assets/js/unified/shift-management.js') ?>"></script>
</body>
</html>
