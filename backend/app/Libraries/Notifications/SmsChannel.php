<?php

declare(strict_types=1);

namespace App\Libraries\Notifications;

/**
 * SMS via BulkSMS (bulksms.com) — a widely-used South African SMS gateway
 * with a simple HTTP API. Set `sms.username` / `sms.password` in .env to
 * activate; without credentials this silently no-ops (push + email still
 * fire), so local dev and demos work without an SMS account.
 */
class SmsChannel implements NotificationChannelInterface
{
    private const API_URL = 'https://api.bulksms.com/v1/messages';

    public function send(array $user, string $title, string $body): bool
    {
        $phone = $user['phone'] ?? null;
        $username = env('sms.username');
        $password = env('sms.password');

        if (empty($phone) || empty($username) || empty($password)) {
            return false;
        }

        try {
            $response = service('curlrequest')->post(self::API_URL, [
                'auth'    => [$username, $password],
                'json'    => [['to' => $phone, 'body' => "{$title}: {$body}"]],
                'timeout' => 5,
            ]);

            return in_array($response->getStatusCode(), [200, 201], true);
        } catch (\Throwable $e) {
            log_message('error', 'SMS notification failed: ' . $e->getMessage());

            return false;
        }
    }
}
