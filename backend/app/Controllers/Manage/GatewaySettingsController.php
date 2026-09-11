<?php

declare(strict_types=1);

namespace App\Controllers\Manage;

use App\Controllers\BaseController;
use App\Models\PaymentGatewaySettingModel;

/**
 * Lets a Body Corporate Admin switch payment methods on/off for their own
 * property only. Merchant credentials are platform-wide (set by the Super
 * Admin) — this screen only ever writes `enabled` (+ manual instructions).
 */
class GatewaySettingsController extends BaseController
{
    private const GATEWAYS = ['ozow', 'payfast', 'manual'];

    public function index()
    {
        $propertyId = auth()->user()->property_id;
        $settings   = model(PaymentGatewaySettingModel::class);
        $enabled    = $settings->enabledForProperty($propertyId);

        $globalDefaults = $settings->where('scope', 'global')->findAll();
        $globalByGateway = array_column($globalDefaults, null, 'gateway');

        $rows = [];
        foreach (self::GATEWAYS as $gateway) {
            $override = $settings->where('scope', 'property')->where('property_id', $propertyId)->where('gateway', $gateway)->first();
            $rows[$gateway] = [
                'enabled'            => $override['enabled'] ?? ($globalByGateway[$gateway]['enabled'] ?? 0),
                'instructions'       => $override['instructions'] ?? ($globalByGateway[$gateway]['instructions'] ?? ''),
                'available_globally' => ! empty($globalByGateway[$gateway]['enabled']),
            ];
        }

        return view('manage/gateways/index', [
            'title'      => 'Payment Settings — Dropa Manage',
            'panelLabel' => 'Body Corporate',
            'nav'        => view('manage/_nav'),
            'gateways'   => $rows,
        ]);
    }

    public function update()
    {
        $propertyId = auth()->user()->property_id;
        $settings   = model(PaymentGatewaySettingModel::class);
        $enabled    = $this->request->getPost('enabled') ?? [];

        foreach (self::GATEWAYS as $gateway) {
            $existing = $settings->where('scope', 'property')->where('property_id', $propertyId)->where('gateway', $gateway)->first();
            $data     = [
                'scope'        => 'property',
                'property_id'  => $propertyId,
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

        $this->audit('gateways.property_update', 'property', (int) $propertyId, ['enabled' => $enabled]);

        return redirect()->to('/manage/gateways')->with('success', 'Payment settings updated for your property.');
    }
}
