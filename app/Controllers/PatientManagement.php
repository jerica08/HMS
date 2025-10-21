<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class PatientManagement extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $stats = [
            'total_patients' => 0,
            'in_patients'    => 0,
            'out_patients'   => 0,
        ];
        $patients = [];

        try {
            // Stats
            $stats['total_patients'] = $this->db->table('patient')->countAllResults();
            $stats['in_patients']    = $this->db->table('patient')->where('patient_type', 'inpatient')->countAllResults();
            $stats['out_patients']   = $this->db->table('patient')->where('patient_type', 'outpatient')->countAllResults();

            // Patients list with optional assigned doctor name
            $builder = $this->db->table('patient p')
                ->select('p.patient_id, p.first_name, p.last_name, p.email, p.date_of_birth, p.patient_type, p.status, p.primary_doctor_id, CONCAT(s.first_name, " ", s.last_name) AS primary_doctor_name')
                ->join('doctor d', 'd.doctor_id = p.primary_doctor_id', 'left')
                ->join('staff s', 's.staff_id = d.staff_id', 'left')
                ->orderBy('p.patient_id', 'DESC');
            $patients = $builder->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'PatientManagement index error: '.$e->getMessage());
        }

        $data = [
            'title'         => 'Patient Management',
            'patientStats'  => $stats,
            'patients'      => $patients,
        ];

        return view('admin/patient-management', $data);
    }
}
