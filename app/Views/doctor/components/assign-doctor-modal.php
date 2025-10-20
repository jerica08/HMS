<!-- Assign Doctor Modal -->
<div id="assignDoctorModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; padding:2rem; border-radius:8px; max-width:500px; width:90%; margin:auto; position:relative; box-sizing:border-box;">
        <div class="hms-modal-header">
            <div class="hms-modal-title">
                <i class="fas fa-user-md" style="color:#4f46e5"></i>
                <h2 style="margin:0; font-size:1.25rem;">Assign Doctor</h2>
            </div>
        </div>
        <form id="assignDoctorForm">
            <input type="hidden" id="assignPatientId" name="patient_id">
            <div style="margin:1rem 0;">
                <label for="doctorSelect">Select Doctor*</label>
                <select id="doctorSelect" name="doctor_id" required style="width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:4px; margin-top:0.5rem;">
                    <option value="">Loading doctors...</option>
                </select>
                <small id="err_doctor" style="color:#dc2626"></small>
            </div>
            <div style="display:flex; gap:1rem; justify-content:flex-end; margin-top:1.5rem;">
                <button type="button" onclick="closeAssignDoctorModal()" style="background:#6b7280; color:#fff; border:none; padding:0.75rem 1.5rem; border-radius:4px; cursor:pointer;">Cancel</button>
                <button type="submit" id="assignDoctorBtn" style="background:#2563eb; color:#fff; border:none; padding:0.75rem 1.5rem; border-radius:4px; cursor:pointer;">Assign Doctor</button>
            </div>
        </form>
        <button aria-label="Close" onclick="closeAssignDoctorModal()" style="position:absolute; top:10px; right:10px; background:transparent; border:none; font-size:1.25rem; color:#6b7280; cursor:pointer;">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>