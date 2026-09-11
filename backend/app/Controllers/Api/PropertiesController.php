<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Libraries\LockerSizeRecommender;
use App\Models\LockerModel;
use App\Models\PropertyModel;
use CodeIgniter\Shield\Models\UserModel;

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

    /** Suggest a locker size from a package's weight/dimensions — used when the sender knows them upfront. */
    public function recommendSize()
    {
        $weight = $this->request->getJsonVar('weight_kg');
        $length = $this->request->getJsonVar('length_cm');
        $width  = $this->request->getJsonVar('width_cm');
        $height = $this->request->getJsonVar('height_cm');

        $size = LockerSizeRecommender::recommend(
            $weight !== null ? (float) $weight : null,
            $length !== null ? (float) $length : null,
            $width !== null ? (float) $width : null,
            $height !== null ? (float) $height : null,
        );

        if ($size === null) {
            return $this->fail("That's larger than our biggest locker (XL) — this parcel won't fit.");
        }

        return $this->respond(['size' => $size]);
    }

    /** Other residents at the same property, for the "send to a neighbour" recipient search. */
    public function residents()
    {
        $user = $this->currentUser();
        if (empty($user->property_id)) {
            return $this->failNotFound('Your account is not linked to a property yet.');
        }

        $search = (string) ($this->request->getGet('q') ?? '');

        $query = (new UserModel())->asArray()
            ->select('users.id, users.full_name, units.unit_number')
            ->join('units', 'units.id = users.unit_id')
            ->where('users.property_id', $user->property_id)
            ->where('users.id !=', $user->id);

        if ($search !== '') {
            $query->groupStart()
                ->like('users.full_name', $search)
                ->orLike('units.unit_number', $search)
                ->groupEnd();
        }

        return $this->respond($query->orderBy('units.unit_number', 'ASC')->findAll(20));
    }
}
