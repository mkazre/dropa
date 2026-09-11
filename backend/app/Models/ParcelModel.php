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
        'collected_at', 'reminded_at', 'status',
    ];
    protected $returnType    = 'array';

    /** Parcels visible to a household (shared unit), newest first, with who reserved each one. */
    public function forHousehold(array $tenantIds): array
    {
        return $this->select('parcels.*, users.full_name AS reserved_by')
            ->join('reservations', 'reservations.id = parcels.reservation_id')
            ->join('users', 'users.id = reservations.tenant_id')
            ->whereIn('reservations.tenant_id', $tenantIds)
            ->orderBy('parcels.id', 'DESC')
            ->findAll();
    }
}
