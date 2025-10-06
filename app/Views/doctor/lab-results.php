<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Lab Results Management - HMS Doctor</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        /* Scoped styles for Doctor Lab Results page */
        .filter-row { display: flex; gap: 1rem; align-items: end; flex-wrap: wrap; }
        .filter-input { padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.9rem; }
        .patient-table { background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
        .table-header { background: #f9fafb; padding: 1rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
        .btn-small { padding: .5rem 1rem; font-size: .8rem; }
        .patient-table { overflow-x: auto; }
        .table { width: 100%; border-collapse: separate; border-spacing: 0; min-width: 760px; }
        .table thead th { background: #f8fafc; color: #374151; font-weight: 600; text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #e5e7eb; }
        .table tbody td { padding: 0.75rem 1rem; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
        .table tbody tr:hover { background: #f9fafb; }
        .table th:nth-child(6), .table th:nth-child(7), .table th:nth-child(8), .table td:nth-child(6), .table td:nth-child(7), .table td:nth-child(8) { text-align: center; }
        .badge { display: inline-block; padding: 0.25rem 0.6rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        .badge-danger { background: #fecaca; color: #991b1b; }
        /* Modal base */
        .modal { display: none; position: fixed; z-index: 1000; inset: 0; background: rgba(0,0,0,0.5); }
        .modal.show { display: flex; align-items: center; justify-content: center; }
        .modal-content { background: #fff; border-radius: 8px; width: 90%; max-width: 840px; max-height: 90vh; overflow-y: auto; box-shadow: 0 4px 20px rgba(0,0,0,0.15); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem; border-bottom: 1px solid #e2e8f0; background: #f7fafc; }
        .modal-header h3 { margin: 0; color: #2d3748; }
        .modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #718096; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; }
        .modal-close:hover { color: #2d3748; }
        .modal-body { padding: 1.25rem; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; padding: 1rem; border-top: 1px solid #e2e8f0; background: #f7fafc; }
    </style>
</head>
<body class="doctor">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <?php include APPPATH . 'Views/doctor/components/sidebar.php'; ?>

        <main class="content">
            <h1 class="page-title">Laboratory Results</h1>
            <div class="page-actions">
                <button class="btn btn-success" id="requestLabTestBtn">
                    <i class="fas fa-plus"></i> Request Lab Test
                </button>
            </div><br>

            <div class="dashboard-overview">
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue"><i class="fas fa-flask"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">New Results</h3>
                            <p class="card-subtitle">Awaiting Review</p>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric"><div class="metric-value blue">0</div><div class="metric-label">New</div></div>
                        <div class="metric"><div class="metric-value green">0</div><div class="metric-label">Critical</div></div>
                        <div class="metric"><div class="metric-value orange">0</div><div class="metric-label">Pending Tests</div></div>
                    </div>
                </div>
            </div>

            <div class="search-filter">
                <h3 style="margin-bottom: 1rem;">Search Lab Results</h3>
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Search Patient</label>
                        <input type="text" class="filter-input" placeholder="Search by patient name, test type, or result ID..." id="labSearch" value="">
                    </div>
                    <div class="filter-group" id="testType">
                        <label>All Test Types</label>
                        <select class="filter-input" id="testTypeFilter">
                            <option value="">All Test Types</option>
                            <option value="blood-chemistry">Blood Chemistry</option>
                            <option value="hematology">Hematology</option>
                            <option value="cardiology">Cardiology</option>
                            <option value="microbiology">Microbiology</option>
                            <option value="immunology">Immunology</option>
                        </select>
                    </div>
                    <div class="filter-group" id="statusFilter">
                        <label>All Status</label>
                        <select class="filter-input" id="statusFilterSelect">
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

            <div class="patient-table">
                <div class="table-header">
                    <h3>Recent Lab Result</h3>
                    <div style="display: flex; gap: 0.5rem;">
                        <button class="btn btn-primary btn-small" id="exportLab"><i class="fas fa-download"></i> Export</button>
                        <button class="btn btn-secondary btn-small" id="refreshLab"><i class="fas fa-sync"></i> Refresh</button>
                    </div>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Test ID</th>
                            <th>Patient</th>
                            <th>Test Type</th>
                            <th>Date Collection</th>
                            <th>Date Reported</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background-color: #fed7d7;">
                            <td>LAB001234</td>
                            <td>
                                <div>
                                    <strong>John Smith</strong><br>
                                    <small>P001234 | Age: 55</small>
                                </div>
                            </td>
                            <td>Cardiac Enzymes</td>
                            <td>Aug 20, 2025 08:30</td>
                            <td>Aug 20, 2025 14:15</td>
                            <td><span class="badge badge-danger">Critical</span></td>
                            <td><span class="badge badge-danger">STAT</span></td>
                            <td>
                                <button class="btn btn-danger review-lab-btn" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Review</button>
                                <button class="btn btn-secondary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Print</button>
                            </td>
                        </tr>
                        <tr>
                            <td>LAB001236</td>
                            <td>
                                <div>
                                    <strong>Robert Johnson</strong><br>
                                    <small>P001345 | Age: 38</small>
                                </div>
                            </td>
                            <td>Complete Blood Count</td>
                            <td>Aug 19, 2025 16:30</td>
                            <td>Aug 20, 2025 09:15</td>
                            <td><span class="badge badge-success">Normal</span></td>
                            <td><span class="badge badge-info">Routine</span></td>
                            <td>
                                <button class="btn btn-primary view-lab-btn" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">View</button>
                                <button class="btn btn-secondary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Print</button>
                            </td>
                        </tr>
                        <tr>
                            <td>LAB001237</td>
                            <td>
                                <div>
                                    <strong>Emily Davis</strong><br>
                                    <small>P001089 | Age: 29</small>
                                </div>
                            </td>
                            <td>HbA1c</td>
                            <td>Aug 19, 2025 14:00</td>
                            <td>Aug 20, 2025 08:30</td>
                            <td><span class="badge badge-warning">Abnormal</span></td>
                            <td><span class="badge badge-warning">Urgent</span></td>
                            <td>
                                <button class="btn btn-warning review-lab-btn" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Review</button>
                                <button class="btn btn-secondary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Print</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Modals -->
            <div class="modal" id="requestLabModal">
                <div class="modal-content" style="max-width: 720px;">
                    <div class="modal-header">
                        <h3>Request Lab Test</h3>
                        <button class="modal-close" id="closeRequestLab">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form id="requestLabForm">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                                <div>
                                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Patient *</label>
                                    <select class="form-select" required>
                                        <option value="">Select Patient</option>
                                        <option value="P001234">John Smith (P001234)</option>
                                        <option value="P001345">Robert Johnson (P001345)</option>
                                        <option value="P001089">Emily Davis (P001089)</option>
                                    </select>
                                </div>
                                <div>
                                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Priority *</label>
                                    <select class="form-select" required>
                                        <option value="">Select</option>
                                        <option>Routine</option>
                                        <option>Urgent</option>
                                        <option>STAT</option>
                                    </select>
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                                <div>
                                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Test Type *</label>
                                    <select class="form-select" required>
                                        <option value="">Select Test</option>
                                        <option>Blood Chemistry</option>
                                        <option>Hematology</option>
                                        <option>Cardiology</option>
                                        <option>Microbiology</option>
                                        <option>Immunology</option>
                                    </select>
                                </div>
                                <div>
                                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Collection Date *</label>
                                    <input type="datetime-local" class="form-input" required>
                                </div>
                            </div>
                            <div style="margin-bottom: 1rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Clinical Notes</label>
                                <textarea class="form-input" rows="3" placeholder="Relevant clinical details..."></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="cancelRequestLab">Cancel</button>
                        <button type="submit" form="requestLabForm" class="btn btn-success">Submit Request</button>
                    </div>
                </div>
            </div>

            <div class="modal" id="viewLabModal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Lab Result Details</h3>
                        <button class="modal-close" id="closeViewLab">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                            <div>
                                <h4 style="margin-bottom: 1rem; color: #2d3748;">Result Information</h4>
                                <div style="background: #f7fafc; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                                    <div style="margin-bottom: 0.5rem;"><strong>Test ID:</strong> <span id="labTestId">LAB001236</span></div>
                                    <div style="margin-bottom: 0.5rem;"><strong>Type:</strong> <span id="labTestType">CBC</span></div>
                                    <div style="margin-bottom: 0.5rem;"><strong>Collected:</strong> <span id="labCollected">Aug 19, 2025 16:30</span></div>
                                    <div style="margin-bottom: 0.5rem;"><strong>Reported:</strong> <span id="labReported">Aug 20, 2025 09:15</span></div>
                                    <div style="margin-bottom: 0.5rem;"><strong>Status:</strong> <span id="labStatus" class="badge badge-success">Normal</span></div>
                                    <div><strong>Priority:</strong> <span id="labPriority" class="badge badge-info">Routine</span></div>
                                </div>
                                <h4 style="margin-bottom: 1rem; color: #2d3748;">Values</h4>
                                <div style="background: #e6fffa; padding: 1rem; border-radius: 8px;">
                                    <div>Hemoglobin: 13.5 g/dL (12-16)</div>
                                    <div>WBC: 6.2 x10^9/L (4-11)</div>
                                    <div>Platelets: 250 x10^9/L (150-400)</div>
                                </div>
                            </div>
                            <div>
                                <h4 style="margin-bottom: 1rem; color: #2d3748;">Patient Information</h4>
                                <div style="background: #f0fff4; padding: 1rem; border-radius: 8px;">
                                    <div style="margin-bottom: 0.5rem;"><strong>Name:</strong> <span id="labPatientName">Robert Johnson</span></div>
                                    <div style="margin-bottom: 0.5rem;"><strong>Patient ID:</strong> <span id="labPatientId">P001345</span></div>
                                    <div style="margin-bottom: 0.5rem;"><strong>Age:</strong> <span id="labPatientAge">38 years</span></div>
                                    <div><strong>Phone:</strong> <span id="labPatientPhone">(555) 987-6543</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="closeViewLabBtn">Close</button>
                        <button type="button" class="btn btn-primary" id="printLabBtn">Print</button>
                    </div>
                </div>
            </div>

            <div class="modal" id="reviewLabModal">
                <div class="modal-content" style="max-width: 720px;">
                    <div class="modal-header">
                        <h3>Review Lab Result</h3>
                        <button class="modal-close" id="closeReviewLab">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form id="reviewLabForm">
                            <div style="margin-bottom: 1rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Assessment</label>
                                <textarea class="form-input" rows="4" placeholder="Enter your clinical assessment..."></textarea>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                                <div>
                                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Status</label>
                                    <select class="form-select">
                                        <option>Reviewed</option>
                                        <option>Follow-up Needed</option>
                                        <option>Escalate</option>
                                    </select>
                                </div>
                                <div>
                                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Notify Patient</label>
                                    <select class="form-select">
                                        <option>No</option>
                                        <option>Yes</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="cancelReviewLab">Cancel</button>
                        <button type="submit" form="reviewLabForm" class="btn btn-danger">Save Review</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
    (function() {
        const requestModal = document.getElementById('requestLabModal');
        const viewModal = document.getElementById('viewLabModal');
        const reviewModal = document.getElementById('reviewLabModal');
        function open(m) { m.classList.add('show'); }
        function close(m) { m.classList.remove('show'); }
        document.getElementById('requestLabTestBtn').addEventListener('click', function(){ open(requestModal); });
        document.getElementById('closeRequestLab').addEventListener('click', function(){ close(requestModal); });
        document.getElementById('cancelRequestLab').addEventListener('click', function(){ close(requestModal); });
        document.getElementById('requestLabForm').addEventListener('submit', function(e){ e.preventDefault(); alert('Lab request submitted.'); close(requestModal); this.reset(); });
        document.querySelectorAll('.view-lab-btn').forEach(function(btn){ btn.addEventListener('click', function(){ open(viewModal); }); });
        document.getElementById('closeViewLab').addEventListener('click', function(){ close(viewModal); });
        document.getElementById('closeViewLabBtn').addEventListener('click', function(){ close(viewModal); });
        document.getElementById('printLabBtn').addEventListener('click', function(){ window.print(); });
        document.querySelectorAll('.review-lab-btn').forEach(function(btn){ btn.addEventListener('click', function(){ open(reviewModal); }); });
        document.getElementById('closeReviewLab').addEventListener('click', function(){ close(reviewModal); });
        document.getElementById('cancelReviewLab').addEventListener('click', function(){ close(reviewModal); });
        document.getElementById('reviewLabForm').addEventListener('submit', function(e){ e.preventDefault(); alert('Review saved.'); close(reviewModal); });
        window.addEventListener('click', function(e){ [requestModal, viewModal, reviewModal].forEach(function(m){ if (e.target === m) close(m); }); });
    })();
    </script>

    <script src="<?= base_url('js/logout.js') ?>"></script>
</body>
</html>
