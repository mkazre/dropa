<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePropertyInvoicesMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'property_id'  => ['type' => 'INT', 'unsigned' => true],
            'period_month' => ['type' => 'DATE'],
            'amount'       => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'status'       => ['type' => 'ENUM', 'constraint' => ['pending', 'paid'], 'default' => 'pending'],
            'paid_at'      => ['type' => 'DATETIME', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['property_id', 'period_month']);
        $this->forge->addForeignKey('property_id', 'properties', 'id', '', 'CASCADE');
        $this->forge->createTable('property_invoices');
    }

    public function down()
    {
        $this->forge->dropTable('property_invoices');
    }
}
