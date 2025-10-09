<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Exception;

class Nurse extends BaseController
{
    public function dashboard()
    {
        $data = [
            'title' => 'Nurse Dashboard',
            'page' => 'dashboard'
        ];

        try {
            // Dashboard statistics - using placeholder data for now
            $data['statistics'] = [
                'assigned_patients' => 0,
                'pending_vitals' => 0,
                'completed_tasks' => 0,
                'medication_due' => 0
            ];

            $data['assigned_patients'] = [];
            $data['pending_tasks'] = [];
            $data['recent_activities'] = [];

        } catch (Exception $e) {
            // Fallback data when database/models fail
            $data['statistics'] = [
                'assigned_patients' => 0,
                'pending_vitals' => 0,
                'completed_tasks' => 0,
                'medication_due' => 0
            ];
            $data['assigned_patients'] = [];
            $data['pending_tasks'] = [];
            $data['recent_activities'] = [];
            $data['error'] = 'Database not configured. Please contact administrator.';
        }

        return view('nurse/dashboard', $data);
    }

    public function patient()
    {
        try {
            $data = [
                'title' => 'Patient Management',
                'page' => 'patient',
                'patients' => [], // Would come from patient model
                'assigned_patients' => [] // Patients assigned to this nurse
            ];

            return view('nurse/patient', $data);

        } catch (Exception $e) {
            return view('nurse/patient', [
                'title' => 'Patient Management',
                'page' => 'patient',
                'patients' => [],
                'assigned_patients' => [],
                'error' => 'Database not configured. Please contact administrator.'
            ]);
        }
    }

    public function medication()
    {
        try {
            $data = [
                'title' => 'Medication Management',
                'page' => 'medication',
                'medications' => [], // Would come from medication model
                'patient_medications' => [], // Medications for assigned patients
                'medication_schedule' => []
            ];

            return view('nurse/medication', $data);

        } catch (Exception $e) {
            return view('nurse/medication', [
                'title' => 'Medication Management',
                'page' => 'medication',
                'medications' => [],
                'patient_medications' => [],
                'medication_schedule' => [],
                'error' => 'Database not configured. Please contact administrator.'
            ]);
        }
    }

    public function vitals()
    {
        try {
            $data = [
                'title' => 'Vital Signs',
                'page' => 'vitals',
                'patients' => [], // Would come from patient model
                'vital_records' => [], // Would come from vitals model
                'normal_ranges' => [
                    'blood_pressure_systolic' => ['min' => 90, 'max' => 120],
                    'blood_pressure_diastolic' => ['min' => 60, 'max' => 80],
                    'temperature' => ['min' => 36.1, 'max' => 37.2],
                    'pulse_rate' => ['min' => 60, 'max' => 100],
                    'respiratory_rate' => ['min' => 12, 'max' => 20],
                    'oxygen_saturation' => ['min' => 95, 'max' => 100]
                ]
            ];

            return view('nurse/vitals', $data);

        } catch (Exception $e) {
            return view('nurse/vitals', [
                'title' => 'Vital Signs',
                'page' => 'vitals',
                'patients' => [],
                'vital_records' => [],
                'normal_ranges' => [
                    'blood_pressure_systolic' => ['min' => 90, 'max' => 120],
                    'blood_pressure_diastolic' => ['min' => 60, 'max' => 80],
                    'temperature' => ['min' => 36.1, 'max' => 37.2],
                    'pulse_rate' => ['min' => 60, 'max' => 100],
                    'respiratory_rate' => ['min' => 12, 'max' => 20],
                    'oxygen_saturation' => ['min' => 95, 'max' => 100]
                ],
                'error' => 'Database not configured. Please contact administrator.'
            ]);
        }
    }

    public function shiftReport()
    {
        try {
            $data = [
                'title' => 'Shift Report',
                'page' => 'shift-report',
                'shift_reports' => [], // Would come from shift report model
                'nurses' => [], // Other nurses for handover
                'patients' => [] // Patients for the shift
            ];

            return view('nurse/shift-report', $data);

        } catch (Exception $e) {
            return view('nurse/shift-report', [
                'title' => 'Shift Report',
                'page' => 'shift-report',
                'shift_reports' => [],
                'nurses' => [],
                'patients' => [],
                'error' => 'Database not configured. Please contact administrator.'
            ]);
        }
    }

    /**
     * Record patient vitals (AJAX)
     */
    public function recordVitals()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'patient_id' => 'required',
            'blood_pressure_systolic' => 'required|numeric|greater_than[0]',
            'blood_pressure_diastolic' => 'required|numeric|greater_than[0]',
            'temperature' => 'required|numeric|greater_than[0]',
            'pulse_rate' => 'required|numeric|greater_than[0]',
            'respiratory_rate' => 'required|numeric|greater_than[0]',
            'oxygen_saturation' => 'required|numeric|greater_than[0]',
            'weight' => 'required|numeric|greater_than[0]',
            'height' => 'required|numeric|greater_than[0]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors()
            ]);
        }

        try {
            $vitalsData = [
                'patient_id' => $this->request->getPost('patient_id'),
                'blood_pressure_systolic' => $this->request->getPost('blood_pressure_systolic'),
                'blood_pressure_diastolic' => $this->request->getPost('blood_pressure_diastolic'),
                'temperature' => $this->request->getPost('temperature'),
                'pulse_rate' => $this->request->getPost('pulse_rate'),
                'respiratory_rate' => $this->request->getPost('respiratory_rate'),
                'oxygen_saturation' => $this->request->getPost('oxygen_saturation'),
                'weight' => $this->request->getPost('weight'),
                'height' => $this->request->getPost('height'),
                'notes' => $this->request->getPost('notes'),
                'recorded_by' => session()->get('user_id'), // Current nurse
                'recorded_at' => date('Y-m-d H:i:s')
            ];

            // For now, return success (would save to database in real implementation)
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Vital signs recorded successfully',
                'vitals_id' => 'VIT-' . date('Ymd') . '-' . rand(100, 999)
            ]);

        } catch (Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to record vital signs'
            ]);
        }
    }

    /**
     * Administer medication (AJAX)
     */
    public function administerMedication()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'patient_id' => 'required',
            'medication_id' => 'required',
            'dosage' => 'required',
            'route' => 'required|in_list[oral,intravenous,intramuscular,subcutaneous,topical]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors()
            ]);
        }

        try {
            $medicationData = [
                'patient_id' => $this->request->getPost('patient_id'),
                'medication_id' => $this->request->getPost('medication_id'),
                'dosage' => $this->request->getPost('dosage'),
                'route' => $this->request->getPost('route'),
                'administered_by' => session()->get('user_id'), // Current nurse
                'administered_at' => date('Y-m-d H:i:s'),
                'notes' => $this->request->getPost('notes')
            ];

            // For now, return success (would save to database in real implementation)
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Medication administered successfully',
                'administration_id' => 'MED-' . date('Ymd') . '-' . rand(100, 999)
            ]);

        } catch (Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to record medication administration'
            ]);
        }
    }

    /**
     * Create shift report (AJAX)
     */
    public function createShiftReport()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'shift_date' => 'required|valid_date',
            'shift_type' => 'required|in_list[day,evening,night]',
            'patients_seen' => 'required|numeric|greater_than[0]',
            'medications_administered' => 'required|numeric|greater_than[0]',
            'vitals_recorded' => 'required|numeric|greater_than[0]',
            'incidents' => 'required|min_length[10]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors()
            ]);
        }

        try {
            $reportData = [
                'nurse_id' => session()->get('user_id'), // Current nurse
                'shift_date' => $this->request->getPost('shift_date'),
                'shift_type' => $this->request->getPost('shift_type'),
                'patients_seen' => $this->request->getPost('patients_seen'),
                'medications_administered' => $this->request->getPost('medications_administered'),
                'vitals_recorded' => $this->request->getPost('vitals_recorded'),
                'incidents' => $this->request->getPost('incidents'),
                'handover_notes' => $this->request->getPost('handover_notes'),
                'created_at' => date('Y-m-d H:i:s')
            ];

            // For now, return success (would save to database in real implementation)
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Shift report created successfully',
                'report_id' => 'RPT-' . date('Ymd') . '-' . rand(100, 999)
            ]);

        } catch (Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create shift report'
            ]);
        }
    }

    /**
     * Get dashboard statistics (AJAX)
     */
    public function getDashboardStats()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        try {
            // For now, return mock data (would fetch from database in real implementation)
            $statistics = [
                'assigned_patients' => rand(5, 20),
                'pending_vitals' => rand(0, 8),
                'completed_tasks' => rand(10, 25),
                'medication_due' => rand(2, 12)
            ];

            return $this->response->setJSON([
                'success' => true,
                'statistics' => $statistics
            ]);

        } catch (Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to get dashboard statistics'
            ]);
        }
    }

    /**
     * Get recent activities (AJAX)
     */
    public function getRecentActivities()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        try {
            // For now, return mock data (would fetch from database in real implementation)
            $activities = [
                [
                    'description' => 'Recorded vital signs for Patient ID: PAT001',
                    'timestamp' => date('Y-m-d H:i:s', strtotime('-30 minutes'))
                ],
                [
                    'description' => 'Administered medication to Patient ID: PAT003',
                    'timestamp' => date('Y-m-d H:i:s', strtotime('-1 hour'))
                ],
                [
                    'description' => 'Created shift report for Day shift',
                    'timestamp' => date('Y-m-d H:i:s', strtotime('-2 hours'))
                ]
            ];

            return $this->response->setJSON([
                'success' => true,
                'activities' => $activities
            ]);

        } catch (Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to get recent activities'
            ]);
        }
    }
}
