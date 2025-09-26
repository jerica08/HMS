<!--Sidebar Component for Laboratorist Pages-->
<nav class="sidebar">     
    <ul class="nav-menu">
        <li class="nav-item">
            <a href="<?= base_url('admin/dashboard') ?>" class="nav-link active">
                <i class="fas fa-tachometer-alt nav-icon"></i>
                Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('admin/staff') ?>" class="nav-link">
                <i class="fas fa-user-tie nav-icon"></i>
                Staff Management
            </a>
        </li>                    
        <li class="nav-item">
            <a href="<?= base_url('admin/users') ?>" class="nav-link">
                <i class="fas fa-users nav-icon"></i>
                User Management
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('admin/patient') ?>" class="nav-link">
                <i class="fas fa-user-injured nav-icon"></i>
                Patient Management
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('admin/resource') ?>" class="nav-link">
                <i class="fas fa-hospital nav-icon"></i>
                Resource Management
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('admin/financial') ?>" class="nav-link">
                <i class="fas fa-dollar-sign nav-icon"></i>
                Financial Management
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('admin/communication') ?>" class="nav-link">
                <i class="fas fa-comments nav-icon"></i>
                Communication
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('admin/analytics') ?>" class="nav-link">
                <i class="fas fa-chart-bar nav-icon"></i>
                Analytics & Reports
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('admin/systemSettings') ?>" class="nav-link">
                <i class="fas fa-cogs nav-icon"></i>
                System Settings
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('admin/securityAccess') ?>" class="nav-link">
                <i class="fas fa-shield-alt nav-icon"></i>
                Security & Access
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('admin/auditLogs') ?>" class="nav-link">
                <i class="fas fa-clipboard-list nav-icon"></i>
                Audit Logs
            </a>
        </li>
    </ul>          
</nav>