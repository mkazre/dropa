<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ParcelModel extends Model
{
    protected $table         = 'parcels';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'reservation_id', 'sender_name', 'sender_contact', 'pickup_pin',
        'qr_token', 'delegate_code', 'photo_url', 'deposited_at',
        'collected_at', 'status',
    ];
    protected $returnType    = 'array';
}
