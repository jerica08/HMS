<?php
// Shared dynamic navigation bar based on user role
// Accept either 'user_role' (used in this app) or a generic 'role' (for compatibility)
$role = session('user_role') ?? session('role');
$name = session('user_name') ?? session('name');
?>
<header class="header" style="background: #4682B4;color: white;padding: 1rem 2rem;box-shadow: 0 2px 10px rgba(0,0,0,0.1);position: sticky;top: 0;z-index: 100;">        
    <nav>
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
    
 