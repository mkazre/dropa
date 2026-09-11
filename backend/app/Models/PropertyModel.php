<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class PropertyModel extends Model
{
    protected $table            = 'properties';
    protected $primaryKey       = 'id';
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'name', 'type', 'address', 'lat', 'lng', 'logo_url', 'brand_color',
        'reservation_hold_hours', 'status', 'subscription_monthly_fee',
    ];
    protected $returnType       = 'array';
}
