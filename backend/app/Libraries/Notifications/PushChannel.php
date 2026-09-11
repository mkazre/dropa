<?php

declare(strict_types=1);

namespace App\Libraries\Notifications;

/**
 * Sends push notifications via Expo's push API (the RN app is Expo-managed,
 * so its device tokens are Expo push tokens — no separate FCM/APNs
 * credentials needed on our side).
 */
class PushChannel implements NotificationChannelInterface
{
    private const EXPO_PUSH_URL = 'https://exp.host/--/api/v2/push/send';

    public function send(array $user, string $title, string $body): bool
    {
        $token = $user['push_token'] ?? null;
        if (empty($token)) {
            return false;
        }

        try {
            $response = service('curlrequest')->post(self::EXPO_PUSH_URL, [
                'json'    => ['to' => $token, 'title' => $title, 'body' => $body, 'sound' => 'default'],
                'headers' => ['Content-Type' => 'application/json'],
                'timeout' => 5,
            ]);

            return $response->getStatusCode() === 200;
        } catch (\Throwable $e) {
            log_message('error', 'Push notification failed: ' . $e->getMessage());

            return false;
        }
    }
}
