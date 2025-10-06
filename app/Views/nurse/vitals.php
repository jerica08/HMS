<!DOCTYPE html>
<html lang="en">
<head>
     <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vitals - HMS Nurse</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
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
        .status-critical { background:#fecaca; color:#991b1b; }
        .status-stable { background:#dcfce7; color:#166534; }
        .status-emergency { background:#fed7cc; color:#c2410c; }
        .btn.btn-primary.btn-small, .btn.btn-secondary.btn-small { padding: .45rem .75rem; font-size:.85rem; }

        /* Modal base */
        .modal { display: none; position: fixed; z-index: 1000; inset: 0; background: rgba(0,0,0,0.5); }
        .modal.show { display: flex; align-items: center; justify-content: center; }
        .modal-content { background: #fff; border-radius: 8px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 4px 20px rgba(0,0,0,0.15); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border-bottom: 1px solid #e2e8f0; background: #f7fafc; }
        .modal-header h3 { margin: 0; color: #2d3748; }
        .modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #718096; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; }
        .modal-close:hover { color: #2d3748; }
        .modal-body { padding: 1.5rem; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 1rem; padding: 1.5rem; border-top: 1px solid #e2e8f0; background: #f7fafc; }
        .form-input, .form-select { width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.9rem; }
        .form-input:focus, .form-select:focus { outline: none; border-color: #4299e1; box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1); }
    </style>
</head>
<body class="nurse">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <?= $this->include('Views/nurse/components/sidebar') ?>

        <main class="content">
            <h1 class="page-title">Vital Signs Management</h1>
            <div class="page-actions">
                <button class="btn btn-success"><i class="fas fa-plus"></i> Record New Vitals</button>
            </div>
            <br>

            <div class="dashboard-overview">
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue"><i class="fas fa-heartbeat"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Today's Vitals</h3>
                            <p class="card-subtitle">Vital signs recorded today</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric"><div class="metric-value blue">0</div><div class="metric-label">Total Recorded</div></div>
                        <div class="metric"><div class="metric-value green">0</div><div class="metric-label">Normal</div></div>
                        <div class="metric"><div class="metric-value purple">0</div><div class="metric-label">Abnormal</div></div>
                    </div>
                </div>
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern green"><i class="fas fa-clock"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Monitoring Schedule</h3>
                            <p class="card-subtitle">Upcoming vital checks</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric"><div class="metric-value green">0</div><div class="metric-label">Next Hour</div></div>
                        <div class="metric"><div class="metric-value blue">0</div><div class="metric-label">Today</div></div>
                        <div class="metric"><div class="metric-value purple">0</div><div class="metric-label">Overdue</div></div>
                    </div>
                </div>
            </div>

            <div class="search-filters">
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Patient Name/ID</label>
                        <input type="text" class="filter-input" placeholder="Search patient...">
                    </div>
                    <div class="filter-group">
                        <label>Room Number</label>
                        <input type="text" class="filter-input" placeholder="Room #">
                    </div>
                    <div class="filter-group">
                        <label>Status</label>
                        <select class="filter-input">
                            <option value="">All Status</option>
                            <option value="stable">Stable</option>
                            <option value="critical">Critical</option>
                            <option value="emergency">Emergency</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Date</label>
                        <input type="date" class="filter-input">
                    </div>
                    <div class="filter-group">
                        <label>&nbsp;</label>
                        <button class="btn btn-primary btn-small">Apply Filters</button>
                    </div>
                </div>
            </div>

            <div class="patient-table">
                <div class="table-header">
                    <h3>Patient Vital Signs</h3>
                    <div class="action-buttons">
                        <button class="btn btn-primary btn-small"><i class="fas fa-download"></i> Export</button>
                        <button class="btn btn-secondary btn-small"><i class="fas fa-print"></i> Print</button>
                    </div>
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Room</th>
                                <th>Blood Pressure</th>
                                <th>Heart Rate</th>
                                <th>Temperature</th>
                                <th>Oxygen Sat</th>
                                <th>Status</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>John Smith</strong><br><small>ID: P001234</small></td>
                                <td>205</td>
                                <td><span style="color:#ef4444;font-weight:bold;">180/120</span></td>
                                <td>95 bpm</td>
                                <td>98.6°F</td>
                                <td>98%</td>
                                <td><span class="badge status-critical">Critical</span></td>
                                <td>10:30 AM</td>
                                <td>
                                    <button class="btn btn-primary btn-small">Update</button>
                                    <button class="btn btn-secondary btn-small">View</button>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Maria Garcia</strong><br><small>ID: P001235</small></td>
                                <td>203</td>
                                <td>120/80</td>
                                <td>72 bpm</td>
                                <td>98.2°F</td>
                                <td>99%</td>
                                <td><span class="badge status-stable">Stable</span></td>
                                <td>11:15 AM</td>
                                <td>
                                    <button class="btn btn-primary btn-small">Update</button>
                                    <button class="btn btn-secondary btn-small">View</button>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Sarah Wilson</strong><br><small>ID: P001237</small></td>
                                <td>207</td>
                                <td>140/90</td>
                                <td>88 bpm</td>
                                <td>99.1°F</td>
                                <td>96%</td>
                                <td><span class="badge status-emergency">Emergency</span></td>
                                <td>11:30 AM</td>
                                <td>
                                    <button class="btn btn-primary btn-small">Update</button>
                                    <button class="btn btn-secondary btn-small">View</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Record New Vitals Modal -->
            <div class="modal" id="recordVitalsModal">
                <div class="modal-content" style="max-width: 720px;">
                    <div class="modal-header">
                        <h3>Record New Vital Signs</h3>
                        <button class="modal-close" data-close>&times;</button>
                    </div>
                    <div class="modal-body">
                        <form id="recordVitalsForm">
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                                <div>
                                    <label>Patient ID/Name</label>
                                    <input type="text" class="form-input" name="patient_search" placeholder="Search patient..." required>
                                </div>
                                <div>
                                    <label>Room Number</label>
                                    <input type="text" class="form-input" name="room_number" placeholder="Room #" required>
                                </div>
                            </div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                                <div>
                                    <label>Blood Pressure (Systolic)</label>
                                    <input type="number" class="form-input" name="bp_systolic" placeholder="120" min="60" max="250" required>
                                </div>
                                <div>
                                    <label>Blood Pressure (Diastolic)</label>
                                    <input type="number" class="form-input" name="bp_diastolic" placeholder="80" min="40" max="150" required>
                                </div>
                            </div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                                <div>
                                    <label>Heart Rate (bpm)</label>
                                    <input type="number" class="form-input" name="heart_rate" placeholder="72" min="30" max="200" required>
                                </div>
                                <div>
                                    <label>Temperature (°F)</label>
                                    <input type="number" class="form-input" name="temperature" placeholder="98.6" step="0.1" min="95" max="110" required>
                                </div>
                            </div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                                <div>
                                    <label>Oxygen Saturation (%)</label>
                                    <input type="number" class="form-input" name="oxygen_sat" placeholder="98" min="70" max="100" required>
                                </div>
                                <div>
                                    <label>Respiratory Rate</label>
                                    <input type="number" class="form-input" name="respiratory_rate" placeholder="16" min="8" max="40">
                                </div>
                            </div>
                            <div style="margin-bottom:1rem;">
                                <label>Notes</label>
                                <textarea class="form-input" name="notes" rows="3" placeholder="Additional observations or notes..."></textarea>
                            </div>
                            <div>
                                <label>Priority Level</label>
                                <select class="form-select" name="priority" required>
                                    <option value="">Select Priority</option>
                                    <option value="normal">Normal</option>
                                    <option value="elevated">Elevated</option>
                                    <option value="critical">Critical</option>
                                    <option value="emergency">Emergency</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-close>Cancel</button>
                        <button type="submit" form="recordVitalsForm" class="btn btn-success">Save Vitals</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        const recordVitalsModal = document.getElementById('recordVitalsModal');
        function openModal(m){ m.classList.add('show'); document.body.style.overflow='hidden'; }
        function closeModal(m){ m.classList.remove('show'); document.body.style.overflow='auto'; }
        document.querySelector('.page-actions .btn').addEventListener('click', () => openModal(recordVitalsModal));
        document.querySelectorAll('[data-close]').forEach(btn => btn.addEventListener('click', () => closeModal(recordVitalsModal)));
        window.addEventListener('click', (e) => { if (e.target === recordVitalsModal) closeModal(recordVitalsModal); });
        document.getElementById('recordVitalsForm').addEventListener('submit', function(e){ e.preventDefault(); alert('Vital signs recorded successfully!'); closeModal(recordVitalsModal); this.reset(); });
    </script>

    <script src="<?= base_url('js/logout.js') ?>"></script>
</body>
</html>
