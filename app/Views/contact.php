<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width", initial-scale="1.0">
        <title>Contact Page</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
        <style>
            .navbar {
                position: fixed;
                top: 0;
                width: 100%;
                z-index: 1000;
            }
            .main-content {
                margin-top: 20px;
                padding: 20px;
                display: center;;
            }
            .button {
                background-color: #3f87c6ff;
                color: black;
                border: none;
                border-radius: 5px;
                padding: 10px 20px;
                cursor: pointer;
                transition: background 0.3s;
            }
            .button-active {
            background-color: #3f87c6ff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background 0.3s;
            }
            .button:hover {
                background-color: #3f87c6ff;
                background-color: #24649cff;
            }
            
        </style>
    </head>
    <body>
        <header>
            <nav class="navigationbar">
                <nav class="btm-navbar" style="background-color:#24649cff;font-family: 'Times New Roman', serif;">
                    <div class="container-fluid d-flex justify-content-between align-items-center">
                        <a class="navbar-brand text-white" href="#"><h2>Hospital Management System</h2></a>
                        <ul class="nav d-flex align-items-center gap-3">
                            <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('/') ?>"><button class="button"> Home</button></a></li>
                            <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('login')?>"><button class="button"> Log-In</button></a></li>                
                            <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('about') ?>"><button class="button"> About Us</button></a></li>
                            <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('contact') ?>"><button class="button"> Contact Us</button></a></li>
                        </ul>
                    </div>
                </nav>
            </nav>
        </header>     
        <main class="main-content">
            <div class="container" style="text-align:center; margin-top: 50px;">
                <h2 style="font-weight:1000; font-family: 'Times New Roman', serif;">Display here the  contact info.</h2>
                
            </div>
        </main>
    </body>
</html>
