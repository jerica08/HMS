<!DOCTYPE html>
<html lang="en">
    <head>
          <meta charset="utf-8">
    <meta name="viewport" content="width=device-width", initial-scale="1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>
    <style>
        /* Inline styles to align header nav horizontally */
        .btm-navbar .nav {
            display: flex;
            align-items: right;
            gap: 0.75rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .button-active {
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.25);
            color: #fff;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
        }
        .button-active:hover { background: rgba(255,255,255,0.25); }
    </style>
    <body>
       <header class="header">
            <div class="header-content">
                <div class="logo">
                    <h1><i class="fas fa-hospital"></i> Learning Managmeent System</h1>                    
                </div>
                <div class="navbar">                   
                    <nav class="btm-navbar">
                        <ul class="nav d-flex align-items-center gap-3">
                            <li class="nav-item"><a  href=""><button class="button-active"> Home</button></a></li>
                            <li class="nav-item"><a  href=""><button class="button-active"> Login</button></a></li>
                            <li class="nav-item"><a  href=""><button class="button-active"> About Us</button></a></li>
                            <li class="nav-item"><a  href=""><button class="button-active"> Contact</button></a></li>                           
                        </ul>
                    </nav>
                </div>
            </div>
        </header>
        <main>
            
        </main>
    </body>