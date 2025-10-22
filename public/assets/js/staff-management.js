(function(){
  // Staff Management JavaScript Module
  // Configuration from meta tags
  const getBaseUrl = () => document.querySelector('meta[name="base-url"]')?.content || '';
  const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
  const getUserRole = () => document.querySelector('meta[name="user-role"]')?.content || 'admin';

  // API endpoints
  const endpoints = {
    staffApi: getBaseUrl() + 'admin/staff-management/api',
    staffCreate: getBaseUrl() + 'admin/staff-management/create',
    staffUpdate: getBaseUrl() + 'admin/staff-management/update',
    staffGet: getBaseUrl() + 'admin/staff-management/staff',
    staffDelete: getBaseUrl() + 'admin/staff-management/delete'
  };

  // DOM elements
  const elements = {
    addStaffBtn: document.getElementById('addStaffBtn'),
    addStaffModal: document.getElementById('addStaffModal'),
    addStaffForm: document.getElementById('addStaffForm'),
    closeAddStaffModal: document.getElementById('closeAddStaffModal'),
    cancelAddStaffBtn: document.getElementById('cancelAddStaffBtn'),
    saveStaffBtn: document.getElementById('saveStaffBtn'),
    staffTableBody: document.getElementById('staffTableBody'),
    designationSelect: document.getElementById('designation')
  };

  // Staff Management Module
  const StaffManager = {
    // Initialize the module
    init() {
      this.bindEvents();
      this.loadStaffTable();
    },

    // Bind event listeners
    bindEvents() {
      // Add staff button
      if (elements.addStaffBtn) {
        elements.addStaffBtn.addEventListener('click', () => this.openAddStaffModal());
      }

      // Modal close buttons
      if (elements.closeAddStaffModal) {
        elements.closeAddStaffModal.addEventListener('click', () => this.closeAddStaffModal());
      }
      if (elements.cancelAddStaffBtn) {
        elements.cancelAddStaffBtn.addEventListener('click', () => this.closeAddStaffModal());
      }

      // Save staff button
      if (elements.saveStaffBtn) {
        elements.saveStaffBtn.addEventListener('click', () => this.saveStaff());
      }

      // Form submission
      if (elements.addStaffForm) {
        elements.addStaffForm.addEventListener('submit', (e) => {
          e.preventDefault();
          this.saveStaff();
        });
      }

      // Modal background click to close
      if (elements.addStaffModal) {
        elements.addStaffModal.addEventListener('click', (e) => {
          if (e.target === elements.addStaffModal) {
            this.closeAddStaffModal();
          }
        });
      }

      // Escape key to close modal
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && elements.addStaffModal?.classList.contains('active')) {
          this.closeAddStaffModal();
        }
      });
    },

    // Load staff table data
    async loadStaffTable() {
      if (!elements.staffTableBody) return;

      try {
        elements.staffTableBody.innerHTML = '<tr><td colspan="8" class="text-center">Loading staff...</td></tr>';
        
        const response = await fetch(endpoints.staffApi, {
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`);
        }

        const staff = await response.json();
        this.renderStaffTable(staff);
      } catch (error) {
        console.error('Failed to load staff:', error);
        elements.staffTableBody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Failed to load staff members</td></tr>';
      }
    },

    // Render staff table
    renderStaffTable(staff) {
      if (!Array.isArray(staff) || staff.length === 0) {
        elements.staffTableBody.innerHTML = '<tr><td colspan="8" class="text-center">No staff members found</td></tr>';
        return;
      }

      const userRole = getUserRole();
      const canViewDepartment = userRole === 'admin' || userRole === 'doctor' || userRole === 'nurse';

      elements.staffTableBody.innerHTML = staff.map(member => {
        const staffId = member.staff_id || member.id;
        const fullName = member.full_name || `${member.first_name || ''} ${member.last_name || ''}`.trim();
        const role = (member.designation || member.role || '').toLowerCase();
        const roleDisplay = role.replace('_', ' ');
        
        return `
          <tr>
            <td>${this.escapeHtml(member.employee_id || 'N/A')}</td>
            <td>
              <div class="staff-info">
                <div class="staff-name">${this.escapeHtml(fullName)}</div>
                <div class="staff-id">ID: ${this.escapeHtml(staffId)}</div>
              </div>
            </td>
            <td>
              <span class="role-badge ${role}">
                ${this.escapeHtml(this.capitalizeFirst(roleDisplay))}
              </span>
            </td>
            ${canViewDepartment ? `<td>${this.escapeHtml(member.department || 'N/A')}</td>` : ''}
            <td>${this.escapeHtml(member.contact_no || 'N/A')}</td>
            <td>${this.escapeHtml(member.email || 'N/A')}</td>
            <td>${this.escapeHtml(member.date_joined || 'N/A')}</td>
            <td>
              <div class="action-buttons">
                <button class="btn btn-info btn-sm" onclick="viewStaff(${staffId})" title="View">
                  <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-warning btn-sm" onclick="editStaff(${staffId})" title="Edit">
                  <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-danger btn-sm" onclick="deleteStaff(${staffId})" title="Delete">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        `;
      }).join('');
    },

    // Open add staff modal
    openAddStaffModal() {
      if (elements.addStaffModal) {
        elements.addStaffModal.classList.add('active');
        elements.addStaffModal.setAttribute('aria-hidden', 'false');
        
        // Reset form
        if (elements.addStaffForm) {
          elements.addStaffForm.reset();
          // Set default date joined to today
          const dateJoinedInput = document.getElementById('date_joined');
          if (dateJoinedInput) {
            dateJoinedInput.value = new Date().toISOString().split('T')[0];
          }
        }
      }
    },

    // Close add staff modal
    closeAddStaffModal() {
      if (elements.addStaffModal) {
        elements.addStaffModal.classList.remove('active');
        elements.addStaffModal.setAttribute('aria-hidden', 'true');
        
        // Reset form
        if (elements.addStaffForm) {
          elements.addStaffForm.reset();
        }
      }
    },

    // Save staff member
    async saveStaff() {
      if (!elements.addStaffForm) return;

      try {
        // Disable save button
        if (elements.saveStaffBtn) {
          elements.saveStaffBtn.disabled = true;
          elements.saveStaffBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        }

        const formData = new FormData(elements.addStaffForm);
        formData.append('csrf_token', getCsrfToken());

        const response = await fetch(endpoints.staffCreate, {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: formData
        });

        const result = await response.json();

        if (response.ok && result.status === 'success') {
          this.showNotification('Staff member added successfully!', 'success');
          this.closeAddStaffModal();
          this.loadStaffTable();
        } else {
          const errorMessage = result.message || 'Failed to add staff member';
          const errors = result.errors ? Object.values(result.errors).join('\n') : '';
          this.showNotification(errors ? `${errorMessage}:\n${errors}` : errorMessage, 'error');
        }
      } catch (error) {
        console.error('Error saving staff:', error);
        this.showNotification('An error occurred while saving staff member', 'error');
      } finally {
        // Re-enable save button
        if (elements.saveStaffBtn) {
          elements.saveStaffBtn.disabled = false;
          elements.saveStaffBtn.innerHTML = '<i class="fas fa-save"></i> Add Staff Member';
        }
      }
    },

    // Show notification
    showNotification(message, type = 'info') {
      // Simple alert for now - can be enhanced with custom notification system
      if (type === 'error') {
        alert('Error: ' + message);
      } else {
        alert(message);
      }
    },

    // Utility functions
    escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    },

    capitalizeFirst(str) {
      return str.charAt(0).toUpperCase() + str.slice(1);
    }
  };

  // Global functions for inline onclick handlers
  window.viewStaff = function(staffId) {
    console.log('View staff:', staffId);
    // TODO: Implement view staff modal
    alert('View staff functionality will be implemented soon');
  };

  window.editStaff = function(staffId) {
    console.log('Edit staff:', staffId);
    // TODO: Implement edit staff modal
    alert('Edit staff functionality will be implemented soon');
  };

  window.deleteStaff = function(staffId) {
    if (confirm('Are you sure you want to delete this staff member?')) {
      console.log('Delete staff:', staffId);
      // TODO: Implement delete staff functionality
      alert('Delete staff functionality will be implemented soon');
    }
  };

  // Initialize when DOM is loaded
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => StaffManager.init());
  } else {
    StaffManager.init();
  }

})();