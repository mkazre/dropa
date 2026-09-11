<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ParcelModel;
use App\Models\PropertyModel;
use App\Models\ReservationModel;

class DashboardController extends BaseController
{
    public function index()
    {
        return view('admin/dashboard', [
            'title'       => 'Dashboard — Dropa Admin',
            'panelLabel'  => 'Super Admin',
            'nav'         => view('admin/_nav'),
            'properties'  => model(PropertyModel::class)->countAll(),
            'reservations' => model(ReservationModel::class)->countAll(),
            'parcelsAwaiting' => model(ParcelModel::class)->where('status', 'awaiting_collection')->countAllResults(),
            'parcelsCollected' => model(ParcelModel::class)->where('status', 'collected')->countAllResults(),
        ]);
    }
}
