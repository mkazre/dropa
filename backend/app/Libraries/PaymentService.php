<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Libraries\Payments\PaymentGatewayFactory;
use App\Models\PaymentGatewaySettingModel;
use App\Models\PaymentModel;
use App\Models\PricingRuleModel;
use App\Models\ReservationModel;
use RuntimeException;

/**
 * Quotes and takes payment for a reservation. A property's enabled gateways
 * (payment_gateway_settings, property-level overriding global) decide what
 * a tenant can pay with; `pricing_rules` decides how much.
 */
class PaymentService
{
    public function __construct(
        private ReservationModel $reservations = new ReservationModel(),
        private PaymentModel $payments = new PaymentModel(),
        private PricingRuleModel $pricingRules = new PricingRuleModel(),
        private PaymentGatewaySettingModel $gatewaySettings = new PaymentGatewaySettingModel(),
    ) {
    }

    /** What a reservation costs and which gateways can pay for it. */
    public function quote(array $reservation): array
    {
        $rule   = $this->pricingRules->forPropertyAndSize((int) $reservation['property_id'], $reservation['size_requested']);
        $amount = (float) ($rule['daily_rate'] ?? 0);

        $enabled = $this->gatewaySettings->enabledForProperty((int) $reservation['property_id']);

        return [
            'amount'  => $amount,
            'methods' => array_values(array_map(
                static fn ($row) => ['method' => $row['gateway'], 'instructions' => $row['instructions'] ?? null],
                $enabled
            )),
        ];
    }

    /**
     * Start payment: creates the payment row and asks the chosen gateway to
     * initiate — a redirect URL for online gateways, or instructions text
     * for manual/offline.
     */
    public function initiate(int $reservationId, string $method): array
    {
        $reservation = $this->reservations->find($reservationId);
        if ($reservation === null) {
            throw new RuntimeException('Reservation not found.');
        }

        $enabled = $this->gatewaySettings->enabledForProperty((int) $reservation['property_id']);
        if (! isset($enabled[$method])) {
            throw new RuntimeException('That payment method is not available for this property.');
        }

        $quote = $this->quote($reservation);

        $paymentId = $this->payments->insert([
            'reservation_id' => $reservationId,
            'method'         => $method,
            'amount'         => $quote['amount'],
            'status'         => 'pending',
        ], true);

        $credentials = json_decode($enabled[$method]['credentials'] ?? '{}', true) ?: [];

        if ($method === 'manual') {
            $credentials['instructions'] = $enabled[$method]['instructions'] ?? null;
        } elseif ($method === 'ozow') {
            $credentials += [
                'notify_url'  => site_url('api/v1/webhooks/ozow'),
                'success_url' => site_url('api/v1/payments/return'),
                'cancel_url'  => site_url('api/v1/payments/return'),
                'error_url'   => site_url('api/v1/payments/return'),
            ];
        } elseif ($method === 'payfast') {
            $credentials += [
                'notify_url' => site_url('api/v1/webhooks/payfast'),
                'return_url' => site_url('api/v1/payments/return'),
                'cancel_url' => site_url('api/v1/payments/return'),
            ];
        }

        $result = PaymentGatewayFactory::make($method)->initiate($quote['amount'], "DROPA-{$paymentId}", $credentials);

        $this->payments->update($paymentId, ['gateway_ref' => $result['reference']]);

        return array_merge($result, ['payment_id' => $paymentId, 'amount' => $quote['amount']]);
    }

    /** A tenant uploads proof of an offline/manual payment for a property admin to review. */
    public function attachProof(int $paymentId, string $proofUrl): void
    {
        $this->payments->update($paymentId, ['proof_of_payment_url' => $proofUrl]);
    }

    /** Body Corporate Admin approves a manual payment. */
    public function approveManual(int $paymentId, int $approvedBy): void
    {
        $this->payments->update($paymentId, ['status' => 'paid', 'approved_by' => $approvedBy]);
    }

    /** Resolve which payment a gateway reference like "DROPA-42" points to. */
    public function findByReference(string $reference): ?array
    {
        if (! preg_match('/^DROPA-(\d+)$/', $reference, $m)) {
            return null;
        }

        return $this->payments->find((int) $m[1]);
    }

    public function markPaid(int $paymentId): void
    {
        $this->payments->update($paymentId, ['status' => 'paid']);
    }

    public function markFailed(int $paymentId): void
    {
        $this->payments->update($paymentId, ['status' => 'failed']);
    }
}
