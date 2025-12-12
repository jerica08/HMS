<?php
<<<<<<< HEAD
use App\Libraries\PermissionManager;

=======
>>>>>>> 03d4e70 (COMMITenter the commit message for your changes. Lines starting)
// Get user role and permissions
$userRole = session()->get('role') ?? 'guest';
$currentUri = uri_string();

<<<<<<< HEAD
// Define module-based navigation items with permission checks
$navigationItems = [];

// Dashboard - always available for authenticated users
$dashboardUrl = $userRole . '/dashboard';
if ($userRole === 'it_staff') {
    $dashboardUrl = 'it-staff/dashboard';
}
$navigationItems[] = ['url' => $dashboardUrl, 'icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard', 'module' => null];

// Define module mappings with their URLs and icons
$moduleMappings = [
    'patients' => [
        'admin' => ['url' => 'admin/patient-management', 'icon' => 'fas fa-user-injured', 'label' => 'Patient Management'],
        'doctor' => ['url' => 'doctor/patient-management', 'icon' => 'fas fa-user-injured', 'label' => 'Patient Management'],
        'nurse' => ['url' => 'nurse/patients', 'icon' => 'fas fa-users', 'label' => 'Patients'],
        'receptionist' => ['url' => 'receptionist/patients', 'icon' => 'fas fa-users', 'label' => 'Patient Registration'],
        'pharmacist' => ['url' => 'unified/patient-records', 'icon' => 'fas fa-folder-open', 'label' => 'Patient Records'],
        'laboratorist' => ['url' => 'unified/patient-records', 'icon' => 'fas fa-folder-open', 'label' => 'Patient Records'],
        'accountant' => ['url' => 'unified/patient-records', 'icon' => 'fas fa-folder-open', 'label' => 'Patient Records'],
        'it_staff' => ['url' => 'it-staff/patients', 'icon' => 'fas fa-user-injured', 'label' => 'Patient Management'],
    ],
    'appointments' => [
        'admin' => ['url' => 'admin/appointments', 'icon' => 'fas fa-calendar-check', 'label' => 'Appointments'],
        'doctor' => ['url' => 'doctor/appointments', 'icon' => 'fas fa-calendar-check', 'label' => 'Appointments'],
        'receptionist' => ['url' => 'receptionist/appointments', 'icon' => 'fas fa-calendar-alt', 'label' => 'Appointments'],
        'accountant' => ['url' => 'accountant/appointments', 'icon' => 'fas fa-calendar-check', 'label' => 'Appointments'],
    ],
    'staff' => [
        'admin' => ['url' => 'admin/staff-management', 'icon' => 'fas fa-user-tie', 'label' => 'Staff Management'],
        'it_staff' => ['url' => 'it-staff/staff', 'icon' => 'fas fa-user-tie', 'label' => 'Staff Management'],
    ],
    'users' => [
        'admin' => ['url' => 'admin/user-management', 'icon' => 'fas fa-users', 'label' => 'User Management'],
        'it_staff' => ['url' => 'it-staff/users', 'icon' => 'fas fa-users', 'label' => 'User Management'],
    ],
    'resources' => [
        'admin' => ['url' => 'admin/resource-management', 'icon' => 'fas fa-hospital', 'label' => 'Resource Management'],
        'doctor' => ['url' => 'admin/resource-management', 'icon' => 'fas fa-hospital', 'label' => 'Resources'],
        'pharmacist' => ['url' => 'admin/resource-management', 'icon' => 'fas fa-hospital', 'label' => 'Resources'],
        'laboratorist' => ['url' => 'admin/resource-management', 'icon' => 'fas fa-hospital', 'label' => 'Resources'],
        'it_staff' => ['url' => 'admin/resource-management', 'icon' => 'fas fa-hospital', 'label' => 'Resource Management'],
    ],
    'shifts' => [
        'admin' => ['url' => 'admin/schedule', 'icon' => 'fas fa-calendar-days', 'label' => 'Schedule Management'],
        'doctor' => ['url' => 'doctor/schedule', 'icon' => 'fas fa-calendar-days', 'label' => 'My Schedule'],
        
    ],
    'prescriptions' => [
        'admin' => ['url' => 'admin/prescriptions', 'icon' => 'fas fa-prescription-bottle', 'label' => 'Prescriptions'],
        'doctor' => ['url' => 'doctor/prescriptions', 'icon' => 'fas fa-prescription-bottle', 'label' => 'Prescriptions'],
        'pharmacist' => ['url' => 'pharmacist/prescriptions', 'icon' => 'fas fa-prescription-bottle', 'label' => 'Prescriptions'],
    ],
    'reports' => [
        'admin' => ['url' => 'admin/analytics', 'icon' => 'fas fa-chart-bar', 'label' => 'Analytics & Reports'],
        'accountant' => ['url' => 'accountant/analytics', 'icon' => 'fas fa-chart-bar', 'label' => 'Analytics'],
        'laboratorist' => ['url' => 'laboratorist/reports', 'icon' => 'fas fa-chart-bar', 'label' => 'Reports'],
        'pharmacist' => ['url' => 'pharmacist/reports', 'icon' => 'fas fa-chart-bar', 'label' => 'Reports'],
        'it_staff' => ['url' => 'admin/analytics', 'icon' => 'fas fa-chart-bar', 'label' => 'Analytics & Reports'],
    ],
    'billing' => [
        'accountant' => ['url' => 'accountant/billing', 'icon' => 'fas fa-file-invoice-dollar', 'label' => 'Billing Management'],
        'admin' => ['url' => 'admin/financial-management', 'icon' => 'fas fa-dollar-sign', 'label' => 'Billing Management'],
    ],
    'system' => [
        'admin' => ['url' => 'admin/system-settings', 'icon' => 'fas fa-cogs', 'label' => 'System Settings'],
        'it_staff' => ['url' => 'it-staff/system-settings', 'icon' => 'fas fa-cogs', 'label' => 'System Settings'],
    ],
];

// Additional modules that may not be in PermissionManager but are in the system
$additionalModules = [
    'labs' => [
        'admin' => ['url' => 'admin/labs', 'icon' => 'fas fa-flask', 'label' => 'Labs'],
        'doctor' => ['url' => 'doctor/labs', 'icon' => 'fas fa-flask', 'label' => 'Lab Orders'],
        'laboratorist' => ['url' => 'laboratorist/labs', 'icon' => 'fas fa-flask', 'label' => 'Lab Worklist'],
    ],
    'departments' => [
        'admin' => ['url' => 'admin/department-management', 'icon' => 'fas fa-building', 'label' => 'Department Management'],
    ],
    'rooms' => [
        'admin' => ['url' => 'admin/room-management', 'icon' => 'fas fa-bed', 'label' => 'Room Management'],
    ],
    'inventory' => [
        'pharmacist' => ['url' => 'pharmacist/inventory', 'icon' => 'fas fa-pills', 'label' => 'Pharmacy Inventory'],
    ],
    'financial' => [
        'accountant' => ['url' => 'accountant/financial', 'icon' => 'fas fa-dollar-sign', 'label' => 'Financial Reports'],
    ],
    'results' => [
        'laboratorist' => ['url' => 'laboratorist/results', 'icon' => 'fas fa-file-medical-alt', 'label' => 'Test Results'],
    ],
    'patient_records' => [
        'admin' => ['url' => 'unified/patient-records', 'icon' => 'fas fa-folder-open', 'label' => 'Patient Records'],
        'doctor' => ['url' => 'unified/patient-records', 'icon' => 'fas fa-folder-open', 'label' => 'Patient Records (EHR)'],
        'nurse' => ['url' => 'unified/patient-records', 'icon' => 'fas fa-folder-open', 'label' => 'Patient Records'],
    ],
];

// Check permissions and build navigation items
foreach ($moduleMappings as $module => $roleMappings) {
    // For nurses, exclude 'view_assigned' from permission checks
    // For other roles, include all view permissions
    // For vital_signs, check for create permission instead
    if ($module === 'vital_signs') {
        $permissionActions = ['create'];
    } elseif ($userRole === 'nurse') {
        // For nurses, only check for 'view' permission, NOT 'view_assigned'
        $permissionActions = ['view', 'view_all', 'view_own'];
    } else {
        $permissionActions = ['view', 'view_all', 'view_assigned', 'view_own'];
    }
    
    if (PermissionManager::hasAnyPermission($userRole, $module, $permissionActions)) {
        if (isset($roleMappings[$userRole])) {
            $navigationItems[] = array_merge($roleMappings[$userRole], ['module' => $module]);
        }
    }
}

// Add additional modules (these may not have explicit permissions in PermissionManager)
// Labs - available if user has patients or prescriptions permission (nurses excluded from prescriptions check)
$labsPermissionActions = ($userRole === 'nurse') ? ['view', 'view_all', 'view_own'] : ['view', 'view_all', 'view_assigned', 'view_own'];
if ($userRole === 'nurse') {
    // Nurses can access labs if they have patients permission (no prescriptions check)
    if (PermissionManager::hasAnyPermission($userRole, 'patients', $labsPermissionActions)) {
        if (isset($additionalModules['labs'][$userRole])) {
            $navigationItems[] = array_merge($additionalModules['labs'][$userRole], ['module' => 'labs']);
        }
    }
} else {
    // Other roles can access labs if they have patients or prescriptions permission
    if (PermissionManager::hasAnyPermission($userRole, 'patients', $labsPermissionActions) || 
        PermissionManager::hasAnyPermission($userRole, 'prescriptions', $labsPermissionActions)) {
        if (isset($additionalModules['labs'][$userRole])) {
            $navigationItems[] = array_merge($additionalModules['labs'][$userRole], ['module' => 'labs']);
        }
    }
}

// Departments - typically admin only, but check if user has staff or resources permission
$deptPermissionActions = ($userRole === 'nurse') ? ['view', 'view_all'] : ['view', 'view_all', 'view_assigned'];
if (PermissionManager::hasAnyPermission($userRole, 'staff', $deptPermissionActions) || 
    PermissionManager::hasAnyPermission($userRole, 'resources', $deptPermissionActions)) {
    if (isset($additionalModules['departments'][$userRole])) {
        $navigationItems[] = array_merge($additionalModules['departments'][$userRole], ['module' => 'departments']);
    }
}

// Rooms - typically admin only, but check if user has resources permission
$roomsPermissionActions = ($userRole === 'nurse') ? ['view', 'view_all'] : ['view', 'view_all', 'view_assigned'];
if (PermissionManager::hasAnyPermission($userRole, 'resources', $roomsPermissionActions)) {
    if (isset($additionalModules['rooms'][$userRole])) {
        $navigationItems[] = array_merge($additionalModules['rooms'][$userRole], ['module' => 'rooms']);
    }
}

// Inventory - for pharmacists (they have prescriptions permission)
$inventoryPermissionActions = ($userRole === 'nurse') ? ['view', 'view_all', 'fulfill'] : ['view', 'view_all', 'view_assigned', 'fulfill'];
if (PermissionManager::hasAnyPermission($userRole, 'prescriptions', $inventoryPermissionActions)) {
    if (isset($additionalModules['inventory'][$userRole])) {
        $navigationItems[] = array_merge($additionalModules['inventory'][$userRole], ['module' => 'inventory']);
    }
}

// Financial reports - for accountants (they have billing permission)
if (PermissionManager::hasAnyPermission($userRole, 'billing', ['view', 'create', 'edit', 'process'])) {
    if (isset($additionalModules['financial'][$userRole])) {
        $navigationItems[] = array_merge($additionalModules['financial'][$userRole], ['module' => 'financial']);
    }
}

// Lab results - for laboratorists (they have reports permission)
if (PermissionManager::hasAnyPermission($userRole, 'reports', ['view', 'generate'])) {
    if (isset($additionalModules['results'][$userRole])) {
        $navigationItems[] = array_merge($additionalModules['results'][$userRole], ['module' => 'results']);
    }
}

// Patient Records - available if user has patients permission (excluding view_assigned for nurses)
$patientRecordsPermissionActions = ($userRole === 'nurse') ? ['view', 'view_all', 'view_own'] : ['view', 'view_all', 'view_assigned', 'view_own'];
if (PermissionManager::hasAnyPermission($userRole, 'patients', $patientRecordsPermissionActions)) {
    if (isset($additionalModules['patient_records'][$userRole])) {
        $navigationItems[] = array_merge($additionalModules['patient_records'][$userRole], ['module' => 'patient_records']);
    }
=======
// Define role-based navigation items (matching dashboard sidebar structure)
$navigationItems = [];

// Admin navigation
if ($userRole === 'admin') {
    $navigationItems = [
        ['url' => 'admin/dashboard', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard'],
        ['url' => 'admin/staff-management', 'icon' => 'fas fa-user-tie', 'label' => 'Staff Management'],
        ['url' => 'admin/user-management', 'icon' => 'fas fa-users', 'label' => 'User Management'],
        ['url' => 'admin/patient-management', 'icon' => 'fas fa-user-injured', 'label' => 'Patient Management'],
        ['url' => 'admin/schedule', 'icon' => 'fas fa-calendar-days', 'label' => 'Schedule Management'],
        ['url' => 'admin/appointments', 'icon' => 'fas fa-calendar-check', 'label' => 'Appointments'],
        ['url' => 'admin/prescriptions', 'icon' => 'fas fa-prescription-bottle', 'label' => 'Prescriptions'],
        ['url' => 'admin/labs', 'icon' => 'fas fa-flask', 'label' => 'Labs'],
        ['url' => 'admin/resource-management', 'icon' => 'fas fa-hospital', 'label' => 'Resource Management'],
        ['url' => 'admin/department-management', 'icon' => 'fas fa-building', 'label' => 'Department Management'],
        ['url' => 'admin/room-management', 'icon' => 'fas fa-bed', 'label' => 'Room Management'],
        ['url' => 'admin/financial-management', 'icon' => 'fas fa-dollar-sign', 'label' => 'Billing Management'],
        ['url' => 'admin/analytics', 'icon' => 'fas fa-chart-bar', 'label' => 'Analytics & Reports'],
        ['url' => 'unified/patient-records', 'icon' => 'fas fa-folder-open', 'label' => 'Patient Records'],
        
    ];
}

// Doctor navigation
elseif ($userRole === 'doctor') {
    $navigationItems = [
        ['url' => 'doctor/dashboard', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard'],
        ['url' => 'doctor/patient-management', 'icon' => 'fas fa-users', 'label' => 'Patient Management'],
        ['url' => 'doctor/appointments', 'icon' => 'fas fa-calendar-alt', 'label' => 'Appointments'],
        ['url' => 'doctor/prescriptions', 'icon' => 'fas fa-prescription-bottle', 'label' => 'Prescriptions'],
        ['url' => 'doctor/labs', 'icon' => 'fas fa-flask', 'label' => 'Lab Orders'],
        ['url' => 'doctor/schedule', 'icon' => 'fas fa-calendar-days', 'label' => 'Schedule'],
        ['url' => 'unified/patient-records', 'icon' => 'fas fa-folder-open', 'label' => 'Patient Records'],
        //['url' => 'doctor/schedule', 'icon' => 'fas fa-clock', 'label' => 'My Schedule'],
    ];
}

// Nurse navigation
elseif ($userRole === 'nurse') {
    $navigationItems = [
        ['url' => 'nurse/dashboard', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard'],
        ['url' => 'nurse/patients', 'icon' => 'fas fa-users', 'label' => 'Patients'],
        ['url' => 'nurse/appointments', 'icon' => 'fas fa-calendar-alt', 'label' => 'Appointments'],
        ['url' => 'nurse/prescriptions', 'icon' => 'fas fa-prescription-bottle', 'label' => 'Prescriptions'],
        ['url' => 'unified/patient-records', 'icon' => 'fas fa-folder-open', 'label' => 'Patient Records'],
        //['url' => 'nurse/lab-results', 'icon' => 'fas fa-flask', 'label' => 'Lab Results'],
        //['url' => 'nurse/schedule', 'icon' => 'fas fa-clock', 'label' => 'My Schedule'],
    ];
}

// Receptionist navigation
elseif ($userRole === 'receptionist') {
    $navigationItems = [
        ['url' => 'receptionist/dashboard', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard'],
        ['url' => 'receptionist/patients', 'icon' => 'fas fa-users', 'label' => 'Patient Registration'],
        ['url' => 'receptionist/appointments', 'icon' => 'fas fa-calendar-alt', 'label' => 'Appointments'],
        ['url' => 'receptionist/billing', 'icon' => 'fas fa-file-invoice-dollar', 'label' => 'Billing'],
        ['url' => 'receptionist/reports', 'icon' => 'fas fa-chart-bar', 'label' => 'Reports'],
    ];
}

// Pharmacist navigation
elseif ($userRole === 'pharmacist') {
    $navigationItems = [
        ['url' => 'pharmacist/dashboard', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard'],
        ['url' => 'pharmacist/prescriptions', 'icon' => 'fas fa-prescription-bottle', 'label' => 'Prescriptions'],
        ['url' => 'pharmacist/inventory', 'icon' => 'fas fa-pills', 'label' => 'Pharmacy Inventory'],
        ['url' => 'unified/patient-records', 'icon' => 'fas fa-folder-open', 'label' => 'Patient Records'],
        ['url' => 'pharmacist/reports', 'icon' => 'fas fa-chart-bar', 'label' => 'Reports'],
    ];
}

// Laboratorist navigation
elseif ($userRole === 'laboratorist') {
    $navigationItems = [
        ['url' => 'laboratorist/dashboard', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard'],
        ['url' => 'laboratorist/labs', 'icon' => 'fas fa-flask', 'label' => 'Lab Worklist'],
        ['url' => 'laboratorist/results', 'icon' => 'fas fa-file-medical-alt', 'label' => 'Test Results'],
        ['url' => 'unified/patient-records', 'icon' => 'fas fa-folder-open', 'label' => 'Patient Records'],
        ['url' => 'laboratorist/reports', 'icon' => 'fas fa-chart-bar', 'label' => 'Reports'],
    ];
}

// Accountant navigation
elseif ($userRole === 'accountant') {
    $navigationItems = [
        ['url' => 'accountant/dashboard', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard'],
        ['url' => 'accountant/billing', 'icon' => 'fas fa-file-invoice-dollar', 'label' => 'Billing Management'],
        ['url' => 'accountant/financial', 'icon' => 'fas fa-dollar-sign', 'label' => 'Financial Reports'],
        ['url' => 'accountant/analytics', 'icon' => 'fas fa-chart-bar', 'label' => 'Analytics'],
    ];
}

// IT Staff navigation
elseif ($userRole === 'it_staff') {
    $navigationItems = [
        ['url' => 'it-staff/dashboard', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard'],
        ['url' => 'it-staff/staff', 'icon' => 'fas fa-user-tie', 'label' => 'Staff Management'],
        ['url' => 'it-staff/users', 'icon' => 'fas fa-users', 'label' => 'User Management'],
        //['url' => 'it-staff/system-settings', 'icon' => 'fas fa-cogs', 'label' => 'System Settings'],
       // ['url' => 'it-staff/backups', 'icon' => 'fas fa-database', 'label' => 'Backups'],
        //['url' => 'it-staff/logs', 'icon' => 'fas fa-file-alt', 'label' => 'System Logs'],
    ];
>>>>>>> 03d4e70 (COMMITenter the commit message for your changes. Lines starting)
}
?>

<nav class="sidebar">
    <ul class="nav-menu">
        <?php foreach ($navigationItems as $item): ?>
            <li class="nav-item">
                <a href="<?= base_url($item['url']) ?>" 
                   class="nav-link <?= ($currentUri === $item['url']) ? 'active' : '' ?>">
                    <i class="<?= $item['icon'] ?> nav-icon"></i>
                    <?= $item['label'] ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
