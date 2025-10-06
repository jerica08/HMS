<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Laboratory - Sample Management - HMS</title>
    <link rel="stylesheet" href="assets/css/common.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    />
  </head>
  <body class="lab-theme">

    <?php include APPPATH . 'Views/template/header.php'; ?>
    <div class="main-container">
      <?php include APPPATH . 'Views/laboratorists/components/sidebar.php'; ?>

      <!-- Main Content -->
      <main class="content">
        <h1 class="page-title">Sample Management</h1>

        <!-- Quick Actions -->
        <div class="quick-actions">
          <a href="#" class="btn btn-primary"><i class="fas fa-plus"></i> Log Sample</a>
          <a href="#" class="btn btn-secondary"><i class="fas fa-inbox"></i> Bulk Receive</a>
          <a href="#" class="btn btn-secondary"><i class="fas fa-tags"></i> Print Labels</a>
        </div>

        <!-- Filters -->
        <div class="table-container" style="margin-top: 1.5rem">
          <h3 style="margin-bottom: 1rem">Filters</h3>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem">
            <div>
              <label style="display:block; font-weight:600; margin-bottom: .5rem">Search</label>
              <input type="text" id="search" placeholder="Sample ID or Patient Name" style="width:100%; padding:.6rem; border:1px solid #e2e8f0; border-radius:8px" />
            </div>
            <div>
              <label style="display:block; font-weight:600; margin-bottom: .5rem">Status</label>
              <select id="status" style="width:100%; padding:.6rem; border:1px solid #e2e8f0; border-radius:8px">
                <option value="">All</option>
                <option value="received">Received</option>
                <option value="processing">Processing</option>
                <option value="completed">Completed</option>
                <option value="rejected">Rejected</option>
              </select>
            </div>
            <div>
              <label style="display:block; font-weight:600; margin-bottom: .5rem">Sample Type</label>
              <select id="filterSampleType" style="width:100%; padding:.6rem; border:1px solid #e2e8f0; border-radius:8px">
                <option value="">All</option>
                <option value="blood">Blood</option>
                <option value="serum">Serum</option>
                <option value="urine">Urine</option>
                <option value="swab">Swab</option>
              </select>
            </div>
            <div>
              <label style="display:block; font-weight:600; margin-bottom: .5rem">Date</label>
              <input type="date" id="filterDate" style="width:100%; padding:.6rem; border:1px solid #e2e8f0; border-radius:8px" />
            </div>
          </div>
        </div>

        <!-- Log/Receive Sample Form -->
        <div class="table-container" style="margin-top: 1.5rem">
          <h3 id="log" style="margin-bottom: 1rem">Log New Sample</h3>
          <form id="sample-form">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem">
              <div>
                <label for="sampleId" style="display:block; font-weight:600; margin-bottom: .5rem">Sample ID</label>
                <input id="sampleId" name="sampleId" type="text" placeholder="Auto or e.g., LAB-2025-100" style="width:100%; padding:.75rem; border:1px solid #e2e8f0; border-radius:8px" />
              </div>
              <div>
                <label for="patientId" style="display:block; font-weight:600; margin-bottom: .5rem">Patient ID</label>
                <input id="patientId" name="patientId" type="text" placeholder="e.g., P-2025-001" style="width:100%; padding:.75rem; border:1px solid #e2e8f0; border-radius:8px" required />
              </div>
              <div>
                <label for="patientName" style="display:block; font-weight:600; margin-bottom: .5rem">Patient Name</label>
                <input id="patientName" name="patientName" type="text" placeholder="e.g., Juan Dela Cruz" style="width:100%; padding:.75rem; border:1px solid #e2e8f0; border-radius:8px" required />
              </div>
              <div>
                <label for="testType" style="display:block; font-weight:600; margin-bottom: .5rem">Test Type</label>
                <select id="testType" name="testType" style="width:100%; padding:.75rem; border:1px solid #e2e8f0; border-radius:8px" required>
                  <option value="">Select a test</option>
                  <option value="cbc">Complete Blood Count (CBC)</option>
                  <option value="bmp">Basic Metabolic Panel (BMP)</option>
                  <option value="lft">Liver Function Tests (LFT)</option>
                  <option value="urinalysis">Urinalysis</option>
                  <option value="culture">Blood Culture</option>
                </select>
              </div>
              <div>
                <label for="sampleType" style="display:block; font-weight:600; margin-bottom: .5rem">Sample Type</label>
                <select id="sampleType" name="sampleType" style="width:100%; padding:.75rem; border:1px solid #e2e8f0; border-radius:8px">
                  <option value="blood">Blood</option>
                  <option value="serum">Serum</option>
                  <option value="urine">Urine</option>
                  <option value="swab">Swab</option>
                </select>
              </div>
              <div>
                <label for="priority" style="display:block; font-weight:600; margin-bottom: .5rem">Priority</label>
                <select id="priority" name="priority" style="width:100%; padding:.75rem; border:1px solid #e2e8f0; border-radius:8px">
                  <option value="routine">Routine</option>
                  <option value="urgent">Urgent</option>
                  <option value="stat">STAT</option>
                </select>
              </div>
              <div>
                <label for="collectionDate" style="display:block; font-weight:600; margin-bottom: .5rem">Collection Date</label>
                <input id="collectionDate" name="collectionDate" type="date" style="width:100%; padding:.75rem; border:1px solid #e2e8f0; border-radius:8px" />
              </div>
              <div>
                <label for="collectionTime" style="display:block; font-weight:600; margin-bottom: .5rem">Collection Time</label>
                <input id="collectionTime" name="collectionTime" type="time" style="width:100%; padding:.75rem; border:1px solid #e2e8f0; border-radius:8px" />
              </div>
              <div>
                <label for="receivedBy" style="display:block; font-weight:600; margin-bottom: .5rem">Received By</label>
                <input id="receivedBy" name="receivedBy" type="text" placeholder="e.g., Staff Initials" style="width:100%; padding:.75rem; border:1px solid #e2e8f0; border-radius:8px" />
              </div>
            </div>
            <div style="margin-top: 1rem">
              <label for="remarks" style="display:block; font-weight:600; margin-bottom: .5rem">Remarks</label>
              <textarea id="remarks" name="remarks" rows="3" placeholder="Additional notes..." style="width:100%; padding:.75rem; border:1px solid #e2e8f0; border-radius:8px; resize: vertical"></textarea>
            </div>
            <div class="quick-actions" style="justify-content: flex-end; margin-top: 1rem">
              <button type="reset" class="btn btn-secondary"><i class="fas fa-eraser"></i> Clear</button>
              <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Sample</button>
            </div>
          </form>
        </div>

        <!-- Sample Queue Table -->
        <div class="table-container" style="margin-top: 2rem">
          <h3 id="queue" style="margin-bottom: 1rem">Sample Processing Queue</h3>
          <table class="table">
            <thead>
              <tr>
                <th>Sample ID</th>
                <th>Patient</th>
                <th>Test Type</th>
                <th>Sample Type</th>
                <th>Status</th>
                <th>Received</th>
                <th>ETA</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>LAB-2025-041</td>
                <td>John Martinez</td>
                <td>Cardiac Enzymes</td>
                <td>Serum</td>
                <td><span class="badge badge-warning">Processing</span></td>
                <td>09:20 AM</td>
                <td>10:15 AM</td>
                <td>
                  <a href="#" class="btn btn-primary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem">Update</a>
                </td>
              </tr>
              <tr>
                <td>LAB-2025-042</td>
                <td>Sarah Wilson</td>
                <td>Blood Gas Analysis</td>
                <td>Blood</td>
                <td><span class="badge badge-info">Queued</span></td>
                <td>09:45 AM</td>
                <td>11:00 AM</td>
                <td>
                  <a href="#" class="btn btn-success" style="padding: 0.3rem 0.8rem; font-size: 0.8rem">Start</a>
                </td>
              </tr>
              <tr>
                <td>LAB-2025-043</td>
                <td>Allison Wang</td>
                <td>Blood Culture</td>
                <td>Blood</td>
                <td><span class="badge badge-success">Completed</span></td>
                <td>08:50 AM</td>
                <td>Completed</td>
                <td>
                  <a href="#" class="btn btn-secondary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem">Review</a>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </main>
    </div>

    <script>
      // Sidebar active state
      document.querySelectorAll('.nav-link').forEach((link) => {
        link.addEventListener('click', function () {
          document.querySelectorAll('.nav-link').forEach((l) => l.classList.remove('active'));
          this.classList.add('active');
        });
      });

      function handleLogout() {
        if (confirm('Are you sure you want to logout?')) {
          alert('Logged out (demo)');
        }
      }

      // Demo submit behavior for sample form
      (function(){
        var f = document.getElementById('sample-form');
        if (f) {
          f.addEventListener('submit', function(e){
            e.preventDefault();
            alert('Sample logged (demo). Integrate with backend to save.');
            f.reset();
          });
        }
      })();
    </script>
  </body>
  </html>
