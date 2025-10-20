<!-- Add Patient Modal -->
<div id="patientModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; padding:2rem; border-radius:8px; max-width:960px; width:98%; margin:auto; position:relative; max-height:90vh; overflow:auto; box-sizing:border-box; -webkit-overflow-scrolling:touch;">
        <div class="hms-modal-header">
            <div class="hms-modal-title">
                <i class="fas fa-user-plus" style="color:#4f46e5"></i>
                <h2 id="patientModalTitle" style="margin:0; font-size:1.25rem;">Add New Patient</h2>
            </div>
        </div>
        <form id="patientForm">
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:1rem; padding-bottom:5rem;">
                <div>
                    <label for="first_name">First Name*</label>
                    <input type="text" id="first_name" name="first_name" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                    <small id="err_first_name" style="color:#dc2626"></small>
                </div>
                <div>
                    <label for="middle_name">Middle Name</label>
                    <input type="text" id="middle_name" name="middle_name" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                </div>
                <div>
                    <label for="last_name">Last Name*</label>
                    <input type="text" id="last_name" name="last_name" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                    <small id="err_last_name" style="color:#dc2626"></small>
                </div>
                <div>
                    <label for="date_of_birth">Date of Birth*</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                    <small id="err_date_of_birth" style="color:#dc2626"></small>
                </div>
                <div>
                    <label for="age">Age</label>
                    <input type="number" id="age" name="age" readonly style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px; background:#f9fafb;">
                </div>
                <div>
                    <label for="gender">Gender*</label>
                    <select id="gender" name="gender" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                        <option value="">Select...</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                    <small id="err_gender" style="color:#dc2626"></small>
                </div>
                <div>
                    <label for="civil_status">Civil Status</label>
                    <select id="civil_status" name="civil_status" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                        <option value="">Select...</option>
                        <option value="single">Single</option>
                        <option value="married">Married</option>
                        <option value="widowed">Widowed</option>
                        <option value="separated">Separated</option>
                    </select>
                    <small id="err_civil_status" style="color:#dc2626"></small>
                </div>
                <div>
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                    <small id="err_phone" style="color:#dc2626"></small>
                </div>
                <div>
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                </div>
                <div style="grid-column: 1 / -1;">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                    <small id="err_address" style="color:#dc2626"></small>
                </div>
                <div>
                    <label for="province">Province</label>
                    <input type="text" id="province" name="province" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                    <small id="err_province" style="color:#dc2626"></small>
                </div>
                <div>
                    <label for="city">City/Municipality</label>
                    <input type="text" id="city" name="city" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                    <small id="err_city" style="color:#dc2626"></small>
                </div>
                <div>
                    <label for="barangay">Barangay</label>
                    <input type="text" id="barangay" name="barangay" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                    <small id="err_barangay" style="color:#dc2626"></small>
                </div>
                <div>
                    <label for="zip_code">ZIP Code</label>
                    <input type="text" id="zip_code" name="zip_code" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                    <small id="err_zip_code" style="color:#dc2626"></small>
                </div>
                <div>
                    <label for="insurance_provider">Insurance Provider</label>
                    <input type="text" id="insurance_provider" name="insurance_provider" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                </div>
                <div>
                    <label for="insurance_number">Insurance Number</label>
                    <input type="text" id="insurance_number" name="insurance_number" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                </div>
                <div>
                    <label for="emergency_contact_name">Emergency Contact Name</label>
                    <input type="text" id="emergency_contact_name" name="emergency_contact_name" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                    <small id="err_emergency_contact_name" style="color:#dc2626"></small>
                </div>
                <div>
                    <label for="emergency_contact_phone">Emergency Contact Phone</label>
                    <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                    <small id="err_emergency_contact_phone" style="color:#dc2626"></small>
                </div>
                <div>
                    <label for="patient_type">Patient Type</label>
                    <select id="patient_type" name="patient_type" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                        <option value="">Select...</option>
                        <option value="outpatient">Outpatient</option>
                        <option value="inpatient">Inpatient</option>
                        <option value="emergency">Emergency</option>
                    </select>
                </div>
                <div>
                    <label for="status">Status</label>
                    <select id="status" name="status" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div style="grid-column: 1 / -1;">
                    <label for="medical_notes">Medical Notes</label>
                    <textarea id="medical_notes" name="medical_notes" rows="3" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;"></textarea>
                </div>
            </div>
            <div style="display:flex; gap:1rem; justify-content:flex-end; margin-top:1.5rem; position:sticky; bottom:0; background:#fff; padding-top:1rem; border-top:1px solid #e5e7eb;">
                <button type="button" onclick="closeAddPatientsModal()" style="background:#6b7280; color:#fff; border:none; padding:0.75rem 1.5rem; border-radius:4px; cursor:pointer;">Cancel</button>
                <button type="submit" id="savePatientBtn" style="background:#2563eb; color:#fff; border:none; padding:0.75rem 1.5rem; border-radius:4px; cursor:pointer;">Save Patient</button>
            </div>
        </form>
        <button aria-label="Close" onclick="closeAddPatientsModal()" style="position:absolute; top:10px; right:10px; background:transparent; border:none; font-size:1.25rem; color:#6b7280; cursor:pointer;">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>