<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class LockerModel extends Model
{
    protected $table         = 'lockers';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'rack_id', 'label', 'size', 'external_compartment_id', 'status',
    ];
    protected $returnType    = 'array';

    public function availableAtProperty(int $propertyId, string $size): array
    {
        return $this->select('lockers.*')
            ->join('locker_racks', 'locker_racks.id = lockers.rack_id')
            ->where('locker_racks.property_id', $propertyId)
            ->where('lockers.size', $size)
            ->where('lockers.status', 'available')
            ->findAll();
    }
}
