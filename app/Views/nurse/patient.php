<!DOCTYPE html>
<html lang="en">
<head>
     <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Care - HMS Nurse</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/users.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
            /* Filters */
            .filter-row { display: flex; gap: 1rem; align-items: end; flex-wrap: wrap; }
            .filter-group { display: flex; flex-direction: column; gap: 0.5rem; min-width: 150px; }
            .filter-input { padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 5px; font-size: 0.9rem; }

            /* Table enhancements */
            .table-container { background:#fff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.06); overflow:auto; max-height:60vh; }
            .table { width:100%; border-collapse: separate; border-spacing:0; min-width: 900px; }
            .table thead th { position: sticky; top: 0; background:#f8fafc; color:#374151; font-weight:600; text-align:left; padding: .75rem 1rem; border-bottom:1px solid #e5e7eb; z-index:1; }
            .table tbody td { padding:.75rem 1rem; border-bottom:1px solid #f3f4f6; vertical-align: middle; }
            .table tbody tr:nth-child(odd) { background:#fcfcfd; }
            .table tbody tr:hover { background:#f9fafb; }
            .table th:last-child, .table td:last-child { text-align:right; white-space: nowrap; }

            /* Badges & compact buttons */
            .badge { display:inline-block; padding:.25rem .6rem; border-radius:999px; font-size:.75rem; font-weight:600; }
            .badge-success { background:#dcfce7; color:#166534; }
            .badge-warning { background:#fef3c7; color:#92400e; }
            .badge-danger  { background:#fecaca; color:#991b1b; }
            .btn.btn-primary.btn-compact, .btn.btn-secondary.btn-compact { padding: .35rem .65rem; font-size:.8rem; }
    </style>
</head>
<body class="nurse">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <?= $this->include('Views/nurse/components/sidebar') ?>

        <main class="content">
            <h1 class="page-title"> Patient Care Management</h1>
            <div class="page-actions">
                <button class="btn btn-success"><i class="fas fa-plus"></i> Add Patient Note</button>
            </div>
            <br>

            <div class="dashboard-overview">
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue"><i class="fas fa-bed"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Total Patients</h3>
                            <p class="card-subtitle">Assigned to you</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric"><div class="metric-value blue">0</div></div>
                    </div>
                </div>
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern purple"><i class="fas fa-check-circle"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Stable Patients</h3>
                            <p class="card-subtitle">Normal condition</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric"><div class="metric-value purple">0</div></div>
                    </div>
                </div>
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern purple"><i class="fas fa-exclamation-triangle"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Critical Patients</h3>
                            <p class="card-subtitle">Requires attentions</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric"><div class="metric-value purple">0</div></div>
                    </div>
                </div>
            </div>

            <div class="search-filter">
                <h3>My Assigned Patients</h3>
                <div class="search-filter">
                    <div class="filter-row">
                        <div class="filter-group">
                            <input type="text" class="filter-input" placeholder="Search by patient name, test type, or result ID..." id="labSearch" value="">
                        </div>
                        <div class="filter-group" id="statusFilter">
                            <select class="filter-input" id="roleFilter">
                                <option value="">All Status</option>
                                <option value="new">New</option>
                                <option value="reviewed">Reviewed</option>
                                <option value="critical">Critical</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>&nbsp;</label>
                            <button class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                        </div>
                    </div>
                </div>

                <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Room</th>
                            <th>Patient</th>
                            <th>Age</th>
                            <th>Condition</th>
                            <th>Status</th>
                            <th>Last Check</th>
                            <th>Priority</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="patientTableBody">
                        <tr>
                            <td>201</td>
                            <td>
                                <div class="patient-info">
                                    <strong>John Smith</strong>
                                    <small>ID: P001</small>
                                </div>
                            </td>
                            <td>45</td>
                            <td>Post-operative recovery</td>
                            <td><span class="badge badge-success">Stable</span></td>
                            <td>2 hours ago</td>
                            <td><span class="badge badge-success">Low</span></td>
                            <td>
                                <button class="btn btn-primary btn-compact">View</button>
                                <button class="btn btn-secondary btn-compact">Note</button>
                                <button class="btn btn-secondary btn-compact">Edit</button>
                            </td>
                        </tr>
                        <tr>
                            <td>203</td>
                            <td>
                                <div class="patient-info">
                                    <strong>Maria Garcia</strong>
                                    <small>ID: P002</small>
                                </div>
                            </td>
                            <td>62</td>
                            <td>Cardiac monitoring</td>
                            <td><span class="badge badge-danger">Critical</span></td>
                            <td>30 minutes ago</td>
                            <td><span class="badge badge-danger">High</span></td>
                            <td>
                                <button class="btn btn-primary btn-compact">View</button>
                                <button class="btn btn-secondary btn-compact">Note</button>
                                <button class="btn btn-secondary btn-compact">Edit</button>
                            </td>
                        </tr>
                        <tr>
                            <td>205</td>
                            <td>
                                <div class="patient-info">
                                    <strong>David Lee</strong>
                                    <small>ID: P003</small>
                                </div>
                            </td>
                            <td>38</td>
                            <td>Wound care treatment</td>
                            <td><span class="badge badge-warning">Recovering</span></td>
                            <td>1 hour ago</td>
                            <td><span class="badge badge-warning">Medium</span></td>
                            <td>
                                <button class="btn btn-primary btn-compact">View</button>
                                <button class="btn btn-secondary btn-compact">Note</button>
                                <button class="btn btn-secondary btn-compact">Edit</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>
            </div>
        </main>
    </div>

    <script src="<?= base_url('js/logout.js') ?>"></script>
</body>
</html>
