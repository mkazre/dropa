<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\PropertyModel;
use App\Models\UnitModel;

class AccountController extends BaseApiController
{
    /** The tenant app calls this on launch to auto-detect the user's property/unit. */
    public function me()
    {
        $user = $this->currentUser();

        $property = $user->property_id ? model(PropertyModel::class)->find($user->property_id) : null;
        $unit     = $user->unit_id ? model(UnitModel::class)->find($user->unit_id) : null;

        return $this->respond([
            'id'       => $user->id,
            'name'     => $user->full_name,
            'email'    => $user->email,
            'phone'    => $user->phone,
            'groups'   => $user->getGroups(),
            'property' => $property,
            'unit'     => $unit,
        ]);
    }

    /** The RN app calls this once it has an Expo push token, so deposit/collect notifications can reach it. */
    public function registerPushToken()
    {
        $token = $this->request->getJsonVar('push_token');
        if (empty($token)) {
            return $this->failValidationErrors('push_token is required.');
        }

        db_connect()->table('users')->where('id', $this->currentUser()->id)->update(['push_token' => $token]);

        return $this->respondNoContent();
    }
}
