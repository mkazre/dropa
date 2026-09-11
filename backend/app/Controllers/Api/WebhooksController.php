<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Libraries\PaymentService;
use App\Models\PaymentGatewaySettingModel;
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
        $payload = $this->request->getPost() ?: $this->request->getJSON(true) ?? [];
        $this->log('ozow', $payload['Status'] ?? 'notify', $payload);

        $this->settlePayment('ozow', $payload['TransactionReference'] ?? '', $payload, strtolower($payload['Status'] ?? '') === 'complete');

        return $this->respond(['received' => true]);
    }

    public function payfast()
    {
        $payload = $this->request->getPost() ?: $this->request->getJSON(true) ?? [];
        $this->log('payfast', $payload['payment_status'] ?? 'itn', $payload);

        $this->settlePayment('payfast', $payload['m_payment_id'] ?? '', $payload, strtolower($payload['payment_status'] ?? '') === 'complete');

        return $this->respond(['received' => true]);
    }

    /** Verify the callback's signature, then mark the referenced payment paid or failed. */
    private function settlePayment(string $gateway, string $reference, array $payload, bool $providerSaysPaid): void
    {
        $paymentService = new PaymentService();
        $payment        = $paymentService->findByReference($reference);
        if ($payment === null) {
            return;
        }

        $settings    = model(PaymentGatewaySettingModel::class)->where('scope', 'global')->where('gateway', $gateway)->first();
        $credentials = json_decode($settings['credentials'] ?? '{}', true) ?: [];

        $gatewayImpl = \App\Libraries\Payments\PaymentGatewayFactory::make($gateway);
        $verified    = $gatewayImpl->verify($payload, $credentials);

        if ($verified && $providerSaysPaid) {
            $paymentService->markPaid((int) $payment['id']);
        } elseif ($verified) {
            $paymentService->markFailed((int) $payment['id']);
        }
        // An unverified signature is neither paid nor failed — it's logged
        // via webhooks_log above for manual review, but never trusted.
    }

    private function log(string $provider, string $event, array $payload = []): void
    {
        model(WebhooksLogModel::class)->insert([
            'provider' => $provider,
            'event'    => $event,
            'payload'  => json_encode($payload ?: $this->request->getBody()),
        ]);
    }
}
