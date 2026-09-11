<?php

declare(strict_types=1);

namespace App\Libraries\Hardware;

use App\Models\LockerModel;

/**
 * Pure-software simulation of a locker rack. Powers demos, local dev and any
 * property that hasn't had hardware installed yet — the rest of the app
 * behaves identically whether a rack is Mock or a real Hive-Box unit.
 */
class MockHardwareProvider implements HardwareProviderInterface
{
    public function reserveCompartment(string $externalRackId, string $size, string $depositCode): ?string
    {
        $lockers = model(LockerModel::class);
        $locker  = $lockers->where('rack_id', $externalRackId)
            ->where('size', $size)
            ->where('status', 'available')
            ->first();

        if ($locker === null) {
            return null;
        }

        $lockers->update($locker['id'], ['status' => 'reserved']);

        return (string) $locker['id'];
    }

    public function cancelReservation(string $externalRackId, string $externalCompartmentId): bool
    {
        return model(LockerModel::class)->update((int) $externalCompartmentId, ['status' => 'available']);
    }

    public function issuePickupCode(string $externalRackId, string $externalCompartmentId, string $pickupPin): bool
    {
        // Mock hardware has no on-device code store; the pickup PIN lives in
        // the `parcels` table and is checked by our own kiosk/API endpoints.
        return true;
    }

    public function openCompartment(string $externalRackId, string $externalCompartmentId): bool
    {
        return true;
    }
}
