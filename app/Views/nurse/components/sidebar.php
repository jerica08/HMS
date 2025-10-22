<nav class="sidebar">
    <ul class="nav-menu">
        <li class="nav-item">
            <a href="<?= base_url('nurse/dashboard') ?>" class="nav-link <?= (uri_string() === 'nurse/dashboard') ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt nav-icon"></i>
                Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('nurse/patient-management') ?>" class="nav-link <?= (in_array(uri_string(), ['nurse/patient','nurse/patient-management'])) ? 'active' : '' ?>">
                <i class="fas fa-heart nav-icon"></i>
              Patient Management
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('nurse/medication') ?>" class="nav-link <?= (uri_string() === 'nurse/medication') ? 'active' : '' ?>">
                <i class="fas fa-pills nav-icon"></i>
              Medication
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('nurse/vitals') ?>" class="nav-link <?= (uri_string() === 'nurse/vitals') ? 'active' : '' ?>">
                <i class="fas fa-heartbeat nav-icon"></i>
              Vital Signs
            </a>
        </li>
          <li class="nav-item">
            <a href="<?= base_url('nurse/shift-report') ?>" class="nav-link <?= (uri_string() === 'nurse/shift-report') ? 'active' : '' ?>">
                <i class="fas fa-file-medical nav-icon"></i>
               Shift Reports
            </a>
        </li>
    </ul>
</nav>