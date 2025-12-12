<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPaymentTypeToPatientsTable extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('patients') || $this->db->fieldExists('payment_type', 'patients')) {
            return;
        }

        $fields = [
            'payment_type' => [
                'type'       => 'ENUM',
                'constraint' => ['Cash', 'HMO / Insurance'],
                'default'    => 'Cash',
                'null'       => false,
                'after'      => 'patient_type',
            ],
        ];

        $this->forge->addColumn('patients', $fields);
    }

    public function down()
    {
        if (! $this->db->tableExists('patients') || ! $this->db->fieldExists('payment_type', 'patients')) {
            return;
        }

        $this->forge->dropColumn('patients', 'payment_type');
    }
}
