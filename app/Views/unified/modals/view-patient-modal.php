<!-- View Patient Modal -->
<div id="viewPatientModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; padding:2rem; border-radius:8px; max-width:800px; width:98%; margin:auto; position:relative; max-height:90vh; overflow:auto; box-sizing:border-box; -webkit-overflow-scrolling:touch;">
        <div class="hms-modal-header">
            <div class="hms-modal-title">
                <i class="fas fa-user" style="color:#4f46e5"></i>
                <h2 style="margin:0; font-size:1.25rem;">Patient Details</h2>
            </div>
            <button type="button" onclick="closeViewPatientModal()" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#666;">&times;</button>
        </div>
        <div id="viewPatientContent" style="padding-bottom:5rem;">
            <div style="text-align: center; padding: 2rem;">
                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #ccc; margin-bottom: 1rem;" aria-hidden="true"></i>
                <p>Loading patient details...</p>
            </div>
        </div>
        <div style="position:absolute; bottom:1rem; right:2rem; left:2rem; display:flex; gap:1rem; justify-content:flex-end; background:#fff; padding-top:1rem; border-top:1px solid #eee;">
            <button type="button" onclick="closeViewPatientModal()" class="btn btn-secondary">Close</button>
            <?php if (in_array('edit', $permissions ?? [])): ?>
                <button type="button" class="btn btn-warning" id="editFromViewBtn">
                    <i class="fas fa-edit"></i> Edit Patient
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>
