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
        const todayLabel = new Date().toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
        html += '<h2>Appointment Schedule - ' + todayLabel + '</h2>';
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

    // Open billing modal
    function openBillingModal(appointmentId) {
        const modal = document.getElementById('billingModal');
        const idInput = document.getElementById('billing_appointment_id');
        const amountInput = document.getElementById('billing_amount');
        if (!modal || !idInput || !amountInput) return;

        idInput.value = appointmentId || '';
        amountInput.value = '';

        modal.classList.add('active');
        modal.removeAttribute('hidden');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeBillingModal() {
        const modal = document.getElementById('billingModal');
        if (!modal) return;
        modal.classList.remove('active');
        modal.setAttribute('hidden', 'true');
        modal.setAttribute('aria-hidden', 'true');
    }

    function submitBillingModal() {
        const idInput = document.getElementById('billing_appointment_id');
        const amountInput = document.getElementById('billing_amount');
        if (!idInput || !amountInput) return;

        const appointmentId = parseInt(idInput.value, 10);
        const unitPrice = parseFloat(amountInput.value);

        if (!appointmentId || isNaN(unitPrice) || unitPrice <= 0) {
            alert('Please enter a valid positive amount.');
            return;
        }

        addAppointmentToBill(appointmentId, unitPrice);
    }

    // Core function to call backend billing endpoint
    function addAppointmentToBill(appointmentId, unitPrice) {
        if (!appointmentId) return;

        const baseUrlMeta = document.querySelector('meta[name="base-url"]');
        const baseUrl = baseUrlMeta ? baseUrlMeta.content : '';
        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenMeta ? csrfTokenMeta.content : '';

        const url = `${baseUrl}/appointments/${appointmentId}/bill`;

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({
                unit_price: unitPrice,
                quantity: 1
            })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showAppointmentsNotification(result.message || 'Appointment added to billing.', 'success');
                closeBillingModal();
            } else {
                showAppointmentsNotification(result.message || 'Failed to add appointment to billing.', 'error');
            }
        })
        .catch(error => {
            console.error('Error adding appointment to billing:', error);
            showAppointmentsNotification('Failed to add appointment to billing. Please try again.', 'error');
        });
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
                // Reset modal to "create" state
                const titleEl = document.getElementById('newAppointmentTitle');
                if (titleEl) {
                    const icon = titleEl.querySelector('i');
                    titleEl.textContent = ' Schedule New Appointment';
                    if (icon) {
                        titleEl.prepend(icon);
                    }
                }
                const saveBtn = document.getElementById('saveAppointmentBtn');
                if (saveBtn) {
                    saveBtn.innerHTML = '<i class="fas fa-calendar-check"></i> Schedule Appointment';
                }

                // Clear any previous appointment id
                const idInput = document.getElementById('appointment_id');
                if (idInput) {
                    idInput.value = '';
                }

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

        // Use date filter only if a date selector exists and has a value
        const dateFilterEl = document.getElementById('dateSelector');
        if (dateFilterEl && dateFilterEl.value) {
            params.append('date', dateFilterEl.value);
        }

        const url = params.toString()
            ? `${baseUrl}/appointments/api?${params.toString()}`
            : `${baseUrl}/appointments/api`;

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
            // Match PHP view headers: Patient, [Doctor if admin], Type, Status, Actions
            td.colSpan = isAdmin ? 5 : 4;
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
            // Support both unified API (appointment_id) and any legacy id field
            const apptId = appt.appointment_id || appt.id;
            const tr = document.createElement('tr');

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
            patientTd.appendChild(patientDiv);
            tr.appendChild(patientTd);

            if (isAdmin) {
                const doctorTd = document.createElement('td');
                const docDiv = document.createElement('div');
                const docStrong = document.createElement('strong');
                docStrong.textContent = `Dr. ${(appt.doctor_first_name || '') + ' ' + (appt.doctor_last_name || '')}`.trim();
                docDiv.appendChild(docStrong);

                const dept = (appt.doctor_department || '').trim();
                if (dept) {
                    const br3 = document.createElement('br');
                    const docSmall = document.createElement('small');
                    docSmall.textContent = dept;
                    docDiv.appendChild(br3);
                    docDiv.appendChild(docSmall);
                }

                doctorTd.appendChild(docDiv);
                tr.appendChild(doctorTd);
            }

            const typeTd = document.createElement('td');
            typeTd.textContent = appt.appointment_type || 'N/A';
            tr.appendChild(typeTd);

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

            // View Details
            const viewBtn = document.createElement('button');
            viewBtn.className = 'btn btn-primary';
            viewBtn.style.padding = '0.3rem 0.6rem';
            viewBtn.style.fontSize = '0.75rem';
            viewBtn.innerHTML = '<i class="fas fa-eye"></i> View';
            viewBtn.onclick = function() { viewAppointment(apptId); };
            actionsDiv.appendChild(viewBtn);

            const statusLower = (appt.status || 'scheduled').toLowerCase();

            // Complete (status) for admin/doctor when not already completed
            if ((userRole === 'admin' || userRole === 'doctor') && statusLower !== 'completed') {
                const completeBtn = document.createElement('button');
                completeBtn.className = 'btn btn-success';
                completeBtn.style.padding = '0.3rem 0.6rem';
                completeBtn.style.fontSize = '0.75rem';
                completeBtn.innerHTML = '<i class="fas fa-check"></i> Complete';
                completeBtn.onclick = function() { markCompleted(apptId); };
                actionsDiv.appendChild(completeBtn);
            }

            // Edit Details
            if (['admin', 'doctor', 'receptionist'].includes(userRole)) {
                const editBtn = document.createElement('button');
                editBtn.className = 'btn btn-warning';
                editBtn.style.padding = '0.3rem 0.6rem';
                editBtn.style.fontSize = '0.75rem';
                editBtn.innerHTML = '<i class="fas fa-edit"></i> Edit';
                editBtn.onclick = function() { editAppointment(apptId); };
                actionsDiv.appendChild(editBtn);
            }

            // Delete (admin only)
            if (userRole === 'admin') {
                const delBtn = document.createElement('button');
                delBtn.className = 'btn btn-danger';
                delBtn.style.padding = '0.3rem 0.6rem';
                delBtn.style.fontSize = '0.75rem';
                delBtn.innerHTML = '<i class="fas fa-trash"></i> Delete';
                delBtn.onclick = function() { deleteAppointment(apptId); };
                actionsDiv.appendChild(delBtn);
            }

            // Add to Bill (admin/accountant)
            if (userRole === 'admin' || userRole === 'accountant') {
                const billBtn = document.createElement('button');
                billBtn.className = 'btn btn-secondary';
                billBtn.style.padding = '0.3rem 0.6rem';
                billBtn.style.fontSize = '0.75rem';
                billBtn.innerHTML = '<i class="fas fa-file-invoice-dollar"></i> Add to Bill';
                billBtn.onclick = function() { openBillingModal(apptId); };
                actionsDiv.appendChild(billBtn);
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
        if (!appointmentId) {
            alert('Missing appointment ID – cannot load details.');
            return;
        }

        // Basic feedback so users see the button is working
        // (modal will open once data is loaded).
        // You can remove this alert later if not needed.
        // eslint-disable-next-line no-alert
        alert('Loading appointment details...');

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
        if (!appointmentId) {
            alert('Missing appointment ID – cannot complete.');
            return;
        }

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
        if (!appointmentId) {
            alert('Missing appointment ID – cannot edit.');
            return;
        }

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

                // Open modal first so that patient/doctor lists are loaded
                openNewAppointmentModal();

                // Set hidden id
                const idInput = document.getElementById('appointment_id');
                if (idInput) {
                    idInput.value = appt.appointment_id || appointmentId;
                }

                // Populate patient (after options are loaded by openNewAppointmentModal)
                const patientSelect = document.getElementById('appointment_patient');
                if (patientSelect) {
                    setTimeout(function() {
                        patientSelect.value = appt.patient_id || '';
                    }, 300);
                }

                // Populate doctor (admin only, after available doctors list is loaded)
                const doctorSelect = document.getElementById('appointment_doctor');
                if (doctorSelect && appt.doctor_id) {
                    setTimeout(function() {
                        const targetValue = String(appt.doctor_id);
                        let foundOption = null;

                        for (let i = 0; i < doctorSelect.options.length; i++) {
                            if (String(doctorSelect.options[i].value) === targetValue) {
                                foundOption = doctorSelect.options[i];
                                break;
                            }
                        }

                        // If current doctor is not in the filtered list, append it so it can be selected
                        if (!foundOption) {
                            const opt = document.createElement('option');
                            opt.value = targetValue;
                            opt.textContent = appt.doctor_name || `Doctor #${targetValue}`;
                            doctorSelect.appendChild(opt);
                        }

                        doctorSelect.value = targetValue;
                    }, 300);
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

                // Update modal title/button for edit context
                const titleEl = document.getElementById('newAppointmentTitle');
                if (titleEl) {
                    const icon = titleEl.querySelector('i');
                    titleEl.textContent = ' Edit Appointment';
                    if (icon) {
                        titleEl.prepend(icon);
                    }
                }
                const saveBtn = document.getElementById('saveAppointmentBtn');
                if (saveBtn) {
                    saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
                }
            })
            .catch(error => {
                console.error('Error loading appointment for edit:', error);
                showAppointmentsNotification('Failed to load appointment for editing. Please try again.', 'error');
            });
    }

    // Delete appointment
    function deleteAppointment(appointmentId) {
        if (!appointmentId) {
            alert('Missing appointment ID – cannot delete.');
            return;
        }

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

    // Make sure inline onclick handlers can find these functions
    // (e.g. onclick="viewAppointment(…)" in the PHP view).
    window.viewAppointment   = viewAppointment;
    window.markCompleted     = markCompleted;
    window.editAppointment   = editAppointment;
    window.deleteAppointment = deleteAppointment;
    window.openBillingModal  = openBillingModal;
