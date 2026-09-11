<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LockerModel;
use App\Models\LockerRackModel;
use App\Models\PropertyModel;
use App\Models\WebhooksLogModel;

/** Platform-wide view of every locker rack — status, provider, and occupancy — across every property. */
class HardwareController extends BaseController
{
    public function index()
    {
        $racks         = model(LockerRackModel::class)->orderBy('property_id', 'ASC')->findAll();
        $propertiesById = array_column(model(PropertyModel::class)->findAll(), null, 'id');

        $lockerCounts = [];
        foreach ($racks as $rack) {
            $byStatus = model(LockerModel::class)->select('status, COUNT(*) AS count')
                ->where('rack_id', $rack['id'])
                ->groupBy('status')
                ->findAll();
            $lockerCounts[$rack['id']] = array_column($byStatus, 'count', 'status');
        }

        $lastWebhookByProvider = [];
        foreach (['hivebox', 'ozow', 'payfast'] as $provider) {
            $last = model(WebhooksLogModel::class)->where('provider', $provider)->orderBy('id', 'DESC')->first();
            $lastWebhookByProvider[$provider] = $last['created_at'] ?? null;
        }

        return view('admin/hardware/index', [
            'title'       => 'Hardware — Dropa Admin',
            'panelLabel'  => 'Super Admin',
            'nav'         => view('admin/_nav'),
            'racks'       => $racks,
            'properties'  => $propertiesById,
            'lockerCounts' => $lockerCounts,
            'lastWebhook' => $lastWebhookByProvider,
        ]);
    }
}
