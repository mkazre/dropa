<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Libraries\Hardware\HardwareProviderFactory;
use App\Libraries\ReservationService;
use App\Models\LockerModel;
use App\Models\LockerRackModel;
use App\Models\ReservationModel;
use RuntimeException;

class ReservationsController extends BaseApiController
{
    /** Tenant taps "Expecting a parcel" and picks a size. */
    public function create()
    {
        $user = $this->currentUser();
        $size = $this->request->getJsonVar('size');

        if (empty($user->property_id)) {
            return $this->failForbidden('Your account is not linked to a property yet.');
        }
        if (! in_array($size, ['S', 'M', 'L', 'XL'], true)) {
            return $this->failValidationErrors('size must be one of S, M, L, XL.');
        }

        try {
            $reservation = (new ReservationService())->reserve((int) $user->property_id, (int) $user->id, $size);
        } catch (RuntimeException $e) {
            return $this->fail($e->getMessage());
        }

        return $this->respondCreated($reservation);
    }

    public function mine()
    {
        $user = $this->currentUser();

        return $this->respond(
            model(ReservationModel::class)->where('tenant_id', $user->id)->orderBy('id', 'DESC')->findAll()
        );
    }

    public function cancel($id)
    {
        $user        = $this->currentUser();
        $reservations = model(ReservationModel::class);
        $reservation = $reservations->find($id);

        if ($reservation === null || (int) $reservation['tenant_id'] !== (int) $user->id) {
            return $this->failNotFound();
        }
        if ($reservation['status'] !== 'held') {
            return $this->fail('Only a held (not yet deposited) reservation can be cancelled.');
        }

        if ($reservation['locker_id']) {
            $locker = model(LockerModel::class)->find($reservation['locker_id']);
            $rack   = model(LockerRackModel::class)->find($locker['rack_id']);
            HardwareProviderFactory::make($rack['hardware_provider'])
                ->cancelReservation((string) $rack['id'], (string) $locker['id']);
        }

        $reservations->update($id, ['status' => 'cancelled']);

        return $this->respondDeleted(['id' => $id]);
    }
}
