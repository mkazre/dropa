<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMaintenanceTicketsMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'property_id' => ['type' => 'INT', 'unsigned' => true],
            'locker_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'raised_by'   => ['type' => 'INT', 'unsigned' => true],
            'issue'       => ['type' => 'VARCHAR', 'constraint' => 255],
            'status'      => ['type' => 'ENUM', 'constraint' => ['open', 'in_progress', 'resolved'], 'default' => 'open'],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('property_id', 'properties', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('locker_id', 'lockers', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('raised_by', 'users', 'id', '', 'CASCADE');
        $this->forge->createTable('maintenance_tickets');
    }

    public function down()
    {
        $this->forge->dropTable('maintenance_tickets');
    }
}
