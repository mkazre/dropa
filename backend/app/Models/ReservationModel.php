<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ReservationModel extends Model
{
    protected $table         = 'reservations';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'property_id', 'tenant_id', 'locker_id', 'size_requested',
        'deposit_code', 'status', 'expires_at',
    ];
    protected $returnType    = 'array';
}
