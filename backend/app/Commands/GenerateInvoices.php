<?php

declare(strict_types=1);

namespace App\Commands;

use App\Models\PropertyInvoiceModel;
use App\Models\PropertyModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Generates this month's subscription invoice for every property with a
 * monthly fee set, skipping any that already have one. Run monthly:
 * `php spark billing:generate-invoices`.
 */
class GenerateInvoices extends BaseCommand
{
    protected $group       = 'Dropa';
    protected $name        = 'billing:generate-invoices';
    protected $description = "Generate this month's subscription invoices for properties with a monthly fee.";

    public function run(array $params)
    {
        $properties = model(PropertyModel::class)->where('subscription_monthly_fee >', 0)->findAll();
        $invoices   = model(PropertyInvoiceModel::class);
        $period     = date('Y-m-01');

        $created = 0;
        foreach ($properties as $property) {
            if ($invoices->where('property_id', $property['id'])->where('period_month', $period)->first()) {
                continue;
            }
            $invoices->insert([
                'property_id'  => $property['id'],
                'period_month' => $period,
                'amount'       => $property['subscription_monthly_fee'],
                'status'       => 'pending',
            ]);
            $created++;
        }

        CLI::write("{$created} invoice(s) generated for {$period}.", 'green');
    }
}
