<?php

declare(strict_types=1);

namespace App\Libraries\Hardware;

class HardwareProviderFactory
{
    public static function make(string $providerKey): HardwareProviderInterface
    {
        return match ($providerKey) {
            'hivebox' => new HiveBoxHardwareProvider(),
            default   => new MockHardwareProvider(),
        };
    }
}
