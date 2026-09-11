<?php

declare(strict_types=1);

namespace App\Libraries\Payments;

/**
 * Ozow instant EFT — hosted payment page (https://pay.ozow.com).
 *
 * Verified against Ozow's published integration spec:
 *   Request HashCheck  = sha512(lowercase(SiteCode+CountryCode+CurrencyCode+Amount+
 *                          TransactionReference+BankReference+Optional1..5+Customer+
 *                          CancelUrl+ErrorUrl+SuccessUrl+NotifyUrl+IsTest+PrivateKey))
 *   Notify Hash        = sha512(lowercase(SiteCode+TransactionId+TransactionReference+
 *                          Amount+Status+Optional1..5+CurrencyCode+IsTest+StatusMessage+PrivateKey))
 *
 * `credentials` (from payment_gateway_settings, decoded from JSON) must supply:
 *   site_code, private_key, is_test ('true'|'false'), and the four callback URLs.
 */
class OzowGateway implements PaymentGatewayInterface
{
    private const PAY_URL = 'https://pay.ozow.com';

    public function initiate(float $amount, string $reference, array $credentials): array
    {
        $siteCode   = $credentials['site_code'] ?? '';
        $privateKey = $credentials['private_key'] ?? '';
        $isTest     = ($credentials['is_test'] ?? 'true') === 'true' ? 'true' : 'false';

        $fields = [
            'SiteCode'             => $siteCode,
            'CountryCode'          => 'ZA',
            'CurrencyCode'         => 'ZAR',
            'Amount'               => number_format($amount, 2, '.', ''),
            'TransactionReference' => $reference,
            'BankReference'        => $reference,
            'Optional1'            => '',
            'Optional2'            => '',
            'Optional3'            => '',
            'Optional4'            => '',
            'Optional5'            => '',
            'Customer'             => '',
            'CancelUrl'            => $credentials['cancel_url'] ?? '',
            'ErrorUrl'             => $credentials['error_url'] ?? '',
            'SuccessUrl'           => $credentials['success_url'] ?? '',
            'NotifyUrl'            => $credentials['notify_url'] ?? '',
            'IsTest'               => $isTest,
        ];

        $fields['HashCheck'] = $this->hash($fields, $privateKey);

        return [
            'redirect_url' => self::PAY_URL . '?' . http_build_query($fields),
            'instructions' => null,
            'reference'    => $reference,
        ];
    }

    public function verify(array $payload, array $credentials): bool
    {
        $privateKey = $credentials['private_key'] ?? '';
        $received   = $payload['Hash'] ?? '';

        $ordered = [
            'SiteCode'             => $payload['SiteCode'] ?? '',
            'TransactionId'        => $payload['TransactionId'] ?? '',
            'TransactionReference' => $payload['TransactionReference'] ?? '',
            'Amount'               => $payload['Amount'] ?? '',
            'Status'               => $payload['Status'] ?? '',
            'Optional1'            => $payload['Optional1'] ?? '',
            'Optional2'            => $payload['Optional2'] ?? '',
            'Optional3'            => $payload['Optional3'] ?? '',
            'Optional4'            => $payload['Optional4'] ?? '',
            'Optional5'            => $payload['Optional5'] ?? '',
            'CurrencyCode'         => $payload['CurrencyCode'] ?? '',
            'IsTest'               => $payload['IsTest'] ?? '',
            'StatusMessage'        => $payload['StatusMessage'] ?? '',
        ];

        $expected = $this->hash($ordered, $privateKey);

        return hash_equals($expected, strtolower((string) $received));
    }

    private function hash(array $orderedFields, string $privateKey): string
    {
        $concatenated = implode('', $orderedFields) . $privateKey;

        return hash('sha512', strtolower($concatenated));
    }
}
