<?php

declare(strict_types=1);

namespace App\Commands;

use App\Libraries\Notifications\NotificationService;
use App\Models\ParcelModel;
use App\Models\ReservationModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Shield\Models\UserModel;

/**
 * Reminds a tenant once every 24h that a parcel is still waiting for them.
 * Intended to run daily via cron / Windows Task Scheduler:
 * `php spark parcels:remind`.
 */
class RemindUncollectedParcels extends BaseCommand
{
    protected $group       = 'Dropa';
    protected $name        = 'parcels:remind';
    protected $description = 'Send a reminder for every parcel still awaiting collection, at most once per day.';

    public function run(array $params)
    {
        $parcels      = model(ParcelModel::class);
        $reservations = model(ReservationModel::class);
        $notifications = new NotificationService();

        $due = $parcels->where('status', 'awaiting_collection')
            ->groupStart()
                ->where('reminded_at', null)
                ->orWhere('reminded_at <', date('Y-m-d H:i:s', strtotime('-24 hours')))
            ->groupEnd()
            ->findAll();

        foreach ($due as $parcel) {
            $reservation = $reservations->find($parcel['reservation_id']);
            $user        = (new UserModel())->findById($reservation['tenant_id']);
            if ($user === null) {
                continue;
            }

            $notifications->notify(
                ['id' => $user->id, 'email' => $user->email, 'phone' => $user->phone, 'push_token' => $user->push_token],
                'Reminder: a parcel is waiting for you',
                "Your parcel is still at the locker with pickup PIN {$parcel['pickup_pin']}. Collect it when you can.",
            );

            $parcels->update($parcel['id'], ['reminded_at' => date('Y-m-d H:i:s')]);
        }

        CLI::write(count($due) . ' reminder(s) sent.', 'green');
    }
}
