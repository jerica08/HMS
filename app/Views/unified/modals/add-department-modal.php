<div id="addDepartmentModal" class="hms-modal-overlay" hidden>
    <div class="hms-modal" role="dialog" aria-modal="true" aria-labelledby="addDepartmentTitle">
        <div class="hms-modal-header">
            <div class="hms-modal-title" id="addDepartmentTitle"><i class="fas fa-building" style="color:#2563eb"></i> Add Department</div>
            <button type="button" class="btn btn-secondary btn-small" data-modal-close="addDepartmentModal" aria-label="Close"><i class="fas fa-times"></i></button>
        </div>

        <div class="hms-modal-body">
            <form id="addDepartmentForm" class="patient-form" autocomplete="off">
                <div class="form-section">
                    <div class="section-header">
                        <div>
                            <h4>Department Details</h4>
                        </div>
                    </div>
                    <div class="form-grid">
<<<<<<< HEAD
=======
                        <div>
                            <label class="form-label" for="department_category">Department Category*</label>
                            <select id="department_category" name="department_category" class="form-select" required onchange="toggleDepartmentFields()">
                                <option value="">Select category...</option>
                                <option value="medical">Medical Department</option>
                                <option value="non_medical">Non-Medical Department</option>
                            </select>
                        </div>
>>>>>>> 03d4e70 (COMMITenter the commit message for your changes. Lines starting)
                        <div class="full">
                            <label class="form-label" for="department_name">Department Name*</label>
                            <input type="text" id="department_name" name="name" class="form-input" required maxlength="150">
                        </div>
                        <div>
                            <label class="form-label" for="department_code">Department Code</label>
                            <input type="text" id="department_code" name="code" class="form-input" maxlength="50" placeholder="Optional">
<<<<<<< HEAD
                        </div>
=======
                        </div>                       
>>>>>>> 03d4e70 (COMMITenter the commit message for your changes. Lines starting)
                        <div>
                            <label class="form-label" for="department_floor">Floor</label>
                            <input type="text" id="department_floor" name="floor" class="form-input" placeholder="e.g., 3F" maxlength="100">
                        </div>
<<<<<<< HEAD
                        <div>
                            <label class="form-label" for="department_type">Department Type</label>
                            <select id="department_type" name="department_type" class="form-select" required>
                                <option value="">Select type...</option>
                                <option value="Clinical">Clinical</option>
                                <option value="Administrative">Administrative</option>
                                <option value="Emergency">Emergency</option>
                                <option value="Diagnostic">Diagnostic</option>
                                <option value="Support">Support</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="department_head">Head of Department</label>
                            <select id="department_head" name="department_head" class="form-select">
                                <option value="">Select doctor...</option>
=======
                       
                        <div id="non_medical_function_field" style="display: none;">
                            <label class="form-label" for="non_medical_function">Function</label>
                            <select id="non_medical_function" name="non_medical_function" class="form-select">
                                <option value="">Select function...</option>
                                <option value="Administrative">Administrative</option>
                                <option value="Support Services">Support Services</option>
                                <option value="Management">Management</option>
                                <option value="Technical">Technical</option>
                                <option value="Operations">Operations</option>
                            </select>
                        </div>                 
                        <div>
                            <label class="form-label" for="department_head">Head of Department</label>
                            <select id="department_head" name="department_head" class="form-select">
                                <option value="">Select staff...</option>
>>>>>>> 03d4e70 (COMMITenter the commit message for your changes. Lines starting)
                                <?php foreach (($departmentHeads ?? []) as $head): ?>
                                    <option value="<?= esc($head['staff_id']) ?>">
                                        <?= esc($head['full_name']) ?>
                                        <?= isset($head['position']) && $head['position'] ? ' - ' . esc($head['position']) : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
<<<<<<< HEAD
=======
                          <div>
                            <label class="form-label" for="department_contact">Contact Number</label>
                            <input type="text" id="department_contact" name="contact_number" class="form-input" placeholder="09XXXXXXXXX">
                            <small id="err_contact_number" class="form-error"></small>
                        </div>
>>>>>>> 03d4e70 (COMMITenter the commit message for your changes. Lines starting)
                        <div>
                            <label class="form-label" for="department_status">Status</label>
                            <select id="department_status" name="status" class="form-select">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="full">
                            <label class="form-label" for="department_description">Description</label>
                            <textarea id="department_description" name="description" class="form-input" rows="3" placeholder="Department details"></textarea>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="hms-modal-actions">
            <button type="button" class="btn btn-secondary" data-modal-close="addDepartmentModal">Cancel</button>
            <button type="submit" class="btn btn-success" form="addDepartmentForm" id="saveDepartmentBtn"><i class="fas fa-save"></i> Save Department</button>
        </div>
    </div>
</div>
<<<<<<< HEAD
=======

<script>
function toggleDepartmentFields() {
    const category = document.getElementById('department_category').value;
    const nonMedicalField = document.getElementById('non_medical_function_field');
    const nonMedicalFunction = document.getElementById('non_medical_function');
    
    // Hide non-medical field first
    nonMedicalField.style.display = 'none';
    
    // Show non-medical field if selected
    if (category === 'non_medical') {
        nonMedicalField.style.display = 'block';
        nonMedicalFunction.required = true;
    } else {
        nonMedicalFunction.required = false;
        nonMedicalFunction.value = '';
    }
}

// Add contact number validation
document.addEventListener('DOMContentLoaded', function() {
    const contactField = document.getElementById('department_contact');
    if (contactField) {
        contactField.addEventListener('input', function() {
            const value = this.value.trim();
            const errorElement = document.getElementById('err_contact_number');
            
            // Clear previous error
            if (errorElement) {
                errorElement.textContent = '';
            }
            this.classList.remove('is-invalid', 'error');
            
            // Only validate if field has value
            if (!value) return;
            
            // Validate contact number format
            const contactPattern = /^09\d{9}$/;
            if (!contactPattern.test(value)) {
                const errorMessage = value.startsWith('09') 
                    ? 'Contact number must be exactly 11 digits.'
                    : 'Contact number must start with 09.';
                
                if (errorElement) {
                    errorElement.textContent = errorMessage;
                }
                this.classList.add('is-invalid', 'error');
            }
        });
    }
});
</script>
>>>>>>> 03d4e70 (COMMITenter the commit message for your changes. Lines starting)
