<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class WebhooksLogModel extends Model
{
    protected $table         = 'webhooks_log';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $updatedField  = '';
    protected $allowedFields = ['provider', 'event', 'payload', 'processed'];
    protected $returnType    = 'array';
}
