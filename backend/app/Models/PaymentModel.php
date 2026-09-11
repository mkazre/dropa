<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table         = 'payments';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'reservation_id', 'method', 'amount', 'status', 'gateway_ref',
        'proof_of_payment_url', 'approved_by',
    ];
    protected $returnType    = 'array';
}
