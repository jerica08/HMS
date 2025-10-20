<!-- View Patient Modal -->
<div id="viewPatientModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; padding:2rem; border-radius:8px; max-width:800px; width:95%; margin:auto; position:relative; max-height:90vh; overflow:auto; box-sizing:border-box;">
        <div class="hms-modal-header">
            <div class="hms-modal-title">
                <i class="fas fa-user" style="color:#4f46e5"></i>
                <h2 style="margin:0; font-size:1.25rem;">Patient Details</h2>
            </div>
        </div>
        <div id="viewPatientContent" style="margin:1rem 0;">
            <div style="text-align:center; padding:2rem; color:#6b7280;">Loading patient details...</div>
        </div>
        <div style="display:flex; gap:1rem; justify-content:flex-end; margin-top:1.5rem;">
            <button type="button" onclick="closeViewPatientModal()" style="background:#6b7280; color:#fff; border:none; padding:0.75rem 1.5rem; border-radius:4px; cursor:pointer;">Close</button>
        </div>
        <button aria-label="Close" onclick="closeViewPatientModal()" style="position:absolute; top:10px; right:10px; background:transparent; border:none; font-size:1.25rem; color:#6b7280; cursor:pointer;">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>