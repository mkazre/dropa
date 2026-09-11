<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class PaymentGatewaySettingModel extends Model
{
    protected $table         = 'payment_gateway_settings';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['scope', 'property_id', 'gateway', 'enabled', 'credentials', 'instructions'];
    protected $returnType    = 'array';

    /**
     * Which gateways are enabled for a property: property-level setting
     * wins when present, otherwise falls back to the global setting.
     *
     * @return array<string, array> gateway => settings row
     */
    public function enabledForProperty(?int $propertyId): array
    {
        $global = $this->where('scope', 'global')->findAll();
        $result = [];
        foreach ($global as $row) {
            $result[$row['gateway']] = $row;
        }

        if ($propertyId !== null) {
            $overrides = $this->where('scope', 'property')->where('property_id', $propertyId)->findAll();
            foreach ($overrides as $row) {
                $result[$row['gateway']] = $row;
            }
        }

        return array_filter($result, static fn ($row) => (bool) $row['enabled']);
    }
}
