<?php

declare(strict_types=1);

namespace App\Libraries\Hardware;

/**
 * A locker rack's hardware provider — the boundary between our domain
 * (reservations/parcels) and whatever controls the physical doors.
 *
 * Every reservation/deposit/collection flows through here so the rest of
 * the app never needs to know whether a rack is a Mock simulator or a
 * real Hive-Box unit.
 */
interface HardwareProviderInterface
{
    /**
     * Ask the rack to hold a compartment of the given size for a reservation.
     * Returns the external compartment id it assigned, or null if none free.
     */
    public function reserveCompartment(string $externalRackId, string $size, string $depositCode): ?string;

    /**
     * Release a held-but-never-used compartment back to the rack's pool.
     */
    public function cancelReservation(string $externalRackId, string $externalCompartmentId): bool;

    /**
     * Tell the rack a pickup PIN/QR is now valid for a deposited parcel.
     */
    public function issuePickupCode(string $externalRackId, string $externalCompartmentId, string $pickupPin): bool;

    /**
     * Ask the rack to open a compartment door remotely (e.g. in-app unlock).
     */
    public function openCompartment(string $externalRackId, string $externalCompartmentId): bool;
}
