<?php

declare(strict_types=1);

namespace App\Libraries\Payments;

/**
 * Ozow instant EFT. See https://ozow.com for the hosted-payment-page +
 * hash-signature spec. Credentials (siteCode, privateKey, apiKey) are
 * stored per payment_gateway_settings row (global or per-property).
 */
class OzowGateway implements PaymentGatewayInterface
{
    public function initiate(float $amount, string $reference, array $credentials): array
    {
        // TODO: build the Ozow hosted-payment-page request (site code,
        // amount, reference, success/cancel/error/notify URLs) and sign it
        // with the private key per Ozow's HashCheck spec.
        return [
            'redirect_url' => null,
            'instructions' => null,
            'reference'    => $reference,
        ];
    }

    public function verify(array $payload, array $credentials): bool
    {
        // TODO: recompute and compare the Ozow HashCheck signature.
        return false;
    }
}
