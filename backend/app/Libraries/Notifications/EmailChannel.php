<?php

declare(strict_types=1);

namespace App\Libraries\Notifications;

class EmailChannel implements NotificationChannelInterface
{
    public function send(array $user, string $title, string $body): bool
    {
        $to = $user['email'] ?? null;
        if (empty($to)) {
            return false;
        }

        $email = service('email');
        $email->setTo($to);
        $email->setSubject($title);
        $email->setMessage(nl2br(esc($body)));

        try {
            return $email->send();
        } catch (\Throwable $e) {
            log_message('error', 'Email notification failed: ' . $e->getMessage());

            return false;
        }
    }
}
