<!-- Add Patient Modal -->
<div id="addPatientModal" class="hms-modal-overlay" hidden>
    <div class="hms-modal" role="dialog" aria-modal="true" aria-labelledby="addPatientTitle">
        <div class="hms-modal-header">
            <div class="hms-modal-title" id="addPatientTitle">
                <i class="fas fa-user-plus" style="color:#4f46e5"></i>
                Add New Patient
            </div>
            <button type="button" class="btn btn-secondary btn-small" onclick="closeAddPatientModal()" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="hms-modal-body">
            <div class="patient-tabs">
                <div class="patient-tabs__nav" role="tablist" aria-label="Patient Type Selection">
                    <button type="button" class="patient-tabs__btn active" role="tab" aria-selected="true" aria-controls="outpatientTab" id="outpatientTabBtn" data-tab-target="outpatientTab">
                        <i class="fas fa-user-md"></i>
                        Outpatient
                    </button>
                    <button type="button" class="patient-tabs__btn" role="tab" aria-selected="false" aria-controls="inpatientTab" id="inpatientTabBtn" data-tab-target="inpatientTab">
                        <i class="fas fa-procedures"></i>
                        Inpatient
                    </button>
                </div>
                <div class="patient-tabs__content" data-form-wrapper>
                    <section id="outpatientTab" class="patient-tabs__panel active" role="tabpanel" aria-labelledby="outpatientTabBtn">
                        <form id="addPatientForm" class="patient-form" data-form-type="outpatient" novalidate>
                            <input type="hidden" name="patient_type" value="Outpatient">
                            <input type="hidden" name="country" value="Philippines">
                            <input type="hidden" name="region" value="Region XII - SOCCSKSARGEN">

                            <div class="form-section">
                                <div class="section-header">
                                    <div>
                                        <h4>1. Patient Information</h4>
                                        <p class="section-subtitle">Provide the patient's basic demographic details.</p>
                                    </div>
                                </div>
                                <div class="form-grid">
                                    <div>
                                        <label class="form-label" for="patient_identifier">Patient ID</label>
                                        <input type="text" id="patient_identifier" class="form-input" value="Auto-generated" readonly>
                                        <small class="form-hint">Assigned automatically after saving.</small>
                                    </div>
                                    <div>
                                        <label class="form-label" for="last_name">Last Name*</label>
                                        <input type="text" id="last_name" name="last_name" class="form-input" required>
                                        <small id="err_last_name" class="form-error"></small>
                                    </div>
                                    <div>
                                        <label class="form-label" for="first_name">First Name*</label>
                                        <input type="text" id="first_name" name="first_name" class="form-input" required>
                                        <small id="err_first_name" class="form-error"></small>
                                    </div>
                                    <div>
                                        <label class="form-label" for="middle_name">Middle Name</label>
                                        <input type="text" id="middle_name" name="middle_name" class="form-input">
                                    </div>
                                    <div>
                                        <label class="form-label" for="date_of_birth">Date of Birth*</label>
                                        <input type="date" id="date_of_birth" name="date_of_birth" class="form-input" required>
                                        <small id="err_date_of_birth" class="form-error"></small>
                                    </div>
                                    <div>
                                        <label class="form-label" for="age_display">Age</label>
                                        <input type="text" id="age_display" class="form-input" readonly placeholder="Auto-calculated">
                                    </div>
                                    <div>
                                        <label class="form-label" for="gender">Sex*</label>
                                        <select id="gender" name="gender" class="form-select" required>
                                            <option value="">Select...</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="other">Other</option>
                                        </select>
                                        <small id="err_gender" class="form-error"></small>
                                    </div>
                                    <div>
                                        <label class="form-label" for="civil_status">Civil Status*</label>
                                        <select id="civil_status" name="civil_status" class="form-select" required>
                                            <option value="">Select...</option>
                                            <option value="Single">Single</option>
                                            <option value="Married">Married</option>
                                            <option value="Widowed">Widowed</option>
                                        </select>
                                        <small id="err_civil_status" class="form-error"></small>
                                    </div>
                                    <div>
                                        <label class="form-label" for="phone">Contact Number*</label>
                                        <input type="text" id="phone" name="phone" class="form-input" required>
                                        <small id="err_phone" class="form-error"></small>
                                    </div>
                                    <div>
                                        <label class="form-label" for="email">Email Address</label>
                                        <input type="email" id="email" name="email" class="form-input">
                                        <small id="err_email" class="form-error"></small>
                                    </div>
                                    <div>
                                        <label class="form-label" for="outpatient_province">Province*</label>
                                        <select id="outpatient_province" name="province" class="form-select" required disabled>
                                            <option value="">Select a province...</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label" for="outpatient_city">City / Municipality*</label>
                                        <select id="outpatient_city" name="city" class="form-select" required disabled>
                                            <option value="">Select a city or municipality...</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label" for="outpatient_barangay">Barangay*</label>
                                        <select id="outpatient_barangay" name="barangay" class="form-select" required disabled>
                                            <option value="">Select a barangay...</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label" for="outpatient_street_name">Street Name*</label>
                                        <input type="text" id="outpatient_street_name" name="street_name" class="form-input" placeholder="e.g., Mabini St." required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="outpatient_subdivision">Subdivision / Village</label>
                                        <input type="text" id="outpatient_subdivision" name="subdivision" class="form-input" placeholder="Optional">
                                    </div>
                                    <div>
                                        <label class="form-label" for="outpatient_building_name">Building Name</label>
                                        <input type="text" id="outpatient_building_name" name="building_name" class="form-input" placeholder="Optional">
                                    </div>
                                    <div>
                                        <label class="form-label" for="outpatient_house_number">House / Lot / Block / Unit No.*</label>
                                        <input type="text" id="outpatient_house_number" name="house_number" class="form-input" placeholder="e.g., Blk 4 Lot 8" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="outpatient_zip_code">ZIP Code</label>
                                        <input type="text" id="outpatient_zip_code" name="zip_code" class="form-input" placeholder="Optional">
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="section-header">
                                    <div>
                                        <h4>2. Emergency Contact</h4>
                                        <p class="section-subtitle">Who should we reach out to during emergencies?</p>
                                    </div>
                                </div>
                                <div class="form-grid">
                                    <div>
                                        <label class="form-label" for="emergency_contact_name">Contact Person Name*</label>
                                        <input type="text" id="emergency_contact_name" name="emergency_contact_name" class="form-input" required>
                                        <small id="err_emergency_contact_name" class="form-error"></small>
                                    </div>
                                    <div>
                                        <label class="form-label" for="emergency_contact_relationship">Relationship*</label>
                                        <input type="text" id="emergency_contact_relationship" name="emergency_contact_relationship" class="form-input" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="emergency_contact_phone">Contact Number*</label>
                                        <input type="text" id="emergency_contact_phone" name="emergency_contact_phone" class="form-input" required>
                                        <small id="err_emergency_contact_phone" class="form-error"></small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="section-header">
                                    <div>
                                        <h4>3. Medical Information</h4>
                                        <p class="section-subtitle">Capture current complaints and pertinent medical history.</p>
                                    </div>
                                </div>
                                <div class="form-grid">
                                    <div class="full">
                                        <label class="form-label" for="chief_complaint">Chief Complaint / Reason for Visit*</label>
                                        <textarea id="chief_complaint" name="chief_complaint" class="form-input" rows="3" required></textarea>
                                    </div>
                                    <div class="full">
                                        <label class="form-label" for="allergies">Allergies</label>
                                        <textarea id="allergies" name="allergies" class="form-input" rows="2" placeholder="e.g., Penicillin, Latex"></textarea>
                                    </div>
                                    <div class="full">
                                        <label class="form-label" for="existing_conditions">Existing Conditions</label>
                                        <textarea id="existing_conditions" name="existing_conditions" class="form-input" rows="2" placeholder="Hypertension, Diabetes, etc."></textarea>
                                    </div>
                                    <div class="full">
                                        <label class="form-label" for="current_medications">Current Medications</label>
                                        <textarea id="current_medications" name="current_medications" class="form-input" rows="2"></textarea>
                                    </div>
                                </div>

                                <div class="vitals-grid">
                                    <div>
                                        <label class="form-label" for="blood_pressure">Blood Pressure</label>
                                        <input type="text" id="blood_pressure" name="blood_pressure" class="form-input" placeholder="120/80">
                                    </div>
                                    <div>
                                        <label class="form-label" for="heart_rate">Heart Rate</label>
                                        <input type="text" id="heart_rate" name="heart_rate" class="form-input" placeholder="72 bpm">
                                    </div>
                                    <div>
                                        <label class="form-label" for="respiratory_rate">Respiratory Rate</label>
                                        <input type="text" id="respiratory_rate" name="respiratory_rate" class="form-input" placeholder="16 cpm">
                                    </div>
                                    <div>
                                        <label class="form-label" for="temperature">Temperature</label>
                                        <input type="text" id="temperature" name="temperature" class="form-input" placeholder="37°C">
                                    </div>
                                    <div>
                                        <label class="form-label" for="weight_kg">Weight (kg)</label>
                                        <input type="number" step="0.1" min="0" id="weight_kg" name="weight_kg" class="form-input">
                                    </div>
                                    <div>
                                        <label class="form-label" for="height_cm">Height (cm)</label>
                                        <input type="number" step="0.1" min="0" id="height_cm" name="height_cm" class="form-input">
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="section-header">
                                    <div>
                                        <h4>4. Outpatient Visit Details</h4>
                                        <p class="section-subtitle">Assign the visit to the correct clinic and care team.</p>
                                    </div>
                                </div>
                                <div class="form-grid">
                                    <div>
                                        <label class="form-label" for="department">Department / Clinic*</label>
                                        <select id="department" name="department" class="form-select" required>
                                            <?php if (!empty($departments)): ?>
                                                <option value="">Select department...</option>
                                                <?php foreach ($departments as $department): ?>
                                                    <option value="<?= esc($department['department_id']) ?>">
                                                        <?= esc($department['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <option value="">Select department...</option>
                                                <option value="Internal Medicine">Internal Medicine</option>
                                                <option value="Pediatrics">Pediatrics</option>
                                                <option value="OB-GYN">OB-GYN</option>
                                                <option value="Surgery">Surgery</option>
                                                <option value="ENT">ENT</option>
                                                <option value="Cardiology">Cardiology</option>
                                                <option value="Dermatology">Dermatology</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <?php if (in_array($userRole ?? '', ['admin', 'receptionist', 'it_staff'])): ?>
                                    <div>
                                        <label class="form-label" for="assigned_doctor">Assigned Doctor</label>
                                        <select id="assigned_doctor" name="assigned_doctor" class="form-select">
                                            <?php if (!empty($availableDoctors)): ?>
                                                <option value="">Select Doctor (Optional)</option>
                                                <?php foreach ($availableDoctors as $d): ?>
                                                    <option value="<?= esc($d['staff_id'] ?? $d['id']) ?>" data-department="<?= esc($d['department'] ?? '') ?>">
                                                        <?= esc(trim(($d['first_name'] ?? '') . ' ' . ($d['last_name'] ?? ''))) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <option value="">No doctors available</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <?php endif; ?>
                                    <div>
                                        <label class="form-label" for="appointment_datetime">Appointment Date &amp; Time*</label>
                                        <input type="datetime-local" id="appointment_datetime" name="appointment_datetime" class="form-input" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="visit_type">Visit Type*</label>
                                        <select id="visit_type" name="visit_type" class="form-select" required>
                                            <option value="">Select...</option>
                                            <option value="New">New Patient</option>
                                            <option value="Follow-up">Follow-up</option>
                                            <option value="Emergency">Emergency Visit</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label" for="payment_type">Payment Type*</label>
                                        <select id="payment_type" name="payment_type" class="form-select" required>
                                            <option value="">Select...</option>
                                            <option value="Cash">Cash</option>
                                            <option value="HMO / Insurance">HMO / Insurance</option>
                                            <option value="PhilHealth">PhilHealth</option>
                                            <option value="Company Account">Company Account</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="section-header">
                                    <div>
                                        <h4>5. HMO / Insurance (Optional)</h4>
                                        <p class="section-subtitle">Complete these only if the patient is covered.</p>
                                    </div>
                                </div>
                                <div class="form-grid">
                                    <div>
                                        <label class="form-label" for="insurance_provider">Insurance Provider</label>
                                        <select id="insurance_provider" name="insurance_provider" class="form-select">
                                            <option value="">Select provider...</option>
                                            <option value="Maxicare">Maxicare</option>
                                            <option value="Intellicare">Intellicare</option>
                                            <option value="Medicard">Medicard</option>
                                            <option value="PhilCare">PhilCare</option>
                                            <option value="Avega">Avega</option>
                                            <option value="Generali Philippines">Generali Philippines</option>
                                            <option value="Insular Health Care">Insular Health Care</option>
                                            <option value="EastWest Healthcare">EastWest Healthcare</option>
                                            <option value="ValuCare (ValueCare)">ValuCare (ValueCare)</option>
                                            <option value="Caritas Health Shield">Caritas Health Shield</option>
                                            <option value="FortuneCare">FortuneCare</option>
                                            <option value="Kaiser">Kaiser</option>
                                            <option value="Pacific Cross">Pacific Cross</option>
                                            <option value="Asalus Health Care (Healthway / FamilyDOC)">Asalus Health Care (Healthway / FamilyDOC)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label" for="insurance_card_number">Card Number</label>
                                        <input type="text" id="insurance_card_number" name="insurance_card_number" class="form-input">
                                    </div>
                                    <div>
                                        <label class="form-label" for="insurance_validity">Validity</label>
                                        <input type="date" id="insurance_validity" name="insurance_validity" class="form-input">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </section>
                    <section id="inpatientTab" class="patient-tabs__panel" role="tabpanel" aria-labelledby="inpatientTabBtn" hidden>
                        <form id="addInpatientForm" class="patient-form" data-form-type="inpatient" novalidate>
                            <input type="hidden" name="patient_type" value="Inpatient">
                            <input type="hidden" name="country" value="Philippines">
                            <input type="hidden" name="region" value="Region XII - SOCCSKSARGEN">
 
                            <div class="form-section">
                                <div class="section-header">
                                    <div>
                                        <h4>1. Patient Information</h4>
                                        <p class="section-subtitle">Baseline identity details for admission.</p>
                                    </div>
                                </div>
                                <div class="form-grid">
                                    <div>
                                        <label class="form-label" for="inpatient_patient_id">Patient ID</label>
                                        <input type="text" id="inpatient_patient_id" class="form-input" value="Auto-generated" readonly>
                                    </div>
                                    <div class="full">
                                        <label class="form-label" for="inpatient_full_name">Full Name*</label>
                                        <input type="text" id="inpatient_full_name" name="full_name" class="form-input" placeholder="Last, First Middle" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="inpatient_date_of_birth">Date of Birth*</label>
                                        <input type="date" id="inpatient_date_of_birth" name="date_of_birth" class="form-input" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="inpatient_age">Age*</label>
                                        <input type="text" id="inpatient_age" name="age" class="form-input" placeholder="Auto-calculated" readonly required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="inpatient_gender">Sex*</label>
                                        <select id="inpatient_gender" name="gender" class="form-select" required>
                                            <option value="">Select...</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label" for="inpatient_province">Province*</label>
                                        <select id="inpatient_province" name="province" class="form-select" required disabled>
                                            <option value="">Select a province...</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label" for="inpatient_city">City / Municipality*</label>
                                        <select id="inpatient_city" name="city" class="form-select" required disabled>
                                            <option value="">Select a city or municipality...</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label" for="inpatient_barangay">Barangay*</label>
                                        <select id="inpatient_barangay" name="barangay" class="form-select" required disabled>
                                            <option value="">Select a barangay...</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label" for="street_name">Street Name*</label>
                                        <input type="text" id="street_name" name="street_name" class="form-input" placeholder="e.g., Mabini St." required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="subdivision">Subdivision / Village</label>
                                        <input type="text" id="subdivision" name="subdivision" class="form-input" placeholder="Optional">
                                    </div>
                                    <div>
                                        <label class="form-label" for="building_name">Building Name</label>
                                        <input type="text" id="building_name" name="building_name" class="form-input" placeholder="Optional">
                                    </div>
                                    <div>
                                        <label class="form-label" for="house_number">House / Lot / Block / Unit No.*</label>
                                        <input type="text" id="house_number" name="house_number" class="form-input" placeholder="e.g., Blk 4 Lot 8" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="zip_code">ZIP Code</label>
                                        <input type="text" id="zip_code" name="zip_code" class="form-input" placeholder="Optional">
                                    </div>
                                    <div>
                                        <label class="form-label" for="inpatient_contact">Contact Number*</label>
                                        <input type="text" id="inpatient_contact" name="contact_number" class="form-input" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="inpatient_civil_status">Civil Status*</label>
                                        <select id="inpatient_civil_status" name="civil_status" class="form-select" required>
                                            <option value="">Select...</option>
                                            <option value="Single">Single</option>
                                            <option value="Married">Married</option>
                                            <option value="Widowed">Widowed</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="section-header">
                                    <div>
                                        <h4>2. Emergency Contact / Guardian</h4>
                                        <p class="section-subtitle">Primary and secondary contacts during hospitalization.</p>
                                    </div>
                                </div>
                                <div class="form-grid">
                                    <div>
                                        <label class="form-label" for="guardian_name">Name*</label>
                                        <input type="text" id="guardian_name" name="guardian_name" class="form-input" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="guardian_relationship">Relationship*</label>
                                        <input type="text" id="guardian_relationship" name="guardian_relationship" class="form-input" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="guardian_contact">Contact Number*</label>
                                        <input type="text" id="guardian_contact" name="guardian_contact" class="form-input" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="secondary_contact">Secondary Contact</label>
                                        <input type="text" id="secondary_contact" name="secondary_contact" class="form-input" placeholder="Optional">
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="section-header">
                                    <div>
                                        <h4>3. Admission Details</h4>
                                        <p class="section-subtitle">Information gathered during admission intake.</p>
                                    </div>
                                </div>
                                <div class="form-grid">
                                    <div>
                                        <label class="form-label" for="admission_number">Admission Number</label>
                                        <input type="text" id="admission_number" class="form-input" value="Auto-generated" readonly>
                                    </div>
                                    <div>
                                        <label class="form-label" for="admission_datetime">Date &amp; Time of Admission*</label>
                                        <input type="datetime-local" id="admission_datetime" name="admission_datetime" class="form-input" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="admission_type">Admission Type*</label>
                                        <select id="admission_type" name="admission_type" class="form-select" required>
                                            <option value="">Select...</option>
                                            <option value="ER">ER Admission</option>
                                            <option value="Scheduled">Scheduled Admission</option>
                                            <option value="Transfer">Transfer from Other Facility</option>
                                        </select>
                                    </div>
                                    <div class="full">
                                        <label class="form-label" for="admitting_diagnosis">Admitting Diagnosis*</label>
                                        <textarea id="admitting_diagnosis" name="admitting_diagnosis" class="form-input" rows="2" required></textarea>
                                    </div>
                                    <div>
                                        <label class="form-label" for="admitting_doctor">Admitting Doctor*</label>
                                        <input type="text" id="admitting_doctor" name="admitting_doctor" class="form-input" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="admitting_department">Department / Ward*</label>
                                        <input type="text" id="admitting_department" name="admitting_department" class="form-input" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="consent_uploaded">Consent Form Uploaded?</label>
                                        <select id="consent_uploaded" name="consent_uploaded" class="form-select">
                                            <option value="">Select...</option>
                                            <option value="1">Yes</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="section-header">
                                    <div>
                                        <h4>4. Room &amp; Bed Assignment</h4>
                                        <p class="section-subtitle">Allocate the patient to the appropriate room and bed.</p>
                                    </div>
                                </div>
                                <div class="form-grid">
                                    <div>
                                        <label class="form-label" for="room_type">Room Type*</label>
                                        <select id="room_type" name="room_type" class="form-select" required>
                                            <option value="">Select...</option>
                                            <?php if (!empty($roomTypes)): ?>
                                                <?php foreach ($roomTypes as $type): ?>
                                                    <option value="<?= esc($type['room_type_id']) ?>" data-rate="<?= esc($type['base_daily_rate'] ?? '') ?>">
                                                        <?= esc($type['type_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <option value="" disabled>(No room types defined yet)</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label" for="floor_number">Floor Number*</label>
                                        <select id="floor_number" name="floor_number" class="form-select" required disabled>
                                            <option value="">Select a floor...</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label" for="room_number">Room Number*</label>
                                        <select id="room_number" name="room_number" class="form-select" required disabled>
                                            <option value="">Select a room...</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label" for="bed_number">Bed Number*</label>
                                        <input type="text" id="bed_number" name="bed_number" class="form-input" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="daily_rate">Daily Room Rate</label>
                                        <input type="text" id="daily_rate" name="daily_rate" class="form-input" value="Auto-calculated" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="section-header">
                                    <div>
                                        <h4>5. Patient Classification Based on Illness</h4>
                                    </div>
                                </div>
                                <div class="pill-select">
                                    <?php
                                    $classifications = ['Medical', 'Surgical', 'Maternity', 'Pediatric', 'Geriatric', 'Infectious', 'Psychiatric', 'Rehabilitation'];
                                    foreach ($classifications as $classification): ?>
                                        <label class="pill-option">
                                            <input type="radio" name="patient_classification" value="<?= esc($classification) ?>">
                                            <span><?= esc($classification) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="section-header">
                                    <div>
                                        <h4>6. Medical History</h4>
                                    </div>
                                </div>
                                <div class="form-grid">
                                    <div class="full">
                                        <label class="form-label" for="history_allergies">Allergies</label>
                                        <textarea id="history_allergies" name="history_allergies" class="form-input" rows="2"></textarea>
                                    </div>
                                    <div class="full">
                                        <label class="form-label" for="past_medical_history">Past Medical History</label>
                                        <textarea id="past_medical_history" name="past_medical_history" class="form-input" rows="2"></textarea>
                                    </div>
                                    <div class="full">
                                        <label class="form-label" for="past_surgical_history">Past Surgical History</label>
                                        <textarea id="past_surgical_history" name="past_surgical_history" class="form-input" rows="2"></textarea>
                                    </div>
                                    <div class="full">
                                        <label class="form-label" for="family_history">Family History</label>
                                        <textarea id="family_history" name="family_history" class="form-input" rows="2"></textarea>
                                    </div>
                                    <div class="full">
                                        <label class="form-label" for="history_current_medications">Current Medications</label>
                                        <textarea id="history_current_medications" name="history_current_medications" class="form-input" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="section-header">
                                    <div>
                                        <h4>7. Initial Assessment (Nurse)</h4>
                                    </div>
                                </div>
                                <div class="vitals-grid">
                                    <div>
                                        <label class="form-label" for="assessment_bp">Blood Pressure</label>
                                        <input type="text" id="assessment_bp" name="assessment_bp" class="form-input">
                                    </div>
                                    <div>
                                        <label class="form-label" for="assessment_hr">Heart Rate</label>
                                        <input type="text" id="assessment_hr" name="assessment_hr" class="form-input">
                                    </div>
                                    <div>
                                        <label class="form-label" for="assessment_rr">Respiratory Rate</label>
                                        <input type="text" id="assessment_rr" name="assessment_rr" class="form-input">
                                    </div>
                                    <div>
                                        <label class="form-label" for="assessment_temp">Temperature</label>
                                        <input type="text" id="assessment_temp" name="assessment_temp" class="form-input">
                                    </div>
                                    <div>
                                        <label class="form-label" for="assessment_spo2">SpO2</label>
                                        <input type="text" id="assessment_spo2" name="assessment_spo2" class="form-input">
                                    </div>
                                </div>
                                <div class="form-grid">
                                    <div>
                                        <label class="form-label" for="loc">Level of Consciousness*</label>
                                        <select id="loc" name="level_of_consciousness" class="form-select" required>
                                            <option value="">Select...</option>
                                            <option value="Alert">Alert</option>
                                            <option value="Semi-conscious">Semi-conscious</option>
                                            <option value="Unconscious">Unconscious</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label" for="pain_level">Pain Level (0-10)</label>
                                        <input type="number" min="0" max="10" id="pain_level" name="pain_level" class="form-input">
                                    </div>
                                    <div class="full">
                                        <label class="form-label" for="initial_findings">Initial Findings</label>
                                        <textarea id="initial_findings" name="initial_findings" class="form-input" rows="2"></textarea>
                                    </div>
                                    <div class="full">
                                        <label class="form-label" for="assessment_remarks">Remarks</label>
                                        <textarea id="assessment_remarks" name="assessment_remarks" class="form-input" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="section-header">
                                    <div>
                                        <h4>8. Insurance / Billing Details</h4>
                                    </div>
                                </div>
                                <div class="form-grid">
                                    <div>
                                        <label class="form-label" for="philhealth_number">PhilHealth Number</label>
                                        <input type="text" id="philhealth_number" name="philhealth_number" class="form-input">
                                    </div>
                                    <div>
                                        <label class="form-label" for="hmo_provider">HMO Provider</label>
                                        <input type="text" id="hmo_provider" name="hmo_provider" class="form-input">
                                    </div>
                                    <div>
                                        <label class="form-label" for="hmo_approval_code">HMO Approval Code</label>
                                        <input type="text" id="hmo_approval_code" name="hmo_approval_code" class="form-input">
                                    </div>
                                    <div>
                                        <label class="form-label" for="company_guarantee">Company Guarantee Letter</label>
                                        <select id="company_guarantee" name="company_guarantee" class="form-select">
                                            <option value="">Select...</option>
                                            <option value="1">Yes</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label" for="billing_payment_method">Payment Method*</label>
                                        <select id="billing_payment_method" name="payment_method" class="form-select" required>
                                            <option value="">Select...</option>
                                            <option value="Cash Deposit">Cash Deposit</option>
                                            <option value="Insurance">Insurance</option>
                                            <option value="Company Billing">Company Billing</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="section-header">
                                    <div>
                                        <h4>9. Consent &amp; Legal Information</h4>
                                    </div>
                                </div>
                                <div class="form-grid">
                                    <div class="full">
                                        <label class="form-label" for="consent_upload">Signed Admission Consent</label>
                                        <input type="file" id="consent_upload" name="consent_upload" class="form-input">
                                    </div>
                                    <div>
                                        <label class="form-label" for="billing_responsible_name">Responsible Person (Full Name)*</label>
                                        <input type="text" id="billing_responsible_name" name="billing_responsible_name" class="form-input" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="billing_responsible_signature">Signature*</label>
                                        <input type="text" id="billing_responsible_signature" name="billing_responsible_signature" class="form-input" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="billing_responsible_contact">Contact Number*</label>
                                        <input type="text" id="billing_responsible_contact" name="billing_responsible_contact" class="form-input" required>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
        <div class="hms-modal-actions">
            <button type="button" class="btn btn-secondary" onclick="closeAddPatientModal()">Cancel</button>
            <button type="submit" id="savePatientBtn" class="btn btn-success" data-active-form="addPatientForm">
                <i class="fas fa-save"></i> Save Patient
            </button>
        </div>
    </div>
</div>
