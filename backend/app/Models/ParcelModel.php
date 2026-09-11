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

    /**
     * Parcels visible to a household (shared unit) — plus anything a
     * household member sent to someone else (peer-to-peer). Newest first,
     * tagged with who it's for and who arranged it.
     */
    public function forHousehold(array $tenantIds): array
    {
        return $this->select('parcels.*, users.full_name AS reserved_by, senders.full_name AS sent_by')
            ->join('reservations', 'reservations.id = parcels.reservation_id')
            ->join('users', 'users.id = reservations.tenant_id')
            ->join('users AS senders', 'senders.id = reservations.created_by', 'left')
            ->groupStart()
                ->whereIn('reservations.tenant_id', $tenantIds)
                ->orWhereIn('reservations.created_by', $tenantIds)
            ->groupEnd()
            ->orderBy('parcels.id', 'DESC')
            ->findAll();
    }
}
