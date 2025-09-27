<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            font-family: 'Times New Roman', serif;
        }
        .button {
            background-color: #24649cff;
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
            
        .main-content {
            margin-top: 50px;
            padding: 20px;
        }
        .card {
            max-width: 400px;
            margin: 0 auto;
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #24649cff;
            color: black;
            text-align: center;
            padding: 1.5rem;
            border-radius: 10px 10px 0 0 !important;
        }
        .form-control {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            font-family: 'Times New Roman', serif;
        }
        .form-control:focus {
            border-color: #3f87c6ff;
            box-shadow: 0 0 5px #24649cff;
        }
        .btn-primary {
            background-color: #3f87c6ff;
            border: none;
            color: black;
            font-weight: bold;
            padding: 10px;
        }
        .btn-primary:hover {
            background-color: #3f87c6ff;
            color: black;
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

                        <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('/login') ?>"><button class="button-active"> Log-In</button></a></li>                
                        <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('/about') ?>"><button class="button"> About Us</button></a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('/contact') ?>"><button class="button"> Contact Us</button></a></li>
                    </ul>
                </div>
            </nav>
        </nav>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="mb-0">Login</h3>
                        </div>
                        <div class="card-body p-4">
                            <form action="" method="post">
                                <input type="hidden" name="" value="" />
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" 
                                           class="form-control" 
                                           id="email" 
                                           name="email" 
                                           value="" 
                                           placeholder="Enter your email address"
                                           required>                                  
                                        <div class="text-danger small mt-1">                                         
                                        </div>                                  
                                </div>

                                <div class="mb-4">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Enter your password"
                                           required>
                                    
                                        <div class="text-danger small mt-1">
                                           
                                        </div>                                   
                                </div>
                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-primary">Log In</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
