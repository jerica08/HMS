<nav class="sidebar">
    <ul class="nav-menu">
        <li class="nav-item">
            <a href="<?= base_url('receptionist/dashboard') ?>" class="nav-link <?= (uri_string() === 'receptionist/dashboard') ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt nav-icon"></i>
                Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('receptionist/patient-registration') ?>" class="nav-link <?= (uri_string() === 'receptionist/patient-registration') ? 'active' : '' ?>">
                <i class="fas fa-user-plus nav-icon"></i>
              Patient Registration
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('receptionist/appointment-booking') ?>" class="nav-link <?= (uri_string() === 'receptionist/appointment-booking') ? 'active' : '' ?>">
                <i class="fas fa-calendar-alt nav-icon"></i>
             Appointment Booking
            </a>
        </li>
    </ul>
</nav>