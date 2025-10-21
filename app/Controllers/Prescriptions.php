<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Prescriptions extends BaseController
{
    protected $db;
    protected $builder;
    protected $doctorId;

    /**
     * Constructor - initializes database connection and checks doctor authentication
     */
    public function __construct()
    {
        // DB Connection
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('prescriptions');

        // Authentication is now handled by the roleauth filter in routes
        $session = session();

        // Get doctor_id from staff_id
        $staffId = $session->get('staff_id');
        if ($staffId) {
            $doctor = $this->db->table('doctor')->where('staff_id', $staffId)->get()->getRowArray();
            $this->doctorId = $doctor ? $doctor['doctor_id'] : null;
        }
    }

    /**
     * Main prescriptions view with dashboard data
     */
    public function prescriptions()
    {
        // Fetch patients assigned to this doctor for the dropdown
        $patients = [];
        try {
            $patients = $this->db->table('patient')
                ->select('patient_id, first_name, last_name')
                ->where('primary_doctor_id', $this->doctorId)
                ->get()
                ->getResultArray();
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            log_message('error', 'Failed to fetch patients: ' . $e->getMessage());
        }

        // Fetch prescriptions created by this doctor
        $prescriptions = [];
        try {
            $prescriptions = $this->db->table('prescriptions')
                ->select('prescriptions.*, patient.first_name, patient.last_name, patient.patient_id as pat_id')
                ->join('patient', 'patient.patient_id = prescriptions.patient_id')
                ->where('prescriptions.doctor_id', $this->doctorId)
                ->orderBy('prescriptions.created_at', 'DESC')
                ->get()
                ->getResultArray();
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            log_message('error', 'Failed to fetch prescriptions: ' . $e->getMessage());
        }

        // Calculate metrics
        $today = date('Y-m-d');
        $totalToday = 0;
        $pending = 0; // active
        $sent = 0; // completed
        try {
            $totalToday = $this->db->table('prescriptions')->where('DATE(date_issued)', $today)->countAllResults();
            $pending = $this->db->table('prescriptions')->where('status', 'active')->countAllResults();
            $sent = $this->db->table('prescriptions')->where('status', 'completed')->countAllResults();
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            // Table might not exist yet
        }

        $data = [
            'patients' => $patients,
            'prescriptions' => $prescriptions,
            'totalToday' => $totalToday,
            'pending' => $pending,
            'sent' => $sent,
        ];

        return view('doctor/prescriptions', $data);
    }

    /**
     * Create a new prescription via POST
     */
    public function createPrescription()
    {
        // Expect POST data from form
        $input = $this->request->getPost();

        // Basic validation
        $validation = \Config\Services::validation();
        $validation->setRules([
            'patient_id'    => 'required|numeric',
            'date_issued'   => 'required|valid_date',
            'medication'    => 'required|max_length[255]',
            'dosage'        => 'required|max_length[100]',
            'frequency'     => 'required|max_length[100]',
            'duration'      => 'required|max_length[50]',
            'notes'         => 'permit_empty|max_length[1000]'
        ]);

        if (!$validation->run($input)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validation->getErrors(),
            ])->setStatusCode(422);
        }

        // Get doctor_id from session
        $doctorId = session()->get('staff_id');
        if (!$doctorId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Doctor not authenticated',
            ])->setStatusCode(401);
        }

        // Generate prescription_id
        $prescriptionId = 'RX' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        // Prepare data for insertion
        $data = [
            'prescription_id' => $prescriptionId,
            'patient_id'      => $input['patient_id'],
            'doctor_id'       => $doctorId,
            'medication'      => $input['medication'],
            'dosage'          => $input['dosage'],
            'frequency'       => $input['frequency'],
            'duration'        => $input['duration'],
            'notes'           => $input['notes'] ?? null,
            'date_issued'     => $input['date_issued'],
            'status'          => 'active',
            'created_at'      => date('Y-m-d H:i:s'),
        ];

        try {
            $builder = $this->db->table('prescriptions');
            $inserted = $builder->insert($data);
            if ($inserted) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Prescription created successfully',
                    'prescription_id' => $prescriptionId,
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Failed to create prescription: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to create prescription',
        ])->setStatusCode(500);
    }

    /**
     * Get prescriptions data for AJAX requests
     */
    public function getPrescriptionsAPI()
    {
        try {
            $prescriptions = $this->db->table('prescriptions')
                ->select('prescriptions.*, patient.first_name, patient.last_name, patient.patient_id as pat_id')
                ->join('patient', 'patient.patient_id = prescriptions.patient_id')
                ->where('prescriptions.doctor_id', $this->doctorId)
                ->orderBy('prescriptions.created_at', 'DESC')
                ->get()
                ->getResultArray();

            return $this->response->setJSON([
                'success' => true,
                'data' => $prescriptions
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to fetch prescriptions API: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to load prescriptions',
                'data' => []
            ])->setStatusCode(500);
        }
    }

    /**
     * Update prescription status
     */
    public function updatePrescriptionStatus()
    {
        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        
        if (!isset($input['prescription_id']) || !isset($input['status'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Missing required fields: prescription_id and status'
            ])->setStatusCode(400);
        }
        
        $prescriptionId = $input['prescription_id'];
        $status = $input['status'];
        $doctorId = session()->get('staff_id');
        
        // Validate status
        $validStatuses = ['active', 'completed', 'cancelled', 'expired'];
        if (!in_array($status, $validStatuses)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid status'
            ])->setStatusCode(400);
        }
        
        try {
            // Verify prescription belongs to this doctor
            $prescription = $this->db->table('prescriptions')
                ->where('prescription_id', $prescriptionId)
                ->where('doctor_id', $doctorId)
                ->get()
                ->getRowArray();
            
            if (!$prescription) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Prescription not found'
                ])->setStatusCode(404);
            }
            
            // Update status
            $updated = $this->db->table('prescriptions')
                ->where('prescription_id', $prescriptionId)
                ->update([
                    'status' => $status,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            
            if ($updated) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Prescription status updated successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update prescription status'
                ])->setStatusCode(500);
            }
            
        } catch (\Throwable $e) {
            log_message('error', 'Failed to update prescription status: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Database error'
            ])->setStatusCode(500);
        }
    }

    /**
     * Get single prescription details
     */
    public function getPrescription($prescriptionId)
    {
        try {
            $prescription = $this->db->table('prescriptions')
                ->select('prescriptions.*, patient.first_name, patient.last_name, patient.patient_id as pat_id')
                ->join('patient', 'patient.patient_id = prescriptions.patient_id')
                ->where('prescriptions.prescription_id', $prescriptionId)
                ->where('prescriptions.doctor_id', $this->doctorId)
                ->get()
                ->getRowArray();

            if (!$prescription) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Prescription not found'
                ])->setStatusCode(404);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $prescription
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to fetch prescription: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Database error'
            ])->setStatusCode(500);
        }
    }

    /**
     * Update prescription details
     */
    public function updatePrescription($prescriptionId)
    {
        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        
        // Basic validation
        $validation = \Config\Services::validation();
        $validation->setRules([
            'medication'    => 'required|max_length[255]',
            'dosage'        => 'required|max_length[100]',
            'frequency'     => 'required|max_length[100]',
            'duration'      => 'required|max_length[50]',
            'notes'         => 'permit_empty|max_length[1000]'
        ]);

        if (!$validation->run($input)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validation->getErrors(),
            ])->setStatusCode(422);
        }
        
        try {
            // Verify prescription belongs to this doctor
            $prescription = $this->db->table('prescriptions')
                ->where('prescription_id', $prescriptionId)
                ->where('doctor_id', $this->doctorId)
                ->get()
                ->getRowArray();
            
            if (!$prescription) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Prescription not found'
                ])->setStatusCode(404);
            }
            
            // Update prescription
            $updateData = [
                'medication' => $input['medication'],
                'dosage' => $input['dosage'],
                'frequency' => $input['frequency'],
                'duration' => $input['duration'],
                'notes' => $input['notes'] ?? null,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $updated = $this->db->table('prescriptions')
                ->where('prescription_id', $prescriptionId)
                ->update($updateData);
            
            if ($updated) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Prescription updated successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update prescription'
                ])->setStatusCode(500);
            }
            
        } catch (\Throwable $e) {
            log_message('error', 'Failed to update prescription: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Database error'
            ])->setStatusCode(500);
        }
    }
}