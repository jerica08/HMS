<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class NonMedicalDepartmentsSeeder extends Seeder
{
    public function run()
    {
        $nonMedicalDepartments = [
            [
                'name' => 'Administration',
                'code' => 'ADM',
                'function' => 'Administrative',
                'floor' => '5',
                'contact_number' => '09123456780',
                'description' => 'Hospital administration and management',
                'status' => 'Active'
            ],
            [
                'name' => 'Human Resources',
                'code' => 'HR',
                'function' => 'Administrative',
                'floor' => '5',
                'contact_number' => '09123456781',
                'description' => 'Staff recruitment, training, and personnel management',
                'status' => 'Active'
            ],
            [
                'name' => 'Finance',
                'code' => 'FIN',
                'function' => 'Administrative',
                'floor' => '5',
                'contact_number' => '09123456782',
                'description' => 'Financial management, billing, and accounting',
                'status' => 'Active'
            ],
            [
                'name' => 'Pharmacy',
                'code' => 'PHARM',
                'function' => 'Support Services',
                'floor' => '1',
                'contact_number' => '09123456783',
                'description' => 'Medication dispensing and pharmaceutical services',
                'status' => 'Active'
            ],
            [
                'name' => 'Medical Records',
                'code' => 'MR',
                'function' => 'Administrative',
                'floor' => '1',
                'contact_number' => '09123456784',
                'description' => 'Patient records management and health information',
                'status' => 'Active'
            ],
            [
                'name' => 'Housekeeping',
                'code' => 'HK',
                'function' => 'Support Services',
                'floor' => 'B',
                'contact_number' => '09123456785',
                'description' => 'Facility cleaning and sanitation services',
                'status' => 'Active'
            ],
            [
                'name' => 'Maintenance',
                'code' => 'MAINT',
                'function' => 'Support Services',
                'floor' => 'B',
                'contact_number' => '09123456786',
                'description' => 'Building maintenance and equipment repair',
                'status' => 'Active'
            ],
            [
                'name' => 'Security',
                'code' => 'SEC',
                'function' => 'Support Services',
                'floor' => '1',
                'contact_number' => '09123456787',
                'description' => 'Hospital security and safety services',
                'status' => 'Active'
            ],
            [
                'name' => 'Information Technology',
                'code' => 'IT',
                'function' => 'Administrative',
                'floor' => '4',
                'contact_number' => '09123456788',
                'description' => 'IT support and hospital information systems',
                'status' => 'Active'
            ],
            [
                'name' => 'Cafeteria',
                'code' => 'CAF',
                'function' => 'Support Services',
                'floor' => 'G',
                'contact_number' => '09123456779',
                'description' => 'Food services for patients and staff',
                'status' => 'Active'
            ]
        ];

        $this->db->table('non_medical_departments')->insertBatch($nonMedicalDepartments);
    }
}
