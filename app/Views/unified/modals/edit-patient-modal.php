<!-- Edit Patient Modal -->
<div id="editPatientModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; padding:2rem; border-radius:8px; max-width:960px; width:98%; margin:auto; position:relative; max-height:90vh; overflow:auto; box-sizing:border-box; -webkit-overflow-scrolling:touch;">
        <div class="hms-modal-header">
            <div class="hms-modal-title">
                <i class="fas fa-user-edit" style="color:#4f46e5"></i>
                <h2 style="margin:0; font-size:1.25rem;">Edit Patient</h2>
            </div>
            <button type="button" onclick="closeEditPatientModal()" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#666;">&times;</button>
        </div>
        <form id="editPatientForm">
            <input type="hidden" id="edit_patient_id" name="patient_id">
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:1rem; padding-bottom:5rem;">
                <div>
                    <label for="edit_first_name">First Name*</label>
                    <input type="text" id="edit_first_name" name="first_name" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                    <small id="err_edit_first_name" style="color:#dc2626"></small>
                </div>
                <div>
                    <label for="edit_middle_name">Middle Name</label>
                    <input type="text" id="edit_middle_name" name="middle_name" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                </div>
                <div>
                    <label for="edit_last_name">Last Name*</label>
                    <input type="text" id="edit_last_name" name="last_name" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                    <small id="err_edit_last_name" style="color:#dc2626"></small>
                </div>
                <div>
                    <label for="edit_date_of_birth">Date of Birth*</label>
                    <input type="date" id="edit_date_of_birth" name="date_of_birth" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                    <small id="err_edit_date_of_birth" style="color:#dc2626"></small>
                </div>
                <div>
                    <label for="edit_gender">Gender*</label>
                    <select id="edit_gender" name="gender" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                        <option value="">Select...</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                    <small id="err_edit_gender" style="color:#dc2626"></small>
                </div>
                <div>
                    <label for="edit_civil_status">Civil Status*</label>
                    <select id="edit_civil_status" name="civil_status" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                        <option value="">Select...</option>
                        <option value="Single">Single</option>
                        <option value="Married">Married</option>
                        <option value="Divorced">Divorced</option>
                        <option value="Widowed">Widowed</option>
                    </select>
                    <small id="err_edit_civil_status" style="color:#dc2626"></small>
                </div>
                <div>
                    <label for="edit_phone">Phone Number*</label>
                    <input type="text" id="edit_phone" name="phone" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                    <small id="err_edit_phone" style="color:#dc2626"></small>
                </div>
                <div>
                    <label for="edit_email">Email</label>
                    <input type="email" id="edit_email" name="email" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                    <small id="err_edit_email" style="color:#dc2626"></small>
                </div>
                <div>
                    <label for="edit_address">Address*</label>
                    <input type="text" id="edit_address" name="address" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                    <small id="err_edit_address" style="color:#dc2626"></small>
                </div>
                <div>
                    <label for="edit_province">Province*</label>
                    <input type="text" id="edit_province" name="province" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                    <small id="err_edit_province" style="color:#dc2626"></small>
                </div>
                <div>
                    <label for="edit_city">City*</label>
                    <input type="text" id="edit_city" name="city" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                    <small id="err_edit_city" style="color:#dc2626"></small>
                </div>
                <div>
                    <label for="edit_barangay">Barangay*</label>
                    <input type="text" id="edit_barangay" name="barangay" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                    <small id="err_edit_barangay" style="color:#dc2626"></small>
                </div>
                <div>
                    <label for="edit_zip_code">ZIP Code*</label>
                    <input type="text" id="edit_zip_code" name="zip_code" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                    <small id="err_edit_zip_code" style="color:#dc2626"></small>
                </div>
                <div>
                    <label for="edit_patient_type">Patient Type</label>
                    <select id="edit_patient_type" name="patient_type" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                        <option value="Outpatient">Outpatient</option>
                        <option value="Inpatient">Inpatient</option>
                        <option value="Emergency">Emergency</option>
                    </select>
                </div>
                <div>
                    <label for="edit_blood_group">Blood Group</label>
                    <select id="edit_blood_group" name="blood_group" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                        <option value="">Select...</option>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                    </select>
                </div>
                <div>
                    <label for="edit_status">Status</label>
                    <select id="edit_status" name="status" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div>
                    <label for="edit_insurance_provider">Insurance Provider</label>
                    <input type="text" id="edit_insurance_provider" name="insurance_provider" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                </div>
                <div>
                    <label for="edit_insurance_number">Insurance Number</label>
                    <input type="text" id="edit_insurance_number" name="insurance_number" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                </div>
                <div>
                    <label for="edit_emergency_contact_name">Emergency Contact Name*</label>
                    <input type="text" id="edit_emergency_contact_name" name="emergency_contact_name" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                    <small id="err_edit_emergency_contact_name" style="color:#dc2626"></small>
                </div>
                <div>
                    <label for="edit_emergency_contact_phone">Emergency Contact Phone*</label>
                    <input type="text" id="edit_emergency_contact_phone" name="emergency_contact_phone" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px;">
                    <small id="err_edit_emergency_contact_phone" style="color:#dc2626"></small>
                </div>
                <div style="grid-column: 1 / -1;">
                    <label for="edit_medical_notes">Medical Notes</label>
                    <textarea id="edit_medical_notes" name="medical_notes" rows="3" style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px; resize:vertical;"></textarea>
                </div>
            </div>
            <div style="position:absolute; bottom:1rem; right:2rem; left:2rem; display:flex; gap:1rem; justify-content:flex-end; background:#fff; padding-top:1rem; border-top:1px solid #eee;">
                <button type="button" onclick="closeEditPatientModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" id="updatePatientBtn" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Patient
                </button>
            </div>
        </form>
    </div>
