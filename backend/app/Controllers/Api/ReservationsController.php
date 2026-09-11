<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Libraries\Hardware\HardwareProviderFactory;
use App\Libraries\ReservationService;
use App\Models\LockerModel;
use App\Models\LockerRackModel;
use App\Models\ReservationModel;
use App\Models\UnitModel;
use CodeIgniter\Shield\Models\UserModel;
use RuntimeException;

class ReservationsController extends BaseApiController
{
    /**
     * Tenant taps "Expecting a parcel" and picks a size — or, for a
     * peer-to-peer send, names a neighbour's unit to reserve it for them
     * instead (the sender is dropping it off in person, so no deposit code
     * needs to change hands).
     */
    public function create()
    {
        $user               = $this->currentUser();
        $size               = $this->request->getJsonVar('size');
        $recipientUnitNumber = $this->request->getJsonVar('recipient_unit_number');

        if (empty($user->property_id)) {
            return $this->failForbidden('Your account is not linked to a property yet.');
        }
        if (! in_array($size, ['S', 'M', 'L', 'XL'], true)) {
            return $this->failValidationErrors('size must be one of S, M, L, XL.');
        }

        $recipientId = null;
        if (! empty($recipientUnitNumber)) {
            $unit = model(UnitModel::class)->where('property_id', $user->property_id)->where('unit_number', $recipientUnitNumber)->first();
            $recipient = $unit ? (new UserModel())->where('unit_id', $unit['id'])->first() : null;

            if ($recipient === null) {
                return $this->fail("No resident found for unit {$recipientUnitNumber}.");
            }
            $recipientId = (int) $recipient->id;
        }

        try {
            $reservation = (new ReservationService())->reserve((int) $user->property_id, (int) $user->id, $size, $recipientId);
        } catch (RuntimeException $e) {
            return $this->fail($e->getMessage());
        }

        return $this->respondCreated($reservation);
    }

    /** Reservations for the whole household (shared unit), not just this login. */
    public function mine()
    {
        return $this->respond(model(ReservationModel::class)->forHousehold($this->householdTenantIds()));
    }

    public function cancel($id)
    {
        $user        = $this->currentUser();
        $reservations = model(ReservationModel::class);
        $reservation = $reservations->find($id);

        $isOwner = $reservation !== null && (
            (int) $reservation['tenant_id'] === (int) $user->id
            || (int) ($reservation['created_by'] ?? 0) === (int) $user->id
        );
        if (! $isOwner) {
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
