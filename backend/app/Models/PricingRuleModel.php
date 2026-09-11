<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class PricingRuleModel extends Model
{
    protected $table         = 'pricing_rules';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['property_id', 'size', 'free_hours', 'daily_rate'];
    protected $returnType    = 'array';

    /** Property-specific rule if one exists, else the platform default (property_id null). */
    public function forPropertyAndSize(?int $propertyId, string $size): ?array
    {
        if ($propertyId !== null) {
            $rule = $this->where('property_id', $propertyId)->where('size', $size)->first();
            if ($rule !== null) {
                return $rule;
            }
        }

        return $this->where('property_id', null)->where('size', $size)->first();
    }
}
