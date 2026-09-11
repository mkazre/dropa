<?php

declare(strict_types=1);

namespace App\Libraries\Hardware;

/**
 * Adapter for Hive-Box smart parcel lockers (hive-box.com).
 *
 * Hive-Box's Platform API is cloud-to-cloud and exposes a "Storage" service
 * that maps closely onto our reserve -> deposit -> collect flow:
 *   - create storage order  ~= reserveCompartment()  (returns a deposit code)
 *   - cancel order          ~= cancelReservation()
 *   - send pickup code      ~= issuePickupCode()
 *   - (their Routing service reports deposit/pickup success + compartment
 *     faults back to us via webhook — see WebhooksController)
 *
 * Hive-Box's full endpoint/auth spec is not publicly documented; this class
 * is a placeholder to be completed once developer credentials are obtained
 * from Hive-Box for a purchased rack. Until then, properties should stay on
 * the `mock` hardware_provider.
 */
class HiveBoxHardwareProvider implements HardwareProviderInterface
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = env('hivebox.baseUrl', '');
        $this->apiKey  = env('hivebox.apiKey', '');
    }

    public function reserveCompartment(string $externalRackId, string $size, string $depositCode): ?string
    {
        // TODO: POST {{baseUrl}}/storage/order — create storage order for
        // $externalRackId + $size, passing our $depositCode (or accepting
        // theirs, per their spec), return the compartment id they assign.
        throw new \RuntimeException('HiveBoxHardwareProvider is not yet implemented — pending Hive-Box developer API access.');
    }

    public function cancelReservation(string $externalRackId, string $externalCompartmentId): bool
    {
        // TODO: POST {{baseUrl}}/storage/order/cancel
        throw new \RuntimeException('HiveBoxHardwareProvider is not yet implemented — pending Hive-Box developer API access.');
    }

    public function issuePickupCode(string $externalRackId, string $externalCompartmentId, string $pickupPin): bool
    {
        // TODO: POST {{baseUrl}}/storage/pickup-code
        throw new \RuntimeException('HiveBoxHardwareProvider is not yet implemented — pending Hive-Box developer API access.');
    }

    public function openCompartment(string $externalRackId, string $externalCompartmentId): bool
    {
        // TODO: confirm with Hive-Box whether remote door-open outside of a
        // storage/pickup order is exposed by the Platform API.
        throw new \RuntimeException('HiveBoxHardwareProvider is not yet implemented — pending Hive-Box developer API access.');
    }
}
