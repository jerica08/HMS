<div id="addDepartmentModal" class="hms-modal-overlay" hidden>
    <div class="hms-modal" role="dialog" aria-modal="true" aria-labelledby="addDepartmentTitle">
        <div class="hms-modal-header">
            <div class="hms-modal-title" id="addDepartmentTitle">
                <i class="fas fa-building" style="color:#2563eb"></i>
                Add Department
            </div>
            <button type="button" class="btn btn-secondary btn-small" onclick="window.AddDepartmentModal?.close()" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="hms-modal-body">
            <form id="addDepartmentForm" class="patient-form" autocomplete="off">
                <div class="form-section">
                    <div class="section-header">
                        <div>
                            <h4>Department Details</h4>
                            <p class="section-subtitle">Provide the core information for this department.</p>
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="full">
                            <label class="form-label" for="department_name">Department Name*</label>
                            <input type="text" id="department_name" name="name" class="form-input" required maxlength="150">
                            <small class="form-error" id="err_department_name"></small>
                        </div>
                        <div class="full">
                            <label class="form-label" for="department_description">Description</label>
                            <textarea id="department_description" name="description" class="form-input" rows="3" placeholder="Optional description"></textarea>
                        </div>
                        <div>
                            <label class="form-label" for="department_head">Department Head</label>
                            <select id="department_head" name="department_head" class="form-select">
                                <option value="">Select Head (Optional)</option>
                                <?php foreach (($departmentHeads ?? []) as $head): ?>
                                    <option value="<?= esc($head['staff_id']) ?>">
                                        <?= esc($head['full_name']) ?>
                                        <?= isset($head['position']) && $head['position'] ? ' - ' . esc($head['position']) : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="department_status">Status</label>
                            <select id="department_status" name="status" class="form-select">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="full">
                            <label class="form-label" for="department_specialties">Specialties / Services</label>
                            <select id="department_specialties" name="specialties[]" class="form-select" multiple size="4">
                                <?php foreach (($specialties ?? []) as $spec): ?>
                                    <option value="<?= esc($spec) ?>"><?= esc($spec) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-hint">Hold CTRL (or CMD) to select multiple specialties.</small>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="hms-modal-actions">
            <button type="button" class="btn btn-secondary" onclick="window.AddDepartmentModal?.close()">Cancel</button>
            <button type="submit" class="btn btn-success" form="addDepartmentForm" id="saveDepartmentBtn">
                <i class="fas fa-save"></i> Save Department
            </button>
        </div>
    </div>
</div>
