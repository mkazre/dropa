<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class UnitModel extends Model
{
    protected $table         = 'units';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['property_id', 'unit_number'];
    protected $returnType    = 'array';
}
