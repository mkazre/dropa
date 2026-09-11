<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Libraries\PaymentService;
use App\Models\PaymentModel;
use App\Models\ReservationModel;
use RuntimeException;

class PaymentsController extends BaseApiController
{
    /** What a reservation costs and which payment methods are available. */
    public function options($reservationId)
    {
        $reservation = $this->reservationOwnedByCurrentUser((int) $reservationId);
        if ($reservation === null) {
            return $this->failNotFound();
        }

        return $this->respond((new PaymentService())->quote($reservation));
    }

    /** Start a payment for a reservation with the chosen method. */
    public function initiate()
    {
        $reservationId = (int) $this->request->getJsonVar('reservation_id');
        $method        = (string) $this->request->getJsonVar('method');

        $reservation = $this->reservationOwnedByCurrentUser($reservationId);
        if ($reservation === null) {
            return $this->failNotFound();
        }

        try {
            $result = (new PaymentService())->initiate($reservationId, $method);
        } catch (RuntimeException $e) {
            return $this->fail($e->getMessage());
        }

        return $this->respondCreated($result);
    }

    /** Tenant uploads proof of an offline/manual payment. */
    public function uploadProof($paymentId)
    {
        $payment = model(PaymentModel::class)->find($paymentId);
        if ($payment === null) {
            return $this->failNotFound();
        }
        $reservation = $this->reservationOwnedByCurrentUser((int) $payment['reservation_id']);
        if ($reservation === null) {
            return $this->failNotFound();
        }

        $file = $this->request->getFile('proof');
        if ($file === null || ! $file->isValid()) {
            return $this->failValidationErrors('A valid proof-of-payment file is required.');
        }

        $newName = $file->getRandomName();
        $file->move(WRITEPATH . 'uploads/proofs', $newName);

        (new PaymentService())->attachProof((int) $paymentId, 'uploads/proofs/' . $newName);

        return $this->respondNoContent();
    }

    /** Browser landing page after an Ozow/PayFast hosted-page redirect. */
    public function returnPage()
    {
        return $this->response->setBody(
            '<!doctype html><html><body style="font-family:sans-serif;text-align:center;padding:60px 20px">' .
            '<h2>Thanks — you can close this and return to the Dropa app.</h2>' .
            '<p>We\'ll update your reservation as soon as the payment is confirmed.</p></body></html>'
        );
    }

    private function reservationOwnedByCurrentUser(int $reservationId): ?array
    {
        $reservation = model(ReservationModel::class)->find($reservationId);
        if ($reservation === null || (int) $reservation['tenant_id'] !== (int) $this->currentUser()->id) {
            return null;
        }

        return $reservation;
    }
}
