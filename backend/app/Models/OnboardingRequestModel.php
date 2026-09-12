<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class OnboardingRequestModel extends Model
{
    protected $table         = 'onboarding_requests';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['property_name', 'unit_count', 'contact_name', 'email', 'status'];
    protected $returnType    = 'array';
}
