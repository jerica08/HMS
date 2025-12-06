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
                        <label class="form-label" for="location">Location*</label>
                        <input type="text" id="location" name="location" class="form-input" required placeholder="Enter location...">
                        <small id="err_location" style="color:#dc2626"></small>
                    </div>
                    <div>
                        <label class="form-label" for="serial_number">
                            <i class="fas fa-hashtag"></i> Serial Number
                            <small style="color: #666;">(Optional)</small>
                        </label>
                        <input type="text" id="serial_number" name="serial_number" class="form-input" placeholder="Enter serial number...">
                        <small id="err_serial_number" style="color:#dc2626"></small>
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
function handleFormSubmit(e) {
    // Prevent default form submission
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    // Prevent double submission
    if (isSubmitting) {
        console.log('Form submission already in progress');
        return false;
    }
    
    const form = document.getElementById('addResourceForm');
    if (!form) return false;
    
    const formData = new FormData(form);
    const equipmentName = formData.get('equipment_name');
    const category = formData.get('category');
    const quantity = formData.get('quantity');
    const status = formData.get('status');
    const location = formData.get('location');
    
    // Validate required fields
    if (!equipmentName || !category || !quantity || !status || !location) {
        showNotification('Please fill in all required fields', 'error');
        return false;
    }
    
    // Validate medications require batch number and expiry date
    if (category === 'Medications') {
        const batchNumber = formData.get('batch_number');
        const expiryDate = formData.get('expiry_date');
        if (!batchNumber || !expiryDate) {
            showNotification('Batch number and expiry date are required for medications', 'error');
            return false;
        }
    }
    
    isSubmitting = true;
    const submitBtn = document.getElementById('saveResourceBtn');
    
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    }
    
    fetch('<?= base_url('admin/resource-management/create') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(async response => {
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Non-JSON response:', text);
            throw new Error('Server returned non-JSON response');
        }
        
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || 'Network response was not ok');
        }
        
        return response.json();
    })
    .then(data => {
        console.log('Full response data:', JSON.stringify(data, null, 2));
        
        // Check for success - the response should have 'success' property set to true
        if (data && data.success === true) {
            console.log('Success detected - showing success notification');
            showNotification(data.message || 'Resource added successfully!', 'success');
            closeResourceModalUnique();
            // Reload the page to refresh the resource list
            setTimeout(function() {
                window.location.reload();
            }, 1000);
        } else {
            // Show error message
            console.log('Failure detected - success is:', data?.success);
            let errorMsg = 'Failed to add resource';
            if (data && data.message) {
                errorMsg = data.message;
            } else if (data && data.errors) {
                errorMsg = 'Validation errors occurred';
                displayResourceFormErrors(data.errors);
            } else {
                console.error('Unexpected response format:', data);
                errorMsg = 'Unexpected response from server. Resource may have been created. Please refresh the page.';
            }
            showNotification(errorMsg, 'error');
            resetSubmitButton();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while adding the resource: ' + error.message, 'error');
        resetSubmitButton();
    })
    .finally(() => {
        isSubmitting = false;
    });
    
    return false;
}

// Initialize with delay to override external JS
setTimeout(function() {
    const form = document.getElementById('addResourceForm');
    if (form) {
        // Remove any existing submit listeners to prevent duplicates
        form.onsubmit = function(e) {
            e.preventDefault();
            e.stopPropagation();
            return handleFormSubmit(e);
        };
    }
    
    // Set up click handlers
    const submitBtn = document.getElementById('saveResourceBtn');
    if (submitBtn) {
        // Remove existing listeners
        submitBtn.onclick = null;
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            handleFormSubmit(e);
        }, { once: false });
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
    
    console.log('Resource modal functions initialized successfully');
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
