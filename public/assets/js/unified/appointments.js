
    // Export appointments to Excel
    function exportToExcel() {
        const table = document.querySelector('.table').cloneNode(true);
        
        // Remove action buttons column
        const headers = table.querySelectorAll('thead th');
        const lastHeaderIndex = headers.length - 1;
        headers[lastHeaderIndex].remove();
        
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length > 0) {
                cells[cells.length - 1].remove();
            }
        });
        
        // Create Excel content
        let html = '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
        html += '<head><meta charset="UTF-8">';
        html += '<style>';
        html += 'table { border-collapse: collapse; width: 100%; }';
        html += 'th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }';
        html += 'th { background-color: #667eea; color: white; font-weight: bold; }';
        html += 'tr:nth-child(even) { background-color: #f2f2f2; }';
        html += '</style></head><body>';
        html += '<h2>Appointment Schedule - <?= date("F j, Y") ?></h2>';
        html += '<table>' + table.innerHTML + '</table>';
        html += '</body></html>';
        
        const blob = new Blob([html], { type: 'application/vnd.ms-excel' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        const date = new Date().toISOString().split('T')[0];
        const userRole = document.querySelector('meta[name="user-role"]')?.content || 'user';
        
        link.setAttribute('href', url);
        link.setAttribute('download', `appointments_${userRole}_${date}.xls`);
        link.style.visibility = 'hidden';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Dismiss flash notification
    function dismissFlash() {
        const flash = document.getElementById('flashNotice');
        if (flash) flash.remove();
    }

    // Initialize export button
    document.addEventListener('DOMContentLoaded', function() {
        const exportBtn = document.getElementById('exportBtn');
        if (exportBtn) {
            exportBtn.addEventListener('click', exportToExcel);
        }

        // Initialize appointment modal
        initializeAppointmentModal();

        // Initialize filters
        const dateFilter = document.getElementById('dateSelector');
        const statusFilter = document.getElementById('statusFilter');
        const doctorFilter = document.getElementById('doctorFilter');
        const refreshBtnMain = document.getElementById('refreshBtn');

        if (dateFilter)   dateFilter.addEventListener('change', refreshAppointments);
        if (statusFilter) statusFilter.addEventListener('change', refreshAppointments);
        if (doctorFilter) doctorFilter.addEventListener('change', refreshAppointments);
        if (refreshBtnMain) refreshBtnMain.addEventListener('click', function(e) {
            e.preventDefault();
            refreshAppointments();
        });

        // Initial load based on filters
        refreshAppointments();

        // When date changes in the new appointment modal, reload available doctors
        const dateInput = document.getElementById('appointment_date');
        if (dateInput) {
            dateInput.addEventListener('change', function() {
                if (this.value) {
                    loadAvailableDoctors(this.value);
                }
            });
        }
    });

    // Appointment Modal Functions
    function initializeAppointmentModal() {
        console.log('Initializing appointment modal...');
        
        const scheduleBtn = document.getElementById('scheduleAppointmentBtn');
        console.log('Schedule button found:', !!scheduleBtn);
        
        if (scheduleBtn) {
            scheduleBtn.addEventListener('click', function() {
                console.log('Schedule button clicked!');
                openNewAppointmentModal();
            });
        }

        // Close modal when clicking outside
        const modal = document.getElementById('newAppointmentModal');
        console.log('Modal found:', !!modal);
        
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeNewAppointmentModal();
                }
            });
        }

        // Handle form submission
        const form = document.getElementById('newAppointmentForm');
        console.log('Form found:', !!form);
        
        if (form) {
            form.addEventListener('submit', handleAppointmentSubmit);
        }
    }

    function openNewAppointmentModal() {
        console.log('openNewAppointmentModal called!');
        const modal = document.getElementById('newAppointmentModal');
        console.log('Modal element:', modal);
        
        if (modal) {
            console.log('Opening modal - current classes:', modal.className);
            modal.classList.add('active');
            modal.removeAttribute('hidden');
            modal.setAttribute('aria-hidden', 'false');
            console.log('Modal opened - new classes:', modal.className);
            
            loadPatients();

            // Load available doctors for the selected date (simple: has schedule that day)
            const dateInput = document.getElementById('appointment_date');
            let dateValue = dateInput ? dateInput.value : '';

            if (!dateValue) {
                // Default to today if no date selected yet
                const today = new Date().toISOString().split('T')[0];
                if (dateInput) {
                    dateInput.value = today;
                }
                dateValue = today;
            }

            loadAvailableDoctors(dateValue);
        } else {
            console.error('Modal not found!');
        }
    }

    function closeNewAppointmentModal() {
        console.log('closeNewAppointmentModal called!');
        const modal = document.getElementById('newAppointmentModal');
        if (modal) {
            modal.classList.remove('active');
            modal.setAttribute('hidden', 'true');
            modal.setAttribute('aria-hidden', 'true');
            document.getElementById('newAppointmentForm').reset();
            clearFormErrors();
        }
    }

    function loadPatients() {
        const baseUrl = document.querySelector('meta[name="base-url"]').content;
        const patientSelect = document.getElementById('appointment_patient');
        
        fetch(`${baseUrl}/appointments/patients`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    patientSelect.innerHTML = '<option value="">Select Patient...</option>';
                    data.data.forEach(patient => {
                        const option = document.createElement('option');
                        option.value = patient.patient_id;
                        option.textContent = `${patient.first_name} ${patient.last_name}`;
                        patientSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading patients:', error);
            });
    }

    function getWeekdayName(dateStr) {
        const d = new Date(dateStr);
        const days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        if (isNaN(d.getTime())) return '';
        return days[d.getDay()];
    }

    function loadAvailableDoctors(date) {
        const baseUrl = document.querySelector('meta[name="base-url"]').content;
        const doctorSelect = document.getElementById('appointment_doctor');
        const dateHelp = document.getElementById('appointment_date_help');

        if (!doctorSelect) return; // Only for admin (doctor select not shown for others)

        doctorSelect.innerHTML = '<option value="">Loading available doctors...</option>';
        if (dateHelp) dateHelp.textContent = '';

        const weekday = getWeekdayName(date);
        const url = `${baseUrl}/appointments/available-doctors?date=${encodeURIComponent(date)}&weekday=${encodeURIComponent(weekday)}`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                doctorSelect.innerHTML = '<option value="">Select Doctor...</option>';

                if (data.status === 'success' && Array.isArray(data.data) && data.data.length) {
                    data.data.forEach(doctor => {
                        const option = document.createElement('option');
                        option.value = doctor.staff_id;
                        const specialization = doctor.specialization ? ` - ${doctor.specialization}` : '';
                        option.textContent = `${doctor.first_name} ${doctor.last_name}${specialization}`;
                        doctorSelect.appendChild(option);
                    });
                    if (dateHelp) dateHelp.textContent = 'Doctors listed are available on this date.';
                } else {
                    if (dateHelp) dateHelp.textContent = 'No doctors are available on this date.';
                }
            })
            .catch(error => {
                console.error('Error loading available doctors:', error);
                doctorSelect.innerHTML = '<option value="">Error loading doctors</option>';
                if (dateHelp) dateHelp.textContent = 'Failed to load doctor availability.';
            });
    }

    function handleAppointmentSubmit(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        
        const baseUrl = document.querySelector('meta[name="base-url"]').content;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        const isEdit = !!data.appointment_id;
        const url = isEdit
            ? `${baseUrl}/appointments/${data.appointment_id}`
            : `${baseUrl}/appointments/create`;

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            const success = isEdit
                ? (result.status === 'success')
                : !!result.success;

            if (success) {
                const message = isEdit
                    ? (result.message || 'Appointment updated successfully.')
                    : (result.message || 'Appointment scheduled successfully.');
                showAppointmentsNotification(message, 'success');
                closeNewAppointmentModal();
                setTimeout(function() {
                    location.reload();
                }, 800);
            } else {
                const generalError = document.getElementById('appointment_error');
                if (generalError) {
                    generalError.style.display = 'block';
                    generalError.textContent = result.message || 'Failed to save appointment. Please check the form and try again.';
                }
                // Also show toast notification for backend validation errors
                if (result.message) {
                    showAppointmentsNotification(result.message, 'error');
                }
                if (result.errors) {
                    showFormErrors(result.errors);
                }
                const dateHelp = document.getElementById('appointment_date_help');
                if (dateHelp && result.message) {
                    dateHelp.textContent = result.message;
                }
            }
        })
        .catch(error => {
            console.error('Error saving appointment:', error);
            const generalError = document.getElementById('appointment_error');
            if (generalError) {
                generalError.style.display = 'block';
                generalError.textContent = 'An unexpected error occurred while saving the appointment. Please try again.';
            }

            showAppointmentsNotification('Failed to save appointment. Please try again.', 'error');
        });
    }

    function clearFormErrors() {
        const errorElements = document.querySelectorAll('[id^="err_appointment_"]');
        errorElements.forEach(element => {
            element.textContent = '';
        });

        const generalError = document.getElementById('appointment_error');
        if (generalError) {
            generalError.style.display = 'none';
            generalError.textContent = '';
        }
    }

    function showFormErrors(errors) {
        clearFormErrors();
        Object.keys(errors).forEach(field => {
            const errorElement = document.getElementById(`err_appointment_${field}`);
            if (errorElement) {
                errorElement.textContent = errors[field];
            }
        });
    }

    function showAppointmentsNotification(message, type) {
        const container = document.getElementById('appointmentsNotification');
        const iconEl = document.getElementById('appointmentsNotificationIcon');
        const textEl = document.getElementById('appointmentsNotificationText');
        if (!container || !iconEl || !textEl) return;

        const isError = type === 'error';

        // Match user-management flashNotice styling
        container.style.border = isError ? '1px solid #fecaca' : '1px solid #86efac';
        container.style.background = isError ? '#fee2e2' : '#dcfce7';
        container.style.color = isError ? '#991b1b' : '#166534';

        iconEl.className = 'fas ' + (isError ? 'fa-exclamation-triangle' : 'fa-check-circle');
        textEl.textContent = message || '';

        container.style.display = 'flex';

        // Auto-hide after a few seconds
        setTimeout(function() {
            container.style.display = 'none';
        }, 4000);
    }

    function dismissAppointmentNotification() {
        const container = document.getElementById('appointmentsNotification');
        if (container) {
            container.style.display = 'none';
        }
    }

    function formatAppointmentTime(timeStr) {
        if (!timeStr) return '—';
        const [h, m] = timeStr.split(':');
        let hour = parseInt(h, 10);
        const minutes = m || '00';
        const ampm = hour >= 12 ? 'PM' : 'AM';
        hour = hour % 12;
        if (hour === 0) hour = 12;
        return `${hour}:${minutes} ${ampm}`;
    }

    function getStatusBadgeClass(status) {
        const s = (status || 'scheduled').toLowerCase();
        switch (s) {
            case 'completed': return 'badge-success';
            case 'in-progress': return 'badge-info';
            case 'cancelled': return 'badge-danger';
            case 'no-show': return 'badge-warning';
            default: return 'badge-info';
        }
    }

    function refreshAppointments() {
        const baseUrl = document.querySelector('meta[name="base-url"]').content;
        const userRole = document.querySelector('meta[name="user-role"]').content || 'guest';
        const params = new URLSearchParams();

        // Always show today's schedule
        const today = new Date().toISOString().split('T')[0];
        params.append('date', today);

        const url = `${baseUrl}/appointments/api?${params.toString()}`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    renderAppointmentsTable(data.data || []);
                } else {
                    renderAppointmentsTable([]);
                }

                // Title is already rendered with today's date by PHP
            })
            .catch(error => {
                console.error('Error loading appointments:', error);
                renderAppointmentsTable([]);
            });
    }

    function renderAppointmentsTable(appointments) {
        const tbody = document.getElementById('appointmentsTableBody');
        if (!tbody) return;

        const userRole = document.querySelector('meta[name="user-role"]').content || 'guest';
        const isAdmin = userRole === 'admin';

        while (tbody.firstChild) {
            tbody.removeChild(tbody.firstChild);
        }

        if (!appointments || !appointments.length) {
            const tr = document.createElement('tr');
            const td = document.createElement('td');
            td.colSpan = isAdmin ? 8 : 7;
            td.style.textAlign = 'center';
            td.style.padding = '2rem';
            td.style.color = '#6b7280';
            td.innerHTML = `
                <i class="fas fa-calendar-times" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                <p>No appointments found for the selected criteria.</p>
            `;
            tr.appendChild(td);
            tbody.appendChild(tr);
            return;
        }

        appointments.forEach(appt => {
            const tr = document.createElement('tr');

            const timeTd = document.createElement('td');
            const timeStrong = document.createElement('strong');
            timeStrong.textContent = formatAppointmentTime(appt.appointment_time);
            timeTd.appendChild(timeStrong);
            if (appt.duration) {
                const br = document.createElement('br');
                const small = document.createElement('small');
                small.textContent = `${appt.duration} min`;
                timeTd.appendChild(br);
                timeTd.appendChild(small);
            }
            tr.appendChild(timeTd);

            const patientTd = document.createElement('td');
            const patientDiv = document.createElement('div');
            const patientStrong = document.createElement('strong');
            const baseUrl = document.querySelector('meta[name="base-url"]').content;
            const link = document.createElement('a');
            link.href = `${baseUrl}/${userRole}/patient-management?patient_id=${appt.patient_id || ''}`;
            link.className = 'patient-link';
            link.style.color = '#3b82f6';
            link.style.textDecoration = 'none';
            link.textContent = `${appt.patient_first_name || ''} ${appt.patient_last_name || ''}`.trim();
            patientStrong.appendChild(link);
            patientDiv.appendChild(patientStrong);
            const br2 = document.createElement('br');
            const smallInfo = document.createElement('small');
            smallInfo.style.color = '#6b7280';
            const age = appt.patient_age != null ? appt.patient_age : 'N/A';
            const phone = appt.patient_phone || 'N/A';
            smallInfo.textContent = `ID: ${appt.patient_id || 'N/A'} | Age: ${age} | Phone: ${phone}`;
            patientDiv.appendChild(br2);
            patientDiv.appendChild(smallInfo);
            patientTd.appendChild(patientDiv);
            tr.appendChild(patientTd);

            if (isAdmin) {
                const doctorTd = document.createElement('td');
                const docDiv = document.createElement('div');
                const docStrong = document.createElement('strong');
                docStrong.textContent = `Dr. ${(appt.doctor_first_name || '') + ' ' + (appt.doctor_last_name || '')}`.trim();
                const br3 = document.createElement('br');
                const docSmall = document.createElement('small');
                docSmall.textContent = appt.doctor_department || 'N/A';
                docDiv.appendChild(docStrong);
                docDiv.appendChild(br3);
                docDiv.appendChild(docSmall);
                doctorTd.appendChild(docDiv);
                tr.appendChild(doctorTd);
            }

            const typeTd = document.createElement('td');
            typeTd.textContent = appt.appointment_type || 'N/A';
            tr.appendChild(typeTd);

            const reasonTd = document.createElement('td');
            reasonTd.textContent = appt.reason || 'General consultation';
            tr.appendChild(reasonTd);

            const durationTd = document.createElement('td');
            durationTd.textContent = `${appt.duration || 30} min`;
            tr.appendChild(durationTd);

            const statusTd = document.createElement('td');
            const badge = document.createElement('span');
            const status = appt.status || 'scheduled';
            badge.className = `badge ${getStatusBadgeClass(status)}`;
            badge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
            statusTd.appendChild(badge);
            tr.appendChild(statusTd);

            const actionsTd = document.createElement('td');
            const actionsDiv = document.createElement('div');
            actionsDiv.style.display = 'flex';
            actionsDiv.style.gap = '0.25rem';
            actionsDiv.style.flexWrap = 'wrap';

            const viewBtn = document.createElement('button');
            viewBtn.className = 'btn btn-primary';
            viewBtn.style.padding = '0.3rem 0.6rem';
            viewBtn.style.fontSize = '0.75rem';
            viewBtn.innerHTML = '<i class="fas fa-eye"></i> View';
            viewBtn.onclick = function() { viewAppointment(appt.appointment_id); };
            actionsDiv.appendChild(viewBtn);

            const statusLower = (appt.status || 'scheduled').toLowerCase();

            if ((userRole === 'admin' || userRole === 'doctor') && statusLower !== 'completed') {
                const completeBtn = document.createElement('button');
                completeBtn.className = 'btn btn-success';
                completeBtn.style.padding = '0.3rem 0.6rem';
                completeBtn.style.fontSize = '0.75rem';
                completeBtn.innerHTML = '<i class="fas fa-check"></i> Complete';
                completeBtn.onclick = function() { markCompleted(appt.appointment_id); };
                actionsDiv.appendChild(completeBtn);
            }

            if (['admin', 'doctor', 'receptionist'].includes(userRole)) {
                const editBtn = document.createElement('button');
                editBtn.className = 'btn btn-warning';
                editBtn.style.padding = '0.3rem 0.6rem';
                editBtn.style.fontSize = '0.75rem';
                editBtn.innerHTML = '<i class="fas fa-edit"></i> Edit';
                editBtn.onclick = function() { editAppointment(appt.appointment_id); };
                actionsDiv.appendChild(editBtn);
            }

            if (userRole === 'admin' || userRole === 'doctor') {
                const presBtn = document.createElement('button');
                presBtn.className = 'btn btn-info';
                presBtn.style.padding = '0.3rem 0.6rem';
                presBtn.style.fontSize = '0.75rem';
                presBtn.innerHTML = '<i class="fas fa-prescription-bottle"></i> Prescription';
                presBtn.onclick = function() { openPrescriptionModal(appt.appointment_id, appt.patient_id); };
                actionsDiv.appendChild(presBtn);
            }

            if (userRole === 'admin') {
                const delBtn = document.createElement('button');
                delBtn.className = 'btn btn-danger';
                delBtn.style.padding = '0.3rem 0.6rem';
                delBtn.style.fontSize = '0.75rem';
                delBtn.innerHTML = '<i class="fas fa-trash"></i> Delete';
                delBtn.onclick = function() { deleteAppointment(appt.appointment_id); };
                actionsDiv.appendChild(delBtn);
            }

            actionsTd.appendChild(actionsDiv);
            tr.appendChild(actionsTd);

            tbody.appendChild(tr);
        });
    }

    // View Appointment Modal Functions
    function openViewAppointmentModal() {
        const modal = document.getElementById('viewAppointmentModal');
        if (!modal) return;

        // Some unified modals use display, some use an .active class; support both
        modal.style.display = 'flex';
        if (modal.classList) {
            modal.classList.add('active');
        }
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeViewAppointmentModal() {
        const modal = document.getElementById('viewAppointmentModal');
        if (!modal) return;

        modal.style.display = 'none';
        if (modal.classList) {
            modal.classList.remove('active');
        }
        modal.setAttribute('aria-hidden', 'true');
    }

    function viewAppointment(appointmentId) {
        if (!appointmentId) return;

        const baseUrlMeta = document.querySelector('meta[name="base-url"]');
        const baseUrl = baseUrlMeta ? baseUrlMeta.content : '';
        if (!baseUrl) {
            console.error('Base URL meta tag not found');
            return;
        }

        const url = `${baseUrl}/appointments/${appointmentId}`;

        fetch(url)
            .then(response => response.json())
            .then(result => {
                // Support both {status: 'success', data: {...}} and plain objects
                let appt = result;
                if (result && typeof result === 'object' && 'status' in result) {
                    if (result.status !== 'success') {
                        throw new Error(result.message || 'Failed to load appointment details');
                    }
                    appt = result.data || result.appointment || {};
                }

                if (!appt) {
                    throw new Error('Empty appointment response');
                }

                // Populate patient select (read-only)
                const patientSelect = document.getElementById('view_appointment_patient');
                if (patientSelect) {
                    patientSelect.innerHTML = '';
                    const opt = document.createElement('option');
                    opt.value = appt.patient_id || '';
                    const firstName = appt.patient_first_name || appt.first_name || '';
                    const lastName = appt.patient_last_name || appt.last_name || '';
                    const fullName = `${firstName} ${lastName}`.trim();
                    opt.textContent = fullName || (appt.patient_id ? `Patient #${appt.patient_id}` : 'Patient');
                    patientSelect.appendChild(opt);
                }

                // Populate doctor select for admin (if present in view)
                const doctorSelect = document.getElementById('view_appointment_doctor');
                if (doctorSelect) {
                    doctorSelect.innerHTML = '';
                    const opt = document.createElement('option');
                    opt.value = appt.doctor_id || appt.staff_id || '';
                    const docFirst = appt.doctor_first_name || appt.staff_first_name || '';
                    const docLast = appt.doctor_last_name || appt.staff_last_name || '';
                    const docName = `${docFirst} ${docLast}`.trim();
                    opt.textContent = docName || (opt.value ? `Doctor #${opt.value}` : 'Doctor');
                    doctorSelect.appendChild(opt);
                }

                // Date
                const dateInput = document.getElementById('view_appointment_date');
                if (dateInput) {
                    // Prefer appointment_date, fall back to date
                    const dateVal = appt.appointment_date || appt.date || '';
                    if (dateVal) {
                        // If backend sends full datetime, extract date part
                        const isoDate = dateVal.split('T')[0].split(' ')[0];
                        dateInput.value = isoDate;
                    } else {
                        dateInput.value = '';
                    }
                }

                // Type
                const typeSelect = document.getElementById('view_appointment_type');
                if (typeSelect) {
                    const apptType = appt.appointment_type || appt.type || '';
                    typeSelect.value = apptType;
                }

                // Notes / reason
                const notesTextarea = document.getElementById('view_appointment_notes');
                if (notesTextarea) {
                    notesTextarea.value = appt.notes || appt.reason || '';
                }

                openViewAppointmentModal();
            })
            .catch(error => {
                console.error('Error loading appointment details:', error);
                showAppointmentsNotification('Failed to load appointment details. Please try again.', 'error');
            });
    }

    // Complete appointment (status update)
    function markCompleted(appointmentId) {
        if (!appointmentId) return;

        if (!confirm('Mark this appointment as completed?')) {
            return;
        }

        const baseUrlMeta = document.querySelector('meta[name="base-url"]');
        const baseUrl = baseUrlMeta ? baseUrlMeta.content : '';
        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenMeta ? csrfTokenMeta.content : '';

        const url = `${baseUrl}/appointments/${appointmentId}/status`;
        const body = new URLSearchParams();
        body.append('status', 'completed');

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': csrfToken
            },
            body: body.toString()
        })
        .then(response => response.json())
        .then(result => {
            if (result.success || result.status === 'success') {
                showAppointmentsNotification(result.message || 'Appointment marked as completed.', 'success');
                setTimeout(function() {
                    location.reload();
                }, 600);
            } else {
                showAppointmentsNotification(result.message || 'Failed to update appointment status.', 'error');
            }
        })
        .catch(error => {
            console.error('Error updating appointment status:', error);
            showAppointmentsNotification('Failed to update appointment status. Please try again.', 'error');
        });
    }

    // Edit appointment - reuse new appointment modal
    function editAppointment(appointmentId) {
        if (!appointmentId) return;

        const baseUrlMeta = document.querySelector('meta[name="base-url"]');
        const baseUrl = baseUrlMeta ? baseUrlMeta.content : '';
        if (!baseUrl) {
            console.error('Base URL meta tag not found');
            return;
        }

        const url = `${baseUrl}/appointments/${appointmentId}`;

        fetch(url)
            .then(response => response.json())
            .then(result => {
                let appt = result;
                if (result && typeof result === 'object' && 'status' in result) {
                    if (result.status !== 'success') {
                        throw new Error(result.message || 'Failed to load appointment details');
                    }
                    appt = result.data || result.appointment || {};
                }

                if (!appt) {
                    throw new Error('Empty appointment response');
                }

                // Set hidden id
                const idInput = document.getElementById('appointment_id');
                if (idInput) {
                    idInput.value = appt.appointment_id || appointmentId;
                }

                // Populate patient
                const patientSelect = document.getElementById('appointment_patient');
                if (patientSelect) {
                    // Ensure patients list is loaded first
                    loadPatients();
                    setTimeout(function() {
                        patientSelect.value = appt.patient_id || '';
                    }, 200);
                }

                // Populate doctor (admin only)
                const doctorSelect = document.getElementById('appointment_doctor');
                if (doctorSelect && appt.doctor_id) {
                    // Doctors list is pre-rendered in modal; just set value
                    doctorSelect.value = appt.doctor_id;
                }

                // Date
                const dateInput = document.getElementById('appointment_date');
                if (dateInput) {
                    const dateVal = appt.appointment_date || appt.date || '';
                    if (dateVal) {
                        const isoDate = dateVal.split('T')[0].split(' ')[0];
                        dateInput.value = isoDate;
                    }
                }

                // Type
                const typeSelect = document.getElementById('appointment_type');
                if (typeSelect) {
                    const apptType = appt.appointment_type || appt.type || '';
                    typeSelect.value = apptType;
                }

                // Notes / reason
                const notesTextarea = document.getElementById('appointment_notes');
                if (notesTextarea) {
                    notesTextarea.value = appt.notes || appt.reason || '';
                }

                // Update modal title/button for edit context (optional, non-breaking)
                const titleEl = document.getElementById('newAppointmentTitle');
                if (titleEl) {
                    titleEl.childNodes[1].nodeValue = ' Edit Appointment';
                }
                const saveBtn = document.getElementById('saveAppointmentBtn');
                if (saveBtn) {
                    saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
                }

                openNewAppointmentModal();
            })
            .catch(error => {
                console.error('Error loading appointment for edit:', error);
                showAppointmentsNotification('Failed to load appointment for editing. Please try again.', 'error');
            });
    }

    // Delete appointment
    function deleteAppointment(appointmentId) {
        if (!appointmentId) return;

        if (!confirm('Are you sure you want to delete this appointment? This action cannot be undone.')) {
            return;
        }

        const baseUrlMeta = document.querySelector('meta[name="base-url"]');
        const baseUrl = baseUrlMeta ? baseUrlMeta.content : '';
        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenMeta ? csrfTokenMeta.content : '';

        const url = `${baseUrl}/appointments/${appointmentId}`;

        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-Token': csrfToken
            }
        })
        .then(response => response.json())
        .then(result => {
            if (result.success || result.status === 'success') {
                showAppointmentsNotification(result.message || 'Appointment deleted successfully.', 'success');
                setTimeout(function() {
                    location.reload();
                }, 600);
            } else {
                showAppointmentsNotification(result.message || 'Failed to delete appointment.', 'error');
            }
        })
        .catch(error => {
            console.error('Error deleting appointment:', error);
            showAppointmentsNotification('Failed to delete appointment. Please try again.', 'error');
        });
    }

    // Prescription Modal Functions
    function openPrescriptionModal(appointmentId, patientId) {
        const modal = document.getElementById('prescriptionModal');
        if (modal) {
            // Set the patient in the dropdown
            const patientSelect = document.getElementById('patientSelect');
            if (patientSelect && patientId) {
                patientSelect.value = patientId;
                patientSelect.disabled = true; // Lock patient selection
            }
            
            // Show modal
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
        }
    }

    function closePrescriptionModal() {
        const modal = document.getElementById('prescriptionModal');
        if (modal) {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
            
            // Reset form
            const form = document.getElementById('prescriptionForm');
            if (form) {
                form.reset();
                document.getElementById('patientSelect').disabled = false;
            }
        }
    }

    // Initialize prescription modal close button
    document.addEventListener('DOMContentLoaded', function() {
        const closeBtn = document.getElementById('closePrescriptionModal');
        if (closeBtn) {
            closeBtn.addEventListener('click', closePrescriptionModal);
        }
        
        // Close modal when clicking outside
        const modal = document.getElementById('prescriptionModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closePrescriptionModal();
                }
            });
        }
    });
