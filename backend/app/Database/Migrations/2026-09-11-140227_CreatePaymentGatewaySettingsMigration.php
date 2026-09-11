<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePaymentGatewaySettingsMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'scope'       => ['type' => 'ENUM', 'constraint' => ['global', 'property'], 'default' => 'global'],
            'property_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'gateway'     => ['type' => 'ENUM', 'constraint' => ['ozow', 'payfast', 'manual']],
            'enabled'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'credentials' => ['type' => 'TEXT', 'null' => true],
            'instructions' => ['type' => 'TEXT', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['scope', 'property_id', 'gateway']);
        $this->forge->addForeignKey('property_id', 'properties', 'id', '', 'CASCADE');
        $this->forge->createTable('payment_gateway_settings');
    }

    public function down()
    {
        $this->forge->dropTable('payment_gateway_settings');
    }
}
