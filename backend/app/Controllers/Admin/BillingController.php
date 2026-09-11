<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PropertyInvoiceModel;
use App\Models\PropertyModel;

/** Per-property SaaS subscription: a monthly fee, generated invoices, mark-as-paid. */
class BillingController extends BaseController
{
    public function index()
    {
        $properties = model(PropertyModel::class)->orderBy('name', 'ASC')->findAll();
        $invoices   = model(PropertyInvoiceModel::class)->orderBy('period_month', 'DESC')->orderBy('property_id', 'ASC')->findAll();
        $propertiesById = array_column($properties, null, 'id');

        return view('admin/billing/index', [
            'title'      => 'Billing — Dropa Admin',
            'panelLabel' => 'Super Admin',
            'nav'        => view('admin/_nav'),
            'properties' => $properties,
            'invoices'   => $invoices,
            'propertiesById' => $propertiesById,
        ]);
    }

    public function setFee($propertyId)
    {
        $fee = (float) $this->request->getPost('subscription_monthly_fee');
        model(PropertyModel::class)->update($propertyId, ['subscription_monthly_fee' => $fee]);
        $this->audit('billing.set_fee', 'property', (int) $propertyId, ['fee' => $fee]);

        return redirect()->to('/admin/billing')->with('success', 'Subscription fee updated.');
    }

    /** Generates this month's invoice for every property with a fee > 0 that doesn't already have one. */
    public function generateInvoices()
    {
        $properties = model(PropertyModel::class)->where('subscription_monthly_fee >', 0)->findAll();
        $invoices   = model(PropertyInvoiceModel::class);
        $period     = date('Y-m-01');

        $created = 0;
        foreach ($properties as $property) {
            $exists = $invoices->where('property_id', $property['id'])->where('period_month', $period)->first();
            if ($exists) {
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

        $this->audit('billing.generate_invoices', 'property_invoices', null, ['period' => $period, 'created' => $created]);

        return redirect()->to('/admin/billing')->with('success', "{$created} invoice(s) generated for {$period}.");
    }

    public function markPaid($invoiceId)
    {
        model(PropertyInvoiceModel::class)->update($invoiceId, ['status' => 'paid', 'paid_at' => date('Y-m-d H:i:s')]);
        $this->audit('billing.mark_paid', 'property_invoice', (int) $invoiceId);

        return redirect()->to('/admin/billing')->with('success', 'Invoice marked as paid.');
    }
}
