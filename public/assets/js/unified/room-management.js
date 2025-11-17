(function () {
    const roomsTableBody = document.getElementById('roomsTableBody');
    const addRoomBtn = document.getElementById('addRoomBtn');
    const addRoomModal = document.getElementById('addRoomModal');
    const saveRoomBtn = document.getElementById('saveRoomBtn');

    const fetchRooms = async () => {
        try {
            const response = await fetch(`${document.querySelector('meta[name="base-url"]').content}/rooms/api`, {
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

    const submitRoom = async () => {
        const form = document.getElementById('addRoomForm');
        const formData = new FormData(form);
        const payload = {};
        formData.forEach((value, key) => {
            payload[key] = value;
        });

        try {
            const response = await fetch(`${document.querySelector('meta[name="base-url"]').content}/rooms/create`, {
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
})();
