<?php

declare(strict_types=1);

namespace App\Libraries\Tracking;

interface CourierTrackingProviderInterface
{
    /**
     * Checks a tracking number's current status. Returns one of:
     * 'in_transit', 'out_for_delivery', 'delivered', 'unknown'.
     *
     * $watchedSince lets a mock/demo implementation simulate progress over
     * time without a real courier API to call.
     */
    public function checkStatus(string $courier, string $trackingNumber, \DateTimeInterface $watchedSince): string;
}
