<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLockerRacksMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'property_id'       => ['type' => 'INT', 'unsigned' => true],
            'name'              => ['type' => 'VARCHAR', 'constraint' => 100],
            'location_desc'     => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'hardware_provider' => ['type' => 'ENUM', 'constraint' => ['mock', 'hivebox'], 'default' => 'mock'],
            'external_rack_id'  => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'status'            => ['type' => 'ENUM', 'constraint' => ['online', 'offline', 'maintenance'], 'default' => 'online'],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('property_id', 'properties', 'id', '', 'CASCADE');
        $this->forge->createTable('locker_racks');
    }

    public function down()
    {
        $this->forge->dropTable('locker_racks');
    }
}
