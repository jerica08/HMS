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
    <link rel="stylesheet" href="<?= base_url('assets/css/unified/shift-management.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css">
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
                default => 'admin/components/sidebar.php'
            };
            include APPPATH . 'Views/' . $sidebarPath; 
            ?>
        <?php endif; ?>

        <main class="content" role="main">
            <h1 class="page-title"><?= esc($pageConfig['title']) ?></h1>
            <div class="page-actions">
                <?php if ($permissions['canCreate']): ?>
                    <button type="button" id="createShiftBtn" class="btn btn-primary" aria-label="Create New Shift">
                        <i class="fas fa-plus" aria-hidden="true"></i> Create Shift
                    </button>
                <?php endif; ?>
                <?php if (in_array($userRole ?? '', ['admin', 'it_staff'])): ?>
                    <button type="button" class="btn btn-secondary" id="exportBtn" aria-label="Export Data">
                        <i class="fas fa-download" aria-hidden="true"></i> Export
                    </button>
                <?php endif; ?>
            </div>

            <!-- Statistics Overview -->
            <div class="dashboard-overview">
                <?php if ($userRole === 'admin' || $userRole === 'it_staff'): ?>
                    <!-- Total Shifts Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-calendar-day"></i></div>
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
                    <div class="overview-card">
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
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-calendar-day"></i></div>
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
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern green"><i class="fas fa-calendar-day"></i></div>
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
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-calendar-day"></i></div>
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
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern green"><i class="fas fa-calendar-day"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Schedule Status</h3>
                                <p class="card-subtitle">Current status</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value green"><?= $stats['scheduled_shifts'] ?? 0 ?></div>
                                <div class="metric-label">Scheduled</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value purple"><?= $stats['department'] ? 1 : 0 ?></div>
                                <div class="metric-label">Department</div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- General Shifts Overview -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue"><i class="fas fa-calendar-day"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Shifts Overview</h3>
                                <p class="card-subtitle">General statistics</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $stats['total_shifts'] ?? 0 ?></div>
                                <div class="metric-label">Total</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value orange"><?= $stats['today_shifts'] ?? 0 ?></div>
                                <div class="metric-label">Today</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Schedule Status -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern green"><i class="fas fa-calendar-day"></i></div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Schedule Status</h3>
                                <p class="card-subtitle">Current status</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value green"><?= $stats['scheduled_shifts'] ?? 0 ?></div>
                                <div class="metric-label">Scheduled</div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Filters and View Toggle -->
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
                            <option value="Scheduled">Scheduled</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="departmentFilter">Department:</label>
                        <select id="departmentFilter" class="form-select">
                            <option value="">All Departments</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= esc($dept['department']) ?>"><?= esc($dept['department']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="searchFilter">Search:</label>
                        <input type="text" id="searchFilter" class="form-input" placeholder="Search shifts...">
                    </div>
                    
                    <button type="button" id="clearFilters" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>

                <div class="view-toggle">
                    <button type="button" id="listViewBtn" class="btn btn-outline active">
                        <i class="fas fa-list"></i> List
                    </button>
                    <button type="button" id="calendarViewBtn" class="btn btn-outline">
                        <i class="fas fa-calendar"></i> Calendar
                    </button>
                </div>
            </div>

            <!-- List View -->
            <div id="listView" class="view-content">
                <div class="shifts-table-container">
                    <table class="shifts-table">
                        <thead>
                            <tr>
                                <th>Doctor</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Department</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="shiftsTableBody">
                            <tr>
                                <td colspan="7" class="loading-row">
                                    <i class="fas fa-spinner fa-spin"></i> Loading shifts...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Calendar View -->
            <div id="calendarView" class="view-content" style="display: none;">
                <div id="calendar"></div>
            </div>
        </main>
    </div>

    <!-- Create/Edit Shift Modal -->
    <div id="shiftModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-calendar-plus"></i>
                    <span id="modalTitle">Create Shift</span>
                </h3>
                <button type="button" class="modal-close" id="closeShiftModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="shiftForm" class="modal-form">
                <?= csrf_field() ?>
                <input type="hidden" id="shiftId" name="id">
                
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="doctorSelect" class="form-label">Doctor *</label>
                            <select id="doctorSelect" name="doctor_id" class="form-select" required>
                                <option value="">Select Doctor</option>
                                <?php foreach ($availableStaff as $staff): ?>
                                    <option value="<?= esc($staff['doctor_id']) ?>">
                                        <?= esc($staff['first_name'] . ' ' . $staff['last_name']) ?> 
                                        - <?= esc($staff['department']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="shiftDate" class="form-label">Date *</label>
                            <input type="date" id="shiftDate" name="shift_date" class="form-input" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="shiftStart" class="form-label">Start Time *</label>
                            <input type="time" id="shiftStart" name="shift_start" class="form-input" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="shiftEnd" class="form-label">End Time *</label>
                            <input type="time" id="shiftEnd" name="shift_end" class="form-input" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="shiftDepartment" class="form-label">Department</label>
                            <select id="shiftDepartment" name="department" class="form-select">
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= esc($dept['department']) ?>"><?= esc($dept['department']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="shiftType" class="form-label">Shift Type</label>
                            <select id="shiftType" name="shift_type" class="form-select">
                                <option value="">Select Type</option>
                                <?php foreach ($shiftTypes as $type): ?>
                                    <option value="<?= esc($type['shift_type']) ?>"><?= esc($type['shift_type']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="roomWard" class="form-label">Room/Ward</label>
                            <select id="roomWard" name="room_ward" class="form-select">
                                <option value="">Select Room/Ward</option>
                                <?php foreach ($roomsWards as $room): ?>
                                    <option value="<?= esc($room['room_ward']) ?>"><?= esc($room['room_ward']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="shiftStatus" class="form-label">Status</label>
                            <select id="shiftStatus" name="status" class="form-select">
                                <option value="Scheduled">Scheduled</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="shiftNotes" class="form-label">Notes</label>
                            <textarea id="shiftNotes" name="notes" class="form-textarea" rows="3" placeholder="Optional notes..."></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="cancelShiftBtn">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveShiftBtn">
                        <i class="fas fa-save"></i> Save Shift
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Shift Modal -->
    <div id="viewShiftModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-eye"></i>
                    Shift Details
                </h3>
                <button type="button" class="modal-close" id="closeViewShiftModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="modal-body">
                <div class="shift-details">
                    <div class="detail-section">
                        <h4>Basic Information</h4>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <label>Doctor:</label>
                                <span id="viewDoctorName">-</span>
                            </div>
                            <div class="detail-item">
                                <label>Date:</label>
                                <span id="viewShiftDate">-</span>
                            </div>
                            <div class="detail-item">
                                <label>Time:</label>
                                <span id="viewShiftTime">-</span>
                            </div>
                            <div class="detail-item">
                                <label>Duration:</label>
                                <span id="viewShiftDuration">-</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h4>Assignment Details</h4>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <label>Department:</label>
                                <span id="viewShiftDepartment">-</span>
                            </div>
                            <div class="detail-item">
                                <label>Type:</label>
                                <span id="viewShiftType">-</span>
                            </div>
                            <div class="detail-item">
                                <label>Room/Ward:</label>
                                <span id="viewRoomWard">-</span>
                            </div>
                            <div class="detail-item">
                                <label>Status:</label>
                                <span id="viewShiftStatus" class="status-badge">-</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h4>Notes</h4>
                        <div class="notes-content" id="viewShiftNotes">
                            No notes available
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="closeViewShiftBtn">Close</button>
                <?php if ($permissions['canEdit']): ?>
                <button type="button" class="btn btn-primary" id="editFromViewBtn">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>
    <script src="<?= base_url('assets/js/unified/shift-management.js') ?>"></script>
</body>
</html>
