<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Libraries\ReservationService;
use App\Models\ParcelModel;
use RuntimeException;

class ParcelsController extends BaseApiController
{
    /**
     * Courier/sender endpoint — no account needed, just the deposit code the
     * tenant shared with them.
     */
    public function deposit()
    {
        $depositCode   = $this->request->getJsonVar('deposit_code');
        $senderName    = $this->request->getJsonVar('sender_name');
        $senderContact = $this->request->getJsonVar('sender_contact');

        if (empty($depositCode)) {
            return $this->failValidationErrors('deposit_code is required.');
        }

        try {
            $parcel = (new ReservationService())->deposit($depositCode, $senderName, $senderContact);
        } catch (RuntimeException $e) {
            return $this->fail($e->getMessage());
        }

        return $this->respondCreated($parcel);
    }

    /** Tenant collection — PIN keypad, QR scan, or in-app remote unlock all resolve here. */
    public function collect()
    {
        $code = $this->request->getJsonVar('code');

        if (empty($code)) {
            return $this->failValidationErrors('code is required.');
        }

        try {
            $parcel = (new ReservationService())->collect($code);
        } catch (RuntimeException $e) {
            return $this->fail($e->getMessage());
        }

        return $this->respond($parcel);
    }

    /** Parcels for the whole household (shared unit), not just this login. */
    public function mine()
    {
        return $this->respond(model(ParcelModel::class)->forHousehold($this->householdTenantIds()));
    }

    /** Any household member mints a one-time code so someone else can collect on the household's behalf. */
    public function createDelegateCode($parcelId)
    {
        try {
            $parcel = (new ReservationService())->generateDelegateCode((int) $parcelId, $this->householdTenantIds());
        } catch (RuntimeException $e) {
            return $this->fail($e->getMessage());
        }

        return $this->respond($parcel);
    }
}
