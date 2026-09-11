<?php

declare(strict_types=1);

namespace App\Controllers\Manage;

use App\Controllers\BaseController;
use App\Models\PricingRuleModel;

/**
 * A property can override the platform default per size, or leave a size
 * blank to keep inheriting the default (PricingRuleModel::forPropertyAndSize
 * already falls back automatically).
 */
class PricingController extends BaseController
{
    private const SIZES = ['S', 'M', 'L', 'XL'];

    public function index()
    {
        $propertyId = auth()->user()->property_id;
        $model      = model(PricingRuleModel::class);

        $defaults = array_column($model->where('property_id', null)->findAll(), null, 'size');
        $overrides = array_column($model->where('property_id', $propertyId)->findAll(), null, 'size');

        return view('manage/pricing/index', [
            'title'      => 'Pricing — Dropa Manage',
            'panelLabel' => 'Body Corporate',
            'nav'        => view('manage/_nav'),
            'defaults'   => $defaults,
            'overrides'  => $overrides,
            'sizes'      => self::SIZES,
        ]);
    }

    public function update()
    {
        $propertyId = auth()->user()->property_id;
        $model      = model(PricingRuleModel::class);

        foreach (self::SIZES as $size) {
            $freeHours = $this->request->getPost("free_hours_{$size}");
            $dailyRate = $this->request->getPost("daily_rate_{$size}");
            $existing  = $model->where('property_id', $propertyId)->where('size', $size)->first();

            if ($freeHours === '' && $dailyRate === '') {
                // Blank means "use the platform default" — remove any override.
                if ($existing) {
                    $model->delete($existing['id']);
                }
                continue;
            }

            $data = [
                'property_id' => $propertyId,
                'size'        => $size,
                'free_hours'  => (int) $freeHours,
                'daily_rate'  => (float) $dailyRate,
            ];

            if ($existing) {
                $model->update($existing['id'], $data);
            } else {
                $model->insert($data);
            }
        }

        return redirect()->to('/manage/pricing')->with('success', 'Pricing updated for your property.');
    }
}
