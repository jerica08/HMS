<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Laboratory - Test Results - HMS</title>
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
        <h1 class="page-title">Test Results</h1>

        <!-- Quick Actions -->
        <div class="quick-actions">
          <a href="#" class="btn btn-primary"><i class="fas fa-share"></i> Release Selected</a>
          <a href="#" class="btn btn-secondary"><i class="fas fa-envelope"></i> Notify Physicians</a>
          <a href="#" class="btn btn-secondary"><i class="fas fa-download"></i> Export</a>
        </div>

        <!-- Filters -->
        <div class="table-container" style="margin-top: 1.5rem">
          <h3 style="margin-bottom: 1rem">Filters</h3>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem">
            <div>
              <label style="display:block; font-weight:600; margin-bottom: .5rem">Search</label>
              <input type="text" id="search" placeholder="Sample ID, Patient, Test" style="width:100%; padding:.6rem; border:1px solid #e2e8f0; border-radius:8px" />
            </div>
            <div>
              <label style="display:block; font-weight:600; margin-bottom: .5rem">Result Status</label>
              <select id="resultStatus" style="width:100%; padding:.6rem; border:1px solid #e2e8f0; border-radius:8px">
                <option value="">All</option>
                <option value="ready">Ready</option>
                <option value="critical">Critical</option>
                <option value="hold">On Hold</option>
                <option value="released">Released</option>
              </select>
            </div>
            <div>
              <label style="display:block; font-weight:600; margin-bottom: .5rem">Date</label>
              <input type="date" id="filterDate" style="width:100%; padding:.6rem; border:1px solid #e2e8f0; border-radius:8px" />
            </div>
          </div>
        </div>

        <!-- Results Table -->
        <div class="table-container" style="margin-top: 1.5rem">
          <h3 id="critical" style="margin-bottom: 1rem">Results</h3>
          <table class="table">
            <thead>
              <tr>
                <th><input type="checkbox" /></th>
                <th>Sample ID</th>
                <th>Patient</th>
                <th>Test</th>
                <th>Result</th>
                <th>Reference</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><input type="checkbox" /></td>
                <td>LAB-2025-061</td>
                <td>Maria Garcia</td>
                <td>Complete Blood Count</td>
                <td>WBC: 6.5, Hgb: 12.9</td>
                <td>WBC: 4.5-11, Hgb: 12-16</td>
                <td><span class="badge badge-success">Ready</span></td>
                <td>
                  <a href="#" class="btn btn-primary" style="padding:.3rem .8rem; font-size:.8rem">Release</a>
                </td>
              </tr>
              <tr>
                <td><input type="checkbox" /></td>
                <td>LAB-2025-062</td>
                <td>David Lee</td>
                <td>Potassium</td>
                <td>6.8 mEq/L</td>
                <td>3.5-5.0 mEq/L</td>
                <td><span class="badge badge-danger">Critical</span></td>
                <td>
                  <a href="#" class="btn btn-danger" style="padding:.3rem .8rem; font-size:.8rem">Call</a>
                </td>
              </tr>
              <tr>
                <td><input type="checkbox" /></td>
                <td>LAB-2025-063</td>
                <td>Lisa Anderson</td>
                <td>Urinalysis</td>
                <td>Normal</td>
                <td>-</td>
                <td><span class="badge badge-warning">On Hold</span></td>
                <td>
                  <a href="#" class="btn btn-secondary" style="padding:.3rem .8rem; font-size:.8rem">Review</a>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </main>
    </div>

    <script>
      // Sidebar links navigate to static pages; no JS needed here.

      function handleLogout() {
        if (confirm('Are you sure you want to logout?')) {
          alert('Logged out (demo)');
        }
      }
    </script>
  </body>
  </html>
