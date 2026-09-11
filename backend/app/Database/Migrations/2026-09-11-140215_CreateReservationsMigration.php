<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReservationsMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'property_id'    => ['type' => 'INT', 'unsigned' => true],
            'tenant_id'      => ['type' => 'INT', 'unsigned' => true],
            'locker_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'size_requested' => ['type' => 'ENUM', 'constraint' => ['S', 'M', 'L', 'XL']],
            'deposit_code'   => ['type' => 'VARCHAR', 'constraint' => 12],
            'status'         => ['type' => 'ENUM', 'constraint' => ['pending', 'held', 'deposited', 'expired', 'cancelled'], 'default' => 'held'],
            'expires_at'     => ['type' => 'DATETIME'],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('deposit_code');
        $this->forge->addForeignKey('property_id', 'properties', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('tenant_id', 'users', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('locker_id', 'lockers', 'id', '', 'SET NULL');
        $this->forge->createTable('reservations');
    }

    public function down()
    {
        $this->forge->dropTable('reservations');
    }
}
