<?php

declare(strict_types=1);

namespace App\Controllers\Manage;

use App\Controllers\BaseController;
use App\Models\ParcelModel;
use App\Models\ReservationModel;

class ParcelsController extends BaseController
{
    public function index()
    {
        $propertyId     = auth()->user()->property_id;
        $reservations   = model(ReservationModel::class)->where('property_id', $propertyId)->findAll();
        $reservationIds = array_column($reservations, 'id');
        $reservationsById = array_column($reservations, null, 'id');

        $parcels = empty($reservationIds)
            ? []
            : model(ParcelModel::class)->whereIn('reservation_id', $reservationIds)->orderBy('id', 'DESC')->findAll();

        return view('manage/parcels/index', [
            'title'      => 'Parcels — Dropa Manage',
            'panelLabel' => 'Body Corporate',
            'nav'        => view('manage/_nav'),
            'parcels'    => $parcels,
            'reservationsById' => $reservationsById,
        ]);
    }
}
