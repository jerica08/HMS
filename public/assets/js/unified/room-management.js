 (function () {
    const metaBaseUrl = document.querySelector('meta[name="base-url"]');
    const baseUrl = metaBaseUrl ? metaBaseUrl.content : '';
    const roomsTableBody = document.getElementById('roomsTableBody');
    const addRoomBtn = document.getElementById('addRoomBtn');
    const addRoomModal = document.getElementById('addRoomModal');
    const saveRoomBtn = document.getElementById('saveRoomBtn');
    const roomTypeSelect = document.getElementById('modal_room_type');
    const floorInput = document.getElementById('modal_floor');
    const roomNumberInput = document.getElementById('modal_room_number');
    const rateRangeInput = document.getElementById('modal_rate_range');
    const roomNotesInput = document.getElementById('modal_notes');
    const roomTypeMetadata = window.roomTypeMetadata || {};

    const fetchRooms = async () => {
        try {
            const response = await fetch(`${baseUrl}/rooms/api`, {
                headers: { 'Accept': 'application/json' },
            });
            const payload = await response.json();
            if (!payload?.data) throw new Error('Invalid API response');

            renderRooms(payload.data);
        } catch (error) {
            console.error('Failed to load rooms', error);
            roomsTableBody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align:center; color:#b91c1c;">
                        <i class="fas fa-exclamation-triangle"></i> Could not load rooms.
                    </td>
                </tr>
            `;
        }
    };

    const renderRooms = (rooms) => {
        if (!rooms.length) {
            roomsTableBody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align:center;">
                        <i class="fas fa-bed" aria-hidden="true"></i> No rooms yet.
                    </td>
                </tr>
            `;
            return;
        }

        roomsTableBody.innerHTML = rooms.map((room) => `
            <tr>
                <td>
                    ${escapeHtml(room.room_number)}
                    ${room.room_name ? `<small style="display:block; color:#6b7280;">${escapeHtml(room.room_name)}</small>` : ''}
                </td>
                <td>${escapeHtml(room.type_name || '—')}</td>
                <td>${escapeHtml(room.department_name || '—')}</td>
                <td>${room.bed_capacity ?? '—'}</td>
                <td>${capitalize(room.status || 'unknown')}</td>
                <td>${formatCurrency(room.daily_rate)}</td>
                <td>
                    <button class="btn btn-sm btn-outline" disabled>
                        <i class="fas fa-pen"></i>
                    </button>
                </td>
            </tr>
        `).join('');
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

    const formatCurrency = (value) => {
        if (value === null || value === undefined || value === '') {
            return '—';
        }
        return parseFloat(value).toFixed(2);
    };

    const openModal = () => {
        addRoomModal.style.display = 'block';
        addRoomModal.setAttribute('aria-hidden', 'false');
        applySelectedRoomTypeMetadata();
    };

    const closeModal = () => {
        addRoomModal.style.display = 'none';
        addRoomModal.setAttribute('aria-hidden', 'true');
        document.getElementById('addRoomForm').reset();
    };

    const handleModalClick = (event) => {
        if (event.target === addRoomModal || event.target.getAttribute('data-dismiss') === 'modal') {
            closeModal();
        }
    };

    const applySelectedRoomTypeMetadata = () => {
        if (!roomTypeSelect) {
            return;
        }

        const metadata = roomTypeMetadata[roomTypeSelect.value];
        if (floorInput) {
            floorInput.value = metadata?.floor_label ?? '';
        }

        if (roomNumberInput) {
            roomNumberInput.value = metadata?.room_number_template ?? '';
        }

        if (rateRangeInput) {
            rateRangeInput.value = metadata?.rate_range ?? '';
        }

        if (roomNotesInput) {
            roomNotesInput.value = metadata?.notes ?? '';
        }
    };

    const handleRoomTypeChange = () => {
        if (!roomTypeSelect) {
            return;
        }

        applySelectedRoomTypeMetadata();
    };

    const submitRoom = async () => {
        const form = document.getElementById('addRoomForm');
        const formData = new FormData(form);
        const payload = {};
        formData.forEach((value, key) => {
            payload[key] = value;
        });

        try {
            const response = await fetch(`${baseUrl}/rooms/create`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payload),
            });
            const result = await response.json();
            if (!result.success) {
                throw new Error(result.message || 'Failed to create room');
            }

            closeModal();
            fetchRooms();
        } catch (error) {
            console.error(error);
            alert(error.message || 'Could not add room right now.');
        }
    };

    if (roomsTableBody) {
        fetchRooms();
    }

    if (addRoomBtn) {
        addRoomBtn.addEventListener('click', openModal);
    }

    if (saveRoomBtn) {
        saveRoomBtn.addEventListener('click', submitRoom);
    }

    if (addRoomModal) {
        addRoomModal.addEventListener('click', handleModalClick);
    }

    if (roomTypeSelect) {
        roomTypeSelect.addEventListener('change', handleRoomTypeChange);
        applySelectedRoomTypeMetadata();
    }
})();
