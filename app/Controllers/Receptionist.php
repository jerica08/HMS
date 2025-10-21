<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Receptionist extends BaseController
{
    public function dashboard()
    {
        $data = [
            'title' => 'Receptionist Dashboard',
            'page' => 'dashboard'
        ];

        try {
            // Dashboard statistics - using placeholder data for now
            $data['statistics'] = [
                'today_appointments' => 0,
                'pending_appointments' => 0,
                'total_patients' => 0,
                'waiting_patients' => 0
            ];

            $data['recent_appointments'] = [];
            $data['today_schedule'] = [];

        } catch (Exception $e) {
            // Fallback data when database/models fail
            $data['statistics'] = [
                'today_appointments' => 0,
                'pending_appointments' => 0,
                'total_patients' => 0,
                'waiting_patients' => 0
            ];
            $data['recent_appointments'] = [];
            $data['today_schedule'] = [];
            $data['error'] = 'Database not configured. Please contact administrator.';
        }

        return view('receptionist/dashboard', $data);
    }

    public function appointmentBooking()
    {
        try {
            $data = [
                'title' => 'Appointment Booking',
                'page' => 'appointment-booking',
                'appointments' => [], // Would come from appointment model
                'doctors' => [], // Would come from doctor/staff model
                'time_slots' => [],
                'patients' => [] // For patient search
            ];

            return view('receptionist/appointment-booking', $data);

        } catch (Exception $e) {
            return view('receptionist/appointment-booking', [
                'title' => 'Appointment Booking',
                'page' => 'appointment-booking',
                'appointments' => [],
                'doctors' => [],
                'time_slots' => [],
                'patients' => [],
                'error' => 'Database not configured. Please contact administrator.'
            ]);
        }
    }

    public function patientRegistration()
    {
        try {
            $data = [
                'title' => 'Patient Registration',
                'page' => 'patient-registration',
                'patients' => [], // Would come from patient model
                'recent_patients' => []
            ];

            return view('receptionist/patient-registration', $data);

        } catch (Exception $e) {
            return view('receptionist/patient-registration', [
                'title' => 'Patient Registration',
                'page' => 'patient-registration',
                'patients' => [],
                'recent_patients' => [],
                'error' => 'Database not configured. Please contact administrator.'
            ]);
        }
    }

    /**
     * Register new patient (AJAX) - Using unified service
     */
    public function registerPatient()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        $session = session();
        
        $patientService = new \App\Services\PatientService();
        $result = $patientService->createPatient(
            $input, 
            $session->get('role'), 
            $session->get('staff_id')
        );
        
        return $this->response->setJSON($result);
    }

    /**
     * Store patient via form submission (non-AJAX) - Using unified service
     */
    public function storePatient()
    {
        $input = $this->request->getPost();
        $session = session();
        
        $patientService = new \App\Services\PatientService();
        $result = $patientService->createPatient(
            $input, 
            $session->get('role'), 
            $session->get('staff_id')
        );
        
        // Handle form-based response
        if ($this->request->isAJAX()) {
            return $this->response->setJSON($result);
        }
        
        // For regular form submission
        if ($result['status'] === 'success') {
            session()->setFlashdata('success', $result['message']);
        } else {
            session()->setFlashdata('error', $result['message']);
            if (isset($result['errors'])) {
                session()->setFlashdata('errors', $result['errors']);
            }
        }
        
        return redirect()->to(base_url('receptionist/patient-registration'));
    }

    /**
     * Get patients API for receptionist - Using unified service
     */
    public function getPatientsAPI()
    {
        $patientService = new \App\Services\PatientService();
        $result = $patientService->getPatients(); // No doctor filter for receptionist
        
        return $this->response->setJSON($result);
    }

    /**
     * Search patients (AJAX) - Enhanced with unified service
     */
    public function searchPatients()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $search = $this->request->getPost('search');

        if (empty($search)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Search term is required'
            ]);
        }

        try {
            $db = \Config\Database::connect();
            $patients = $db->table('patient p')
                ->select('p.*, CONCAT(s.first_name, " ", s.last_name) as assigned_doctor_name')
                ->join('staff s', 's.staff_id = p.primary_doctor_id', 'left')
                ->groupStart()
                    ->like('p.first_name', $search)
                    ->orLike('p.last_name', $search)
                    ->orLike('p.contact_no', $search)
                    ->orLike('p.email', $search)
                ->groupEnd()
                ->orderBy('p.patient_id', 'DESC')
                ->limit(10)
                ->get()
                ->getResultArray();

            // Compute ages
            foreach ($patients as &$p) {
                $p['age'] = $p['date_of_birth']
                    ? (new \DateTime())->diff(new \DateTime($p['date_of_birth']))->y
                    : null;
            }

            return $this->response->setJSON([
                'success' => true,
                'patients' => $patients
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'Patient search failed: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Search failed'
            ]);
        }
    }

    /**
     * Create new appointment (AJAX)
     */
    public function createAppointment()
{
    if (!$this->request->isAJAX()) {
        return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
    }

    $input = $this->request->getJSON(true) ?? $this->request->getPost();
    $session = session();
    
    $appointmentService = new \App\Services\AppointmentService();
    $result = $appointmentService->createAppointment(
        $input, 
        $session->get('role'), 
        $session->get('staff_id')
    );
    
    return $this->response->setJSON($result);
}


    /**
     * Get available time slots (AJAX)
     */
    public function getTimeSlots()
{
    if (!$this->request->isAJAX()) {
        return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
    }

    $date = $this->request->getPost('date');
    $doctor_id = $this->request->getPost('doctor_id');

    if (empty($date) || empty($doctor_id)) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Date and doctor are required'
        ]);
    }
    
    $appointmentService = new \App\Services\AppointmentService();
    $result = $appointmentService->getAvailableTimeSlots($doctor_id, $date);
    
    return $this->response->setJSON($result);
}
}