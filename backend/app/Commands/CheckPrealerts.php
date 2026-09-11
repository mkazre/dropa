<?php

declare(strict_types=1);

namespace App\Commands;

use App\Libraries\Notifications\NotificationService;
use App\Libraries\ReservationService;
use App\Libraries\Tracking\MockCourierTrackingProvider;
use App\Models\ParcelPrealertModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Shield\Models\UserModel;
use RuntimeException;

/**
 * Checks every watched pre-alert; once a courier says a parcel is out for
 * delivery, reserves a locker automatically so the code is ready before the
 * courier arrives. Run on a schedule: `php spark parcels:check-prealerts`.
 */
class CheckPrealerts extends BaseCommand
{
    protected $group       = 'Dropa';
    protected $name        = 'parcels:check-prealerts';
    protected $description = 'Auto-reserve a locker for any pre-alert whose courier reports it out for delivery.';

    public function run(array $params)
    {
        $prealerts = model(ParcelPrealertModel::class);
        $provider  = new MockCourierTrackingProvider();
        $watching  = $prealerts->where('status', 'watching')->findAll();

        $reserved = 0;
        foreach ($watching as $prealert) {
            $status = $provider->checkStatus(
                $prealert['courier'],
                $prealert['tracking_number'],
                new \DateTimeImmutable($prealert['created_at']),
            );

            if ($status !== 'out_for_delivery') {
                continue;
            }

            try {
                $reservation = (new ReservationService())->reserve(
                    (int) $prealert['property_id'],
                    (int) $prealert['tenant_id'],
                    $prealert['size'],
                );
            } catch (RuntimeException $e) {
                CLI::write("Pre-alert #{$prealert['id']}: could not reserve — {$e->getMessage()}", 'yellow');
                continue;
            }

            $prealerts->update($prealert['id'], ['status' => 'reserved', 'reservation_id' => $reservation['id']]);

            $user = (new UserModel())->findById($prealert['tenant_id']);
            (new NotificationService())->notify(
                ['id' => $user->id, 'email' => $user->email, 'phone' => $user->phone, 'push_token' => $user->push_token],
                'A locker is ready for your delivery',
                "Your {$prealert['courier']} parcel ({$prealert['tracking_number']}) is out for delivery — a locker is reserved and ready.",
            );

            $reserved++;
            CLI::write("Pre-alert #{$prealert['id']}: reserved locker (reservation #{$reservation['id']}).", 'green');
        }

        CLI::write("{$reserved} pre-alert(s) converted to reservations.", 'green');
    }
}
