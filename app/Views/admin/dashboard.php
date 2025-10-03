<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - HMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Times New Roman', serif;
        }
        .navbar {
            background-color: #24649cff !important;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #24649cff;
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <h4 class="mb-0">Hospital Management System</h4>
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                        Welcome, <?= $user['email'] ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">Profile</a></li>
                        <li><a class="dropdown-item" href="#">Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= base_url('/admin/logout') ?>">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Admin Menu</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="#" class="list-group-item list-group-item-action active">Dashboard</a>
                            <a href="#" class="list-group-item list-group-item-action">Manage Users</a>
                            <a href="#" class="list-group-item list-group-item-action">Manage Staff</a>
                            <a href="#" class="list-group-item list-group-item-action">Manage Doctors</a>
                            <a href="#" class="list-group-item list-group-item-action">Manage Patients</a>
                            <a href="#" class="list-group-item list-group-item-action">Reports</a>
                            <a href="#" class="list-group-item list-group-item-action">Settings</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="col-md-9">
                <div class="row">
                    <div class="col-12">
                        <h2>Admin Dashboard</h2>
                        <p class="text-muted">Welcome to the Hospital Management System</p>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-white" style="background-color: #28a745;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4>150</h4>
                                        <p class="mb-0">Total Patients</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-users fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white" style="background-color: #007bff;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4>25</h4>
                                        <p class="mb-0">Doctors</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-user-md fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white" style="background-color: #ffc107;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4>45</h4>
                                        <p class="mb-0">Staff Members</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-users fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white" style="background-color: #dc3545;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4>12</h4>
                                        <p class="mb-0">Appointments today</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-calendar fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Recent Activity</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-success" role="alert">
                                    <strong>Login Successful!</strong> You have successfully logged into the admin dashboard.
                                </div>
                                <p>User ID: <?= $user['user_id'] ?></p>
                                <p>Staff ID: <?= $user['staff_id'] ?></p>
                                <p>Email: <?= $user['email'] ?></p>
                                <p>Role: <?= ucfirst($user['role']) ?></p>
                                <p>Login Time: <?= date('Y-m-d H:i:s') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</body>
</html>