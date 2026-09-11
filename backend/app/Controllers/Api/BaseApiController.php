<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Shield\Models\UserModel;

abstract class BaseApiController extends BaseController
{
    use ResponseTrait;

    protected function currentUser()
    {
        return auth()->user();
    }

    /**
     * Everyone sharing a unit sees each other's reservations/parcels
     * (household visibility) — a unit is one household, not one person.
     * Falls back to just the user themself if they're not linked to a unit.
     *
     * @return list<int>
     */
    protected function householdTenantIds(): array
    {
        $user = $this->currentUser();

        if (empty($user->unit_id)) {
            return [(int) $user->id];
        }

        $ids = (new UserModel())->where('unit_id', $user->unit_id)->findColumn('id');

        return $ids ?: [(int) $user->id];
    }
}
