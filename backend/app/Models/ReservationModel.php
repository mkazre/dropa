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
        'property_id', 'tenant_id', 'created_by', 'locker_id', 'size_requested',
        'deposit_code', 'status', 'expires_at',
    ];
    protected $returnType    = 'array';

    /**
     * Reservations visible to a household (shared unit) — plus anything a
     * household member sent to someone else (peer-to-peer), so a sender can
     * still track a parcel they arranged for another resident. Newest
     * first, tagged with who it's for and who arranged it.
     */
    public function forHousehold(array $tenantIds): array
    {
        return $this->select('reservations.*, users.full_name AS reserved_by, senders.full_name AS sent_by')
            ->join('users', 'users.id = reservations.tenant_id')
            ->join('users AS senders', 'senders.id = reservations.created_by', 'left')
            ->groupStart()
                ->whereIn('reservations.tenant_id', $tenantIds)
                ->orWhereIn('reservations.created_by', $tenantIds)
            ->groupEnd()
            ->orderBy('reservations.id', 'DESC')
            ->findAll();
    }
}
