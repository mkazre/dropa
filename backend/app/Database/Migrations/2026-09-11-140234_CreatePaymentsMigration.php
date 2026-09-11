<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePaymentsMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                  => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'reservation_id'      => ['type' => 'INT', 'unsigned' => true],
            'method'              => ['type' => 'ENUM', 'constraint' => ['ozow', 'payfast', 'manual']],
            'amount'              => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'status'              => ['type' => 'ENUM', 'constraint' => ['pending', 'paid', 'failed', 'refunded'], 'default' => 'pending'],
            'gateway_ref'         => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'proof_of_payment_url' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'approved_by'         => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('reservation_id', 'reservations', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('approved_by', 'users', 'id', '', 'SET NULL');
        $this->forge->createTable('payments');
    }

    public function down()
    {
        $this->forge->dropTable('payments');
    }
}
