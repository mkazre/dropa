<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOnboardingRequestsMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'property_name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'unit_count'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'contact_name'  => ['type' => 'VARCHAR', 'constraint' => 150],
            'email'         => ['type' => 'VARCHAR', 'constraint' => 150],
            'status'        => ['type' => 'ENUM', 'constraint' => ['new', 'contacted', 'converted', 'dismissed'], 'default' => 'new'],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('onboarding_requests');
    }

    public function down()
    {
        $this->forge->dropTable('onboarding_requests');
    }
}
