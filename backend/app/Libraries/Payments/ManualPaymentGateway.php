<?php

declare(strict_types=1);

namespace App\Libraries\Payments;

/**
 * Offline/manual payment: the property publishes instructions (e.g. "pay at
 * the management office" or an EFT account), the tenant uploads a proof of
 * payment, and a property admin approves it from the Body Corporate panel.
 * There is nothing to redirect to or verify automatically.
 */
class ManualPaymentGateway implements PaymentGatewayInterface
{
    public function initiate(float $amount, string $reference, array $credentials): array
    {
        return [
            'redirect_url' => null,
            'instructions' => $credentials['instructions'] ?? 'Please arrange payment with the property office and upload your proof of payment.',
            'reference'    => $reference,
        ];
    }

    public function verify(array $payload, array $credentials): bool
    {
        // Manual payments are approved by a human (property admin), not verified automatically.
        return false;
    }
}
