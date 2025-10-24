<?php
// Get user data for header
$userRole = session()->get('role') ?? 'guest';
$userName = session()->get('name') ?? 'Guest User';
$staffId = session()->get('staff_id') ?? null;
$currentUri = uri_string();

// Role-based dashboard URLs
$dashboardUrls = [
    'admin' => 'admin/dashboard',
    'doctor' => 'doctor/dashboard',
    'nurse' => 'nurse/dashboard',
    'pharmacist' => 'pharmacist/dashboard',
    'laboratorist' => 'laboratorist/dashboard',
    'receptionist' => 'receptionist/dashboard',
    'accountant' => 'accountant/dashboard',
    'it_staff' => 'it-staff/dashboard'
];

$dashboardUrl = $dashboardUrls[$userRole] ?? 'login';

// Role display names
$roleDisplayNames = [
    'admin' => 'Administrator',
    'doctor' => 'Doctor',
    'nurse' => 'Nurse',
    'pharmacist' => 'Pharmacist',
    'laboratorist' => 'Laboratory Technician',
    'receptionist' => 'Receptionist',
    'accountant' => 'Accountant',
    'it_staff' => 'IT Staff'
];

$roleDisplay = $roleDisplayNames[$userRole] ?? ucfirst($userRole);

// Role-based colors
$roleColors = [
    'admin' => '#dc2626',
    'doctor' => '#2563eb',
    'nurse' => '#059669',
    'pharmacist' => '#7c3aed',
    'laboratorist' => '#ea580c',
    'receptionist' => '#0891b2',
    'accountant' => '#ca8a04',
    'it_staff' => '#4f46e5'
];

$roleColor = $roleColors[$userRole] ?? '#6b7280';
?>

<header class="unified-header">
    <div class="header-container">
        <!-- Logo and System Name -->
        <div class="header-brand">
            <a href="<?= base_url($dashboardUrl) ?>" class="brand-link">
                <div class="brand-icon">
                    <i class="fas fa-hospital-alt"></i>
                </div>
                <div class="brand-text">
                    <h1 class="brand-title">HMS</h1>
                    <span class="brand-subtitle">Hospital Management</span>
                </div>
            </a>
        </div>

        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle navigation">
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
        </button>

        <!-- Header Actions -->
        <div class="header-actions">
            <!-- Quick Actions -->
            <div class="quick-actions">
                <?php if ($userRole === 'admin'): ?>
                    <a href="<?= base_url('admin/staff-management') ?>" class="quick-action" title="Staff Management">
                        <i class="fas fa-user-tie"></i>
                    </a>
                    <a href="<?= base_url('admin/analytics') ?>" class="quick-action" title="Analytics">
                        <i class="fas fa-chart-bar"></i>
                    </a>
                <?php elseif ($userRole === 'doctor'): ?>
                    <a href="<?= base_url('doctor/appointments') ?>" class="quick-action" title="Appointments">
                        <i class="fas fa-calendar-check"></i>
                    </a>
                    <a href="<?= base_url('doctor/patient-management') ?>" class="quick-action" title="Patients">
                        <i class="fas fa-users"></i>
                    </a>
                <?php elseif ($userRole === 'nurse'): ?>
                    <a href="<?= base_url('nurse/patient-management') ?>" class="quick-action" title="Patients">
                        <i class="fas fa-user-injured"></i>
                    </a>
                    <a href="<?= base_url('nurse/medications') ?>" class="quick-action" title="Medications">
                        <i class="fas fa-pills"></i>
                    </a>
                <?php elseif ($userRole === 'receptionist'): ?>
                    <a href="<?= base_url('receptionist/appointments') ?>" class="quick-action" title="Appointments">
                        <i class="fas fa-calendar-plus"></i>
                    </a>
                    <a href="<?= base_url('receptionist/patient-management') ?>" class="quick-action" title="Patients">
                        <i class="fas fa-user-plus"></i>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Notifications -->
            <div class="notification-center">
                <button class="notification-btn" id="notificationBtn" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge" id="notificationCount">3</span>
                </button>
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-header">
                        <h3>Notifications</h3>
                        <button class="mark-all-read">Mark all read</button>
                    </div>
                    <div class="notification-list">
                        <div class="notification-item unread">
                            <i class="fas fa-calendar-check notification-icon"></i>
                            <div class="notification-content">
                                <p>New appointment scheduled</p>
                                <span class="notification-time">5 minutes ago</span>
                            </div>
                        </div>
                        <div class="notification-item">
                            <i class="fas fa-user-plus notification-icon"></i>
                            <div class="notification-content">
                                <p>New patient registered</p>
                                <span class="notification-time">1 hour ago</span>
                            </div>
                        </div>
                        <div class="notification-item">
                            <i class="fas fa-file-medical notification-icon"></i>
                            <div class="notification-content">
                                <p>Lab results available</p>
                                <span class="notification-time">2 hours ago</span>
                            </div>
                        </div>
                    </div>
                    <div class="notification-footer">
                        <a href="#" class="view-all-notifications">View all notifications</a>
                    </div>
                </div>
            </div>

            <!-- User Profile -->
            <div class="user-profile">
                <button class="profile-btn" id="profileBtn">
                    <div class="user-avatar" style="background-color: <?= $roleColor ?>">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-details">
                        <span class="user-name"><?= esc($userName) ?></span>
                        <span class="user-role" style="color: <?= $roleColor ?>"><?= esc($roleDisplay) ?></span>
                    </div>
                    <i class="fas fa-chevron-down profile-arrow"></i>
                </button>
                
                <div class="profile-dropdown" id="profileDropdown">
                    <div class="profile-info">
                        <div class="profile-avatar" style="background-color: <?= $roleColor ?>">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="profile-details">
                            <h3><?= esc($userName) ?></h3>
                            <p><?= esc($roleDisplay) ?></p>
                            <?php if ($staffId): ?>
                            <span class="staff-id">ID: <?= esc($staffId) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="profile-menu">
                        <a href="<?= base_url($dashboardUrl) ?>" class="profile-menu-item">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="#" class="profile-menu-item" onclick="openProfileSettings()">
                            <i class="fas fa-user-cog"></i>
                            <span>Profile Settings</span>
                        </a>
                        <a href="#" class="profile-menu-item" onclick="openChangePassword()">
                            <i class="fas fa-key"></i>
                            <span>Change Password</span>
                        </a>
                        <div class="profile-menu-divider"></div>
                        <a href="#" class="profile-menu-item logout-item" onclick="handleLogout()">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Unified Header Styles -->
<style>
.unified-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 0;
    z-index: 1000;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.header-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 1.5rem;
    height: 70px;
    max-width: 1400px;
    margin: 0 auto;
}

/* Brand */
.header-brand .brand-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    text-decoration: none;
    color: white;
    transition: opacity 0.2s ease;
}

.header-brand .brand-link:hover {
    opacity: 0.9;
}

.brand-icon {
    font-size: 2rem;
    color: #fbbf24;
}

.brand-title {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
    color: white;
}

.brand-subtitle {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.8);
    display: block;
    margin-top: -0.25rem;
}

/* Mobile Menu Toggle */
.mobile-menu-toggle {
    display: none;
    flex-direction: column;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.5rem;
    gap: 0.25rem;
}

.hamburger-line {
    width: 20px;
    height: 2px;
    background: white;
    transition: all 0.3s ease;
}

/* Header Actions */
.header-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
}

/* Quick Actions */
.quick-actions {
    display: flex;
    gap: 0.5rem;
}

.quick-action {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    color: white;
    text-decoration: none;
    transition: all 0.2s ease;
    font-size: 1rem;
}

.quick-action:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-1px);
}

/* Notifications */
.notification-center {
    position: relative;
}

.notification-btn {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    border-radius: 8px;
    color: white;
    cursor: pointer;
    transition: all 0.2s ease;
}

.notification-btn:hover {
    background: rgba(255, 255, 255, 0.2);
}

.notification-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    background: #ef4444;
    color: white;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.125rem 0.375rem;
    border-radius: 10px;
    min-width: 18px;
    text-align: center;
}

.notification-dropdown {
    position: absolute;
    top: calc(100% + 0.5rem);
    right: 0;
    width: 320px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    display: none;
    z-index: 1001;
}

.notification-dropdown.show {
    display: block;
    animation: dropdownSlideIn 0.2s ease-out;
}

.notification-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.notification-header h3 {
    margin: 0;
    font-size: 1rem;
    color: #1f2937;
}

.mark-all-read {
    background: none;
    border: none;
    color: #3b82f6;
    font-size: 0.875rem;
    cursor: pointer;
}

.notification-list {
    max-height: 300px;
    overflow-y: auto;
}

.notification-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f3f4f6;
    transition: background-color 0.2s ease;
}

.notification-item:hover {
    background: #f9fafb;
}

.notification-item.unread {
    background: #eff6ff;
}

.notification-icon {
    color: #6b7280;
    margin-top: 0.125rem;
}

.notification-content p {
    margin: 0 0 0.25rem 0;
    font-size: 0.875rem;
    color: #1f2937;
}

.notification-time {
    font-size: 0.75rem;
    color: #6b7280;
}

.notification-footer {
    padding: 0.75rem 1rem;
    border-top: 1px solid #e5e7eb;
    text-align: center;
}

.view-all-notifications {
    color: #3b82f6;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
}

/* User Profile */
.user-profile {
    position: relative;
}

.profile-btn {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    border-radius: 12px;
    padding: 0.5rem;
    color: white;
    cursor: pointer;
    transition: all 0.2s ease;
}

.profile-btn:hover {
    background: rgba(255, 255, 255, 0.2);
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.125rem;
}

.user-details {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.125rem;
}

.user-name {
    font-weight: 600;
    font-size: 0.875rem;
}

.user-role {
    font-size: 0.75rem;
    opacity: 0.9;
}

.profile-arrow {
    font-size: 0.75rem;
    transition: transform 0.2s ease;
}

.profile-btn.active .profile-arrow {
    transform: rotate(180deg);
}

.profile-dropdown {
    position: absolute;
    top: calc(100% + 0.5rem);
    right: 0;
    width: 280px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    display: none;
    z-index: 1001;
}

.profile-dropdown.show {
    display: block;
    animation: dropdownSlideIn 0.2s ease-out;
}

.profile-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.profile-avatar {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.profile-details h3 {
    margin: 0 0 0.25rem 0;
    font-size: 1rem;
    color: #1f2937;
}

.profile-details p {
    margin: 0 0 0.25rem 0;
    font-size: 0.875rem;
    color: #6b7280;
}

.staff-id {
    font-size: 0.75rem;
    color: #9ca3af;
}

.profile-menu-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1.5rem;
    color: #374151;
    text-decoration: none;
    transition: background-color 0.2s ease;
}

.profile-menu-item:hover {
    background: #f9fafb;
}

.profile-menu-item.logout-item {
    color: #dc2626;
}

.profile-menu-item.logout-item:hover {
    background: #fef2f2;
}

.profile-menu-divider {
    height: 1px;
    background: #e5e7eb;
    margin: 0.5rem 0;
}

@keyframes dropdownSlideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    .mobile-menu-toggle {
        display: flex;
    }
    
    .quick-actions {
        display: none;
    }
    
    .user-details {
        display: none;
    }
    
    .brand-subtitle {
        display: none;
    }
    
    .header-container {
        padding: 0 1rem;
    }
}

@media (max-width: 480px) {
    .notification-dropdown,
    .profile-dropdown {
        width: calc(100vw - 2rem);
        right: 1rem;
    }
}
</style>

<!-- Unified Header Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Notification dropdown
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationDropdown = document.getElementById('notificationDropdown');
    
    // Profile dropdown
    const profileBtn = document.getElementById('profileBtn');
    const profileDropdown = document.getElementById('profileDropdown');
    
    // Mobile menu toggle
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    
    // Toggle notification dropdown
    if (notificationBtn && notificationDropdown) {
        notificationBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('show');
            profileDropdown.classList.remove('show');
            profileBtn.classList.remove('active');
        });
    }
    
    // Toggle profile dropdown
    if (profileBtn && profileDropdown) {
        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('show');
            profileBtn.classList.toggle('active');
            notificationDropdown.classList.remove('show');
        });
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        if (notificationDropdown) notificationDropdown.classList.remove('show');
        if (profileDropdown) {
            profileDropdown.classList.remove('show');
            profileBtn.classList.remove('active');
        }
    });
    
    // Mobile menu toggle
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
            // Toggle mobile sidebar (implement based on your sidebar structure)
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                sidebar.classList.toggle('mobile-open');
            }
        });
    }
});

// Logout function
function handleLogout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = '<?= base_url('logout') ?>';
    }
}

// Profile settings function
function openProfileSettings() {
    // Implement profile settings modal or redirect
    alert('Profile settings feature coming soon!');
}

// Change password function
function openChangePassword() {
    // Implement change password modal or redirect
    alert('Change password feature coming soon!');
}
</script>
