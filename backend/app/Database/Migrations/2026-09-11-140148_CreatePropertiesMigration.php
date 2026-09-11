<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePropertiesMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                 => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'               => ['type' => 'VARCHAR', 'constraint' => 150],
            'type'               => ['type' => 'ENUM', 'constraint' => ['complex', 'public_site'], 'default' => 'complex'],
            'address'            => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'lat'                => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true],
            'lng'                => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true],
            'logo_url'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'brand_color'        => ['type' => 'VARCHAR', 'constraint' => 7, 'null' => true],
            'reservation_hold_hours' => ['type' => 'INT', 'unsigned' => true, 'default' => 48],
            'status'             => ['type' => 'ENUM', 'constraint' => ['pending', 'active', 'suspended'], 'default' => 'pending'],
            'created_at'         => ['type' => 'DATETIME', 'null' => true],
            'updated_at'         => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('properties');
    }

    public function down()
    {
        $this->forge->dropTable('properties');
    }
}
