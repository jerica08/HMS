// Main Appointments Management Controller
// Handles appointment list, view switching, and core functionality

const AppointmentManager = {
    // Global variables
    currentView: 'today',
    currentDate: new Date(),
    
    // DOM elements
    todayViewBtn: null,
    weekViewBtn: null,
    monthViewBtn: null,
    dateSelector: null,
    refreshBtn: null,
    scheduleTitle: null,

    init() {
        // Get DOM elements
        this.todayViewBtn = document.getElementById('todayView');
        this.weekViewBtn = document.getElementById('weekView');
        this.monthViewBtn = document.getElementById('monthView');
        this.dateSelector = document.getElementById('dateSelector');
        this.refreshBtn = document.getElementById('refreshBtn');
        this.scheduleTitle = document.getElementById('scheduleTitle');

        // Initialize
        this.setupEventListeners();
        this.initializeView();
        this.startAutoRefresh();
    },

    setupEventListeners() {
        // View switching
        this.todayViewBtn?.addEventListener('click', () => this.switchView('today'));
        this.weekViewBtn?.addEventListener('click', () => this.switchView('week'));
        this.monthViewBtn?.addEventListener('click', () => this.switchView('month'));
        
        // Date selector
        this.dateSelector?.addEventListener('change', () => {
            this.currentDate = new Date(this.dateSelector.value);
            this.refreshAppointments();
        });
        
        // Refresh button
        this.refreshBtn?.addEventListener('click', () => this.refreshAppointments());
    },

    initializeView() {
        // Set current date
        if (this.dateSelector) {
            this.dateSelector.value = formatDate(this.currentDate);
        }
        
        // Update title
        this.updateScheduleTitle();
    },

    startAutoRefresh() {
        // Auto-refresh every 5 minutes
        setInterval(() => this.refreshAppointments(), 300000);
        
        // Update current time every minute
        setInterval(updateCurrentTime, 60000);
    },

    switchView(view) {
        this.currentView = view;
        
        // Update button states
        document.querySelectorAll('.btn-group .btn').forEach(btn => {
            btn.classList.remove('btn-primary', 'active');
            btn.classList.add('btn-secondary');
        });
        
        const activeBtn = document.getElementById(view + 'View');
        if (activeBtn) {
            activeBtn.classList.remove('btn-secondary');
            activeBtn.classList.add('btn-primary', 'active');
        }
        
        // Update title and refresh data
        this.updateScheduleTitle();
        this.refreshAppointments();
    },

    updateScheduleTitle() {
        if (!this.scheduleTitle) return;
        
        let title = '';
        switch(this.currentView) {
            case 'today':
                title = "Today's Schedule - " + formatDateDisplay(this.currentDate);
                break;
            case 'week':
                title = "Weekly Schedule - " + getWeekRange(this.currentDate);
                break;
            case 'month':
                title = "Monthly Schedule - " + this.currentDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
                break;
        }
        this.scheduleTitle.textContent = title;
    },

    refreshAppointments() {
        const tbody = document.getElementById('appointmentsTableBody');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 2rem;"><i class="fas fa-spinner fa-spin"></i> Loading appointments...</td></tr>';
        }
        
        // AJAX call to fetch appointments
        fetch(`${getBaseUrl()}doctor/appointment-data?view=${this.currentView}&date=${formatDate(this.currentDate)}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            this.updateAppointmentsTable(data.appointments || []);
            this.updateStats(data.stats || {});
        })
        .catch(error => {
            console.error('Error:', error);
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 2rem; color: #ef4444;"><i class="fas fa-exclamation-triangle"></i> Error loading appointments</td></tr>';
            }
        });
    },

    updateAppointmentsTable(appointments) {
        const tbody = document.getElementById('appointmentsTableBody');
        if (!tbody) return;
        
        if (appointments.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem; color: #6b7280;">
                        <i class="fas fa-calendar-times" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                        <p>No appointments found for the selected ${this.currentView}.</p>
                        <button class="btn btn-primary" onclick="document.getElementById('scheduleAppointmentBtn').click()">
                            <i class="fas fa-plus"></i> Schedule New Appointment
                        </button>
                    </td>
                </tr>
            `;
            return;
        }
        
        tbody.innerHTML = appointments.map(appointment => {
            const status = appointment.status || 'scheduled';
            const badgeClass = getBadgeClass(status);
            
            return `
                <tr>
                    <td>
                        <strong>${formatTime(appointment.appointment_time)}</strong>
                        ${appointment.duration ? `<br><small>${appointment.duration} min</small>` : ''}
                    </td>
                    <td>
                        <div>
                            <strong>${appointment.patient_first_name || ''} ${appointment.patient_last_name || ''}</strong><br>
                            <small>${appointment.patient_id || 'N/A'} | Age: ${appointment.patient_age || 'N/A'}</small>
                        </div>
                    </td>
                    <td>${appointment.appointment_type || 'N/A'}</td>
                    <td>${appointment.reason || 'General consultation'}</td>
                    <td>${appointment.duration || '30'} min</td>
                    <td><span class="badge ${badgeClass}">${status.charAt(0).toUpperCase() + status.slice(1)}</span></td>
                    <td>
                        <div style="display: flex; gap: 0.25rem; flex-wrap: wrap;">
                            <button class="btn btn-primary" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" onclick="viewAppointment(${appointment.appointment_id})">
                                <i class="fas fa-eye"></i> View
                            </button>
                            ${status !== 'completed' ? `
                                <button class="btn btn-success" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" onclick="markCompleted(${appointment.appointment_id})">
                                    <i class="fas fa-check"></i> Complete
                                </button>
                            ` : ''}
                            <button class="btn btn-secondary" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" onclick="rescheduleAppointment(${appointment.appointment_id})">
                                <i class="fas fa-calendar-alt"></i> Reschedule
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    },

    updateStats(stats) {
        // Update dashboard cards with new stats
        if (stats.today) {
            const todayCard = document.querySelector('.overview-card:nth-child(1)');
            if (todayCard) {
                const metrics = todayCard.querySelectorAll('.metric-value');
                if (metrics[0]) metrics[0].textContent = stats.today.total || 0;
                if (metrics[1]) metrics[1].textContent = stats.today.completed || 0;
                if (metrics[2]) metrics[2].textContent = stats.today.pending || 0;
            }
        }
        
        if (stats.week) {
            const weekCard = document.querySelector('.overview-card:nth-child(2)');
            if (weekCard) {
                const metrics = weekCard.querySelectorAll('.metric-value');
                if (metrics[0]) metrics[0].textContent = stats.week.total || 0;
                if (metrics[1]) metrics[1].textContent = stats.week.cancelled || 0;
                if (metrics[2]) metrics[2].textContent = stats.week.no_shows || 0;
            }
        }
    }
};

// Global appointment management functions
function markCompleted(appointmentId) {
    if (confirm('Mark this appointment as completed?')) {
        updateAppointmentStatus(appointmentId, 'completed');
    }
}

function rescheduleAppointment(appointmentId) {
    window.location.href = `${getBaseUrl()}doctor/appointment/reschedule/${appointmentId}`;
}

function updateAppointmentStatus(appointmentId, status) {
    fetch(`${getBaseUrl()}doctor/update-appointment-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            appointment_id: appointmentId,
            status: status,
            csrf_token: getCsrfToken()
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            AppointmentManager.refreshAppointments();
            showNotification('Appointment status updated successfully', 'success');
        } else {
            showNotification('Error updating appointment status', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    AppointmentManager.init();
});

// Export to global scope
window.AppointmentManager = AppointmentManager;