<nav class="sidebar">
    <ul class="nav-menu">
        <li class="nav-item">
            <a href="<?= base_url('doctor/dashboard') ?>" class="nav-link <?= (uri_string() === 'doctor/dashboard') ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt nav-icon"></i>
                Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('doctor/patient') ?>" class="nav-link <?= (uri_string() === 'doctor/patient') ? 'active' : '' ?>">
                <i class="fas fa-users nav-icon"></i>
               Patients
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('doctor/appointments') ?>" class="nav-link <?= (uri_string() === 'doctor/appointments') ? 'active' : '' ?>">
                <i class="fas fa-calendar-alt nav-icon"></i>
                Appointments
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('doctor/prescriptions') ?>" class="nav-link <?= (uri_string() === 'doctor/prescriptions') ? 'active' : '' ?>">
                <i class="fas fa-prescription-bottle nav-icon"></i>
                Prescriptions
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('doctor/lab-results') ?>" class="nav-link <?= (uri_string() === 'doctor/lab-results') ? 'active' : '' ?>">
                <i class="fas fa-flask nav-icon"></i>
                Lab-Results
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('doctor/EHR') ?>" class="nav-link <?= (uri_string() === 'doctor/EHR') ? 'active' : '' ?>">
                <i class="fas fa-file-medical nav-icon"></i>
                EHR
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('doctor/schedule') ?>" class="nav-link <?= (uri_string() === 'doctor/schedule') ? 'active' : '' ?>">
                <i class="fas fa-clock nav-icon"></i>
                My Schedule
            </a>
        </li>
    </ul>
</nav>