<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\MaintenanceTicketModel;
use App\Models\ParcelModel;
use App\Models\ReservationModel;

class MaintenanceController extends BaseApiController
{
    /** Tenant reports a problem — optionally tied to the locker behind a specific parcel. */
    public function create()
    {
        $user   = $this->currentUser();
        $issue  = trim((string) $this->request->getJsonVar('issue'));
        $parcelId = $this->request->getJsonVar('parcel_id');

        if ($issue === '') {
            return $this->failValidationErrors('Please describe the problem.');
        }
        if (empty($user->property_id)) {
            return $this->failForbidden('Your account is not linked to a property yet.');
        }

        $lockerId = null;
        if ($parcelId) {
            $parcel = model(ParcelModel::class)->find($parcelId);
            if ($parcel !== null) {
                $reservation = model(ReservationModel::class)->find($parcel['reservation_id']);
                $lockerId    = $reservation['locker_id'] ?? null;
            }
        }

        $ticketId = model(MaintenanceTicketModel::class)->insert([
            'property_id' => $user->property_id,
            'locker_id'   => $lockerId,
            'raised_by'   => $user->id,
            'issue'       => $issue,
            'status'      => 'open',
        ], true);

        return $this->respondCreated(model(MaintenanceTicketModel::class)->find($ticketId));
    }

    /** The tenant's own reported tickets, so they can see whether it's been looked at. */
    public function mine()
    {
        return $this->respond(
            model(MaintenanceTicketModel::class)->where('raised_by', $this->currentUser()->id)->orderBy('id', 'DESC')->findAll()
        );
    }
}
