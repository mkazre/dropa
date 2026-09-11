<?php

declare(strict_types=1);

namespace App\Controllers\Manage;

use App\Controllers\BaseController;
use App\Models\LockerModel;
use App\Models\MaintenanceTicketModel;
use CodeIgniter\Shield\Models\UserModel;

class MaintenanceController extends BaseController
{
    public function index()
    {
        $propertyId = auth()->user()->property_id;
        $tickets    = model(MaintenanceTicketModel::class)->where('property_id', $propertyId)->orderBy('id', 'DESC')->findAll();

        $lockerIds  = array_filter(array_column($tickets, 'locker_id'));
        $lockersById = $lockerIds ? array_column(model(LockerModel::class)->whereIn('id', $lockerIds)->findAll(), null, 'id') : [];

        $raisedByIds = array_column($tickets, 'raised_by');
        $usersById   = $raisedByIds ? array_column((new UserModel())->whereIn('id', $raisedByIds)->findAll(), null, 'id') : [];

        return view('manage/maintenance/index', [
            'title'       => 'Maintenance — Dropa Manage',
            'panelLabel'  => 'Body Corporate',
            'nav'         => view('manage/_nav'),
            'tickets'     => $tickets,
            'lockersById' => $lockersById,
            'usersById'   => $usersById,
        ]);
    }

    public function updateStatus($id)
    {
        $propertyId = auth()->user()->property_id;
        $tickets    = model(MaintenanceTicketModel::class);
        $ticket     = $tickets->find($id);

        if ($ticket === null || (int) $ticket['property_id'] !== (int) $propertyId) {
            return redirect()->to('/manage/maintenance')->with('error', 'Ticket not found.');
        }

        $status = $this->request->getPost('status');
        if (! in_array($status, ['open', 'in_progress', 'resolved'], true)) {
            return redirect()->to('/manage/maintenance')->with('error', 'Invalid status.');
        }

        $tickets->update($id, ['status' => $status]);

        if ($status === 'in_progress' && $ticket['locker_id']) {
            model(LockerModel::class)->update($ticket['locker_id'], ['status' => 'out_of_service']);
        }
        if ($status === 'resolved' && $ticket['locker_id']) {
            $locker = model(LockerModel::class)->find($ticket['locker_id']);
            if ($locker && $locker['status'] === 'out_of_service') {
                model(LockerModel::class)->update($ticket['locker_id'], ['status' => 'available']);
            }
        }

        $this->audit('maintenance.status', 'maintenance_ticket', (int) $id, ['status' => $status]);

        return redirect()->to('/manage/maintenance')->with('success', 'Ticket updated.');
    }
}
