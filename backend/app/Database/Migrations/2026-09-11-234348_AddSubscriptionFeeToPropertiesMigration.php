<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSubscriptionFeeToPropertiesMigration extends Migration
{
    public function up()
    {
        $this->forge->addColumn('properties', [
            'subscription_monthly_fee' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0, 'after' => 'status'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('properties', 'subscription_monthly_fee');
    }
}
