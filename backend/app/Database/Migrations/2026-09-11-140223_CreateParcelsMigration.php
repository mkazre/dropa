<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateParcelsMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'reservation_id'  => ['type' => 'INT', 'unsigned' => true],
            'sender_name'     => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'sender_contact'  => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'pickup_pin'      => ['type' => 'VARCHAR', 'constraint' => 6],
            'qr_token'        => ['type' => 'VARCHAR', 'constraint' => 64],
            'delegate_code'   => ['type' => 'VARCHAR', 'constraint' => 12, 'null' => true],
            'photo_url'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'deposited_at'    => ['type' => 'DATETIME', 'null' => true],
            'collected_at'    => ['type' => 'DATETIME', 'null' => true],
            'status'          => ['type' => 'ENUM', 'constraint' => ['awaiting_deposit', 'awaiting_collection', 'collected'], 'default' => 'awaiting_deposit'],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('qr_token');
        $this->forge->addForeignKey('reservation_id', 'reservations', 'id', '', 'CASCADE');
        $this->forge->createTable('parcels');
    }

    public function down()
    {
        $this->forge->dropTable('parcels');
    }
}
