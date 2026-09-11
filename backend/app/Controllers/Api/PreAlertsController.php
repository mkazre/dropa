<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\ParcelPrealertModel;

/**
 * A tenant pastes a courier tracking number ahead of time; once it's out
 * for delivery, `parcels:check-prealerts` (run on a schedule) auto-reserves
 * a locker so the courier already has a code to use when they arrive.
 */
class PreAlertsController extends BaseApiController
{
    public function create()
    {
        $user           = $this->currentUser();
        $courier        = trim((string) $this->request->getJsonVar('courier'));
        $trackingNumber = trim((string) $this->request->getJsonVar('tracking_number'));
        $size           = $this->request->getJsonVar('size') ?? 'M';

        if (empty($user->property_id)) {
            return $this->failForbidden('Your account is not linked to a property yet.');
        }
        if ($courier === '' || $trackingNumber === '') {
            return $this->failValidationErrors('Courier and tracking number are required.');
        }
        if (! in_array($size, ['S', 'M', 'L', 'XL'], true)) {
            return $this->failValidationErrors('size must be one of S, M, L, XL.');
        }

        $id = model(ParcelPrealertModel::class)->insert([
            'property_id'     => $user->property_id,
            'tenant_id'       => $user->id,
            'courier'         => $courier,
            'tracking_number' => $trackingNumber,
            'size'            => $size,
            'status'          => 'watching',
        ], true);

        return $this->respondCreated(model(ParcelPrealertModel::class)->find($id));
    }

    public function mine()
    {
        return $this->respond(
            model(ParcelPrealertModel::class)->where('tenant_id', $this->currentUser()->id)->orderBy('id', 'DESC')->findAll()
        );
    }

    public function cancel($id)
    {
        $model     = model(ParcelPrealertModel::class);
        $prealert  = $model->find($id);

        if ($prealert === null || (int) $prealert['tenant_id'] !== (int) $this->currentUser()->id) {
            return $this->failNotFound();
        }

        $model->update($id, ['status' => 'cancelled']);

        return $this->respondDeleted(['id' => $id]);
    }
}
