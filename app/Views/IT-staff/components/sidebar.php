<nav class="sidebar">
    <ul class="nav-menu">
        <li class="nav-item">
            <a href="<?= base_url('IT-staff/dashboard') ?>" class="nav-link <?= (uri_string() === 'IT-staff/dashboard') ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt nav-icon"></i>
                Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('IT-staff/maintenance') ?>" class="nav-link <?= (uri_string() === 'IT-staff/maintenance') ? 'active' : '' ?>">
                <i class="fas fa-desktop-alt nav-icon"></i>
                System Maintenance
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('IT-staff/security') ?>" class="nav-link <?= (uri_string() === 'IT-staff/security') ? 'active' : '' ?>">
                <i class="fas fa-shield-alt nav-icon"></i>
               Security Management
            </a>
        </li>
    </ul>
</nav>