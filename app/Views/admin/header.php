<!--Header Component for Laboratorist Pages-->
<header class="header">
    <div class="header-content">
        <div class="logo">
            <h1><i class="fas fa-hospital"></i> Administrator</h1>                    
        </div>
        <div class="user-info">
            <div href="" class="fas fa-avatar" href=""></div>
            <div>
                <div style="font-weight: 600;">
                    <?= \App\Helpers\UserHelper::getDisplayName($currentUser ?? null) ?>
                </div>
                <div style="font-size: 0.9rem;opacity:0.8">
                    <?= \App\Helpers\UserHelper::getDisplayRole($currentUser ?? null) ?>
                </div>
            </div>
            <a class="btn btn-secondary" href="<?= base_url('profile') ?>" style="margin-left:.5rem;">
                <i class="fas fa-user"></i> Profile
            </a>
            <button class="logout-btn" onclick="handleLogout()">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </button>
        </div>
    </div>
</header>