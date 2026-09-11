<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ParcelPrealertModel extends Model
{
    protected $table         = 'parcel_prealerts';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'property_id', 'tenant_id', 'courier', 'tracking_number', 'size',
        'status', 'reservation_id',
    ];
    protected $returnType    = 'array';
}
