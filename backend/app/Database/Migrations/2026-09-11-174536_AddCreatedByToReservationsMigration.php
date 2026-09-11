<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCreatedByToReservationsMigration extends Migration
{
    public function up()
    {
        $this->forge->addColumn('reservations', [
            'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'tenant_id'],
        ]);
        $this->forge->addForeignKey('created_by', 'users', 'id', '', 'SET NULL', '', 'reservations');
        $this->forge->processIndexes('reservations');
    }

    public function down()
    {
        $this->forge->dropForeignKey('reservations', 'reservations_created_by_foreign');
        $this->forge->dropColumn('reservations', 'created_by');
    }
}
