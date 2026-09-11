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
            $row = $settings->where('scope', 'global')->where('gateway', $gateway)->first()
                ?? ['gateway' => $gateway, 'enabled' => 0, 'instructions' => '', 'credentials' => null];
            $row['credentials'] = json_decode($row['credentials'] ?? '{}', true) ?: [];
            $rows[$gateway]     = $row;
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

        $credentials = [
            'ozow' => [
                'site_code'   => $this->request->getPost('ozow_site_code'),
                'private_key' => $this->request->getPost('ozow_private_key'),
                'is_test'     => $this->request->getPost('ozow_is_test') ? 'true' : 'false',
            ],
            'payfast' => [
                'merchant_id'  => $this->request->getPost('payfast_merchant_id'),
                'merchant_key' => $this->request->getPost('payfast_merchant_key'),
                'passphrase'   => $this->request->getPost('payfast_passphrase'),
                'sandbox'      => $this->request->getPost('payfast_sandbox') ? 'true' : 'false',
            ],
            'manual' => [],
        ];

        foreach (self::GATEWAYS as $gateway) {
            $existing = $settings->where('scope', 'global')->where('gateway', $gateway)->first();
            $data     = [
                'scope'        => 'global',
                'gateway'      => $gateway,
                'enabled'      => in_array($gateway, $enabled, true) ? 1 : 0,
                'instructions' => $gateway === 'manual' ? $this->request->getPost('manual_instructions') : null,
                'credentials'  => json_encode($credentials[$gateway]),
            ];

            if ($existing) {
                $settings->update($existing['id'], $data);
            } else {
                $settings->insert($data);
            }
        }

        $this->audit('gateways.update', 'payment_gateway_settings', null, ['enabled' => $enabled]);

        return redirect()->to('/admin/gateways')->with('success', 'Payment gateway settings updated.');
    }
}
