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
     * Create new appointment (AJAX)
     */
    public function createAppointment()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'patient_id' => 'required',
            'patient_name' => 'required|min_length[3]',
            'doctor_id' => 'required',
            'appointment_date' => 'required|valid_date',
            'appointment_time' => 'required',
            'reason' => 'required|min_length[5]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors()
            ]);
        }

        try {
            $appointmentData = [
                'patient_id' => $this->request->getPost('patient_id'),
                'patient_name' => $this->request->getPost('patient_name'),
                'doctor_id' => $this->request->getPost('doctor_id'),
                'appointment_date' => $this->request->getPost('appointment_date'),
                'appointment_time' => $this->request->getPost('appointment_time'),
                'reason' => $this->request->getPost('reason'),
                'status' => 'scheduled',
                'notes' => $this->request->getPost('notes')
            ];

            // For now, return success (would save to database in real implementation)
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Appointment scheduled successfully',
                'appointment_id' => 'APT-' . date('Ymd') . '-' . rand(100, 999)
            ]);

        } catch (Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to schedule appointment'
            ]);
        }
    }

    /**
     * Register new patient (AJAX)
     */
    public function registerPatient()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'first_name' => 'required|min_length[2]',
            'last_name' => 'required|min_length[2]',
            'email' => 'required|valid_email',
            'phone' => 'required|min_length[10]',
            'date_of_birth' => 'required|valid_date',
            'gender' => 'required|in_list[male,female,other]',
            'address' => 'required|min_length[10]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors()
            ]);
        }

        try {
            $patientData = [
                'first_name' => $this->request->getPost('first_name'),
                'last_name' => $this->request->getPost('last_name'),
                'email' => $this->request->getPost('email'),
                'phone' => $this->request->getPost('phone'),
                'date_of_birth' => $this->request->getPost('date_of_birth'),
                'gender' => $this->request->getPost('gender'),
                'address' => $this->request->getPost('address'),
                'emergency_contact' => $this->request->getPost('emergency_contact'),
                'medical_history' => $this->request->getPost('medical_history')
            ];

            // For now, return success (would save to database in real implementation)
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Patient registered successfully',
                'patient_id' => 'PAT-' . date('Ymd') . '-' . rand(100, 999)
            ]);

        } catch (Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to register patient'
            ]);
        }
    }

    /**
     * Search patients (AJAX)
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
            // For now, return empty results (would search database in real implementation)
            return $this->response->setJSON([
                'success' => true,
                'patients' => []
            ]);

        } catch (Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Search failed'
            ]);
        }
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

        try {
            // Sample time slots (would come from database in real implementation)
            $timeSlots = [
                '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
                '14:00', '14:30', '15:00', '15:30', '16:00', '16:30'
            ];

            return $this->response->setJSON([
                'success' => true,
                'time_slots' => $timeSlots
            ]);

        } catch (Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to get time slots'
            ]);
        }
    }
}
