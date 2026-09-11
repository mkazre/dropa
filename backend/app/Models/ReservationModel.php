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

    /** Reservations visible to a household (shared unit), newest first, with who reserved each one. */
    public function forHousehold(array $tenantIds): array
    {
        return $this->select('reservations.*, users.full_name AS reserved_by')
            ->join('users', 'users.id = reservations.tenant_id')
            ->whereIn('reservations.tenant_id', $tenantIds)
            ->orderBy('reservations.id', 'DESC')
            ->findAll();
    }
}
