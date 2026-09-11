<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Libraries\Hardware\HardwareProviderFactory;
use App\Models\LockerModel;
use App\Models\LockerRackModel;
use App\Models\ParcelModel;
use App\Models\PropertyModel;
use App\Models\ReservationModel;
use RuntimeException;

/**
 * The core of the digital-letterbox flow: reserve a locker ahead of a
 * delivery, deposit a parcel into it with a courier-facing code, then
 * release it back to the pool once the tenant collects.
 */
class ReservationService
{
    public function __construct(
        private ReservationModel $reservations = new ReservationModel(),
        private ParcelModel $parcels = new ParcelModel(),
        private LockerModel $lockers = new LockerModel(),
        private LockerRackModel $racks = new LockerRackModel(),
        private PropertyModel $properties = new PropertyModel(),
    ) {
    }

    /**
     * Tenant taps "Expecting a parcel": find an available locker of the
     * requested size at the tenant's property and hold it.
     */
    public function reserve(int $propertyId, int $tenantId, string $size): array
    {
        $rack = $this->racks->where('property_id', $propertyId)
            ->where('status', 'online')
            ->first();

        if ($rack === null) {
            throw new RuntimeException('No online locker rack at this property.');
        }

        $depositCode = $this->generateCode(6);
        $provider    = HardwareProviderFactory::make($rack['hardware_provider']);
        $externalId  = $provider->reserveCompartment((string) $rack['id'], $size, $depositCode);

        if ($externalId === null) {
            throw new RuntimeException("No {$size} locker is available at this property right now.");
        }

        $property     = $this->properties->find($propertyId);
        $holdHours    = (int) ($property['reservation_hold_hours'] ?? 48);

        $reservationId = $this->reservations->insert([
            'property_id'    => $propertyId,
            'tenant_id'      => $tenantId,
            'locker_id'      => (int) $externalId,
            'size_requested' => $size,
            'deposit_code'   => $depositCode,
            'status'         => 'held',
            'expires_at'     => date('Y-m-d H:i:s', strtotime("+{$holdHours} hours")),
        ], true);

        return $this->reservations->find($reservationId);
    }

    /**
     * Courier at the kiosk/web enters the deposit code: mark the parcel
     * deposited and issue the tenant's pickup PIN + QR token.
     */
    public function deposit(string $depositCode, ?string $senderName, ?string $senderContact): array
    {
        $reservation = $this->reservations->where('deposit_code', $depositCode)
            ->where('status', 'held')
            ->first();

        if ($reservation === null) {
            throw new RuntimeException('Invalid or already-used deposit code.');
        }

        $locker = $this->lockers->find($reservation['locker_id']);
        $rack   = $this->racks->find($locker['rack_id']);
        $provider = HardwareProviderFactory::make($rack['hardware_provider']);

        $pickupPin = $this->generateCode(6);
        $provider->issuePickupCode((string) $rack['id'], (string) $locker['id'], $pickupPin);

        $this->lockers->update($locker['id'], ['status' => 'occupied']);
        $this->reservations->update($reservation['id'], ['status' => 'deposited']);

        $parcelId = $this->parcels->insert([
            'reservation_id' => $reservation['id'],
            'sender_name'    => $senderName,
            'sender_contact' => $senderContact,
            'pickup_pin'     => $pickupPin,
            'qr_token'       => bin2hex(random_bytes(16)),
            'deposited_at'   => date('Y-m-d H:i:s'),
            'status'         => 'awaiting_collection',
        ], true);

        return $this->parcels->find($parcelId);
    }

    /**
     * Tenant collects at the locker (PIN, QR, or in-app unlock): mark the
     * parcel collected and release the locker back to the property's pool.
     */
    public function collect(string $pinOrQrToken): array
    {
        $parcel = $this->parcels->groupStart()
            ->where('pickup_pin', $pinOrQrToken)
            ->orWhere('qr_token', $pinOrQrToken)
            ->groupEnd()
            ->where('status', 'awaiting_collection')
            ->first();

        if ($parcel === null) {
            throw new RuntimeException('Invalid or already-collected pickup code.');
        }

        $reservation = $this->reservations->find($parcel['reservation_id']);
        $locker      = $this->lockers->find($reservation['locker_id']);
        $rack        = $this->racks->find($locker['rack_id']);
        $provider    = HardwareProviderFactory::make($rack['hardware_provider']);

        $provider->openCompartment((string) $rack['id'], (string) $locker['id']);

        $this->parcels->update($parcel['id'], [
            'status'       => 'collected',
            'collected_at' => date('Y-m-d H:i:s'),
        ]);
        $this->lockers->update($locker['id'], ['status' => 'available']);

        return $this->parcels->find($parcel['id']);
    }

    private function generateCode(int $length): string
    {
        return (string) random_int(
            (int) str_pad('1', $length, '0'),
            (int) str_pad('', $length, '9')
        );
    }
}
