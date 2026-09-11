<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLockersMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                      => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'rack_id'                 => ['type' => 'INT', 'unsigned' => true],
            'label'                   => ['type' => 'VARCHAR', 'constraint' => 20],
            'size'                    => ['type' => 'ENUM', 'constraint' => ['S', 'M', 'L', 'XL'], 'default' => 'M'],
            'external_compartment_id' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'status'                  => ['type' => 'ENUM', 'constraint' => ['available', 'reserved', 'occupied', 'out_of_service'], 'default' => 'available'],
            'created_at'              => ['type' => 'DATETIME', 'null' => true],
            'updated_at'              => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['rack_id', 'label']);
        $this->forge->addForeignKey('rack_id', 'locker_racks', 'id', '', 'CASCADE');
        $this->forge->createTable('lockers');
    }

    public function down()
    {
        $this->forge->dropTable('lockers');
    }
}
