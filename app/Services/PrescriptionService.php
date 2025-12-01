<?php

namespace App\Services;

use App\Libraries\PermissionManager;

class PrescriptionService
{
    protected $db;
    protected $permissionManager;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->permissionManager = new PermissionManager();
    }

    /**
     * Get prescriptions based on user role and permissions
     */
    public function getPrescriptionsByRole($userRole, $staffId = null, $filters = [])
    {
        try {
            $builder = $this->db->table('prescriptions p')
                ->select([
                    'p.id',
                    'p.rx_number as prescription_id',
                    'p.patient_id',
                    'p.patient_name',
                    'p.medication',
                    'p.dosage',
                    'p.frequency',
                    'p.days_supply as duration',
                    'p.quantity',
                    'p.prescriber',
                    'p.priority',
                    'p.notes',
                    'p.status',
                    'p.created_at',
                    'p.updated_at',
                    'p.dispensed_at',
                    'p.created_by',
                    'COALESCE(pat.patient_id, p.patient_id) as pat_id',
                    'pat.date_of_birth',
                    // patients table uses contact_number, not contact_no
                    'pat.contact_number as patient_phone',
                    'pat.email as patient_email'
                ])
                // Join to the correct patients table so listing works
                ->join('patients pat', 'pat.patient_id = p.patient_id', 'left');

            // Apply role-based filtering
            switch ($userRole) {
                case 'admin':
                case 'it_staff':
                    // Admin and IT staff see all prescriptions
                    break;
                    
                case 'doctor':
                    // Doctors: for now, show all prescriptions (no primary_doctor_id column on patients)
                    break;
                    
                case 'nurse':
                    // Nurses: for now, show all prescriptions (no primary_doctor_id to derive department)
                    break;
                    
                case 'pharmacist':
                    // Pharmacists see all prescriptions for dispensing
                    break;
                    
                case 'receptionist':
                    // Receptionists see all prescriptions for coordination
                    break;
                    
                default:
                    // Other roles see no prescriptions
                    $builder->where('1', '0');
                    break;
            }

            // Apply additional filters
            if (!empty($filters['status'])) {
                $builder->where('p.status', $filters['status']);
            }
            
            if (!empty($filters['date'])) {
                $builder->where('DATE(p.created_at)', $filters['date']);
            }
            
            if (!empty($filters['patient_id'])) {
                $builder->where('p.patient_id', $filters['patient_id']);
            }
            
            // Doctor-specific filtering based on patients.primary_doctor_id is disabled

            if (!empty($filters['date_range'])) {
                $builder->where('p.created_at >=', $filters['date_range']['start']);
                $builder->where('p.created_at <=', $filters['date_range']['end']);
            }

            // Search functionality
            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $builder->groupStart()
                    ->like('p.rx_number', $search)
                    ->orLike('p.medication', $search)
                    ->orLike('p.patient_name', $search)
                    ->orLike('p.prescriber', $search)
                    ->groupEnd();
            }

            $builder->orderBy('p.created_at', 'DESC');
            
            return $builder->get()->getResultArray();

        } catch (\Throwable $e) {
            log_message('error', 'PrescriptionService::getPrescriptionsByRole error: ' . $e->getMessage());
            log_message('error', 'PrescriptionService::getPrescriptionsByRole trace: ' . $e->getTraceAsString());
            return [];
        }
    }

    /**
     * Get prescription statistics based on user role
     */
    public function getPrescriptionStats($userRole, $staffId = null)
    {
        try {
            $stats = [];
            $today = date('Y-m-d');
            $thisWeek = [
                'start' => date('Y-m-d', strtotime('monday this week')),
                'end' => date('Y-m-d', strtotime('sunday this week'))
            ];

            switch ($userRole) {
                case 'admin':
                case 'it_staff':
                    $stats = [
                        'total_prescriptions' => $this->getTotalPrescriptions(),
                        'today_prescriptions' => $this->getPrescriptionsCount(['date' => $today]),
                        'week_prescriptions' => $this->getPrescriptionsCount(['date_range' => $thisWeek]),
                        'active_prescriptions' => $this->getPrescriptionsCount(['status' => 'active']),
                        'completed_prescriptions' => $this->getPrescriptionsCount(['status' => 'completed']),
                        'pending_prescriptions' => $this->getPrescriptionsCount(['status' => 'pending']),
                        'cancelled_prescriptions' => $this->getPrescriptionsCount(['status' => 'cancelled']),
                        'active_doctors' => $this->getActiveDoctorsCount()
                    ];
                    break;
                    
                case 'doctor':
                    $stats = [
                        'my_prescriptions' => $this->getPrescriptionsCount([], 'doctor', $staffId),
                        'today_prescriptions' => $this->getPrescriptionsCount(['date' => $today], 'doctor', $staffId),
                        'week_prescriptions' => $this->getPrescriptionsCount(['date_range' => $thisWeek], 'doctor', $staffId),
                        'active_prescriptions' => $this->getPrescriptionsCount(['status' => 'active'], 'doctor', $staffId),
                        'completed_prescriptions' => $this->getPrescriptionsCount(['status' => 'completed'], 'doctor', $staffId),
                        'pending_prescriptions' => $this->getPrescriptionsCount(['status' => 'pending'], 'doctor', $staffId),
                        'my_patients' => $this->getMyPatientsCount($staffId)
                    ];
                    break;
                    
                case 'nurse':
                    $userDept = $this->getUserDepartment($staffId);
                    $stats = [
                        'department_prescriptions' => $this->getDepartmentPrescriptionsCount($userDept),
                        'today_prescriptions' => $this->getDepartmentPrescriptionsCount($userDept, ['date' => $today]),
                        'active_prescriptions' => $this->getDepartmentPrescriptionsCount($userDept, ['status' => 'active']),
                        'pending_prescriptions' => $this->getDepartmentPrescriptionsCount($userDept, ['status' => 'pending']),
                        'department' => $userDept
                    ];
                    break;
                    
                case 'pharmacist':
                    $stats = [
                        'total_prescriptions' => $this->getTotalPrescriptions(),
                        'pending_prescriptions' => $this->getPrescriptionsCount(['status' => 'active']),
                        'dispensed_today' => $this->getPrescriptionsCount(['status' => 'completed', 'date' => $today]),
                        'ready_to_dispense' => $this->getPrescriptionsCount(['status' => 'ready']),
                        'stat_prescriptions' => $this->getStatPrescriptionsCount()
                    ];
                    break;
                    
                case 'receptionist':
                    $stats = [
                        'total_prescriptions' => $this->getTotalPrescriptions(),
                        'today_prescriptions' => $this->getPrescriptionsCount(['date' => $today]),
                        'active_prescriptions' => $this->getPrescriptionsCount(['status' => 'active']),
                        'completed_prescriptions' => $this->getPrescriptionsCount(['status' => 'completed'])
                    ];
                    break;
                    
                default:
                    $stats = ['message' => 'No prescription access for this role'];
                    break;
            }

            return $stats;

        } catch (\Throwable $e) {
            log_message('error', 'PrescriptionService::getPrescriptionStats error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a new prescription (supporting one prescription with many medicines)
     */
    public function createPrescription($data, $userRole, $staffId = null)
    {
        try {
            // Validate permissions
            if (!$this->permissionManager->hasPermission($userRole, 'prescriptions', 'create')) {
                return ['success' => false, 'message' => 'Permission denied'];
            }

            // Basic header validation
            $validation = \Config\Services::validation();
            $validation->setRules([
                'patient_id' => 'required|integer',
                // items array will be validated manually
                'items'      => 'required'
            ]);

            if (!$validation->run($data)) {
                return [
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors'  => $validation->getErrors()
                ];
            }

            // Validate items array for multi-medicine support
            if (empty($data['items']) || !is_array($data['items'])) {
                return [
                    'success' => false,
                    'message' => 'At least one medication item is required',
                    'errors'  => ['items' => 'At least one medication item is required']
                ];
            }

            $items = [];
            foreach ($data['items'] as $index => $item) {
                $medName = trim($item['medication_name'] ?? $item['medication'] ?? '');
                $qty     = (int)($item['quantity'] ?? 0);

                if ($medName === '' || $qty <= 0) {
                    return [
                        'success' => false,
                        'message' => 'Each medication must have a name and positive quantity',
                        'errors'  => [
                            "items[$index]" => 'Medication name and quantity are required'
                        ]
                    ];
                }

                $durationStr = $item['duration'] ?? '';
                $daysSupply  = null;
                if ($durationStr !== '') {
                    $daysSupply = (int) filter_var($durationStr, FILTER_SANITIZE_NUMBER_INT) ?: null;
                }

                $items[] = [
                    'medication_resource_id' => !empty($item['medication_resource_id']) ? (int) $item['medication_resource_id'] : null,
                    'medication_name'        => $medName,
                    'dosage'                 => $item['dosage'] ?? null,
                    'frequency'              => $item['frequency'] ?? null,
                    'duration'               => $durationStr ?: null,
                    'days_supply'            => $daysSupply,
                    'quantity'               => $qty,
                    'notes'                  => $item['notes'] ?? null,
                ];
            }

            // Generate rx_number
            $rxNumber = $this->generatePrescriptionId();

            // Get patient name (use correct patients table)
            $patient = $this->db->table('patients')
                ->select('first_name, last_name')
                ->where('patient_id', $data['patient_id'])
                ->get()
                ->getRowArray();
            
            $patientName = $patient ? ($patient['first_name'] . ' ' . $patient['last_name']) : 'Unknown';

            // Get prescriber name
            $prescriberId = ($userRole === 'doctor') ? $staffId : ($data['doctor_id'] ?? $staffId);
            $prescriber = $this->db->table('staff')
                ->select('first_name, last_name')
                ->where('staff_id', $prescriberId)
                ->get()
                ->getRowArray();
            
            $prescriberName = $prescriber ? ('Dr. ' . $prescriber['first_name'] . ' ' . $prescriber['last_name']) : 'Unknown';

            // Get user_id for created_by (from users table linked to staff_id)
            $user = $this->db->table('users')
                ->select('user_id')
                ->where('staff_id', $staffId)
                ->get()
                ->getRowArray();
            
            $createdBy = $user['user_id'] ?? null;

            // NOTE: Resource stock reservation is currently defined for a single
            // medication/quantity. For multi-medicine prescriptions we keep the
            // logic disabled here to avoid incorrect reservations. It can be
            // reworked later on a per-item basis if needed.

            // Map status values
            $statusMap = [
                'active' => 'queued',
                'pending' => 'queued',
                'ready' => 'ready',
                'completed' => 'dispensed',
                'cancelled' => 'cancelled'
            ];
            
            $status = $statusMap[$data['status'] ?? 'active'] ?? 'queued';

            // Use first item to build a legacy summary for list views
            $firstItem          = $items[0];
            $firstMedication    = $firstItem['medication_name'];
            $medicationSummary  = $firstMedication . (count($items) > 1 ? ' +' . (count($items) - 1) . ' more' : '');

            $prescriptionData = [
                'rx_number'    => $rxNumber,
                'patient_id'   => (int) $data['patient_id'],
                'patient_name' => $patientName,
                // legacy single-medication columns now store a summary so existing
                // list views continue to work without change
                'medication'   => $medicationSummary,
                'dosage'       => $firstItem['dosage'],
                'frequency'    => $firstItem['frequency'],
                'days_supply'  => $firstItem['days_supply'],
                'quantity'     => $firstItem['quantity'],
                'prescriber'   => $prescriberName,
                'priority'     => $data['priority'] ?? 'routine',
                'notes'        => $data['notes'] ?? null,
                'status'       => $status,
                'created_by'   => $createdBy,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s')
            ];

            // Insert header + items in a transaction
            $this->db->transBegin();

            $insertResult = $this->db->table('prescriptions')->insert($prescriptionData);

            if (!$insertResult) {
                $dbError = $this->db->error();
                $this->db->transRollback();
                log_message('error', 'PrescriptionService::createPrescription - Header insert failed: ' . json_encode($dbError));

                return [
                    'success' => false,
                    'message' => 'Failed to create prescription header: ' . ($dbError['message'] ?? 'Unknown database error')
                ];
            }

            $prescriptionId = (int) $this->db->insertID();

            $itemTable = $this->db->table('prescription_items');
            foreach ($items as $itemRow) {
                $row = $itemRow;
                $row['prescription_id'] = $prescriptionId;
                $row['created_at']      = date('Y-m-d H:i:s');
                $row['updated_at']      = date('Y-m-d H:i:s');

                if (!$itemTable->insert($row)) {
                    $dbError = $this->db->error();
                    $this->db->transRollback();
                    log_message('error', 'PrescriptionService::createPrescription - Item insert failed: ' . json_encode($dbError));

                    return [
                        'success' => false,
                        'message' => 'Failed to create prescription items: ' . ($dbError['message'] ?? 'Unknown database error')
                    ];
                }
            }

            $this->db->transCommit();

            return [
                'success'         => true,
                'message'         => 'Prescription created successfully',
                'prescription_id' => $rxNumber,
                'id'              => $prescriptionId
            ];

        } catch (\Throwable $e) {
            log_message('error', 'PrescriptionService::createPrescription error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create prescription'];
        }
    }

    /**
     * Update a prescription
     */
    public function updatePrescription($id, $data, $userRole, $staffId = null)
    {
        try {
            // Get existing prescription
            $existingPrescription = $this->getPrescription($id);
            if (!$existingPrescription) {
                return ['success' => false, 'message' => 'Prescription not found'];
            }

            // Check permissions
            if (!$this->canEditPrescription($existingPrescription, $userRole, $staffId)) {
                return ['success' => false, 'message' => 'Permission denied'];
            }

            // Prepare update data - map form fields to database columns
            $updateData = [];
            
            if (isset($data['medication'])) {
                $updateData['medication'] = $data['medication'];
            }
            
            if (isset($data['dosage'])) {
                $updateData['dosage'] = $data['dosage'];
            }
            
            if (isset($data['frequency'])) {
                $updateData['frequency'] = $data['frequency'];
            }
            
            // Map duration to days_supply (database column name)
            if (isset($data['duration'])) {
                $updateData['days_supply'] = !empty($data['duration']) ? (int)filter_var($data['duration'], FILTER_SANITIZE_NUMBER_INT) : null;
            }
            
            if (isset($data['quantity'])) {
                $updateData['quantity'] = (int)$data['quantity'];
            }
            
            if (isset($data['notes'])) {
                $updateData['notes'] = $data['notes'];
            }
            
            // Map status values (UI to database ENUM)
            if (isset($data['status'])) {
                $statusMap = [
                    'active' => 'queued',
                    'pending' => 'queued',
                    'ready' => 'ready',
                    'completed' => 'dispensed',
                    'cancelled' => 'cancelled',
                    'verifying' => 'verifying',
                    'queued' => 'queued',
                    'dispensed' => 'dispensed'
                ];
                $updateData['status'] = $statusMap[$data['status']] ?? $data['status'];
            }
            
            // Always update the timestamp
            $updateData['updated_at'] = date('Y-m-d H:i:s');

            if (empty($updateData) || count($updateData) === 1) {
                return ['success' => false, 'message' => 'No fields to update'];
            }
            
            $result = $this->db->table('prescriptions')->where('id', $id)->update($updateData);
            
            if ($result) {
                return ['success' => true, 'message' => 'Prescription updated successfully'];
            }

            // Log database error
            $dbError = $this->db->error();
            log_message('error', 'PrescriptionService::updatePrescription - Database error: ' . json_encode($dbError));
            
            return ['success' => false, 'message' => 'Failed to update prescription'];

        } catch (\Throwable $e) {
            log_message('error', 'PrescriptionService::updatePrescription error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update prescription'];
        }
    }

    /**
     * Delete a prescription
     */
    public function deletePrescription($id, $userRole, $staffId = null)
    {
        try {
            // Get existing prescription
            $existingPrescription = $this->getPrescription($id);
            if (!$existingPrescription) {
                return ['success' => false, 'message' => 'Prescription not found'];
            }

            // Check permissions
            if (!$this->canDeletePrescription($existingPrescription, $userRole, $staffId)) {
                return ['success' => false, 'message' => 'Permission denied'];
            }

            if ($this->db->table('prescriptions')->where('id', $id)->delete()) {
                return ['success' => true, 'message' => 'Prescription deleted successfully'];
            }

            return ['success' => false, 'message' => 'Failed to delete prescription'];

        } catch (\Throwable $e) {
            log_message('error', 'PrescriptionService::deletePrescription error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to delete prescription'];
        }
    }

    /**
     * Get a single prescription
     */
    public function getPrescription($id)
    {
        try {
            log_message('info', 'PrescriptionService::getPrescription - Looking for ID: ' . $id);
            
            $prescription = $this->db->table('prescriptions p')
                ->select([
                    'p.id',
                    'p.rx_number as prescription_id',
                    'p.patient_id',
                    'p.patient_name',
                    'p.medication',
                    'p.dosage',
                    'p.frequency',
                    'p.days_supply as duration',
                    'p.quantity',
                    'p.prescriber',
                    'p.priority',
                    'p.notes',
                    'p.status',
                    'p.created_at',
                    'p.updated_at',
                    'p.dispensed_at',
                    'p.created_by',
                    'COALESCE(pat.patient_id, p.patient_id) as pat_id',
                    'pat.first_name as patient_first_name',
                    'pat.last_name as patient_last_name',
                    'pat.date_of_birth',
                    // patients table uses contact_number, not contact_no, and has no status/primary_doctor_id
                    'pat.contact_number as patient_phone',
                    'pat.email as patient_email'
                ])
                ->join('patients pat', 'pat.patient_id = p.patient_id', 'left')
                ->where('p.id', $id)
                ->get()
                ->getRowArray();
            
            log_message('info', 'PrescriptionService::getPrescription - Found: ' . ($prescription ? 'YES' : 'NO'));
            
            if ($prescription) {
                // Use prescription's patient_name if available, otherwise construct from patient table
                if (empty($prescription['patient_name']) && !empty($prescription['patient_first_name'])) {
                    $prescription['patient_name'] = trim(($prescription['patient_first_name'] ?? '') . ' ' . ($prescription['patient_last_name'] ?? ''));
                }

                // Load associated medication items (one prescription, many medicines)
                try {
                    $items = $this->db->table('prescription_items')
                        ->where('prescription_id', $prescription['id'])
                        ->orderBy('id', 'ASC')
                        ->get()
                        ->getResultArray();
                } catch (\Throwable $e) {
                    log_message('error', 'PrescriptionService::getPrescription - Failed to load items: ' . $e->getMessage());
                    $items = [];
                }

                $prescription['items'] = $items;

                log_message('info', 'PrescriptionService::getPrescription - Prescription data: ' . json_encode($prescription));
            }
            
            return $prescription;

        } catch (\Throwable $e) {
            log_message('error', 'PrescriptionService::getPrescription error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return null;
        }
    }

    /**
     * Update prescription status
     */
    public function updatePrescriptionStatus($id, $status, $userRole, $staffId = null)
    {
        try {
            // Get existing prescription
            $existingPrescription = $this->getPrescription($id);
            if (!$existingPrescription) {
                return ['success' => false, 'message' => 'Prescription not found'];
            }

            // Check permissions
            if (!$this->canEditPrescription($existingPrescription, $userRole, $staffId)) {
                return ['success' => false, 'message' => 'Permission denied'];
            }

            // Validate status - map UI status to database ENUM values
            $statusMap = [
                'active' => 'queued',
                'pending' => 'queued',
                'ready' => 'ready',
                'completed' => 'dispensed',
                'cancelled' => 'cancelled',
                'verifying' => 'verifying',
                'queued' => 'queued',
                'dispensed' => 'dispensed'
            ];
            
            $dbStatus = $statusMap[$status] ?? $status;
            $validStatuses = ['queued', 'verifying', 'ready', 'dispensed', 'cancelled'];
            
            if (!in_array($dbStatus, $validStatuses)) {
                return ['success' => false, 'message' => 'Invalid status'];
            }

            $updateData = [
                'status' => $dbStatus,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($this->db->table('prescriptions')->where('id', $id)->update($updateData)) {
                return ['success' => true, 'message' => 'Prescription status updated successfully'];
            }

            return ['success' => false, 'message' => 'Failed to update prescription status'];

        } catch (\Throwable $e) {
            log_message('error', 'PrescriptionService::updatePrescriptionStatus error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update prescription status'];
        }
    }

    /**
     * Get available patients for prescription creation
     */
    public function getAvailablePatients($userRole, $staffId = null)
    {
        try {
            // Use the correct patients table and existing columns (match AppointmentManagement)
            $builder = $this->db->table('patients p')
                ->select([
                    'p.patient_id',
                    'p.first_name',
                    'p.last_name',
                    'p.date_of_birth'
                ]);

            // For now, do not filter by primary doctor since that column is not present
            return $builder->orderBy('p.first_name', 'ASC')->get()->getResultArray();

        } catch (\Throwable $e) {
            log_message('error', 'PrescriptionService::getAvailablePatients error: ' . $e->getMessage());
            return [];
        }
    }

    // Private helper methods

    private function getTotalPrescriptions()
    {
        return $this->db->table('prescriptions')->countAllResults();
    }

    private function getPrescriptionsCount($filters = [], $userRole = null, $staffId = null)
    {
        $builder = $this->db->table('prescriptions p');
        
        if ($userRole === 'doctor' && $staffId) {
            // Join to the correct patients table when filtering by doctor
            $builder->join('patients pat', 'pat.patient_id = p.patient_id', 'inner')
                ->where('pat.primary_doctor_id', $staffId);
        }

        if (!empty($filters['date'])) {
            $builder->where('DATE(p.created_at)', $filters['date']);
        }
        
        if (!empty($filters['status'])) {
            $builder->where('p.status', $filters['status']);
        }

        if (!empty($filters['date_range'])) {
            $builder->where('p.created_at >=', $filters['date_range']['start']);
            $builder->where('p.created_at <=', $filters['date_range']['end']);
        }

        return $builder->countAllResults();
    }

    private function getDepartmentPrescriptionsCount($department, $filters = [])
    {
        try {
            $builder = $this->db->table('prescriptions p')
                ->join('patient pat', 'pat.patient_id = p.patient_id', 'inner')
                ->join('staff s', 's.staff_id = pat.primary_doctor_id', 'inner')
                ->where('s.department', $department);

            if (!empty($filters['date'])) {
                $builder->where('DATE(p.created_at)', $filters['date']);
            }
            
            if (!empty($filters['status'])) {
                $builder->where('p.status', $filters['status']);
            }

            return $builder->countAllResults();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function getActiveDoctorsCount()
    {
        try {
            return $this->db->table('staff')
                ->where('role', 'doctor')
                ->where('status', 'Active')
                ->countAllResults();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function getMyPatientsCount($staffId)
    {
        try {
            return $this->db->table('patient')
                ->where('primary_doctor_id', $staffId)
                ->where('status', 'Active')
                ->countAllResults();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function getStatPrescriptionsCount()
    {
        try {
            return $this->db->table('prescriptions')
                ->where('status', 'active')
                ->where('priority', 'stat')
                ->countAllResults();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function getUserDepartment($staffId)
    {
        try {
            $result = $this->db->table('staff')
                ->select('department')
                ->where('staff_id', $staffId)
                ->get()
                ->getRowArray();
            return $result['department'] ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function generatePrescriptionId()
    {
        $prefix = 'RX';
        $year = date('Y');
        $month = date('m');
        $unique = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $prescriptionId = $prefix . $year . $month . $unique;

        // Ensure uniqueness
        while ($this->db->table('prescriptions')->where('rx_number', $prescriptionId)->countAllResults() > 0) {
            $unique = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $prescriptionId = $prefix . $year . $month . $unique;
        }

        return $prescriptionId;
    }

    private function canEditPrescription($prescription, $userRole, $staffId = null)
    {
        // Admin and IT staff can edit all prescriptions
        if (in_array($userRole, ['admin', 'it_staff'])) {
            return true;
        }

        // Doctors can edit their own prescriptions
        if ($userRole === 'doctor' && $staffId && $prescription['doctor_id'] == $staffId) {
            return true;
        }

        // Pharmacists can update status for dispensing
        if ($userRole === 'pharmacist') {
            return true;
        }

        return false;
    }

    private function canDeletePrescription($prescription, $userRole, $staffId = null)
    {
        // Only admin can delete prescriptions
        if ($userRole === 'admin') {
            return true;
        }

        // Doctors can delete their own prescriptions if not yet dispensed
        if ($userRole === 'doctor' && $staffId && $prescription['doctor_id'] == $staffId && 
            in_array($prescription['status'], ['active', 'pending'])) {
            return true;
        }

        return false;
    }
}
