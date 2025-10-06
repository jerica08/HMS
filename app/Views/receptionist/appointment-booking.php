<!DOCTYPE html>
<html lang ="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width", initial-scale="1.0">
        <title>Appointment Booking</title>
        <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
            .content-header { margin-bottom: 1.5rem; }
            .page-subtitle { margin: 0.5rem 0 0 0; opacity: .8; font-size: .95rem; }
            .form-grid { display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
            .form-group { display:flex; flex-direction:column; }
            .form-group-full { grid-column: 1 / -1; }
            .form-group label { font-weight: 600; margin-bottom: .5rem; color: #374151; }
            .form-group input, .form-group select, .form-group textarea { padding: .75rem; border:1px solid #e2e8f0; border-radius:8px; background:#fff; font-size:.9rem; transition: border-color .2s, box-shadow .2s; }
            .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline:none; border-color:#667eea; box-shadow:0 0 0 3px rgba(102,126,234,.15); }
            .form-actions { display:flex; gap:.75rem; justify-content:flex-end; margin-top:1.5rem; }
            @media (max-width: 768px) { .form-grid { grid-template-columns:1fr; } .form-actions { flex-direction:column; } }
        </style>
    </head>
    <body class="receptionist-theme">
        <?php include APPPATH . 'Views/template/header.php'; ?>
        <div class="main-container">
            <?= $this->include('Views/receptionist/components/sidebar') ?>
            <main class="content">
                <div class="content-header">
                    <h1 class="page-title"><i class="fas fa-calendar-alt"></i> Appointment Booking</h1>
                    <p class="page-subtitle">Schedule a patient with a doctor, set time and visit reason.</p>
                </div>

                <section class="card">
                    <div class="card-header">
                        <h3 class="card-title">Booking Details</h3>
                    </div>
                    <div class="card-body">
                        <form id="appointmentBookingForm" action="<?= base_url('receptionist/appointments/book') ?>" method="post" novalidate>
                            <?= csrf_field() ?>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="patient">Patient</label>
                                    <input type="text" id="patient" name="patient_search" placeholder="Search by name or ID" autocomplete="off" required>
                                </div>
                                <div class="form-group">
                                    <label for="visitType">Visit Type</label>
                                    <select id="visitType" name="visit_type">
                                        <option value="">Select type</option>
                                        <option value="new">New patient</option>
                                        <option value="followup">Follow-up</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="department">Department</label>
                                    <select id="department" name="department_id" required>
                                        <option value="">Select department</option>
                                        <option value="emergency">Emergency</option>
                                        <option value="orthopedics">Orthopedics</option>
                                        <option value="cardiology">Cardiology</option>
                                        <option value="neurology">Neurology</option>
                                        <option value="radiology">Radiology</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="doctor">Doctor</label>
                                    <select id="doctor" name="doctor_id" required>
                                        <option value="">Select doctor</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="date">Date</label>
                                    <input type="date" id="date" name="appointment_date" required>
                                </div>
                                <div class="form-group">
                                    <label for="time">Time</label>
                                    <input type="time" id="time" name="appointment_time" required>
                                </div>
                                <div class="form-group form-group-full">
                                    <label for="reason">Reason for Visit</label>
                                    <input type="text" id="reason" name="reason" placeholder="Brief description">
                                </div>
                                <div class="form-group form-group-full">
                                    <label for="notes">Notes</label>
                                    <textarea id="notes" name="notes" rows="3" placeholder="Optional notes"></textarea>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-calendar-check"></i> Book Appointment</button>
                                <button type="reset" class="btn btn-secondary"><i class="fas fa-undo"></i> Reset</button>
                            </div>
                        </form>
                    </div>
                </section>
            </main>
        </div>

        <script src="<?= base_url('js/logout.js') ?>"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deptSel = document.getElementById('department');
            const doctorSel = document.getElementById('doctor');

            const setDoctorOptions = (doctors) => {
                doctorSel.innerHTML = '';
                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = doctors?.length ? 'Select doctor' : 'No doctors available';
                doctorSel.appendChild(placeholder);
                if (!doctors || doctors.length === 0) { doctorSel.disabled = true; return; }
                const sorted = [...doctors].sort((a, b) => Number(b.available) - Number(a.available));
                sorted.forEach(d => {
                    const opt = document.createElement('option');
                    opt.value = d.id;
                    opt.textContent = d.available ? d.name : `${d.name} (Unavailable)`;
                    if (!d.available) { opt.disabled = true; opt.title = 'This doctor is currently unavailable'; }
                    doctorSel.appendChild(opt);
                });
                doctorSel.disabled = false;
            };

            const fetchDoctors = async (deptVal) => {
                if (!deptVal) { setDoctorOptions([]); return; }
                try {
                    const res = await fetch(`<?= base_url('receptionist/doctors/by-department') ?>/${encodeURIComponent(deptVal.toLowerCase())}`, { headers: { 'Accept': 'application/json' } });
                    const data = await res.json();
                    if (res.ok && data?.status === 'success') { setDoctorOptions(data.data || []); }
                    else { console.error('Failed to load doctors', data); setDoctorOptions([]); }
                } catch (e) { console.error('Error loading doctors', e); setDoctorOptions([]); }
            };

            doctorSel.disabled = true; setDoctorOptions([]);
            deptSel?.addEventListener('change', (e) => {
                const dept = e.target.value;
                doctorSel.innerHTML = '';
                const loadingOpt = document.createElement('option');
                loadingOpt.value = ''; loadingOpt.textContent = 'Loading doctors...';
                doctorSel.appendChild(loadingOpt); doctorSel.disabled = true;
                fetchDoctors(dept);
            });
        });
        </script>
    </body>
    </html>
