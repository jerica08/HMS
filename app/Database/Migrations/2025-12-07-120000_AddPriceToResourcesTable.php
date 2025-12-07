<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPriceToResourcesTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if ($db->tableExists('resources')) {
            // Add price field for medications
            if (!$db->fieldExists('price', 'resources')) {
                $fields = [
                    'price' => [
                        'type'       => 'DECIMAL',
                        'constraint' => '10,2',
                        'null'       => true,
                        'default'    => null,
                        'after'      => 'expiry_date',
                    ],
                ];
                $this->forge->addColumn('resources', $fields);
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        if ($db->tableExists('resources')) {
            // Remove price field if it exists
            if ($db->fieldExists('price', 'resources')) {
                $this->forge->dropColumn('resources', 'price');
            }
        }
    }
}

