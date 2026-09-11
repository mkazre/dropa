<?php

declare(strict_types=1);

namespace App\Libraries\Payments;

class PaymentGatewayFactory
{
    public static function make(string $gateway): PaymentGatewayInterface
    {
        return match ($gateway) {
            'ozow'    => new OzowGateway(),
            'payfast' => new PayfastGateway(),
            default   => new ManualPaymentGateway(),
        };
    }
}
