<nav class="sidebar">
    <ul class="nav-menu">
        <li class="nav-item">
            <a href="<?= base_url('laboratorists/dashboard') ?>" class="nav-link <?= (uri_string() === 'laboratorists/dashboard') ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt nav-icon"></i>
                Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('laboratorists/test-request') ?>" class="nav-link <?= (uri_string() === 'laboratorists/test-request') ? 'active' : '' ?>">
                <i class="fas fa-clipboard-list nav-icon"></i>
               Test Requests
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('laboratorists/sample-management') ?>" class="nav-link <?= (uri_string() === 'laboratorists/sample-management') ? 'active' : '' ?>">
                <i class="fas fa-vial nav-icon"></i>
               Sample Management
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('laboratorists/test-result') ?>" class="nav-link <?= (uri_string() === 'laboratorists/test-result') ? 'active' : '' ?>">
                <i class="fas fa-chart-line nav-icon"></i>
               Test Results
            </a>
        </li>
    </ul>
</nav>