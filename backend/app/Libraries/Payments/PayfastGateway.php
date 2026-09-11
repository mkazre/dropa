<?php

declare(strict_types=1);

namespace App\Libraries\Payments;

/**
 * PayFast card payments. See https://developers.payfast.co.za for the
 * hosted-payment-page + ITN (Instant Transaction Notification) spec.
 * Credentials (merchantId, merchantKey, passphrase) are stored per
 * payment_gateway_settings row (global or per-property).
 */
class PayfastGateway implements PaymentGatewayInterface
{
    public function initiate(float $amount, string $reference, array $credentials): array
    {
        // TODO: build the PayFast hosted-payment-page form fields
        // (merchant id/key, amount, item, return/cancel/notify URLs) and
        // sign them per PayFast's MD5 signature spec.
        return [
            'redirect_url' => null,
            'instructions' => null,
            'reference'    => $reference,
        ];
    }

    public function verify(array $payload, array $credentials): bool
    {
        // TODO: validate the ITN payload against PayFast's servers + signature.
        return false;
    }
}
