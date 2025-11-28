<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateInsuranceClaimsForPatients extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('insurance_claims')) {
            return;
        }

        $fields = [];

        // Generic linkage columns so a claim can belong to either inpatient or outpatient
        if (! $this->db->fieldExists('patient_id', 'insurance_claims')) {
            $fields['patient_id'] = [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'id',
            ];
        }

        if (! $this->db->fieldExists('admission_id', 'insurance_claims')) {
            $fields['admission_id'] = [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'patient_id',
            ];
        }

        if (! $this->db->fieldExists('visit_id', 'insurance_claims')) {
            $fields['visit_id'] = [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'admission_id',
            ];
        }

        if (! $this->db->fieldExists('claim_source', 'insurance_claims')) {
            $fields['claim_source'] = [
                'type'       => 'ENUM',
                'constraint' => ['inpatient', 'outpatient'],
                'null'       => true,
                'after'      => 'visit_id',
            ];
        }

        if (! empty($fields)) {
            $this->forge->addColumn('insurance_claims', $fields);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('insurance_claims')) {
            return;
        }

        $drop = [];
        foreach (['claim_source', 'visit_id', 'admission_id', 'patient_id'] as $field) {
            if ($this->db->fieldExists($field, 'insurance_claims')) {
                $drop[] = $field;
            }
        }

        if (! empty($drop)) {
            $this->forge->dropColumn('insurance_claims', $drop);
        }
    }
}
