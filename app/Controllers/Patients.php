<?php

namespace App\Controllers;

use App\Models\PatientModel;
use CodeIgniter\HTTP\ResponseInterface;

class Patients extends BaseController
{
    protected $db;
    protected $builder;
    protected $doctorId;

    /**
     * Constructor - initializes database connection and checks doctor authentication
     */
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('patient');

        // Ensure doctor is logged in
        $session = session();
        if (!$session->get('isLoggedIn') || $session->get('role') !== 'doctor') {
            redirect()->to(base_url('/login'))->send();
            exit();
        }

        // Get doctor_id from staff_id (ensuring valid doctor account)
        $staffId = $session->get('staff_id');
        if ($staffId) {
            $doctor = $this->db->table('doctor')
                ->where('staff_id', $staffId)
                ->get()
                ->getRowArray();

            $this->doctorId = $doctor ? $doctor['doctor_id'] : null;
        } else {
            $this->doctorId = null;
        }
    }

    /**
     * Display list of patients assigned to this doctor
     */
    public function patients()
    {
        $totalPatients = $inPatients = $outPatients = 0;

        try {
            $totalPatients = $this->builder
                ->where('primary_doctor_id', $this->doctorId)
                ->countAllResults();

            $inPatients = $this->builder
                ->where('primary_doctor_id', $this->doctorId)
                ->where('patient_type', 'inpatient')
                ->countAllResults();

            $outPatients = $this->builder
                ->where('primary_doctor_id', $this->doctorId)
                ->where('patient_type', 'outpatient')
                ->countAllResults();
        } catch (\Throwable $e) {
            log_message('error', 'Error fetching patient counts: ' . $e->getMessage());
        }

        // Fetch full patient list for this doctor
        $patients = [];
        try {
            $patients = $this->builder
                ->select('patient_id as id, first_name, middle_name, last_name, date_of_birth, gender, contact_no as phone, email, patient_type, status')
                ->where('primary_doctor_id', $this->doctorId)
                ->get()
                ->getResultArray();

            // Compute age for each patient
            foreach ($patients as &$patient) {
                $patient['age'] = $patient['date_of_birth']
                    ? (new \DateTime())->diff(new \DateTime($patient['date_of_birth']))->y
                    : null;
            }
        } catch (\Throwable $e) {
            log_message('error', 'Failed to fetch patient list: ' . $e->getMessage());
        }

        return view('doctor/patient', [
            'totalPatients' => $totalPatients,
            'inPatients' => $inPatients,
            'outPatients' => $outPatients,
            'patients' => $patients,
        ]);
    }

    /**
     * Create new patient
     */
    public function createPatient()
{
    $input = $this->request->getJSON(true) ?? $this->request->getPost();
    $session = session();
    
    $patientService = new \App\Services\PatientService();
    $result = $patientService->createPatient(
        $input, 
        $session->get('role'), 
        $session->get('staff_id')
    );
    
    return $this->response->setJSON($result)
        ->setStatusCode($result['status'] === 'success' ? 200 : 422);
}

    /**
     * Get specific patient details
     */
    public function getPatient($id)
{
    $patientService = new \App\Services\PatientService();
    $result = $patientService->getPatient($id);
    
    return $this->response->setJSON($result)
        ->setStatusCode($result['status'] === 'success' ? 200 : 404);
}


    /**
     * Update patient details
     */
    public function updatePatient($id)
{
    $input = $this->request->getJSON(true) ?? $this->request->getPost();
    
    $patientService = new \App\Services\PatientService();
    $result = $patientService->updatePatient($id, $input);
    
    return $this->response->setJSON($result)
        ->setStatusCode($result['status'] === 'success' ? 200 : 422);
}
    /**
     * REST API - Get all patients for logged-in doctor
     */
    public function getPatientsAPI()
{
    $patientService = new \App\Services\PatientService();
    $result = $patientService->getPatients($this->doctorId);
    
    return $this->response->setJSON($result);
}
}
