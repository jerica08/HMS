<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MigrateDepartmentsToMedicalNonMedical extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        
        // Check if the original department table exists and new tables exist
        if ($db->tableExists('department') && $db->tableExists('medical_departments') && $db->tableExists('non_medical_departments')) {
            
            // Migrate medical departments
            $builder = $db->table('department')
                ->select('name, code, type, floor, department_head_id, contact_number, description, status, created_at, updated_at')
                ->where('type', 'Clinical')
                ->orWhere('type', 'Emergency')
                ->orWhere('type', 'Diagnostic');
                
            $medicalDepts = $builder->get()->getResultArray();
            
            foreach ($medicalDepts as $dept) {
                $db->table('medical_departments')->insert([
                    'name' => $dept['name'],
                    'code' => $dept['code'],
                    'specialty' => $this->getSpecialtyFromType($dept['type']),
                    'floor' => $dept['floor'],
                    'department_head_id' => $dept['department_head_id'],
                    'contact_number' => $dept['contact_number'],
                    'description' => $dept['description'],
                    'status' => $dept['status'],
                    'created_at' => $dept['created_at'],
                    'updated_at' => $dept['updated_at']
                ]);
            }
            
            // Migrate non-medical departments
            $builder = $db->table('department')
                ->select('name, code, type, floor, department_head_id, contact_number, description, status, created_at, updated_at')
                ->where('type', 'Administrative');
                
            $nonMedicalDepts = $builder->get()->getResultArray();
            
            foreach ($nonMedicalDepts as $dept) {
                $db->table('non_medical_departments')->insert([
                    'name' => $dept['name'],
                    'code' => $dept['code'],
                    'function' => $dept['type'],
                    'floor' => $dept['floor'],
                    'department_head_id' => $dept['department_head_id'],
                    'contact_number' => $dept['contact_number'],
                    'description' => $dept['description'],
                    'status' => $dept['status'],
                    'created_at' => $dept['created_at'],
                    'updated_at' => $dept['updated_at']
                ]);
            }
        }
    }
    
    private function getSpecialtyFromType($type)
    {
        switch ($type) {
            case 'Clinical':
                return 'General Medicine';
            case 'Emergency':
                return 'Emergency Medicine';
            case 'Diagnostic':
                return 'Diagnostic Services';
            default:
                return 'General Medicine';
        }
    }

    public function down()
    {
        // This migration is not easily reversible since we're splitting data
        // The original department table should be kept for reference
    }
}
