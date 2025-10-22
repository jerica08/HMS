<nav class="sidebar">
    <ul class="nav-menu">
        <li class="nav-item">
            <a href="<?= base_url('admin/dashboard') ?>" class="nav-link <?= (uri_string() === 'admin/dashboard') ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt nav-icon"></i>
                Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('admin/staff-management') ?>" class="nav-link <?= (uri_string() === 'admin/staff-management') ? 'active' : '' ?>">
                <i class="fas fa-user-tie nav-icon"></i>
                Staff Management
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('admin/user-management') ?>" class="nav-link <?= (uri_string() === 'admin/user-management') ? 'active' : '' ?>">
                <i class="fas fa-users nav-icon"></i>
                User Management
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('admin/patient-management') ?>" class="nav-link <?= (uri_string() === 'admin/patient-management') ? 'active' : '' ?>">
                <i class="fas fa-user-injured nav-icon"></i>
                Patient Management
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('admin/shifts') ?>" class="nav-link <?= (uri_string() === 'admin/shifts') ? 'active' : '' ?>">
                <i class="fas fa-calendar-days nav-icon"></i>
                Shifts Management
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('admin/appointments') ?>" class="nav-link <?= (uri_string() === 'admin/appointments') ? 'active' : '' ?>">
                <i class="fas fa-calendar-check nav-icon"></i>
                Appointments
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('admin/prescriptions') ?>" class="nav-link <?= (uri_string() === 'admin/prescriptions') ? 'active' : '' ?>">
                <i class="fas fa-prescription-bottle nav-icon"></i>
                Prescriptions
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('admin/resource') ?>" class="nav-link <?= (uri_string() === 'admin/resource') ? 'active' : '' ?>">
                <i class="fas fa-hospital nav-icon"></i>
                Resource Management
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('admin/financial') ?>" class="nav-link <?= (uri_string() === 'admin/financial') ? 'active' : '' ?>">
                <i class="fas fa-dollar-sign nav-icon"></i>
                Financial Management
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('admin/analytics') ?>" class="nav-link <?= (uri_string() === 'admin/analytics') ? 'active' : '' ?>">
                <i class="fas fa-chart-bar nav-icon"></i>
                Analytics & Reports
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('admin/system-settings') ?>" class="nav-link <?= (uri_string() === 'admin/system-settings') ? 'active' : '' ?>">
                <i class="fas fa-cogs nav-icon"></i>
                System Settings
            </a>
        </li>
    </ul>
</nav>