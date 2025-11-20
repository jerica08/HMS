<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PatientSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'first_name'      => 'Juan',
                'middle_name'     => 'Santos',
                'last_name'       => 'Dela Cruz',
                'date_of_birth'   => '1985-03-15',
                'sex'             => 'Male',
                'civil_status'    => 'Married',
                'contact_number'  => '09171234567',
                'email'           => 'juan.delacruz@example.com',
                'address'         => '123 Rizal St, Quezon City',
                'created_at'      => date('Y-m-d H:i:s'),
            ],
            [
                'first_name'      => 'Maria',
                'middle_name'     => 'Lopez',
                'last_name'       => 'Santos',
                'date_of_birth'   => '1990-07-22',
                'sex'             => 'Female',
                'civil_status'    => 'Single',
                'contact_number'  => '09181234567',
                'email'           => 'maria.santos@example.com',
                'address'         => '456 Bonifacio Ave, Manila',
                'created_at'      => date('Y-m-d H:i:s'),
            ],
            [
                'first_name'      => 'Pedro',
                'middle_name'     => 'Reyes',
                'last_name'       => 'Garcia',
                'date_of_birth'   => '1975-11-05',
                'sex'             => 'Male',
                'civil_status'    => 'Married',
                'contact_number'  => '09191234567',
                'email'           => 'pedro.garcia@example.com',
                'address'         => '789 Mabini St, Makati City',
                'created_at'      => date('Y-m-d H:i:s'),
            ],
            [
                'first_name'      => 'Ana',
                'middle_name'     => 'Ramos',
                'last_name'       => 'Cruz',
                'date_of_birth'   => '2002-01-10',
                'sex'             => 'Female',
                'civil_status'    => 'Single',
                'contact_number'  => '09201234567',
                'email'           => 'ana.cruz@example.com',
                'address'         => '1010 Luna St, Cebu City',
                'created_at'      => date('Y-m-d H:i:s'),
            ],
        ];

        // Insert batch into the `patients` table defined by CreatePatientsTable migration
        $this->db->table('patients')->insertBatch($data);
    }
}
