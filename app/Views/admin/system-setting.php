<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>System Settings - HMS Admin</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        /* Scoped styles for System Settings page */
        .settings-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(380px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .settings-section { background: #fff; border-radius: 8px; padding: 1.25rem; box-shadow: 0 2px 4px rgba(0,0,0,0.06); border: 1px solid #f1f5f9; }
        .section-header { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 1px solid #e2e8f0; }
        .section-icon { width: 40px; height: 40px; border-radius: 8px; background: #6366f1; color: #fff; display: flex; align-items: center; justify-content: center; }
        .section-title { font-size: 1rem; font-weight: 600; color: #1f2937; }

        .setting-item { display: flex; justify-content: space-between; align-items: center; gap: 1rem; padding: 0.75rem 0; border-bottom: 1px solid #f3f4f6; }
        .setting-item:last-child { border-bottom: none; }
        .setting-info { flex: 1; }
        .setting-label { font-weight: 600; color: #111827; margin-bottom: 0.25rem; }
        .setting-description { font-size: 0.85rem; color: #6b7280; }
        .setting-control { margin-left: 0.75rem; }

        .form-input { padding: 0.55rem 0.65rem; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 0.95rem; min-width: 160px; }
        .form-input:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.15); }

        /* Toggle switch */
        .toggle-switch { position: relative; display: inline-block; width: 48px; height: 24px; }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .toggle-switch .slider { position: absolute; cursor: pointer; inset: 0; background: #d1d5db; transition: .3s; border-radius: 999px; }
        .toggle-switch .slider:before { content: ""; position: absolute; height: 18px; width: 18px; left: 3px; top: 3px; background: #fff; border-radius: 999px; transition: .3s; }
        .toggle-switch input:checked + .slider { background: #22c55e; }
        .toggle-switch input:checked + .slider:before { transform: translateX(24px); }

        /* Maintenance schedule */
        .maintenance-schedule { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 1rem; margin-top: 0.75rem; }
        .schedule-item { display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #e5e7eb; }
        .schedule-item:last-child { border-bottom: none; }

        /* Status indicators */
        .backup-status { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem; }
        .status-indicator { width: 10px; height: 10px; border-radius: 999px; }
        .status-success { background: #22c55e; } .status-warning { background: #f59e0b; } .status-error { background: #ef4444; }

        /* Page action buttons */
        .action-buttons { display: flex; gap: 0.5rem; margin-top: 0.75rem; flex-wrap: wrap; }
        .btn-small { padding: 0.45rem 0.8rem; font-size: 0.85rem; }

        @media (max-width: 640px) { .settings-grid { grid-template-columns: 1fr; } .form-input { min-width: 140px; } }
    </style>
</head>
<body class="admin">

    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <?php include APPPATH . 'Views/admin/components/sidebar.php'; ?>

        <main class="content">
            <h1 class="page-title">System Settings</h1>

            <div class="settings-grid">
                <div class="settings-section">
                    <div class="section-header">
                        <div class="section-icon"><i class="fas fa-cog"></i></div>
                        <div class="section-title">General Settings</div>
                    </div>

                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-label">Hospital Name</div>
                            <div class="setting-description">Display name for the hospital</div>
                        </div>
                        <div class="setting-control">
                            <input type="text" class="form-input" value="General Hospital" id="hospitalName">
                        </div>
                    </div>

                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-label">Time Zone</div>
                            <div class="setting-description">System time zone setting</div>
                        </div>
                        <div class="setting-control">
                            <select class="form-input" id="timeZone">
                                <option value="UTC">UTC</option>
                                <option value="EST" selected>Eastern Time</option>
                                <option value="PST">Pacific Time</option>
                                <option value="CST">Central Time</option>
                            </select>
                        </div>
                    </div>

                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-label">Language</div>
                            <div class="setting-description">Default system language</div>
                        </div>
                        <div class="setting-control">
                            <select class="form-input" id="language">
                                <option value="en" selected>English</option>
                                <option value="es">Spanish</option>
                                <option value="fr">French</option>
                            </select>
                        </div>
                    </div>

                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-label">Auto-Save Interval</div>
                            <div class="setting-description">Automatic save frequency (minutes)</div>
                        </div>
                        <div class="setting-control">
                            <input type="number" class="form-input" value="5" min="1" max="60" id="autoSave">
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button class="btn btn-primary btn-small"><i class="fas fa-save"></i> Save Changes</button>
                        <button class="btn btn-secondary btn-small"><i class="fas fa-undo"></i> Reset</button>
                    </div>
                </div>

                <div class="settings-section">
                    <div class="section-header">
                        <div class="section-icon"><i class="fas fa-bell"></i></div>
                        <div class="section-title">Notification Settings</div>
                    </div>

                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-label">Email Notifications</div>
                            <div class="setting-description">Send system alerts via email</div>
                        </div>
                        <div class="setting-control">
                            <label class="toggle-switch">
                                <input type="checkbox" checked id="emailNotifications">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-label">SMS Notifications</div>
                            <div class="setting-description">Send critical alerts via SMS</div>
                        </div>
                        <div class="setting-control">
                            <label class="toggle-switch">
                                <input type="checkbox" id="smsNotifications">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-label">Push Notifications</div>
                            <div class="setting-description">Browser push notifications</div>
                        </div>
                        <div class="setting-control">
                            <label class="toggle-switch">
                                <input type="checkbox" checked id="pushNotifications">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-label">Alert Frequency</div>
                            <div class="setting-description">How often to send alerts</div>
                        </div>
                        <div class="setting-control">
                            <select class="form-input" id="alertFrequency">
                                <option value="immediate" selected>Immediate</option>
                                <option value="hourly">Hourly</option>
                                <option value="daily">Daily</option>
                            </select>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button class="btn btn-primary btn-small"><i class="fas fa-save"></i> Save Changes</button>
                        <button class="btn btn-warning btn-small"><i class="fas fa-test-tube"></i> Test Notifications</button>
                    </div>
                </div>

                <div class="settings-section">
                    <div class="section-header">
                        <div class="section-icon"><i class="fas fa-database"></i></div>
                        <div class="section-title">Backup & Recovery</div>
                    </div>

                    <div class="backup-status">
                        <div class="status-indicator status-success"></div>
                        <span>Last backup: Today at 2:00 AM</span>
                    </div>

                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-label">Automatic Backups</div>
                            <div class="setting-description">Enable scheduled backups</div>
                        </div>
                        <div class="setting-control">
                            <label class="toggle-switch">
                                <input type="checkbox" checked id="autoBackup">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-label">Backup Frequency</div>
                            <div class="setting-description">How often to create backups</div>
                        </div>
                        <div class="setting-control">
                            <select class="form-input" id="backupFrequency">
                                <option value="daily" selected>Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                    </div>

                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-label">Retention Period</div>
                            <div class="setting-description">How long to keep backups (days)</div>
                        </div>
                        <div class="setting-control">
                            <input type="number" class="form-input" value="30" min="7" max="365" id="retentionPeriod">
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button class="btn btn-success btn-small"><i class="fas fa-save"></i> Create Backup Now</button>
                        <button class="btn btn-warning btn-small"><i class="fas fa-undo"></i> Restore Backup</button>
                        <button class="btn btn-secondary btn-small"><i class="fas fa-download"></i> Download</button>
                    </div>
                </div>

                <div class="settings-section">
                    <div class="section-header">
                        <div class="section-icon"><i class="fas fa-tools"></i></div>
                        <div class="section-title">System Maintenance</div>
                    </div>

                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-label">Maintenance Mode</div>
                            <div class="setting-description">Enable system maintenance mode</div>
                        </div>
                        <div class="setting-control">
                            <label class="toggle-switch">
                                <input type="checkbox" id="maintenanceMode">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="maintenance-schedule">
                        <h4 style="margin-bottom: 1rem;">Scheduled Maintenance</h4>
                        <div class="schedule-item">
                            <span>Database Optimization</span>
                            <span>Weekly - Sunday 2:00 AM</span>
                        </div>
                        <div class="schedule-item">
                            <span>Log Cleanup</span>
                            <span>Daily - 1:00 AM</span>
                        </div>
                        <div class="schedule-item">
                            <span>System Updates</span>
                            <span>Monthly - 1st Sunday 3:00 AM</span>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button class="btn btn-primary btn-small"><i class="fas fa-play"></i> Run Maintenance</button>
                        <button class="btn btn-secondary btn-small"><i class="fas fa-calendar"></i> Schedule</button>
                    </div>
                </div>

                <div class="settings-section">
                    <div class="section-header">
                        <div class="section-icon"><i class="fas fa-tachometer-alt"></i></div>
                        <div class="section-title">Performance Settings</div>
                    </div>

                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-label">Cache Enabled</div>
                            <div class="setting-description">Enable system caching</div>
                        </div>
                        <div class="setting-control">
                            <label class="toggle-switch">
                                <input type="checkbox" checked id="cacheEnabled">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-label">Cache Duration</div>
                            <div class="setting-description">Cache expiration time (hours)</div>
                        </div>
                        <div class="setting-control">
                            <input type="number" class="form-input" value="24" min="1" max="168" id="cacheDuration">
                        </div>
                    </div>

                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-label">Session Timeout</div>
                            <div class="setting-description">User session timeout (minutes)</div>
                        </div>
                        <div class="setting-control">
                            <input type="number" class="form-input" value="30" min="5" max="480" id="sessionTimeout">
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button class="btn btn-primary btn-small"><i class="fas fa-save"></i> Save Changes</button>
                        <button class="btn btn-warning btn-small"><i class="fas fa-trash"></i> Clear Cache</button>
                    </div>
                </div>

                <div class="settings-section">
                    <div class="section-header">
                        <div class="section-icon"><i class="fas fa-plug"></i></div>
                        <div class="section-title">Integration Settings</div>
                    </div>

                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-label">API Access</div>
                            <div class="setting-description">Enable external API access</div>
                        </div>
                        <div class="setting-control">
                            <label class="toggle-switch">
                                <input type="checkbox" checked id="apiAccess">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-label">Third-party Integrations</div>
                            <div class="setting-description">Allow third-party connections</div>
                        </div>
                        <div class="setting-control">
                            <label class="toggle-switch">
                                <input type="checkbox" id="thirdPartyIntegrations">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-label">Rate Limiting</div>
                            <div class="setting-description">API rate limit (requests/hour)</div>
                        </div>
                        <div class="setting-control">
                            <input type="number" class="form-input" value="1000" min="100" max="10000" id="rateLimit">
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button class="btn btn-primary btn-small"><i class="fas fa-save"></i> Save Changes</button>
                        <button class="btn btn-secondary btn-small"><i class="fas fa-key"></i> Generate API Key</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="<?= base_url('js/logout.js') ?>"></script>
</body>
</html>
