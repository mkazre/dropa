<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class MaintenanceTicketModel extends Model
{
    protected $table         = 'maintenance_tickets';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['property_id', 'locker_id', 'raised_by', 'issue', 'status'];
    protected $returnType    = 'array';
}
