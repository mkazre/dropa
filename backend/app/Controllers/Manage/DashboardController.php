<?php

declare(strict_types=1);

namespace App\Controllers\Manage;

use App\Controllers\BaseController;
use App\Models\ParcelModel;
use App\Models\ReservationModel;
use App\Models\UnitModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $propertyId = auth()->user()->property_id;

        $reservationIds = model(ReservationModel::class)->where('property_id', $propertyId)->findColumn('id') ?? [];

        return view('manage/dashboard', [
            'title'       => 'Dashboard — Dropa Manage',
            'panelLabel'  => 'Body Corporate',
            'nav'         => view('manage/_nav'),
            'units'       => model(UnitModel::class)->where('property_id', $propertyId)->countAllResults(),
            'reservations' => count($reservationIds),
            'awaiting'    => empty($reservationIds) ? 0 : model(ParcelModel::class)->whereIn('reservation_id', $reservationIds)->where('status', 'awaiting_collection')->countAllResults(),
        ]);
    }
}
