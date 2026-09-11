<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Libraries\ReservationService;
use App\Models\ParcelModel;
use App\Models\ReservationModel;
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

    public function mine()
    {
        $user         = $this->currentUser();
        $reservations = model(ReservationModel::class)->where('tenant_id', $user->id)->findColumn('id') ?? [];

        if (empty($reservations)) {
            return $this->respond([]);
        }

        return $this->respond(
            model(ParcelModel::class)->whereIn('reservation_id', $reservations)->orderBy('id', 'DESC')->findAll()
        );
    }
}
