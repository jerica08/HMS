<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Management - IT Staff - HMS</title>
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="it-theme">

    <?php include APPPATH . 'Views/template/header.php'; ?>
    <div class="main-container">
        <?php include APPPATH . 'Views/IT-staff/components/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="content">
            <h1 class="page-title">Security Management</h1>

            <div class="dashboard-overview">
                <!-- Access Control -->
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue"><i class="fas fa-user-shield"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Access Control</h3>
                            <p class="card-subtitle">Role-based permissions</p>
                        </div>
                    </div>
                    <div class="table-container" style="padding:1rem;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Role</th>
                                    <th>Patients</th>
                                    <th>Billing</th>
                                    <th>Inventory</th>
                                    <th>Admin</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Admin</td>
                                    <td><input type="checkbox" checked></td>
                                    <td><input type="checkbox" checked></td>
                                    <td><input type="checkbox" checked></td>
                                    <td><input type="checkbox" checked></td>
                                </tr>
                                <tr>
                                    <td>Doctor</td>
                                    <td><input type="checkbox" checked></td>
                                    <td><input type="checkbox"></td>
                                    <td><input type="checkbox"></td>
                                    <td><input type="checkbox"></td>
                                </tr>
                                <tr>
                                    <td>Nurse</td>
                                    <td><input type="checkbox" checked></td>
                                    <td><input type="checkbox"></td>
                                    <td><input type="checkbox"></td>
                                    <td><input type="checkbox"></td>
                                </tr>
                                <tr>
                                    <td>Accountant</td>
                                    <td><input type="checkbox"></td>
                                    <td><input type="checkbox" checked></td>
                                    <td><input type="checkbox"></td>
                                    <td><input type="checkbox"></td>
                                </tr>
                                <tr>
                                    <td>IT Staff</td>
                                    <td><input type="checkbox"></td>
                                    <td><input type="checkbox"></td>
                                    <td><input type="checkbox" checked></td>
                                    <td><input type="checkbox" checked></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="quick-actions">
                            <button class="btn btn-primary" onclick="savePermissions()"><i class="fas fa-save"></i> Save Permissions</button>
                            <button class="btn btn-secondary" onclick="resetPermissions()"><i class="fas fa-undo"></i> Reset</button>
                        </div>
                    </div>
                </div>

                <!-- Password & Authentication -->
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern purple"><i class="fas fa-key"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Password & Authentication</h3>
                            <p class="card-subtitle">Policies, reset, MFA</p>
                        </div>
                    </div>
                    <form onsubmit="return applyAuthPolicy(this)">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>User Email (reset)</label>
                                <input class="form-input" type="email" name="reset_email" placeholder="user@example.com">
                            </div>
                            <div class="form-group">
                                <label>Strong Password Policy</label>
                                <select class="form-input" name="policy">
                                    <option>Disabled</option>
                                    <option selected>Enabled</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Multi‑Factor Authentication</label>
                                <select class="form-input" name="mfa">
                                    <option>Disabled</option>
                                    <option selected>Enabled</option>
                                </select>
                            </div>
                            <div class="form-group full-width">
                                <button class="btn btn-primary" type="submit"><i class="fas fa-lock"></i> Apply Policy</button>
                                <button class="btn btn-warning" type="button" onclick="resetPassword()"><i class="fas fa-user-lock"></i> Reset Password</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Data Protection -->
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue"><i class="fas fa-user-secret"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Data Protection</h3>
                            <p class="card-subtitle">Encryption and key management</p>
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Encrypt at Rest</label>
                            <select class="form-input" id="enc-rest">
                                <option>Disabled</option>
                                <option selected>Enabled</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Encrypt in Transit (TLS)</label>
                            <select class="form-input" id="enc-tls">
                                <option>Disabled</option>
                                <option selected>Enabled</option>
                            </select>
                        </div>
                        <div class="form-group full-width">
                            <button class="btn btn-success" onclick="rotateKeys()"><i class="fas fa-sync"></i> Rotate Keys</button>
                            <button class="btn btn-secondary" onclick="exportKey()"><i class="fas fa-file-export"></i> Export Public Key</button>
                        </div>
                    </div>
                </div>

                <!-- Intrusion Monitoring -->
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern purple"><i class="fas fa-radar"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Intrusion Monitoring</h3>
                            <p class="card-subtitle">Detect threats and anomalies</p>
                        </div>
                    </div>
                    <div class="quick-actions">
                        <button class="btn btn-primary" onclick="scanNow()"><i class="fas fa-search"></i> Scan Now</button>
                        <button class="btn btn-warning" onclick="viewThreats()"><i class="fas fa-bug"></i> View Threats</button>
                        <button class="btn btn-secondary" onclick="enableIDS()"><i class="fas fa-toggle-on"></i> Enable IDS</button>
                    </div>
                </div>

                <!-- Audit Logs -->
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern blue"><i class="fas fa-clipboard-list"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Audit Logs</h3>
                            <p class="card-subtitle">Trace all system activities</p>
                        </div>
                    </div>
                    <div class="quick-actions">
                        <button class="btn btn-secondary" onclick="downloadLogs()"><i class="fas fa-download"></i> Download Logs</button>
                        <button class="btn btn-warning" onclick="clearLogs()"><i class="fas fa-trash"></i> Clear Logs</button>
                        <input class="form-input" style="max-width:320px" type="text" id="logQuery" placeholder="Search logs...">
                        <button class="btn btn-primary" onclick="searchLogs()"><i class="fas fa-search"></i> Search</button>
                    </div>
                </div>

                <!-- Compliance -->
                <div class="overview-card">
                    <div class="card-header-modern">
                        <div class="card-icon-modern purple"><i class="fas fa-scale-balanced"></i></div>
                        <div class="card-info">
                            <h3 class="card-title-modern">Compliance</h3>
                            <p class="card-subtitle">Data Privacy Act alignment</p>
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label><input type="checkbox" checked> Privacy policy published and accessible</label>
                        </div>
                        <div class="form-group full-width">
                            <label><input type="checkbox" checked> Data processing consent is collected</label>
                        </div>
                        <div class="form-group full-width">
                            <label><input type="checkbox"> Data retention and disposal policy documented</label>
                        </div>
                        <div class="form-group full-width">
                            <label><input type="checkbox"> Incident response plan tested</label>
                        </div>
                        <div class="form-group full-width">
                            <button class="btn btn-primary" onclick="exportCompliance()"><i class="fas fa-file-export"></i> Export Compliance Report</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Demo handlers
        function handleLogout(){ if(confirm('Are you sure you want to logout?')) alert('Logged out (demo)'); }

        function savePermissions(){ alert('Permissions saved (demo).'); }
        function resetPermissions(){ alert('Permissions reset to defaults (demo).'); }

        function applyAuthPolicy(form){
            const d=new FormData(form);
            alert('Applied: policy='+d.get('policy')+', MFA='+d.get('mfa')+'; Reset target='+ (d.get('reset_email')||'N/A'));
            form.reset();
            return false;
        }
        function resetPassword(){ alert('Password reset email sent (demo).'); }

        function rotateKeys(){ alert('Key rotation triggered (demo).'); }
        function exportKey(){ alert('Public key exported (demo).'); }

        function scanNow(){ alert('Intrusion scan started (demo).'); }
        function viewThreats(){ alert('Viewing threats (demo).'); }
        function enableIDS(){ alert('IDS enabled (demo).'); }

        function downloadLogs(){ alert('Audit logs downloaded (demo).'); }
        function clearLogs(){ if(confirm('Clear all logs?')) alert('Logs cleared (demo).'); }
        function searchLogs(){ alert('Searching logs for: '+ (document.getElementById('logQuery').value||'') ); }
    </script>
</body>
</html>
