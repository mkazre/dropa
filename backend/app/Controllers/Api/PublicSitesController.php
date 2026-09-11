<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\LockerModel;
use App\Models\PropertyModel;

/**
 * Public, pay-per-use locker sites (malls, business centres) — open to any
 * signed-in app user, not just residents of that specific property. This is
 * the first slice of the public-locker rollout described in the plan;
 * everything else (residential complexes) stays scoped to one's own
 * property via PropertiesController.
 */
class PublicSitesController extends BaseApiController
{
    public function index()
    {
        return $this->respond(
            model(PropertyModel::class)->where('type', 'public_site')->where('status', 'active')->findAll()
        );
    }

    public function availability($propertyId)
    {
        $property = $this->publicSite($propertyId);
        if ($property === null) {
            return $this->failNotFound('Not a public locker site.');
        }

        $lockers = model(LockerModel::class);
        $counts  = [];
        foreach (['S', 'M', 'L', 'XL'] as $size) {
            $counts[$size] = count($lockers->availableAtProperty((int) $propertyId, $size));
        }

        return $this->respond($counts);
    }

    private function publicSite($propertyId): ?array
    {
        return model(PropertyModel::class)->where('type', 'public_site')->where('status', 'active')->find($propertyId);
    }
}
