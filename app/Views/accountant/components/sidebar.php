<nav class="sidebar">
    <ul class="nav-menu">
        <li class="nav-item">
            <a href="<?= base_url('accountant/dashboard') ?>" class="nav-link <?= (uri_string() === 'accountant/dashboard') ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt nav-icon"></i>
                Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('accountant/billing') ?>" class="nav-link <?= (uri_string() === 'accountant/billing') ? 'active' : '' ?>">
                <i class="fas fa-file-invoice nav-icon"></i>
                Patients Billing
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('accountant/payments') ?>" class="nav-link <?= (uri_string() === 'accountant/payments') ? 'active' : '' ?>">
                <i class="fas fa-credit-card nav-icon"></i>
                Payment Processing
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('accountant/insurance') ?>" class="nav-link <?= (uri_string() === 'accountant/insurance') ? 'active' : '' ?>">
                <i class="fas fa-shield-alt nav-icon"></i>
               Insurance Claims
            </a>
        </li>
    </ul>
</nav>
