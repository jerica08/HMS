(function() {
    const baseUrl = document.querySelector('meta[name="base-url"]').content.replace(/\/$/, '') + '/';
    const userRole = (document.querySelector('meta[name="user-role"]')?.content || '').toLowerCase();

    const tableBody = document.getElementById('labOrdersTableBody');
    const statusFilter = document.getElementById('labStatusFilter');
    const dateFilter = document.getElementById('labDateFilter');
    const searchInput = document.getElementById('labSearch');
    const refreshBtn = document.getElementById('labRefreshBtn');
    const createBtn = document.getElementById('createLabOrderBtn');

    // Modal elements
    const labOrderModal = document.getElementById('labOrderModal');
    const labOrderForm = document.getElementById('labOrderForm');
    const labOrderModalCloseBtn = document.getElementById('labOrderModalCloseBtn');
    const labOrderModalCancelBtn = document.getElementById('labOrderModalCancelBtn');
    const labOrderModalSubmitBtn = document.getElementById('labOrderModalSubmitBtn');
    const labPatientSelect = document.getElementById('labPatientSelect');
    const labTestSelect = document.getElementById('labTestSelect');
    const labPrioritySelect = document.getElementById('labPriority');

    const totalTodayEl = document.getElementById('labTotalToday');
    const inProgressEl = document.getElementById('labInProgress');
    const completedEl = document.getElementById('labCompleted');

    // Lab tests admin section
    const labTestForm = document.getElementById('labTestForm');
    const labTestIdInput = document.getElementById('labTestId');
    const labTestCodeInput = document.getElementById('labTestCode');
    const labTestNameInput = document.getElementById('labTestName');
    const labTestPriceInput = document.getElementById('labTestPrice');
    const labTestCategoryInput = document.getElementById('labTestCategory');
    const labTestStatusSelect = document.getElementById('labTestStatus');
    const labTestSaveBtn = document.getElementById('labTestSaveBtn');
    const labTestResetBtn = document.getElementById('labTestResetBtn');
    const labTestsTableBody = document.getElementById('labTestsTableBody');

    async function fetchLabOrders() {
        try {
            if (!tableBody) return;

            const params = new URLSearchParams();
            if (statusFilter && statusFilter.value) params.append('status', statusFilter.value);
            if (dateFilter && dateFilter.value) params.append('date', dateFilter.value);
            if (searchInput && searchInput.value) params.append('search', searchInput.value);

            const res = await fetch(baseUrl + 'labs/api?' + params.toString(), { credentials: 'same-origin' });
            const data = await res.json();

            renderLabOrders(Array.isArray(data) ? data : []);
        } catch (e) {
            console.error('Failed to load lab orders', e);
            if (tableBody) {
                tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:1.5rem; color:#b91c1c;">Failed to load lab orders.</td></tr>';
            }
        }
    }

    async function fetchLabTestsForAdmin() {
        if (!labTestsTableBody || userRole !== 'admin') return;

        try {
            labTestsTableBody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:1.5rem; color:#6b7280;">Loading lab tests...</td></tr>';

            const res = await fetch(baseUrl + 'labs/tests', { credentials: 'same-origin' });
            const data = await res.json();

            if (data.status !== 'success' || !Array.isArray(data.data) || !data.data.length) {
                labTestsTableBody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:1.5rem; color:#6b7280;">No lab tests found.</td></tr>';
                return;
            }

            const rows = data.data.map(test => {
                const statusBadge = test.status === 'inactive'
                    ? '<span class="badge badge-danger">Inactive</span>'
                    : '<span class="badge badge-success">Active</span>';

                return `<tr data-test-id="${test.lab_test_id}">
                    <td>${escapeHtml(test.test_code)}</td>
                    <td>${escapeHtml(test.test_name)}</td>
                    <td>${escapeHtml(test.category || '')}</td>
                    <td>${escapeHtml(test.default_price)}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <div style="display:flex;gap:0.25rem;flex-wrap:wrap;">
                            <button class="btn btn-warning" style="padding:0.3rem 0.6rem;font-size:0.75rem;" data-action="edit-test" data-id="${test.lab_test_id}">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-danger" style="padding:0.3rem 0.6rem;font-size:0.75rem;" data-action="delete-test" data-id="${test.lab_test_id}">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </td>
                </tr>`;
            });

            labTestsTableBody.innerHTML = rows.join('');
        } catch (e) {
            console.error('Failed to load lab tests', e);
            labTestsTableBody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:1.5rem; color:#b91c1c;">Failed to load lab tests.</td></tr>';
        }
    }

    function resetLabTestForm() {
        if (!labTestForm) return;
        labTestForm.reset();
        if (labTestIdInput) labTestIdInput.value = '';
        if (labTestStatusSelect) labTestStatusSelect.value = 'active';
    }

    function populateLabTestFormFromRow(row) {
        if (!row || !labTestForm) return;

        const id = row.getAttribute('data-test-id');
        const cells = row.querySelectorAll('td');
        if (!cells.length) return;

        if (labTestIdInput) labTestIdInput.value = id || '';
        if (labTestCodeInput) labTestCodeInput.value = cells[0].textContent.trim();
        if (labTestNameInput) labTestNameInput.value = cells[1].textContent.trim();
        if (labTestCategoryInput) labTestCategoryInput.value = cells[2].textContent.trim();
        if (labTestPriceInput) labTestPriceInput.value = cells[3].textContent.trim();

        if (labTestStatusSelect) {
            const statusSpan = cells[4].querySelector('.badge-danger');
            labTestStatusSelect.value = statusSpan ? 'inactive' : 'active';
        }
    }

    async function saveLabTest() {
        if (!labTestForm) return;

        const isEdit = !!(labTestIdInput && labTestIdInput.value);
        const url = isEdit
            ? baseUrl + 'labs/tests/' + labTestIdInput.value
            : baseUrl + 'labs/tests';

        const payload = {
            test_code: labTestCodeInput?.value?.trim() || '',
            test_name: labTestNameInput?.value?.trim() || '',
            default_price: labTestPriceInput?.value || 0,
            category: labTestCategoryInput?.value?.trim() || null,
            status: labTestStatusSelect?.value || 'active',
        };

        if (!payload.test_code || !payload.test_name) {
            alert('Test code and name are required.');
            return;
        }

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
                credentials: 'same-origin',
            });
            const data = await res.json();

            alert(data.message || (data.status === 'success' ? 'Saved' : 'Failed to save'));
            if (data.status === 'success') {
                resetLabTestForm();
                fetchLabTestsForAdmin();
            }
        } catch (e) {
            console.error('Failed to save lab test', e);
            alert('Failed to save lab test');
        }
    }

    async function deleteLabTest(id) {
        if (!id) return;
        if (!window.confirm('Delete this lab test?')) return;

        try {
            const res = await fetch(baseUrl + 'labs/tests/' + id, {
                method: 'DELETE',
                credentials: 'same-origin',
            });
            const data = await res.json();

            alert(data.message || (data.status === 'success' ? 'Deleted' : 'Failed to delete'));
            if (data.status === 'success') {
                fetchLabTestsForAdmin();
            }
        } catch (e) {
            console.error('Failed to delete lab test', e);
            alert('Failed to delete lab test');
        }
    }

    function renderLabOrders(orders) {
        if (!tableBody) return;

        if (!orders.length) {
            tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:1.5rem; color:#6b7280;">No lab orders found.</td></tr>';
            if (totalTodayEl) totalTodayEl.textContent = '0';
            if (inProgressEl) inProgressEl.textContent = '0';
            if (completedEl) completedEl.textContent = '0';
            return;
        }

        let totalToday = 0;
        let inProgress = 0;
        let completed = 0;
        const todayStr = (new Date()).toISOString().split('T')[0];

        const rows = orders.map(order => {
            const orderedAt = order.ordered_at || order.created_at || '';
            const orderedDate = orderedAt ? orderedAt.substring(0, 10) : '';
            if (orderedDate === todayStr) totalToday++;
            if (order.status === 'in_progress') inProgress++;
            if (order.status === 'completed') completed++;

            const patientName = order.patient_name || ((order.first_name || '') + ' ' + (order.last_name || '')).trim() || 'Unknown';
            const testLabel = (order.test_name || order.test_code || 'N/A');
            const priority = order.priority || 'routine';
            const status = order.status || 'ordered';

            let badgeClass = 'badge-info';
            switch (status) {
                case 'completed': badgeClass = 'badge-success'; break;
                case 'in_progress': badgeClass = 'badge-warning'; break;
                case 'cancelled': badgeClass = 'badge-danger'; break;
                default: badgeClass = 'badge-info';
            }

            const canAct = ['admin', 'doctor', 'laboratorist', 'it_staff'].includes(userRole);
            const actions = [];
            if (canAct && status !== 'completed' && status !== 'cancelled') {
                if (status === 'ordered') {
                    actions.push(`<button class="btn btn-warning" style="padding:0.3rem 0.6rem;font-size:0.75rem;" onclick="LabUI.updateStatus(${order.lab_order_id}, 'in_progress')"><i class="fas fa-play"></i> Start</button>`);
                }
                if (status === 'in_progress' || status === 'ordered') {
                    actions.push(`<button class="btn btn-success" style="padding:0.3rem 0.6rem;font-size:0.75rem;" onclick="LabUI.updateStatus(${order.lab_order_id}, 'completed')"><i class="fas fa-check"></i> Complete</button>`);
                }
                actions.push(`<button class="btn btn-danger" style="padding:0.3rem 0.6rem;font-size:0.75rem;" onclick="LabUI.updateStatus(${order.lab_order_id}, 'cancelled')"><i class="fas fa-times"></i> Cancel</button>`);
            }

            return `<tr>
                <td>${orderedAt ? escapeHtml(orderedAt) : ''}</td>
                <td>${escapeHtml(patientName)}</td>
                <td>${escapeHtml(testLabel)}</td>
                <td>${escapeHtml(priority.charAt(0).toUpperCase() + priority.slice(1))}</td>
                <td><span class="badge ${badgeClass}">${escapeHtml(status.replace('_', ' '))}</span></td>
                <td><div style="display:flex;gap:0.25rem;flex-wrap:wrap;">${actions.join(' ')}</div></td>
            </tr>`;
        });

        tableBody.innerHTML = rows.join('');

        if (totalTodayEl) totalTodayEl.textContent = String(totalToday);
        if (inProgressEl) inProgressEl.textContent = String(inProgress);
        if (completedEl) completedEl.textContent = String(completed);
    }

    async function loadLabPatients() {
        if (!labPatientSelect) return;

        try {
            labPatientSelect.innerHTML = '<option value="">Loading patients...</option>';
            labPatientSelect.disabled = true;

            const res = await fetch(baseUrl + 'labs/patients', { credentials: 'same-origin' });
            const data = await res.json();

            if (data.status === 'success' && Array.isArray(data.data)) {
                if (!data.data.length) {
                    labPatientSelect.innerHTML = '<option value="">No patients available</option>';
                } else {
                    labPatientSelect.innerHTML = '<option value="">Select Patient</option>';
                    data.data.forEach(p => {
                        const option = document.createElement('option');
                        option.value = p.patient_id;
                        const name = `${p.first_name || ''} ${p.last_name || ''}`.trim() || `Patient #${p.patient_id}`;
                        option.textContent = name;
                        labPatientSelect.appendChild(option);
                    });
                }
            } else {
                labPatientSelect.innerHTML = '<option value="">Failed to load patients</option>';
            }
        } catch (e) {
            console.error('Failed to load patients', e);
            if (labPatientSelect) {
                labPatientSelect.innerHTML = '<option value="">Error loading patients</option>';
            }
        } finally {
            if (labPatientSelect) labPatientSelect.disabled = false;
        }
    }

    async function loadLabTests() {
        if (!labTestSelect) return;

        try {
            labTestSelect.innerHTML = '<option value="">Loading tests...</option>';
            labTestSelect.disabled = true;

            const res = await fetch(baseUrl + 'labs/tests', { credentials: 'same-origin' });
            const data = await res.json();

            if (data.status === 'success' && Array.isArray(data.data)) {
                if (!data.data.length) {
                    labTestSelect.innerHTML = '<option value="">No lab tests configured</option>';
                } else {
                    labTestSelect.innerHTML = '<option value="">Select Lab Test</option>';
                    data.data.forEach(test => {
                        const option = document.createElement('option');
                        option.value = test.test_code;
                        option.textContent = `${test.test_name} (${test.test_code})`;
                        option.dataset.testName = test.test_name;
                        labTestSelect.appendChild(option);
                    });
                }
            } else {
                labTestSelect.innerHTML = '<option value="">Failed to load lab tests</option>';
            }
        } catch (e) {
            console.error('Failed to load lab tests', e);
            if (labTestSelect) {
                labTestSelect.innerHTML = '<option value="">Error loading lab tests</option>';
            }
        } finally {
            if (labTestSelect) labTestSelect.disabled = false;
        }
    }

    function openLabOrderModal() {
        if (!labOrderModal || !labOrderForm) return;

        labOrderForm.reset();
        if (labPrioritySelect) labPrioritySelect.value = 'routine';

        labOrderModal.removeAttribute('hidden');
        labOrderModal.setAttribute('aria-hidden', 'false');

        // Center modal overlay
        labOrderModal.style.display = 'flex';
        labOrderModal.style.position = 'fixed';
        labOrderModal.style.top = '0';
        labOrderModal.style.left = '0';
        labOrderModal.style.width = '100vw';
        labOrderModal.style.height = '100vh';
        labOrderModal.style.zIndex = '9999';
        labOrderModal.style.alignItems = 'center';
        labOrderModal.style.justifyContent = 'center';
        labOrderModal.style.background = 'rgba(15, 23, 42, 0.55)';

        loadLabPatients();
        loadLabTests();
    }

    function closeLabOrderModal() {
        if (!labOrderModal) return;
        labOrderModal.setAttribute('aria-hidden', 'true');
        labOrderModal.style.display = 'none';
        labOrderModal.setAttribute('hidden', 'hidden');
    }

    async function submitLabOrderForm() {
        if (!labOrderForm || !labPatientSelect || !labTestSelect) return;

        const patientIdVal = labPatientSelect.value;
        const testCodeVal = labTestSelect.value;
        const priorityVal = labPrioritySelect ? labPrioritySelect.value : 'routine';

        if (!patientIdVal || !testCodeVal) {
            alert('Patient ID and Lab Test are required.');
            return;
        }

        const selectedOption = labTestSelect.options[labTestSelect.selectedIndex];
        const testName = selectedOption?.dataset?.testName || selectedOption?.textContent || null;

        const payload = {
            patient_id: parseInt(patientIdVal, 10),
            test_code: testCodeVal,
            test_name: testName,
            priority: priorityVal
        };

        try {
            const res = await fetch(baseUrl + 'labs/create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload),
                credentials: 'same-origin'
            });
            const data = await res.json();

            alert(data.message || (data.success ? 'Lab order created' : 'Failed to create lab order'));
            if (data.success) {
                closeLabOrderModal();
                fetchLabOrders();
            }
        } catch (e) {
            console.error('Failed to create lab order', e);
            alert('Failed to create lab order');
        }
    }

    async function updateStatus(labOrderId, status) {
        if (!labOrderId || !status) return;

        const confirmText = `Change status of lab order #${labOrderId} to ${status.replace('_', ' ')}?`;
        if (!window.confirm(confirmText)) return;

        try {
            const res = await fetch(baseUrl + 'labs/' + labOrderId + '/status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ status }),
                credentials: 'same-origin'
            });
            const data = await res.json();

            alert(data.message || (data.success ? 'Status updated' : 'Failed to update status'));
            if (data.success) {
                fetchLabOrders();
            }
        } catch (e) {
            console.error('Failed to update lab status', e);
            alert('Failed to update status');
        }
    }

    function escapeHtml(str) {
        if (str == null) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function initEvents() {
        if (statusFilter) statusFilter.addEventListener('change', fetchLabOrders);
        if (dateFilter) dateFilter.addEventListener('change', fetchLabOrders);
        if (searchInput) searchInput.addEventListener('keyup', function(e){ if (e.key === 'Enter') fetchLabOrders(); });
        if (refreshBtn) refreshBtn.addEventListener('click', fetchLabOrders);
        if (createBtn) createBtn.addEventListener('click', openLabOrderModal);

        if (labOrderModalCloseBtn) labOrderModalCloseBtn.addEventListener('click', closeLabOrderModal);
        if (labOrderModalCancelBtn) labOrderModalCancelBtn.addEventListener('click', closeLabOrderModal);
        if (labOrderModalSubmitBtn) labOrderModalSubmitBtn.addEventListener('click', submitLabOrderForm);

        if (labOrderModal) {
            labOrderModal.addEventListener('click', function(e) {
                if (e.target === labOrderModal) {
                    closeLabOrderModal();
                }
            });
        }

        if (labTestSaveBtn && userRole === 'admin') {
            labTestSaveBtn.addEventListener('click', saveLabTest);
        }

        if (labTestResetBtn && userRole === 'admin') {
            labTestResetBtn.addEventListener('click', resetLabTestForm);
        }

        if (labTestsTableBody && userRole === 'admin') {
            labTestsTableBody.addEventListener('click', function(e) {
                const target = e.target.closest('button[data-action]');
                if (!target) return;

                const id = target.getAttribute('data-id');
                const action = target.getAttribute('data-action');
                const row = target.closest('tr');

                if (action === 'edit-test') {
                    populateLabTestFormFromRow(row);
                } else if (action === 'delete-test') {
                    deleteLabTest(id);
                }
            });
        }
    }

    window.LabUI = {
        refresh: fetchLabOrders,
        updateStatus,
        createLabOrder: openLabOrderModal,
    };

    document.addEventListener('DOMContentLoaded', function() {
        initEvents();
        fetchLabOrders();
        fetchLabTestsForAdmin();
    });
})();
