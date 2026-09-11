<?php

declare(strict_types=1);

namespace App\Libraries\Tracking;

/**
 * Stand-in for a real courier tracking integration (e.g. AfterShip, Ship24,
 * or a courier's own API) — none is wired up yet, so this simulates
 * progress purely from elapsed time: "out for delivery" a couple of
 * minutes after a tenant registers the tracking number, so the
 * parcels:check-prealerts command and auto-reserve flow are fully
 * demoable/testable without needing real courier credentials.
 */
class MockCourierTrackingProvider implements CourierTrackingProviderInterface
{
    public function checkStatus(string $courier, string $trackingNumber, \DateTimeInterface $watchedSince): string
    {
        $minutesWatched = (time() - $watchedSince->getTimestamp()) / 60;

        return $minutesWatched >= 2 ? 'out_for_delivery' : 'in_transit';
    }
}
