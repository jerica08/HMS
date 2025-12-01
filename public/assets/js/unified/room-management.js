 (function () {
    const metaBaseUrl = document.querySelector('meta[name="base-url"]');
    const baseUrl = metaBaseUrl ? metaBaseUrl.content.replace(/\/*$/, '') : '';
    const roomsTableBody = document.getElementById('roomsTableBody');
    const addRoomBtn = document.getElementById('addRoomBtn');
    const addRoomModal = document.getElementById('addRoomModal');
    const saveRoomBtn = document.getElementById('saveRoomBtn');
    const modalTitle = document.getElementById('addRoomTitle');
    const assignRoomModal = document.getElementById('assignRoomModal');
    const assignRoomForm = document.getElementById('assignRoomForm');
    const assignRoomIdInput = document.getElementById('assign_room_id');
    const assignPatientSelect = document.getElementById('assign_patient_id');
    const saveAssignRoomBtn = document.getElementById('saveAssignRoomBtn');
    const roomTypeInput = document.getElementById('modal_room_type');
    const floorInput = document.getElementById('modal_floor');
    const roomNumberInput = document.getElementById('modal_room_number');
    const rateRangeInput = document.getElementById('modal_rate_range');
    const roomNotesInput = document.getElementById('modal_notes');
    const bedCapacityInput = document.getElementById('modal_bed_capacity');
    const bedNamesContainer = document.getElementById('modal_bed_names_container');
    const departmentSelect = document.getElementById('modal_department');
    const accommodationSelect = document.getElementById('modal_accommodation_type');
    const roomTypeMetadata = window.roomTypeMetadata || {};

    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfHashMeta = document.querySelector('meta[name="csrf-hash"]');
    const csrfTokenName = csrfTokenMeta ? csrfTokenMeta.content : 'csrf_token';
    let csrfHash = csrfHashMeta ? csrfHashMeta.content : '';
    const csrfField = document.querySelector(`#addRoomForm input[name="${csrfTokenName}"]`);
    const existingRoomNumbers = new Set();
    let roomsData = [];
    let editingRoomId = null;

    const refreshCsrfHash = (newHash) => {
        if (!newHash) {
            return;
        }

        csrfHash = newHash;
        if (csrfHashMeta) {
            csrfHashMeta.setAttribute('content', newHash);
        }
        if (csrfField) {
            csrfField.value = newHash;
        }
    };

    if (departmentSelect && floorInput) {
        departmentSelect.addEventListener('change', () => {
            const selectedOption = departmentSelect.options[departmentSelect.selectedIndex];
            if (!selectedOption) {
                return;
            }
            const deptFloor = selectedOption.getAttribute('data-floor') || '';
            floorInput.value = deptFloor;
        });
    }

    const roomTypeOptionsByAccommodation = {
        'General Ward / General Accommodation': [
            'Ward',
            'Semi-Private Room',
            'Private Room',
            'Deluxe Room',
            'Executive Room',
            'Suite / VIP Suite',
        ],
        'Intensive / Critical Care Units': [
            'ICU Bed (Intensive Care Unit)',
            'CCU Bed (Coronary Care Unit)',
            'NICU Incubator (Neonatal ICU)',
            'PICU Bed (Pediatric ICU)',
            'SICU Bed (Surgical ICU)',
            'MICU Bed (Medical ICU)',
            'HDU Bed (High Dependency Unit)',
        ],
        'Maternity / Obstetrics Accommodation': [
            'Labor Room',
            'Delivery Room',
            'Birthing Room (LDR – Labor, Delivery, Recovery)',
            'Postpartum Room',
            'Maternity Ward',
            'Nursery Room',
        ],
        'Pediatric Accommodation': [
            'Pediatric Ward',
            'Pediatric Private Room',
            'Pediatric Isolation Room',
            'PICU Bed',
            'Neonatal Room',
        ],
        'Isolation Accommodation': [
            'Negative Pressure Isolation Room',
            'Positive Pressure Isolation Room',
            'Isolation Private Room',
            'Isolation Ward',
        ],
        'Surgical / Post-Operative Accommodation': [
            'Recovery Room',
            'PACU Bed (Post-Anesthesia Care Unit)',
            'Post-Op Ward',
            'Post-Op Private Room',
        ],
        'Specialty Units': [
            'Dialysis Station',
            'Oncology Room',
            'Rehabilitation / Physical Therapy Room',
            'Psychiatric Room',
            'TB-DOTS Isolation Room',
        ],
    };

    const populateRoomTypeOptions = (accommodationValue, currentValue = '') => {
        if (!roomTypeInput) {
            return;
        }

        const options = roomTypeOptionsByAccommodation[accommodationValue] || [];
        roomTypeInput.innerHTML = '<option value="">Select room type</option>';

        if (options.length === 0) {
            const opt = document.createElement('option');
            opt.value = 'N/A';
            opt.textContent = 'Not applicable / No predefined room types';
            roomTypeInput.appendChild(opt);
        } else {
            options.forEach((label) => {
                const opt = document.createElement('option');
                opt.value = label;
                opt.textContent = label;
                roomTypeInput.appendChild(opt);
            });
        }

        if (currentValue) {
            roomTypeInput.value = currentValue;
            if (!roomTypeInput.value) {
                const opt = document.createElement('option');
                opt.value = currentValue;
                opt.textContent = currentValue;
                roomTypeInput.appendChild(opt);
                roomTypeInput.value = currentValue;
            }
        }
    };

    if (accommodationSelect) {
        accommodationSelect.addEventListener('change', () => {
            const value = accommodationSelect.value || '';
            populateRoomTypeOptions(value, '');
        });
    }

    const showNotification = (message, type = 'success') => {
        const iconMap = {
            success: 'check-circle',
            error: 'exclamation-triangle',
            warning: 'exclamation-triangle',
            info: 'info-circle',
        };

        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.style.cssText = 'position:fixed;top:20px;right:20px;z-index:1050;min-width:260px;box-shadow:0 4px 12px rgba(0,0,0,0.2);display:flex;align-items:center;gap:0.5rem;';
        alert.innerHTML = `
            <i class="fas fa-${iconMap[type] || 'info-circle'}"></i>
            <span>${message}</span>
            <button type="button" class="btn btn-link" style="margin-left:auto" aria-label="Dismiss">&times;</button>
        `;
        alert.querySelector('button').addEventListener('click', () => alert.remove());
        document.body.appendChild(alert);
        setTimeout(() => alert.remove(), 4000);
    };

    const fetchRooms = async () => {
        try {
            const response = await fetch(`${baseUrl}/rooms/api`, {
                headers: { 'Accept': 'application/json' },
            });
            const payload = await response.json();
            if (!payload?.data) throw new Error('Invalid API response');

            roomsData = payload.data;
            renderRooms(payload.data);
        } catch (error) {
            console.error('Failed to load rooms', error);
            roomsTableBody.innerHTML = `
                <tr>
                    <td colspan="6" style="text-align:center; color:#b91c1c;">
                        <i class="fas fa-exclamation-triangle"></i> Could not load rooms.
                    </td>
                </tr>
            `;
        }
    };

    const renderRooms = (rooms) => {
        existingRoomNumbers.clear();
        rooms.forEach((room) => {
            if (room.room_number) {
                existingRoomNumbers.add(room.room_number.toLowerCase());
            }
        });

        if (!rooms.length) {
            roomsTableBody.innerHTML = `
                <tr>
                    <td colspan="6" style="text-align:center;">
                        <i class="fas fa-bed" aria-hidden="true"></i> No rooms yet.
                    </td>
                </tr>
            `;
            return;
        }

        roomsTableBody.innerHTML = rooms.map((room) => {
            const isOccupied = (room.status || '').toLowerCase() === 'occupied';
            return `
            <tr>
                <td>
                    ${escapeHtml(room.room_number)}
                    ${room.room_type ? `<small style="display:block; color:#6b7280;">${escapeHtml(room.room_type)}</small>` : ''}
                </td>
                <td>${escapeHtml(room.type_name || '—')}</td>
                <td>${escapeHtml(room.department_name || '—')}</td>
                <td>${room.bed_capacity ?? '—'}</td>
                <td>${capitalize(room.status || 'unknown')}</td>
                <td>
                    <div class="table-actions">
                        <button class="btn btn-sm btn-outline" data-action="edit" data-room-id="${room.room_id}" aria-label="Edit room ${escapeHtml(room.room_number)}">
                            <i class="fas fa-pen"></i>
                        </button>
                        <button class="btn btn-sm btn-outline" data-action="assign" data-room-id="${room.room_id}" aria-label="Assign room ${escapeHtml(room.room_number)} to patient">
                            <i class="fas fa-user-plus"></i>
                        </button>
                        ${isOccupied ? `<button class="btn btn-sm btn-outline" data-action="discharge" data-room-id="${room.room_id}" aria-label="Discharge room ${escapeHtml(room.room_number)}">
                            <i class="fas fa-door-open"></i>
                        </button>` : ''}
                        <button class="btn btn-sm btn-outline btn-danger" data-action="delete" data-room-id="${room.room_id}" aria-label="Delete room ${escapeHtml(room.room_number)}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
        }).join('');
    };

    const escapeHtml = (value) => {
        if (!value) return '';
        return value
            .toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    };

    const capitalize = (value) => value ? value.charAt(0).toUpperCase() + value.slice(1) : '—';

    const syncBedNameInputsFromCapacity = (existingNames = []) => {
        if (!bedCapacityInput || !bedNamesContainer) {
            return;
        }

        const capacityRaw = bedCapacityInput.value;
        const capacity = parseInt(capacityRaw, 10);

        bedNamesContainer.innerHTML = '';

        if (!Number.isFinite(capacity) || capacity <= 0) {
            return;
        }

        for (let i = 0; i < capacity; i += 1) {
            const wrapper = document.createElement('div');
            wrapper.className = 'mb-1';

            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'bed_names[]';
            input.className = 'form-input';
            input.placeholder = `e.g. 101-${String.fromCharCode(65 + i)}`;

            if (Array.isArray(existingNames) && typeof existingNames[i] === 'string') {
                input.value = existingNames[i];
            }

            wrapper.appendChild(input);
            bedNamesContainer.appendChild(wrapper);
        }
    };

    const openModal = ({ skipAutofill = false } = {}) => {
        addRoomModal.style.display = 'block';
        addRoomModal.setAttribute('aria-hidden', 'false');
        if (!skipAutofill) {
            applySelectedRoomTypeMetadata();
        }
        if (modalTitle) {
            modalTitle.querySelector('span')?.remove();
        }
    };

    const closeModal = () => {
        addRoomModal.style.display = 'none';
        addRoomModal.setAttribute('aria-hidden', 'true');
        const form = document.getElementById('addRoomForm');
        form.reset();
        form.removeAttribute('data-room-id');
        applySelectedRoomTypeMetadata();
        if (bedNamesContainer) {
            bedNamesContainer.innerHTML = '';
        }
        editingRoomId = null;
        if (modalTitle) {
            modalTitle.innerHTML = '<i class="fas fa-hotel" style="color:#0ea5e9"></i> Add New Room';
        }
    };

    const handleModalClick = (event) => {
        if (event.target === addRoomModal || event.target.getAttribute('data-dismiss') === 'modal') {
            closeModal();
        }
    };

    const generateUniqueRoomNumber = (template) => {
        if (!template) {
            return '';
        }

        const match = template.match(/^(.*?)(\d+)(?!.*\d)/);
        let prefix = template;
        let suffix = '01';

        if (match) {
            prefix = match[1];
            suffix = match[2];
        } else if (!template.endsWith('-')) {
            prefix = `${template}-`;
        }

        let counter = parseInt(suffix, 10);
        if (Number.isNaN(counter)) {
            counter = 1;
        }
        const width = suffix.length || 2;

        let candidate = `${prefix}${String(counter).padStart(width, '0')}`;
        let normalized = candidate.toLowerCase();

        while (existingRoomNumbers.has(normalized)) {
            counter += 1;
            candidate = `${prefix}${String(counter).padStart(width, '0')}`;
            normalized = candidate.toLowerCase();
        }

        return candidate;
    };

    const applySelectedRoomTypeMetadata = () => {
        const metadata = roomTypeMetadata['default'] ?? {};
        if (floorInput) {
            floorInput.value = metadata?.floor_label ?? '';
        }

        if (roomNumberInput) {
            const template = metadata?.room_number_template ?? '';
            roomNumberInput.value = generateUniqueRoomNumber(template);
        }

        if (rateRangeInput) {
            rateRangeInput.value = metadata?.rate_range ?? '';
        }

        if (roomNotesInput) {
            roomNotesInput.value = metadata?.notes ?? '';
        }
    };

    const handleRoomTypeInput = () => {};

    const handleTableClick = (event) => {
        const actionBtn = event.target.closest('button[data-action]');
        if (!actionBtn) {
            return;
        }

        const { action, roomId } = actionBtn.dataset;
        const room = roomsData.find((item) => String(item.room_id) === String(roomId));
        if (!room) {
            showNotification('Room not found in current list.', 'error');
            return;
        }

        if (action === 'edit') {
            startEditRoom(room);
        } else if (action === 'delete') {
            confirmDeleteRoom(room);
        } else if (action === 'assign') {
            openAssignModal(room);
        } else if (action === 'discharge') {
            confirmDischargeRoom(room);
        }
    };

    const fillFormWithRoom = (room) => {
        const form = document.getElementById('addRoomForm');
        form.setAttribute('data-room-id', room.room_id);
        editingRoomId = room.room_id;

        if (roomTypeInput) {
            roomTypeInput.value = room.room_type_id || '';
        }
        applySelectedRoomTypeMetadata();
        roomNumberInput.value = room.room_number || '';
        const roomNameInput = document.getElementById('modal_room_name');
        if (roomNameInput) {
            roomNameInput.value = room.room_type || '';
        }
        if (floorInput) {
            floorInput.value = room.floor_number || '';
        }
        if (rateRangeInput) {
            rateRangeInput.value = room.rate_range || '';
        }
        const hourlyRateInput = document.getElementById('modal_hourly_rate');
        if (hourlyRateInput) {
            hourlyRateInput.value = room.hourly_rate || '';
        }
        const overtimeChargeInput = document.getElementById('modal_overtime_charge');
        if (overtimeChargeInput) {
            overtimeChargeInput.value = room.overtime_charge_per_hour || '';
        }
        const extraChargeInput = document.getElementById('modal_extra_charge');
        if (extraChargeInput) {
            extraChargeInput.value = room.extra_person_charge || '';
        }
        const bedCapacityInputEl = document.getElementById('modal_bed_capacity');
        if (bedCapacityInputEl) {
            bedCapacityInputEl.value = room.bed_capacity || '';
        }
        syncBedNameInputsFromCapacity();
        document.getElementById('modal_department').value = room.department_id || '';
        document.getElementById('modal_status').value = room.status || 'available';
    };

    const startEditRoom = (room) => {
        fillFormWithRoom(room);
        if (modalTitle) {
            modalTitle.innerHTML = '<i class="fas fa-hotel" style="color:#0ea5e9"></i> Edit Room';
        }
        openModal({ skipAutofill: true });
    };

    const confirmDeleteRoom = (room) => {
        if (!confirm(`Delete room ${room.room_number}? This cannot be undone.`)) {
            return;
        }
        deleteRoom(room.room_id);
    };

    const confirmDischargeRoom = (room) => {
        if (!confirm(`Discharge room ${room.room_number}? This will free the room and finalize the stay.`)) {
            return;
        }
        dischargeRoom(room.room_id);
    };

    const dischargeRoom = async (roomId) => {
        const formData = new FormData();
        formData.set('room_id', roomId);
        formData.set(csrfTokenName, csrfHash);

        try {
            const response = await fetch(`${baseUrl}/rooms/discharge`, {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' },
            });

            const result = await response.json();
            refreshCsrfHash(result?.csrf_hash);

            if (!result.success) {
                throw new Error(result.message || 'Failed to discharge room');
            }

            const msg = result.billing_message || 'Room discharged successfully.';
            showNotification(msg, 'success');
            fetchRooms();
        } catch (error) {
            console.error(error);
            showNotification(error.message || 'Could not discharge room right now.', 'error');
        }
    };

    const openAssignModal = (room) => {
        if (!assignRoomModal || !assignRoomForm || !assignRoomIdInput || !assignPatientSelect) {
            return;
        }

        assignRoomIdInput.value = room.room_id;
        assignPatientSelect.innerHTML = '<option value="">Loading patients...</option>';

        assignRoomModal.style.display = 'block';
        assignRoomModal.setAttribute('aria-hidden', 'false');

        loadPatientsForAssign();
    };

    const closeAssignModal = () => {
        if (!assignRoomModal || !assignRoomForm || !assignPatientSelect) return;
        assignRoomModal.style.display = 'none';
        assignRoomModal.setAttribute('aria-hidden', 'true');
        assignRoomForm.reset();
        assignPatientSelect.innerHTML = '<option value="">Select patient</option>';
    };

    const loadPatientsForAssign = async () => {
        try {
            const response = await fetch(`${baseUrl}/rooms/patients`, {
                headers: { 'Accept': 'application/json' },
            });
            const payload = await response.json();
            const patients = payload?.data || [];

            if (!patients.length) {
                assignPatientSelect.innerHTML = '<option value="">No patients available</option>';
                return;
            }

            assignPatientSelect.innerHTML = [
                '<option value="">Select patient</option>',
                ...patients.map((p) => {
                    const name = escapeHtml(p.full_name || `${p.first_name || ''} ${p.last_name || ''}`.trim() || `Patient #${p.patient_id}`);
                    return `<option value="${p.patient_id}">${name}</option>`;
                }),
            ].join('');
        } catch (error) {
            console.error('Failed to load patients for room assignment', error);
            assignPatientSelect.innerHTML = '<option value="">Error loading patients</option>';
        }
    };

    const submitAssignRoom = async () => {
        if (!assignRoomForm || !assignPatientSelect || !assignRoomIdInput) return;

        const patientId = (assignPatientSelect.value || '').trim();
        const roomId = (assignRoomIdInput.value || '').trim();

        if (!roomId) {
            showNotification('Room information is missing.', 'error');
            return;
        }

        if (!patientId) {
            showNotification('Please select a patient.', 'error');
            assignPatientSelect.focus();
            return;
        }

        const formData = new FormData(assignRoomForm);
        formData.set('room_id', roomId);
        formData.set('patient_id', patientId);

        try {
            const response = await fetch(`${baseUrl}/rooms/assign`, {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' },
            });

            const result = await response.json();
            refreshCsrfHash(result?.csrf_hash);

            if (!result.success) {
                throw new Error(result.message || 'Failed to assign room');
            }

            showNotification('Room assigned to patient successfully.', 'success');
            closeAssignModal();
            fetchRooms();
        } catch (error) {
            console.error(error);
            showNotification(error.message || 'Could not assign room right now.', 'error');
        }
    };

    const submitRoom = async () => {
        const form = document.getElementById('addRoomForm');
        const formData = new FormData(form);
        const selectedRoomTypeId = (roomTypeInput?.value || '').trim();

        if (!selectedRoomTypeId) {
            showNotification('Please select a room type.', 'error');
            roomTypeInput?.focus();
            return;
        }

        formData.set('room_type_id', selectedRoomTypeId);
        formData.set('custom_room_type', '');

        let endpoint = `${baseUrl}/rooms/create`;
        if (editingRoomId) {
            formData.append('room_id', editingRoomId);
            endpoint = `${baseUrl}/rooms/${editingRoomId}/update`;
        }

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' },
            });
            const result = await response.json();
            refreshCsrfHash(result?.csrf_hash);
            if (!result.success) {
                throw new Error(result.message || 'Failed to save room');
            }

            const submittedRoomNumber = (formData.get('room_number') || '').toString().trim().toLowerCase();
            if (submittedRoomNumber) {
                existingRoomNumbers.add(submittedRoomNumber);
            }

            showNotification(`Room ${editingRoomId ? 'updated' : 'saved'} successfully.`, 'success');
            closeModal();
            fetchRooms();
        } catch (error) {
            console.error(error);
            showNotification(error.message || 'Could not process room right now.', 'error');
        }
    };

    const deleteRoom = async (roomId) => {
        try {
            const payload = new URLSearchParams();
            payload.append(csrfTokenName, csrfHash);

            const response = await fetch(`${baseUrl}/rooms/${roomId}/delete`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                },
                body: payload.toString(),
            });

            const result = await response.json();
            refreshCsrfHash(result?.csrf_hash);
            if (!result.success) {
                throw new Error(result.message || 'Unable to delete room');
            }

            showNotification('Room deleted successfully.', 'success');
            fetchRooms();
        } catch (error) {
            console.error(error);
            showNotification(error.message || 'Could not delete room.', 'error');
        }
    };

    if (roomsTableBody) {
        fetchRooms();
        roomsTableBody.addEventListener('click', handleTableClick);
    }

    if (addRoomBtn) {
        addRoomBtn.addEventListener('click', () => openModal());
    }

    if (saveRoomBtn) {
        saveRoomBtn.addEventListener('click', submitRoom);
    }

    if (bedCapacityInput && bedNamesContainer) {
        const handleCapacityChange = () => {
            syncBedNameInputsFromCapacity();
        };

        bedCapacityInput.addEventListener('input', handleCapacityChange);
        bedCapacityInput.addEventListener('change', handleCapacityChange);
    }

    if (addRoomModal) {
        addRoomModal.addEventListener('click', handleModalClick);
    }

    if (assignRoomModal) {
        assignRoomModal.addEventListener('click', (event) => {
            if (event.target === assignRoomModal || event.target.getAttribute('data-dismiss') === 'modal') {
                closeAssignModal();
            }
        });
    }

    if (saveAssignRoomBtn) {
        saveAssignRoomBtn.addEventListener('click', submitAssignRoom);
    }

    applySelectedRoomTypeMetadata();
})();
