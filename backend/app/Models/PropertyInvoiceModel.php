<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class PropertyInvoiceModel extends Model
{
    protected $table         = 'property_invoices';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['property_id', 'period_month', 'amount', 'status', 'paid_at'];
    protected $returnType    = 'array';
}
