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

    document.addEventListener('DOMContentLoaded', function() {
        const exportBtn = document.getElementById('exportBtn');
        if (exportBtn) exportBtn.addEventListener('click', exportToExcel);

        // Initialize modals
        if (window.AddAppointmentModal) window.AddAppointmentModal.init();
        if (window.EditAppointmentModal) window.EditAppointmentModal.init();
        if (window.ViewAppointmentModal) window.ViewAppointmentModal.init();

        // Initialize filters
        const dateSelector = document.getElementById('dateSelector');
        if (dateSelector) {
            dateSelector.addEventListener('change', refreshAppointments);
        }

        const statusFilterAppointment = document.getElementById('statusFilterAppointment');
        if (statusFilterAppointment) {
            statusFilterAppointment.addEventListener('change', applyFilters);
        }

        const searchAppointment = document.getElementById('searchAppointment');
        if (searchAppointment) {
            let searchTimeout;
            searchAppointment.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(applyFilters, 300);
            });
        }

        const clearFiltersBtn = document.getElementById('clearFiltersAppointment');
        if (clearFiltersBtn) {
            clearFiltersBtn.addEventListener('click', clearFilters);
        }

        const refreshBtn = document.getElementById('refreshBtn');
        if (refreshBtn) refreshBtn.addEventListener('click', (e) => { e.preventDefault(); refreshAppointments(); });

        refreshAppointments();
    });


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

    let allAppointments = [];

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
                    allAppointments = data.data || [];
                    applyFilters();
                } else {
                    allAppointments = [];
                    renderAppointmentsTable([]);
                }

                // Title is already rendered with today's date by PHP
            })
            .catch(error => {
                console.error('Error loading appointments:', error);
                allAppointments = [];
                renderAppointmentsTable([]);
            });
    }

    function applyFilters() {
        const searchInput = document.getElementById('searchAppointment');
        const statusFilter = document.getElementById('statusFilterAppointment');
        
        const searchTerm = (searchInput?.value || '').toLowerCase().trim();
        const statusValue = (statusFilter?.value || '').toLowerCase();

        const filtered = allAppointments.filter(appt => {
            // Search filter
            if (searchTerm) {
                const searchableText = [
                    appt.patient_first_name || '',
                    appt.patient_last_name || '',
                    appt.doctor_first_name || '',
                    appt.doctor_last_name || '',
                    appt.appointment_type || '',
                    appt.doctor_department || ''
                ].join(' ').toLowerCase();

                if (!searchableText.includes(searchTerm)) {
                    return false;
                }
            }

            // Status filter
            if (statusValue && (appt.status || '').toLowerCase() !== statusValue) {
                return false;
            }

            return true;
        });

        renderAppointmentsTable(filtered);
    }

    function clearFilters() {
        const searchInput = document.getElementById('searchAppointment');
        const statusFilter = document.getElementById('statusFilterAppointment');
        const dateFilter = document.getElementById('dateSelector');

        if (searchInput) searchInput.value = '';
        if (statusFilter) statusFilter.value = '';
        if (dateFilter) {
            const today = new Date();
            dateFilter.value = today.toISOString().split('T')[0];
        }

        refreshAppointments();
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

    // Global functions for backward compatibility
    window.markCompleted = markCompleted;
    window.deleteAppointment = deleteAppointment;
    window.openBillingModal = openBillingModal;
