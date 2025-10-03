<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Patient Management - HMS Admin</title>
        <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
            .patient-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
                gap: 1.5rem;
                margin-bottom: 2rem;
            }
            .patient-section {
                background: white;
                border-radius: 8px;
                padding: 1.5rem;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .section-header {
                display: flex;
                align-items: center;
                gap: 1rem;
                margin-bottom: 1.5rem;
                padding-bottom: 1rem;
                border-bottom: 1px solid #e2e8f0;
            }
            .section-icon {
                width: 40px;
                height: 40px;
                border-radius: 8px;
                background: #3b82f6;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 1.2rem;
            }
            .patient-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 1rem 0;
                border-bottom: 1px solid #f3f4f6;
            }
            .patient-item:last-child {
                border-bottom: none;
            }
            .patient-info {
                flex: 1;
            }
            .patient-name {
                font-weight: 500;
                color: #1f2937;
                margin-bottom: 0.25rem;
            }
            .patient-details {
                font-size: 0.8rem;
                color: #6b7280;
            }
            .patient-status {
                padding: 0.25rem 0.75rem;
                border-radius: 15px;
                font-size: 0.8rem;
                font-weight: 500;
            }
            .status-admitted { background: #fef3c7; color: #92400e; }
            .status-discharged { background: #dcfce7; color: #166534; }
            .status-critical { background: #fecaca; color: #991b1b; }
            .status-stable { background: #dbeafe; color: #1e40af; }
            .status-emergency { background: #fed7cc; color: #c2410c; }
            .search-filters {
                background: white;
                border-radius: 8px;
                padding: 1.5rem;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                margin-bottom: 2rem;
            }
            .filter-row {
                display: flex;
                gap: 1rem;
                align-items: end;
                flex-wrap: wrap;
            }
            .filter-group {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
                min-width: 150px;
            }
            .filter-input {
                padding: 0.5rem;
                border: 1px solid #e2e8f0;
                border-radius: 5px;
                font-size: 0.9rem;
            }
            .patient-table {
                background: white;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .table-header {
                background: #f8fafc;
                padding: 1rem;
                border-bottom: 1px solid #e2e8f0;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .patient-avatar {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background: #4299e1;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: bold;
                font-size: 0.9rem;
            }
            .action-buttons {
                display: flex;
                gap: 0.5rem;
                margin-top: 1rem;
                flex-wrap: wrap;
            }
            .btn-small {
                padding: 0.5rem 1rem;
                font-size: 0.8rem;
            }
            .critical-alert {
                background: #fef2f2;
                border: 1px solid #fecaca;
                border-left: 4px solid #ef4444;
                border-radius: 8px;
                padding: 1rem;
                margin-bottom: 1rem;
            }
            .alert-header {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                font-weight: 600;
                color: #991b1b;
                margin-bottom: 0.5rem;
            }
            .alert-content {
                color: #7f1d1d;
                font-size: 0.9rem;
            }
            .quick-actions {
                background: white;
                border-radius: 8px;
                padding: 1.5rem;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                margin-bottom: 2rem;
            }
            .actions-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1rem;
                margin-top: 1rem;
            }
            .patient-flow {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.75rem;
                background: #f8fafc;
                border-radius: 6px;
                margin: 0.5rem 0;
                font-size: 0.9rem;
            }
            .flow-number {
                font-weight: bold;
                color: #3b82f6;
            }
        </style>
    </head>
    <body class="admin">

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
                    <button class="logout-btn" onclick="handleLogout()">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </button>
                </div>
            </div>
        </header>
        <!--Main Content-->
        <div class="main-container">
                <!--sidebar-->
                <nav class="sidebar">
                
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="<?= base_url('admin/dashboard') ?>" class="nav-link">
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
        
            <!--Main Content-->
            <main class="content">
                <h1 class="page-title"> Patient Management</h1>

                <!--Dashboard overview cards-->
                <div class="dashboard-overview">
                    <!-- Total Patient Cards -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern blue">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Total Patient</h3>
                                <p class="card-subtitle">All Registered Users</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value blue"><?= $patientStats['total_patients'] ?? 0 ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Active User Card -->
                    <div class="overview-card">
                        <div class="card-header-modern">
                            <div class="card-icon-modern purple">
                                <i class="fas fa-bed"></i>
                            </div>
                            <div class="card-info">
                                <h3 class="card-title-modern">Admitted Patient</h3>
                                <p class="card-subtitle">Currently active</p>
                            </div>
                        </div>
                        <div class="card-metrics">
                            <div class="metric">
                                <div class="metric-value purple">0</div>
                            </div>
                        </div>   
                    </div>
                </div>       

                <div class="patient-view">         
                    <!--Filter and Actions-->    
                    <div class="search-filter">
                        <h3 style="margin-bottom: 1rem;">Patient Search & Filters</h3>
                        <div class="filter-row">
                            <div class="filter-group">
                                <label> Search Patient</label>
                                <input type="text" class="filter-input" placeholder="Search by name, email, or ID..." 
                                    id="searchInput" value="">
                            </div>
                            <div class="filter-group">
                                <label>Status Filter</label>
                                <select class="filter-input" id="statusFilter">
                                    <option value="">All Status</option>
                                    <option value="admitted">Admitted</option>
                                    <option value="discharged">Dishcarge</option>
                                    <option value="critical">Critical</option>
                                    <option value="emergency">Emergency</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label> Role Filter</label>
                                <select class="filter-input" id="roleFilter">
                                    <option value="">All Roles</option>
                                    <option value="admin">Administrator</option>
                                    <option value="doctor">Doctor</option>
                                    <option value="nurse">Nurse</option>
                                    <option value="receptionist">Receptionist</option>
                                    <option value="laboratorist">Laboratory Staff</option>
                                    <option value="pharmacist">Pharmacist</option>
                                    <option value="accountant">Accountant</option>
                                    <option value="it_staff">IT Staff</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label>Department</label>
                                <select class="filter-input" id="departmentFilter">
                                    <option value="">All Departments</option>
                                    <option value="emergency">Emergency</option>
                                    <option value="icu">ICU</option>
                                    <option value="cardiology">Cardiology</option>
                                    <option value="general">General Ward</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label>Date Range</label>
                                <select class="filter-input" id="dateFilter">
                                    <option value="today">Today</option>
                                    <option value="week">This Week</option>
                                    <option value="month">This Month</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label>&nbsp;</label>
                                <button class="btn btn-primary" onclick="applyFilters()">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>     
                        </div>          
                    </div><br>
                    <!-- Patient List Table -->
                    <div class="patient-table">
                        <div class="table-header">
                            <h3>Patient Directory</h3>
                            <div style="display: flex; gap: 0.5rem;">
                                <button class="btn btn-secondary btn-small" onclick="refreshPatients()">
                                    <i class="fas fa-refresh"></i> Refresh
                                </button>
                                <button class="btn btn-primary btn-small" onclick="addPatient()">
                                    <i class="fas fa-plus"></i> Add Patient
                                </button>
                            </div>
                        </div>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>ID</th>
                                    <th>Age</th>
                                    <th>Department</th>
                                    <th>Room</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <div class="patient-avatar">MS</div>
                                            <div>
                                                <div style="font-weight: 500;">Example1</div>
                                                <div style="font-size: 0.8rem; color: #6b7280;">example@email.com</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>P-2024-0156</td>
                                    <td>45</td>
                                    <td>ICU</td>
                                    <td>301</td>
                                    <td><span class="patient-status status-critical">Critical</span></td>
                                    <td>
                                        <button class="btn btn-secondary btn-small">View</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
        <script src="/js/logout.js"></script>
    </body>
</html>