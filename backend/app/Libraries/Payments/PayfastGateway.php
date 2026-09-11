<?php

declare(strict_types=1);

namespace App\Libraries\Payments;

/**
 * PayFast card payments — hosted "Onsite/Redirect" payment page.
 *
 * Built against PayFast's long-stable, widely-implemented spec (their live
 * docs page is a JS app we can't scrape here — verify against a sandbox
 * transaction before going live):
 *   - Signature fields are concatenated in the ORDER THEY APPEAR ON THE
 *     FORM, not alphabetically — this is the classic PayFast gotcha.
 *   - Empty fields are skipped entirely (not included as `key=`).
 *   - Values are urlencode()'d (PHP's urlencode gives `+` for spaces, which
 *     is what PayFast expects), then `&passphrase=<urlencoded passphrase>`
 *     is appended if one is configured, then MD5-hashed (lowercase hex).
 *   - `credentials` (JSON) must supply: merchant_id, merchant_key, and
 *     optionally passphrase, sandbox ('true'|'false'), return_url,
 *     cancel_url, notify_url.
 */
class PayfastGateway implements PaymentGatewayInterface
{
    public function initiate(float $amount, string $reference, array $credentials): array
    {
        $sandbox = ($credentials['sandbox'] ?? 'true') === 'true';

        $fields = [
            'merchant_id'  => $credentials['merchant_id'] ?? '',
            'merchant_key' => $credentials['merchant_key'] ?? '',
            'return_url'   => $credentials['return_url'] ?? '',
            'cancel_url'   => $credentials['cancel_url'] ?? '',
            'notify_url'   => $credentials['notify_url'] ?? '',
            'm_payment_id' => $reference,
            'amount'       => number_format($amount, 2, '.', ''),
            'item_name'    => 'Dropa locker reservation',
        ];
        $fields = array_filter($fields, static fn ($v) => $v !== '' && $v !== null);
        $fields['signature'] = $this->sign($fields, $credentials['passphrase'] ?? null);

        $processUrl = $sandbox ? 'https://sandbox.payfast.co.za/eng/process' : 'https://www.payfast.co.za/eng/process';

        return [
            'redirect_url' => $processUrl . '?' . http_build_query($fields),
            'instructions' => null,
            'reference'    => $reference,
        ];
    }

    public function verify(array $payload, array $credentials): bool
    {
        $received = $payload['signature'] ?? '';
        unset($payload['signature']);

        $expected = $this->sign($payload, $credentials['passphrase'] ?? null);

        // NOTE: production use should also (a) confirm the ITN came from a
        // PayFast IP range and (b) POST the payload back to PayFast's
        // /eng/query/validate endpoint and require a "VALID" response,
        // per their ITN security spec, before trusting `amount`/`status`.
        return hash_equals($expected, $received);
    }

    private function sign(array $fields, ?string $passphrase): string
    {
        $pairs = [];
        foreach ($fields as $key => $value) {
            if ($value === '' || $value === null) {
                continue;
            }
            $pairs[] = $key . '=' . urlencode((string) $value);
        }

        $query = implode('&', $pairs);
        if (! empty($passphrase)) {
            $query .= '&passphrase=' . urlencode($passphrase);
        }

        return md5($query);
    }
}
