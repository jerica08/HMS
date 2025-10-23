<?php
// Get user role and permissions
$userRole = session()->get('role') ?? 'guest';
$currentUri = uri_string();

// Define role-based navigation items (matching dashboard sidebar structure)
$navigationItems = [];

// Admin navigation
if ($userRole === 'admin') {
    $navigationItems = [
        ['url' => 'admin/dashboard', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard'],
        ['url' => 'admin/staff-management', 'icon' => 'fas fa-user-tie', 'label' => 'Staff Management'],
        ['url' => 'admin/user-management', 'icon' => 'fas fa-users', 'label' => 'User Management'],
        ['url' => 'admin/patient-management', 'icon' => 'fas fa-user-injured', 'label' => 'Patient Management'],
        ['url' => 'admin/shifts', 'icon' => 'fas fa-calendar-days', 'label' => 'Shifts Management'],
        ['url' => 'admin/appointments', 'icon' => 'fas fa-calendar-check', 'label' => 'Appointments'],
        ['url' => 'admin/prescriptions', 'icon' => 'fas fa-prescription-bottle', 'label' => 'Prescriptions'],
        ['url' => 'admin/resource', 'icon' => 'fas fa-hospital', 'label' => 'Resource Management'],
        ['url' => 'admin/financial', 'icon' => 'fas fa-dollar-sign', 'label' => 'Financial Management'],
        ['url' => 'admin/analytics', 'icon' => 'fas fa-chart-bar', 'label' => 'Analytics & Reports'],
        ['url' => 'admin/system-settings', 'icon' => 'fas fa-cogs', 'label' => 'System Settings'],
    ];
}

// Doctor navigation
elseif ($userRole === 'doctor') {
    $navigationItems = [
        ['url' => 'doctor/dashboard', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard'],
        ['url' => 'doctor/patients', 'icon' => 'fas fa-users', 'label' => 'Patients'],
        ['url' => 'doctor/appointments', 'icon' => 'fas fa-calendar-alt', 'label' => 'Appointments'],
        ['url' => 'doctor/prescriptions', 'icon' => 'fas fa-prescription-bottle', 'label' => 'Prescriptions'],
        ['url' => 'doctor/lab-results', 'icon' => 'fas fa-flask', 'label' => 'Lab-Results'],
        ['url' => 'doctor/EHR', 'icon' => 'fas fa-file-medical', 'label' => 'EHR'],
        ['url' => 'doctor/schedule', 'icon' => 'fas fa-clock', 'label' => 'My Schedule'],
    ];
}

// Nurse navigation
elseif ($userRole === 'nurse') {
    $navigationItems = [
        ['url' => 'nurse/dashboard', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard'],
        ['url' => 'nurse/patients', 'icon' => 'fas fa-users', 'label' => 'Patients'],
        ['url' => 'nurse/appointments', 'icon' => 'fas fa-calendar-alt', 'label' => 'Appointments'],
        ['url' => 'nurse/prescriptions', 'icon' => 'fas fa-prescription-bottle', 'label' => 'Prescriptions'],
        ['url' => 'nurse/lab-results', 'icon' => 'fas fa-flask', 'label' => 'Lab Results'],
        ['url' => 'nurse/schedule', 'icon' => 'fas fa-clock', 'label' => 'My Schedule'],
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
        ['url' => 'pharmacist/reports', 'icon' => 'fas fa-chart-bar', 'label' => 'Reports'],
    ];
}

// Laboratorist navigation
elseif ($userRole === 'laboratorist') {
    $navigationItems = [
        ['url' => 'laboratorist/dashboard', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard'],
        ['url' => 'laboratorist/lab-tests', 'icon' => 'fas fa-flask', 'label' => 'Lab Tests'],
        ['url' => 'laboratorist/results', 'icon' => 'fas fa-file-medical-alt', 'label' => 'Test Results'],
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
        ['url' => 'it-staff/users', 'icon' => 'fas fa-users', 'label' => 'User Management'],
        ['url' => 'it-staff/system-settings', 'icon' => 'fas fa-cogs', 'label' => 'System Settings'],
        ['url' => 'it-staff/backups', 'icon' => 'fas fa-database', 'label' => 'Backups'],
        ['url' => 'it-staff/logs', 'icon' => 'fas fa-file-alt', 'label' => 'System Logs'],
    ];
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
