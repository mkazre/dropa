<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PricingRuleModel;

/** Platform-default pricing — what a property inherits unless it sets its own override. */
class PricingController extends BaseController
{
    private const SIZES = ['S', 'M', 'L', 'XL'];

    public function index()
    {
        $rules = model(PricingRuleModel::class)->where('property_id', null)->findAll();
        $bySize = array_column($rules, null, 'size');

        return view('admin/pricing/index', [
            'title'      => 'Pricing — Dropa Admin',
            'panelLabel' => 'Super Admin',
            'nav'        => view('admin/_nav'),
            'rules'      => $bySize,
            'sizes'      => self::SIZES,
        ]);
    }

    public function update()
    {
        $model = model(PricingRuleModel::class);

        foreach (self::SIZES as $size) {
            $data = [
                'property_id' => null,
                'size'        => $size,
                'free_hours'  => (int) $this->request->getPost("free_hours_{$size}"),
                'daily_rate'  => (float) $this->request->getPost("daily_rate_{$size}"),
            ];

            $existing = $model->where('property_id', null)->where('size', $size)->first();
            if ($existing) {
                $model->update($existing['id'], $data);
            } else {
                $model->insert($data);
            }
        }

        return redirect()->to('/admin/pricing')->with('success', 'Platform default pricing updated.');
    }
}
