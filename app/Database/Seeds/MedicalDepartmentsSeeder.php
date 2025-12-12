<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MedicalDepartmentsSeeder extends Seeder
{
    public function run()
    {
        $medicalDepartments = [
            [
                'name' => 'Internal Medicine',
                'code' => 'IM',
                'floor' => '2',
                'contact_number' => '09123456789',
                'description' => 'General medical care and treatment for adult diseases',
                'status' => 'Active'
            ],
            [
                'name' => 'Emergency Medicine',
                'code' => 'EM',
                'floor' => '1',
                'contact_number' => '09123456790',
                'description' => '24/7 emergency medical care and trauma services',
                'status' => 'Active'
            ],
            [
                'name' => 'Pediatrics',
                'code' => 'PED',
                'floor' => '3',
                'contact_number' => '09123456791',
                'description' => 'Medical care for infants, children, and adolescents',
                'status' => 'Active'
            ],
            [
                'name' => 'Cardiology',
                'code' => 'CARD',
                'floor' => '2',
                'contact_number' => '09123456792',
                'description' => 'Diagnosis and treatment of heart and cardiovascular diseases',
                'status' => 'Active'
            ],
            [
                'name' => 'Surgery',
                'code' => 'SURG',
                'floor' => '4',
                'contact_number' => '09123456793',
                'description' => 'Surgical procedures and operations',
                'status' => 'Active'
            ],
            [
                'name' => 'Obstetrics and Gynecology',
                'code' => 'OBGY',
                'floor' => '3',
                'contact_number' => '09123456794',
                'description' => 'Maternal health, childbirth, and women\'s reproductive health',
                'status' => 'Active'
            ],
            [
                'name' => 'Diagnostic Imaging',
                'code' => 'RAD',
                'floor' => '1',
                'contact_number' => '09123456795',
                'description' => 'X-ray, CT scan, MRI, and other imaging services',
                'status' => 'Active'
            ],
            [
                'name' => 'Laboratory',
                'code' => 'LAB',
                'floor' => '1',
                'contact_number' => '09123456796',
                'description' => 'Clinical laboratory testing and pathology services',
                'status' => 'Active'
            ],
            [
                'name' => 'Orthopedics',
                'code' => 'ORTHO',
                'floor' => '2',
                'contact_number' => '09123456797',
                'description' => 'Treatment of bones, joints, ligaments, and muscles',
                'status' => 'Active'
            ],
            [
                'name' => 'Neurology',
                'code' => 'NEURO',
                'floor' => '2',
                'contact_number' => '09123456798',
                'description' => 'Diagnosis and treatment of nervous system disorders',
                'status' => 'Active'
            ]
        ];

        $this->db->table('medical_departments')->insertBatch($medicalDepartments);
    }
}
