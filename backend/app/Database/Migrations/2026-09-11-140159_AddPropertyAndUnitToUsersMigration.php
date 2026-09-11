<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPropertyAndUnitToUsersMigration extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'property_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'id'],
            'unit_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'property_id'],
            'full_name'   => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'after' => 'unit_id'],
            'phone'       => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true, 'after' => 'full_name'],
        ]);
        $this->forge->addForeignKey('property_id', 'properties', 'id', '', 'SET NULL', '', 'users');
        $this->forge->addForeignKey('unit_id', 'units', 'id', '', 'SET NULL', '', 'users');
        $this->forge->processIndexes('users');
    }

    public function down()
    {
        $this->forge->dropForeignKey('users', 'users_property_id_foreign');
        $this->forge->dropForeignKey('users', 'users_unit_id_foreign');
        $this->forge->dropColumn('users', ['property_id', 'unit_id', 'full_name', 'phone']);
    }
}
