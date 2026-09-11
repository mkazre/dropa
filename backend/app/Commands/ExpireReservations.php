<?php

declare(strict_types=1);

namespace App\Commands;

use App\Libraries\Hardware\HardwareProviderFactory;
use App\Models\LockerModel;
use App\Models\LockerRackModel;
use App\Models\ReservationModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Releases lockers held by reservations nobody ever deposited into before
 * their hold window ran out. Intended to run every few minutes via cron /
 * Windows Task Scheduler: `php spark reservations:expire`.
 */
class ExpireReservations extends BaseCommand
{
    protected $group       = 'Dropa';
    protected $name        = 'reservations:expire';
    protected $description = 'Release lockers whose reservation hold window has passed without a deposit.';

    public function run(array $params)
    {
        $reservations = model(ReservationModel::class);
        $lockers      = model(LockerModel::class);
        $racks        = model(LockerRackModel::class);

        $stale = $reservations->where('status', 'held')->where('expires_at <', date('Y-m-d H:i:s'))->findAll();

        foreach ($stale as $reservation) {
            $locker = $lockers->find($reservation['locker_id']);
            if ($locker !== null) {
                $rack = $racks->find($locker['rack_id']);
                HardwareProviderFactory::make($rack['hardware_provider'])
                    ->cancelReservation((string) $rack['id'], (string) $locker['id']);
                $lockers->update($locker['id'], ['status' => 'available']);
            }

            $reservations->update($reservation['id'], ['status' => 'expired']);
            CLI::write("Expired reservation #{$reservation['id']} (locker released).", 'yellow');
        }

        CLI::write(count($stale) . ' reservation(s) expired.', 'green');
    }
}
