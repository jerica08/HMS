<?php
// Shared dynamic navigation bar based on user role
// Accept either 'user_role' (used in this app) or a generic 'role' (for compatibility)
$role = session('user_role') ?? session('role');
$name = session('user_name') ?? session('name');
?>
<header>        
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background-color:#24649cff; font-family: 'Times New Roman', serif;">
        <div class="container-fluid">
            <a class="navbar-brand text-white" href="#"><h2><?= esc(session('role') ?? 'User') ?></h2></a>
            <div class="collapse navbar-collapse justify-content-end" id="mainNavbar">
                <ul class="navbar-nav mb-2 mb-lg-0 d-flex align-items-center gap-3">
                    <li class="nav-item">
                        <a class="btn btn-outline-light" href="">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-light text-primary" href="">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>     
    
 