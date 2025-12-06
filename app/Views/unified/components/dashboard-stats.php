<?php
/**
 * Dashboard Statistics Component
 * Renders role-specific stat cards using the stat-card component
 */

// Admin Statistics
if ($userRole === 'admin'): ?>
    <?= $this->include('unified/components/stat-card', [
        'icon' => 'fas fa-users',
        'iconColor' => 'blue',
        'title' => 'Total Patients',
        'subtitle' => 'System-wide (trends)',
        'metrics' => [
            ['value' => $dashboardStats['total_patients'] ?? 0, 'label' => 'Registered', 'color' => 'blue'],
            ['value' => $dashboardStats['active_patients'] ?? 0, 'label' => 'Active', 'color' => 'green']
        ],
        'actionUrl' => 'admin/patient-management',
        'actionText' => 'Manage'
    ]) ?>

    <?= $this->include('unified/components/stat-card', [
        'icon' => 'fas fa-calendar-alt',
        'iconColor' => 'orange',
        'title' => 'Today\'s Appointments',
        'subtitle' => 'Schedule breakdown',
        'metrics' => [
            ['value' => $dashboardStats['today_scheduled_appointments'] ?? 0, 'label' => 'Scheduled', 'color' => 'blue'],
            ['value' => $dashboardStats['today_completed_appointments'] ?? 0, 'label' => 'Completed', 'color' => 'green']
        ],
        'actionUrl' => 'admin/appointments',
        'actionText' => 'View Schedule'
    ]) ?>

    <?= $this->include('unified/components/stat-card', [
        'icon' => 'fas fa-procedures',
        'iconColor' => 'green',
        'title' => 'Patient Types',
        'subtitle' => 'Inpatients vs Outpatients',
        'metrics' => [
            ['value' => $dashboardStats['inpatients'] ?? 0, 'label' => 'Inpatients', 'color' => 'green'],
            ['value' => $dashboardStats['outpatients'] ?? 0, 'label' => 'Outpatients', 'color' => 'blue']
        ],
        'actionUrl' => 'admin/patient-management',
        'actionText' => 'View Patients'
    ]) ?>

    <?= $this->include('unified/components/stat-card', [
        'icon' => 'fas fa-bed',
        'iconColor' => 'purple',
        'title' => 'Beds & Capacity',
        'subtitle' => 'Occupied vs Available',
        'metrics' => [
            ['value' => $dashboardStats['occupied_beds'] ?? 0, 'label' => 'Occupied Beds', 'color' => 'purple'],
            ['value' => $dashboardStats['bed_capacity_total'] ?? 0, 'label' => 'Total Capacity', 'color' => 'green'],
            ['value' => $dashboardStats['available_beds'] ?? 0, 'label' => 'Available Beds', 'color' => 'blue']
        ],
        'actionUrl' => 'admin/room-management',
        'actionText' => 'Manage Beds'
    ]) ?>

    <?= $this->include('unified/components/stat-card', [
        'icon' => 'fas fa-user-md',
        'iconColor' => 'green',
        'title' => 'Staff On Duty',
        'subtitle' => 'Today',
        'metrics' => [
            ['value' => $dashboardStats['total_doctors'] ?? 0, 'label' => 'Total Doctors', 'color' => 'blue'],
            ['value' => $dashboardStats['total_staff'] ?? 0, 'label' => 'Total Staff', 'color' => 'purple']
        ],
        'actionUrl' => 'admin/staff-management',
        'actionText' => 'Manage Staff'
    ]) ?>

<?php elseif ($userRole === 'doctor'): ?>
    <?= $this->include('unified/components/stat-card', [
        'icon' => 'fas fa-calendar-day',
        'iconColor' => 'blue',
        'title' => 'Today\'s Appointments',
        'subtitle' => date('F j, Y'),
        'metrics' => [
            ['value' => $dashboardStats['today_appointments'] ?? 0, 'label' => 'Total', 'color' => 'blue'],
            ['value' => $dashboardStats['completed_today'] ?? 0, 'label' => 'Completed', 'color' => 'green'],
            ['value' => $dashboardStats['pending_today'] ?? 0, 'label' => 'Pending', 'color' => 'orange']
        ],
        'actionUrl' => 'doctor/appointments',
        'actionText' => 'View Schedule'
    ]) ?>

    <?= $this->include('unified/components/stat-card', [
        'icon' => 'fas fa-users',
        'iconColor' => 'green',
        'title' => 'My Patients',
        'subtitle' => 'Under your care',
        'metrics' => [
            ['value' => $dashboardStats['my_patients'] ?? 0, 'label' => 'Total', 'color' => 'green'],
            ['value' => $dashboardStats['new_patients_week'] ?? 0, 'label' => 'New This Week', 'color' => 'blue'],
            ['value' => $dashboardStats['critical_patients'] ?? 0, 'label' => 'Critical', 'color' => 'red']
        ],
        'actionUrl' => 'doctor/patients',
        'actionText' => 'View Patients'
    ]) ?>

    <?= $this->include('unified/components/stat-card', [
        'icon' => 'fas fa-prescription-bottle-alt',
        'iconColor' => 'purple',
        'title' => 'Prescriptions',
        'subtitle' => 'Medication management',
        'metrics' => [
            ['value' => $dashboardStats['prescriptions_pending'] ?? 0, 'label' => 'Pending', 'color' => 'purple'],
            ['value' => $dashboardStats['prescriptions_today'] ?? 0, 'label' => 'Today', 'color' => 'green']
        ],
        'actionUrl' => 'doctor/prescriptions',
        'actionText' => 'Manage'
    ]) ?>

<?php elseif ($userRole === 'nurse'): ?>
    <?= $this->include('unified/components/stat-card', [
        'icon' => 'fas fa-user-nurse',
        'iconColor' => 'blue',
        'title' => 'Department Patients',
        'subtitle' => 'Your department',
        'metrics' => [
            ['value' => $dashboardStats['department_patients'] ?? 0, 'label' => 'Total', 'color' => 'blue'],
            ['value' => $dashboardStats['critical_patients'] ?? 0, 'label' => 'Critical', 'color' => 'red']
        ],
        'actionUrl' => 'nurse/patients',
        'actionText' => 'View Patients'
    ]) ?>

    <?= $this->include('unified/components/stat-card', [
        'icon' => 'fas fa-pills',
        'iconColor' => 'green',
        'title' => 'Medications',
        'subtitle' => 'Due today',
        'metrics' => [
            ['value' => $dashboardStats['medications_due'] ?? 0, 'label' => 'Due', 'color' => 'green'],
            ['value' => $dashboardStats['medications_overdue'] ?? 0, 'label' => 'Overdue', 'color' => 'orange']
        ],
        'actionUrl' => 'nurse/prescriptions',
        'actionText' => 'Manage'
    ]) ?>

<?php elseif ($userRole === 'receptionist'): ?>
    <?= $this->include('unified/components/stat-card', [
        'icon' => 'fas fa-calendar-check',
        'iconColor' => 'blue',
        'title' => 'Today\'s Appointments',
        'subtitle' => date('F j, Y'),
        'metrics' => [
            ['value' => $dashboardStats['total_appointments'] ?? 0, 'label' => 'Total', 'color' => 'blue'],
            ['value' => $dashboardStats['scheduled_today'] ?? 0, 'label' => 'Scheduled', 'color' => 'green'],
            ['value' => $dashboardStats['cancelled_today'] ?? 0, 'label' => 'Cancelled', 'color' => 'red']
        ],
        'actionUrl' => 'receptionist/appointments',
        'actionText' => 'Manage'
    ]) ?>

    <?= $this->include('unified/components/stat-card', [
        'icon' => 'fas fa-user-plus',
        'iconColor' => 'green',
        'title' => 'Patient Registration',
        'subtitle' => 'New registrations',
        'metrics' => [
            ['value' => $dashboardStats['new_patients_today'] ?? 0, 'label' => 'Today', 'color' => 'green'],
            ['value' => $dashboardStats['total_patients'] ?? 0, 'label' => 'Total', 'color' => 'blue']
        ],
        'actionUrl' => 'receptionist/patients',
        'actionText' => 'Register'
    ]) ?>

<?php else: ?>
    <?= $this->include('unified/components/stat-card', [
        'icon' => 'fas fa-chart-bar',
        'iconColor' => 'blue',
        'title' => 'System Overview',
        'subtitle' => 'General statistics',
        'metrics' => [
            ['value' => $dashboardStats['total_patients'] ?? 0, 'label' => 'Patients', 'color' => 'blue'],
            ['value' => $dashboardStats['total_appointments'] ?? 0, 'label' => 'Appointments', 'color' => 'green']
        ],
        'actionUrl' => null,
        'actionText' => null
    ]) ?>
<?php endif; ?>

