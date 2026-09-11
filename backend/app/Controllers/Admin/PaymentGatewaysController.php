<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PaymentGatewaySettingModel;

class PaymentGatewaysController extends BaseController
{
    private const GATEWAYS = ['ozow', 'payfast', 'manual'];

    public function index()
    {
        $settings = model(PaymentGatewaySettingModel::class);
        $rows     = [];
        foreach (self::GATEWAYS as $gateway) {
            $rows[$gateway] = $settings->where('scope', 'global')->where('gateway', $gateway)->first()
                ?? ['gateway' => $gateway, 'enabled' => 0, 'instructions' => ''];
        }

        return view('admin/gateways/index', [
            'title'      => 'Payment Gateways — Dropa Admin',
            'panelLabel' => 'Super Admin',
            'nav'        => view('admin/_nav'),
            'gateways'   => $rows,
        ]);
    }

    public function update()
    {
        $settings = model(PaymentGatewaySettingModel::class);
        $enabled  = $this->request->getPost('enabled') ?? [];

        foreach (self::GATEWAYS as $gateway) {
            $existing = $settings->where('scope', 'global')->where('gateway', $gateway)->first();
            $data     = [
                'scope'        => 'global',
                'gateway'      => $gateway,
                'enabled'      => in_array($gateway, $enabled, true) ? 1 : 0,
                'instructions' => $gateway === 'manual' ? $this->request->getPost('manual_instructions') : null,
            ];

            if ($existing) {
                $settings->update($existing['id'], $data);
            } else {
                $settings->insert($data);
            }
        }

        return redirect()->to('/admin/gateways')->with('success', 'Payment gateway settings updated.');
    }
}
