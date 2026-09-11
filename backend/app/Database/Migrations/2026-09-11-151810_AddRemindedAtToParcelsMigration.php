<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRemindedAtToParcelsMigration extends Migration
{
    public function up()
    {
        $this->forge->addColumn('parcels', [
            'reminded_at' => ['type' => 'DATETIME', 'null' => true, 'after' => 'collected_at'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('parcels', 'reminded_at');
    }
}
