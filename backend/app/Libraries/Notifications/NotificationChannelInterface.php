<?php

declare(strict_types=1);

namespace App\Libraries\Notifications;

interface NotificationChannelInterface
{
    /**
     * Send a notification to a user. Returns true if the channel accepted it
     * for delivery (e.g. the push service or SMTP call succeeded) — this is
     * "sent", not "read".
     *
     * @param array $user a plain array with at least: email, phone, push_token
     */
    public function send(array $user, string $title, string $body): bool;
}
