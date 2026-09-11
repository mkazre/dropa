<?php

declare(strict_types=1);

namespace App\Libraries\Payments;

interface PaymentGatewayInterface
{
    /**
     * Start a payment for the given amount. Returns a redirect URL (online
     * gateways) or instructions text (manual/offline).
     *
     * @return array{redirect_url: ?string, instructions: ?string, reference: string}
     */
    public function initiate(float $amount, string $reference, array $credentials): array;

    /**
     * Verify an inbound notification/webhook from the gateway.
     */
    public function verify(array $payload, array $credentials): bool;
}
