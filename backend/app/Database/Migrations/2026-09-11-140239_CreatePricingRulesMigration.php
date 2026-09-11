<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePricingRulesMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'property_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'size'        => ['type' => 'ENUM', 'constraint' => ['S', 'M', 'L', 'XL']],
            'free_hours'  => ['type' => 'INT', 'unsigned' => true, 'default' => 48],
            'daily_rate'  => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['property_id', 'size']);
        $this->forge->addForeignKey('property_id', 'properties', 'id', '', 'CASCADE');
        $this->forge->createTable('pricing_rules');
    }

    public function down()
    {
        $this->forge->dropTable('pricing_rules');
    }
}
