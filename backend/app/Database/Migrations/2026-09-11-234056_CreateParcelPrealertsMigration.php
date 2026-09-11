<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateParcelPrealertsMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'property_id'     => ['type' => 'INT', 'unsigned' => true],
            'tenant_id'       => ['type' => 'INT', 'unsigned' => true],
            'courier'         => ['type' => 'VARCHAR', 'constraint' => 60],
            'tracking_number' => ['type' => 'VARCHAR', 'constraint' => 100],
            'size'            => ['type' => 'ENUM', 'constraint' => ['S', 'M', 'L', 'XL'], 'default' => 'M'],
            'status'          => ['type' => 'ENUM', 'constraint' => ['watching', 'out_for_delivery', 'reserved', 'cancelled'], 'default' => 'watching'],
            'reservation_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('property_id', 'properties', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('tenant_id', 'users', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('reservation_id', 'reservations', 'id', '', 'SET NULL');
        $this->forge->createTable('parcel_prealerts');
    }

    public function down()
    {
        $this->forge->dropTable('parcel_prealerts');
    }
}
