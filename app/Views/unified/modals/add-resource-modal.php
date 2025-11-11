<!-- Add Resource Modal -->
<style>
#addResourceModal {
    display: none;
}

#addResourceModal.active {
    display: flex;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

#addResourceModal .hms-modal {
    background: white;
    border-radius: 8px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}
</style>

<div id="addResourceModal" aria-hidden="true">
    <div class="hms-modal" role="dialog" aria-modal="true" aria-labelledby="addResourceTitle">
        <div class="hms-modal-header">
            <div class="hms-modal-title" id="addResourceTitle">
                <i class="fas fa-boxes" style="color:#4f46e5"></i>
                Add New Resource
            </div>
            <button type="button" class="btn btn-secondary btn-small" id="closeModalBtn" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="addResourceForm">
            <?= csrf_field() ?>
            <div class="hms-modal-body">
                <div class="form-grid">
                    <div>
                        <label class="form-label" for="equipment_name">Equipment Name*</label>
                        <input type="text" id="equipment_name" name="equipment_name" class="form-input" required placeholder="Enter equipment name...">
                        <small id="err_equipment_name" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="category">Category*</label>
                        <select id="category" name="category" class="form-select" required>
                            <option value="">Select Category...</option>
                            <option value="Medical Equipment">Medical Equipment</option>
                            <option value="Medical Supplies">Medical Supplies</option>
                            <option value="Diagnostic Equipment">Diagnostic Equipment</option>
                            <option value="Lab Equipment">Lab Equipment</option>
                            <option value="Pharmacy Equipment">Pharmacy Equipment</option>
                            <option value="Medications">Medications</option>
                            <option value="Office Equipment">Office Equipment</option>
                            <option value="IT Equipment">IT Equipment</option>
                            <option value="Furniture">Furniture</option>
                            <option value="Vehicles">Vehicles</option>
                            <option value="Other">Other</option>
                        </select>
                        <small id="err_category" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="quantity">Quantity*</label>
                        <input type="number" id="quantity" name="quantity" class="form-input" required min="1" placeholder="Enter quantity...">
                        <small id="err_quantity" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="status">Status*</label>
                        <select id="status" name="status" class="form-select" required>
                            <option value="">Select Status...</option>
                            <option value="Available">Available</option>
                            <option value="In Use">In Use</option>
                            <option value="Maintenance">Maintenance</option>
                            <option value="Out of Order">Out of Order</option>
                        </select>
                        <small id="err_status" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="location">Location*</label>
                        <input type="text" id="location" name="location" class="form-input" required placeholder="Enter location...">
                        <small id="err_location" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="date_acquired">Date Acquired*</label>
                        <input type="date" id="date_acquired" name="date_acquired" class="form-input" required max="<?= date('Y-m-d') ?>">
                        <small id="err_date_acquired" style="color:#dc2626"></small>
                    </div>
                </div>
                
                <!-- Second row for additional fields -->   
                <div class="form-grid" style="margin-top: 1rem;">
                    <div>
                        <label class="form-label" for="supplier">Supplier</label>
                        <input type="text" id="supplier" name="supplier" class="form-input" placeholder="Enter supplier name...">
                        <small id="err_supplier" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="maintenance_schedule">Next Maintenance Date</label>
                        <input type="date" id="maintenance_schedule" name="maintenance_schedule" class="form-input" min="<?= date('Y-m-d') ?>">
                        <small id="err_maintenance_schedule" style="color:#dc2626"></small>
                    </div>
                </div>
                
                <div style="margin-top: 1rem;">
                    <label class="form-label" for="remarks">Remarks</label>
                    <textarea id="remarks" name="remarks" class="form-input" rows="3" placeholder="Additional notes or remarks..." style="width: 100%;"></textarea>
                    <small id="err_remarks" style="color:#dc2626"></small>
                </div>
            </div>
            <div class="hms-modal-actions">
                <button type="button" class="btn btn-secondary" id="cancelModalBtn">Cancel</button>
                <button type="button" id="saveResourceBtn" class="btn btn-primary">
                    <i class="fas fa-save"></i> Add Resource
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let isSubmitting = false;

// UNIQUE function names to avoid conflicts with external JS
function openResourceModalUnique() {
    resetFormUnique();
    document.getElementById('addResourceModal').classList.add('active');
}

function closeResourceModalUnique() {
    document.getElementById('addResourceModal').classList.remove('active');
    resetFormUnique();
}

// Override the external functions with our working versions
window.openAddResourceModal = function() {
    openResourceModalUnique();
};

window.closeAddResourceModal = function() {
    closeResourceModalUnique();
};

function resetFormUnique() {
    const form = document.getElementById('addResourceForm');
    if (form) {
        form.reset();
    }
    clearResourceFormErrors();
    resetSubmitButton();
}

function clearResourceFormErrors() {
    const errorElements = document.querySelectorAll('[id^="err_"]');
    errorElements.forEach(element => {
        element.textContent = '';
    });
}

function resetSubmitButton() {
    const submitBtn = document.getElementById('saveResourceBtn');
    if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Add Resource';
    }
    isSubmitting = false;
}

// Form submission
function handleFormSubmit() {
    if (isSubmitting) return;
    
    const form = document.getElementById('addResourceForm');
    if (!form) return;
    
    const formData = new FormData(form);
    const equipmentName = formData.get('equipment_name');
    const category = formData.get('category');
    const status = formData.get('status');
    const location = formData.get('location');
    
    if (!equipmentName || !category || !status || !location) {
        showNotification('Please fill in all required fields', 'error');
        return;
    }
    
    isSubmitting = true;
    const submitBtn = document.getElementById('saveResourceBtn');
    
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    }
    
    fetch('<?= base_url('admin/resources/add') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showNotification('Resource added successfully!', 'success');
            closeResourceModalUnique();
            if (typeof loadResources === 'function') {
                loadResources();
            }
        } else {
            if (data.errors) {
                displayResourceFormErrors(data.errors);
            } else {
                showNotification(data.message || 'Failed to add resource', 'error');
            }
            resetSubmitButton();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while adding the resource', 'error');
        resetSubmitButton();
    });
}

// Initialize with delay to override external JS
setTimeout(function() {
    // Set up click handlers
    const submitBtn = document.getElementById('saveResourceBtn');
    if (submitBtn) {
        submitBtn.onclick = handleFormSubmit;
    }
    
    const closeBtn = document.getElementById('closeModalBtn');
    if (closeBtn) {
        closeBtn.onclick = closeResourceModalUnique;
    }
    
    const cancelBtn = document.getElementById('cancelModalBtn');
    if (cancelBtn) {
        cancelBtn.onclick = closeResourceModalUnique;
    }
    
    // Override external functions with our working versions
    window.openAddResourceModal = function() {
        openResourceModalUnique();
    };
    
    window.closeAddResourceModal = function() {
        closeResourceModalUnique();
    };
    
    console.log('Resource modal functions overridden successfully');
}, 100);

// Helper functions
function displayResourceFormErrors(errors) {
    clearResourceFormErrors();
    
    for (const [field, message] of Object.entries(errors)) {
        const errorElement = document.getElementById('err_' + field);
        if (errorElement) {
            errorElement.textContent = message;
        }
    }
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 4px;
        color: white;
        z-index: 10000;
        font-weight: 500;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    `;
    
    if (type === 'success') {
        notification.style.backgroundColor = '#10b981';
    } else if (type === 'error') {
        notification.style.backgroundColor = '#ef4444';
    }
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}
</script>
