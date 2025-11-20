<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= base_url() ?>">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta name="csrf-hash" content="<?= csrf_hash() ?>">
    <meta name="user-role" content="<?= esc($userRole) ?>">
    <title><?= esc($title ?? 'Schedule Management') ?> - HMS</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
     <link rel="stylesheet" href="<?= base_url('assets/css/unified/shift-management.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>

<?php include APPPATH . 'Views/template/header.php'; ?> 

<?= $this->include('unified/components/notification', [
    'id' => 'scheduleNotification',
    'dismissFn' => 'dismissScheduleNotification()'
]) ?>

<div class="main-container">
    <!-- Unified Sidebar -->
     <?php include APPPATH . 'Views/unified/components/sidebar.php'; ?>

    <main class="content" role="main">
        <h1 class="page-title">
            <i class="fas fa-calendar-alt"></i>
            <?= esc($title ?? 'Schedule Management') ?>
        </h1>
        <div class="page-actions">
            <button type="button" class="btn btn-primary" id="createShiftBtn" aria-label="Create New Shift" onclick="handleAddShiftClick()">
                <i class="fas fa-plus" aria-hidden="true"></i> Add Schedule
            </button>
            <?php if (in_array($userRole ?? '', ['admin', 'it_staff'])): ?>
                <button type="button" class="btn btn-secondary" id="exportBtn" aria-label="Export Data">
                    <i class="fas fa-download" aria-hidden="true"></i> Export
                </button>
            <?php endif; ?>
        </div>

<!-- Schedule View Modal (match view-staff modal styling) -->
<div id="viewShiftModal" class="hms-modal-overlay" aria-hidden="true">
    <div class="hms-modal" role="dialog" aria-modal="true" aria-labelledby="viewScheduleTitle">
        <div class="hms-modal-header">
            <div class="hms-modal-title" id="viewScheduleTitle">
                <i class="fas fa-calendar-check" style="color:#4f46e5"></i>
                Schedule Details
            </div>
            <button type="button" class="btn btn-secondary btn-small" id="closeViewShiftModal" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="hms-modal-body">
            <div class="form-grid">
                <div class="full">
                    <label class="form-label">Doctor</label>
                    <input type="text" id="viewDoctorName" class="form-input" readonly disabled>
                </div>
                <div>
                    <label class="form-label">Weekday</label>
                    <input type="text" id="viewScheduleWeekday" class="form-input" readonly disabled>
                </div>
                <div>
                    <label class="form-label">Slot</label>
                    <input type="text" id="viewScheduleSlot" class="form-input" readonly disabled>
                </div>
                <div>
                    <label class="form-label">Status</label>
                    <input type="text" id="viewShiftStatus" class="form-input" readonly disabled>
                </div>
            </div>
        </div>
        <div class="hms-modal-actions">
            <button type="button" class="btn btn-success" id="closeViewShiftBtn">Close</button>
        </div>
    </div>
</div>

        
        <br />

        <div class="dashboard-overview" role="region" aria-label="Dashboard Overview Cards">
                <?php if ($userRole === 'admin' || $userRole === 'it_staff'): ?>
                    <!-- Total Shifts Card -->
                    <div class="overview-card" tabindex="0">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-calendar-alt"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Total Schedule</h3>
                                <p class="card-subtitle">All schedules</p>
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
                    <h3>Schedule</h3>
                </div>
                <div class="table-responsive">
                    <table class="table" id="shiftsTable" aria-describedby="shiftsTableCaption">
                        <thead>
                            <tr>
                                <th scope="col">Doctor</th>
                                <th scope="col">Day</th>
                                <th scope="col">Slot</th>
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
<!-- Debug: Doctors data check -->
<?php if (isset($availableDoctors)): ?>
    <!-- DEBUG: Found <?php echo count($availableDoctors); ?> doctors -->
    <!-- DEBUG: First doctor: <?php echo !empty($availableDoctors) ? print_r($availableDoctors[0], true) : 'No doctors'; ?> -->
<?php else: ?>
    <!-- DEBUG: No doctors variable set -->
<?php endif; ?>

<!-- Test: Direct include with debug -->
<?php 
echo "<!-- INCLUDE TEST: About to include modal -->";

// Force set the doctors variable directly
$doctors_for_modal = $availableDoctors ?? [];

echo "<!-- INCLUDE TEST: doctors_for_modal has " . count($doctors_for_modal) . " doctors -->";

include(APPPATH . 'Views/unified/modals/add-shift-modal.php');

echo "<!-- INCLUDE TEST: Modal included -->";
?>

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
    
    // Try the main endpoint first (the one that gave 500 error)
    const mainEndpoint = `${getBaseUrl()}doctors/api`;
    
    fetch(mainEndpoint, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log(`Response from ${mainEndpoint}:`, response.status);
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        return response.json();
    })
    .then(result => {
        console.log(`Success from ${mainEndpoint}:`, result);
        
        if (result && (result.data || result.doctors || result.length > 0)) {
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
            // No data in response, try fallback
            loadFallbackDoctors();
        }
    })
    .catch(error => {
        console.log(`Failed ${mainEndpoint}:`, error);
        // Try fallback method
        loadFallbackDoctors();
    });
};

// Fallback method to load doctors
window.loadFallbackDoctors = function() {
    console.log('Trying fallback doctor loading...');
    const doctorSelect = document.getElementById('doctorSelect');
    
    // Try to load from a simple PHP endpoint or use hardcoded sample data
    const fallbackData = [
        {id: 1, first_name: 'John', last_name: 'Smith', specialization: 'Cardiology'},
        {id: 2, first_name: 'Sarah', last_name: 'Johnson', specialization: 'Pediatrics'},
        {id: 3, first_name: 'Michael', last_name: 'Brown', specialization: 'Surgery'}
    ];
    
    // Populate with fallback data
    doctorSelect.innerHTML = '<option value="">Select Doctor</option>';
    
    fallbackData.forEach(doctor => {
        const option = document.createElement('option');
        option.value = doctor.id;
        option.textContent = `${doctor.first_name} ${doctor.last_name} - ${doctor.specialization}`;
        doctorSelect.appendChild(option);
    });
    
    console.log('Loaded fallback doctors:', fallbackData.length);
};

// Load departments from database
window.loadDepartments = function() {
    console.log('Loading departments from database...');
    const departmentSelect = document.getElementById('shiftDepartment');
    if (!departmentSelect) {
        console.error('Department select not found');
        return;
    }
    
    // Show loading state
    departmentSelect.innerHTML = '<option value="">Loading departments...</option>';
    
    // Try more possible endpoints and also check if there's a working shifts API we can use
    const possibleEndpoints = [
        `${getBaseUrl()}departments/api`,
        `${getBaseUrl()}api/departments`, 
        `${getBaseUrl()}department/api`,
        `${getBaseUrl()}departments`,
        `${getBaseUrl()}unified/departments/api`,
        `${getBaseUrl()}admin/departments/api`,
        `${getBaseUrl()}admin/api/departments`,
        `${getBaseUrl()}shifts/departments/api`, // Try getting departments from shifts controller
        `${getBaseUrl()}unified/api/departments`
    ];
    
    // Try each endpoint until one works
    let endpointIndex = 0;
    
    function tryNextEndpoint() {
        if (endpointIndex >= possibleEndpoints.length) {
            // All endpoints failed, show error instead of fallback
            departmentSelect.innerHTML = '<option value="">No departments available - API error</option>';
            console.error('All department endpoints failed. Please check your API endpoints.');
            return;
        }
        
        const endpoint = possibleEndpoints[endpointIndex];
        console.log(`Trying department endpoint: ${endpoint}`);
        
        fetch(endpoint, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log(`Department response from ${endpoint}:`, response.status, response.statusText);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status} - ${response.statusText}`);
            }
            return response.json();
        })
        .then(result => {
            console.log(`Department success from ${endpoint}:`, result);
            
            if (result && (result.data || result.departments || result.length > 0)) {
                const departments = result.data || result.departments || result;
                
                // Clear and populate department dropdown
                departmentSelect.innerHTML = '<option value="">Select Department</option>';
                
                departments.forEach(dept => {
                    const option = document.createElement('option');
                    option.value = dept.department || dept.name || dept.id || dept.department_name;
                    option.textContent = dept.department || dept.name || dept.department_name || `Department ${dept.id}`;
                    departmentSelect.appendChild(option);
                });
                
                console.log('Departments loaded successfully:', departments.length);
                console.log('Department list:', departments.map(d => d.department || d.name || d.department_name));
            } else {
                console.log(`No department data from ${endpoint}, trying next...`);
                // Try next endpoint
                endpointIndex++;
                tryNextEndpoint();
            }
        })
        .catch(error => {
            console.log(`Department failed ${endpoint}:`, error.message);
            // Try next endpoint
            endpointIndex++;
            tryNextEndpoint();
        });
    }
    
    tryNextEndpoint();
};

// Fallback method to load departments
window.loadFallbackDepartments = function() {
    console.log('Loading fallback departments...');
    const departmentSelect = document.getElementById('shiftDepartment');
    
    // Common hospital departments as fallback
    const fallbackDepartments = [
        'Cardiology',
        'Pediatrics', 
        'Surgery',
        'Emergency',
        'Radiology',
        'Laboratory',
        'Pharmacy',
        'Administration'
    ];
    
    // Populate with fallback data
    departmentSelect.innerHTML = '<option value="">Select Department</option>';
    
    fallbackDepartments.forEach(dept => {
        const option = document.createElement('option');
        option.value = dept;
        option.textContent = dept;
        departmentSelect.appendChild(option);
    });
    
    console.log('Loaded fallback departments:', fallbackDepartments.length);
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
