<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWebhooksLogMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'provider'   => ['type' => 'VARCHAR', 'constraint' => 40],
            'event'      => ['type' => 'VARCHAR', 'constraint' => 60],
            'payload'    => ['type' => 'TEXT'],
            'processed'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('webhooks_log');
    }

    public function down()
    {
        $this->forge->dropTable('webhooks_log');
    }
}
