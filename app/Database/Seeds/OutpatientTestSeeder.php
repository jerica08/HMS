<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class OutpatientTestSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        // Resolve patient table name (patient or patients)
        $patientTable = $db->tableExists('patient') ? 'patient' : 'patients';

        // 1. Insert a test outpatient patient (match patients table columns)
        $patientData = [
            'last_name'       => 'Outpatient',
            'first_name'      => 'Test',
            'middle_name'     => 'Sample',
            'date_of_birth'   => '1995-05-15',
            'sex'             => 'Female',
            'civil_status'    => 'Single',
            'contact_number'  => '09181234567',
            'email'           => 'test.outpatient@example.com',
            'address'         => '456 Clinic Street, Barangay Health, Test City',
            'created_at'      => date('Y-m-d H:i:s'),
        ];

        $db->table($patientTable)->insert($patientData);
        $patientId = (int) $db->insertID();

        if (! $patientId) {
            echo "Failed to insert outpatient test patient.\n";
            return;
        }

        // 2. Insert an outpatient visit linked to this patient
        if (! $db->tableExists('outpatient_visits')) {
            echo "Table outpatient_visits does not exist.\n";
            return;
        }

        $visitData = [
            'patient_id'          => $patientId,
            'department'          => 'Internal Medicine',
            'assigned_doctor'     => 'Dr. Sample Doctor',
            'appointment_datetime'=> date('Y-m-d H:i:s', strtotime('+1 day 09:00:00')),
            'visit_type'          => 'New',
            'chief_complaint'     => 'Headache and dizziness',
            'allergies'           => 'None known',
            'existing_conditions' => 'None',
            'current_medications' => 'Multivitamins',
            'blood_pressure'      => '120/80',
            'heart_rate'          => '76',
            'respiratory_rate'    => '18',
            'temperature'         => '36.8',
            'weight'              => '60',
            'height'              => '165',
            'payment_type'        => 'Cash',
        ];

        $db->table('outpatient_visits')->insert($visitData);
        $visitId = (int) $db->insertID();

        if (! $visitId) {
            echo "Failed to insert outpatient visit.\n";
            return;
        }

        echo "Outpatient test data seeded successfully. Patient ID: {$patientId}, Visit ID: {$visitId}\n";
    }
}
