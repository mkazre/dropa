<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\LockerModel;
use App\Models\PropertyModel;

class PropertiesController extends BaseApiController
{
    /** A tenant only ever sees their own linked property — they never pick one. */
    public function mine()
    {
        $user = $this->currentUser();

        if (empty($user->property_id)) {
            return $this->failNotFound('Your account is not linked to a property yet.');
        }

        return $this->respond(model(PropertyModel::class)->find($user->property_id));
    }

    /** Live per-size locker availability at the tenant's own property. */
    public function lockerAvailability()
    {
        $user = $this->currentUser();

        if (empty($user->property_id)) {
            return $this->failNotFound('Your account is not linked to a property yet.');
        }

        $lockers = model(LockerModel::class);
        $counts  = [];
        foreach (['S', 'M', 'L', 'XL'] as $size) {
            $counts[$size] = count($lockers->availableAtProperty((int) $user->property_id, $size));
        }

        return $this->respond($counts);
    }
}
