<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\WebhooksLogModel;

/**
 * Inbound status callbacks from hardware/payment providers. Every call is
 * logged raw first (so nothing is ever silently lost), then processed.
 */
class WebhooksController extends BaseApiController
{
    public function hivebox()
    {
        $this->log('hivebox', $this->request->getJsonVar('event') ?? 'unknown');
        // TODO: once Hive-Box developer docs are available, handle their
        // Routing service callbacks here: deposit success, pickup success,
        // compartment failure — updating parcels/lockers accordingly.
        return $this->respond(['received' => true]);
    }

    public function ozow()
    {
        $this->log('ozow', 'notify');
        // TODO: verify signature via OzowGateway::verify(), then mark the
        // matching payment as paid/failed.
        return $this->respond(['received' => true]);
    }

    public function payfast()
    {
        $this->log('payfast', 'itn');
        // TODO: verify via PayfastGateway::verify(), then mark the matching
        // payment as paid/failed.
        return $this->respond(['received' => true]);
    }

    private function log(string $provider, string $event): void
    {
        model(WebhooksLogModel::class)->insert([
            'provider' => $provider,
            'event'    => $event,
            'payload'  => $this->request->getBody(),
        ]);
    }
}
