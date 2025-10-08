<nav class="sidebar">
    <ul class="nav-menu">
        <li class="nav-item">
            <a href="<?= base_url('it/dashboard') ?>" class="nav-link <?= (uri_string() === 'it/dashboard') ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt nav-icon"></i>
                Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('it/maintenance') ?>" class="nav-link <?= (uri_string() === 'it/maintenance') ? 'active' : '' ?>">
                <i class="fas fa-desktop-alt nav-icon"></i>
                System Maintenance
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('it/security') ?>" class="nav-link <?= (uri_string() === 'it/security') ? 'active' : '' ?>">
                <i class="fas fa-shield-alt nav-icon"></i>
               Security Management
            </a>
        </li>
    </ul>
</nav>