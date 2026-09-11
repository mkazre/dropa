<?php

declare(strict_types=1);

namespace App\Libraries\Notifications;

use App\Models\NotificationsLogModel;

/**
 * Sends a notification across whichever channels apply, and logs every
 * attempt (delivered or not) to notifications_log so Super Admin/Body
 * Corporate Admin can see what a tenant was actually told.
 *
 * Best-effort by design: a channel failing (no SMS credentials configured,
 * an unreachable push token) never throws — it just isn't logged as sent.
 */
class NotificationService
{
    /** @var array<string, NotificationChannelInterface> */
    private array $channels;

    public function __construct()
    {
        $this->channels = [
            'push'  => new PushChannel(),
            'email' => new EmailChannel(),
            'sms'   => new SmsChannel(),
        ];
    }

    /**
     * @param array $user a plain array with at least: id, email, phone, push_token
     * @param list<string> $only restrict to specific channels, e.g. ['push', 'email']
     */
    public function notify(array $user, string $title, string $body, array $only = ['push', 'email', 'sms']): void
    {
        $log = model(NotificationsLogModel::class);

        foreach ($only as $channel) {
            if (! isset($this->channels[$channel])) {
                continue;
            }

            $sent = $this->channels[$channel]->send($user, $title, $body);

            $log->insert([
                'user_id' => $user['id'],
                'channel' => $channel,
                'title'   => $title,
                'body'    => $body,
                'status'  => $sent ? 'sent' : 'failed',
            ]);
        }
    }
}
