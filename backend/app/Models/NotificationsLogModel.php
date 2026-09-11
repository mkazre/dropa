<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class NotificationsLogModel extends Model
{
    protected $table         = 'notifications_log';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['user_id', 'channel', 'title', 'body', 'status'];
    protected $returnType    = 'array';
}
