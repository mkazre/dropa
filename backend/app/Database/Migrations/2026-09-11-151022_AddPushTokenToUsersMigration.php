<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPushTokenToUsersMigration extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'push_token' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'phone'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'push_token');
    }
}
