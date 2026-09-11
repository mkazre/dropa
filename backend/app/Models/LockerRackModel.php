<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class LockerRackModel extends Model
{
    protected $table         = 'locker_racks';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'property_id', 'name', 'location_desc', 'hardware_provider',
        'external_rack_id', 'status',
    ];
    protected $returnType    = 'array';
}
