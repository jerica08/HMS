<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        $data = [

            [
                'name' => 'Emergency Department',
                'code' => 'ER',
                'type' => 'Clinical',
                'department_head_id' => null, // assign doctor id later
                'floor' => '1F',
                'status' => 'Active',
                'description' => 'Handles all urgent and life-threatening cases 24/7.',
            ],

            [
                'name' => 'Outpatient Department',
                'code' => 'OPD',
                'type' => 'Clinical',
                'department_head_id' => null,
                'floor' => '1F',
                'status' => 'Active',
                'description' => 'Provides consultation, diagnosis, and minor procedures for non-admitted patients.',
            ],

            [
                'name' => 'Inpatient Department',
                'code' => 'IPD',
                'type' => 'Clinical',
                'department_head_id' => null,
                'floor' => '2F',
                'status' => 'Active',
                'description' => 'Manages patients who require hospital admission and ongoing care.',
            ],

            [
                'name' => 'Internal Medicine',
                'code' => 'IM',
                'type' => 'Clinical',
                'department_head_id' => null,
                'floor' => '2F',
                'status' => 'Active',
                'description' => 'Focuses on adult medical conditions like heart, lungs, kidney, and endocrine disorders.',
            ],

            [
                'name' => 'Pediatrics',
                'code' => 'PED',
                'type' => 'Clinical',
                'department_head_id' => null,
                'floor' => '2F',
                'status' => 'Active',
                'description' => 'Treats children from newborns to adolescents.',
            ],

            [
                'name' => 'OB-GYN',
                'code' => 'OBGYN',
                'type' => 'Clinical',
                'department_head_id' => null,
                'floor' => '3F',
                'status' => 'Active',
                'description' => 'Manages pregnancy, childbirth, and female reproductive health.',
            ],

            [
                'name' => 'General Surgery',
                'code' => 'SUR',
                'type' => 'Clinical',
                'department_head_id' => null,
                'floor' => '3F',
                'status' => 'Active',
                'description' => 'Performs surgeries for various general conditions.',
            ],

            [
                'name' => 'Orthopedics',
                'code' => 'ORTHO',
                'type' => 'Clinical',
                'department_head_id' => null,
                'floor' => '3F',
                'status' => 'Active',
                'description' => 'Treats musculoskeletal disorders including bones, joints, and muscles.',
            ],

            [
                'name' => 'Cardiology',
                'code' => 'CARD',
                'type' => 'Clinical',
                'department_head_id' => null,
                'floor' => '3F',
                'status' => 'Active',
                'description' => 'Focuses on heart and vascular system disorders.',
            ],

            [
                'name' => 'Neurology',
                'code' => 'NEURO',
                'type' => 'Clinical',
                'department_head_id' => null,
                'floor' => '3F',
                'status' => 'Active',
                'description' => 'Treats disorders of the nervous system.',
            ],

            [
                'name' => 'Pulmonology',
                'code' => 'PULMO',
                'type' => 'Clinical',
                'department_head_id' => null,
                'floor' => '3F',
                'status' => 'Active',
                'description' => 'Manages lung and respiratory conditions.',
            ],

            [
                'name' => 'Gastroenterology',
                'code' => 'GI',
                'type' => 'Clinical',
                'department_head_id' => null,
                'floor' => '3F',
                'status' => 'Active',
                'description' => 'Focuses on digestive system disorders.',
            ],

            [
                'name' => 'Dermatology',
                'code' => 'DERM',
                'type' => 'Clinical',
                'department_head_id' => null,
                'floor' => '2F',
                'status' => 'Active',
                'description' => 'Treats skin, hair, and nail conditions.',
            ],

            [
                'name' => 'Ophthalmology',
                'code' => 'OPHTHA',
                'type' => 'Clinical',
                'department_head_id' => null,
                'floor' => '2F',
                'status' => 'Active',
                'description' => 'Provides eye care and vision management.',
            ],

            [
                'name' => 'ENT',
                'code' => 'ENT',
                'type' => 'Clinical',
                'department_head_id' => null,
                'floor' => '2F',
                'status' => 'Active',
                'description' => 'Treats ear, nose, throat, and related head & neck conditions.',
            ],

            [
                'name' => 'Psychiatry',
                'code' => 'PSY',
                'type' => 'Clinical',
                'department_head_id' => null,
                'floor' => '4F',
                'status' => 'Active',
                'description' => 'Manages mental health conditions.',
            ],

            [
                'name' => 'Oncology',
                'code' => 'ONC',
                'type' => 'Clinical',
                'department_head_id' => null,
                'floor' => '4F',
                'status' => 'Active',
                'description' => 'Provides cancer diagnosis, treatment, and follow-up care.',
            ],

            [
                'name' => 'Infectious Diseases',
                'code' => 'ID',
                'type' => 'Clinical',
                'department_head_id' => null,
                'floor' => '4F',
                'status' => 'Active',
                'description' => 'Manages infectious and communicable diseases.',
            ],

            [
                'name' => 'Endocrinology',
                'code' => 'ENDO',
                'type' => 'Clinical',
                'department_head_id' => null,
                'floor' => '4F',
                'status' => 'Active',
                'description' => 'Focuses on hormonal and metabolic disorders.',
            ],

            [
                'name' => 'Urology',
                'code' => 'URO',
                'type' => 'Clinical',
                'department_head_id' => null,
                'floor' => '4F',
                'status' => 'Active',
                'description' => 'Manages urinary tract and male reproductive system disorders.',
            ],

        ];

        // Insert all records
        $this->db->table('department')->insertBatch($data);
    }
}
