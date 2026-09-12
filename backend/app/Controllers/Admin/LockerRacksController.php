<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LockerModel;
use App\Models\LockerRackModel;
use App\Models\PropertyModel;

/** Super Admin sets up the physical locker racks/lockers for a property — a Body Corporate has no reason to know rack/hardware details. */
class LockerRacksController extends BaseController
{
    public function index($propertyId)
    {
        $property = model(PropertyModel::class)->find($propertyId);
        if ($property === null) {
            return redirect()->to('/admin/properties')->with('error', 'Property not found.');
        }

        $racks = model(LockerRackModel::class)->where('property_id', $propertyId)->orderBy('name', 'ASC')->findAll();
        foreach ($racks as &$rack) {
            $rack['locker_count'] = model(LockerModel::class)->where('rack_id', $rack['id'])->countAllResults();
        }
        unset($rack);

        return view('admin/racks/index', [
            'title'      => 'Lockers — ' . $property['name'] . ' — Dropa Admin',
            'panelLabel' => 'Super Admin',
            'nav'        => view('admin/_nav'),
            'property'   => $property,
            'racks'      => $racks,
        ]);
    }

    public function createRack($propertyId)
    {
        $name = trim((string) $this->request->getPost('name'));
        if ($name === '') {
            return redirect()->to("/admin/properties/{$propertyId}/lockers")->with('error', 'Rack name is required.');
        }

        $id = model(LockerRackModel::class)->insert([
            'property_id'       => $propertyId,
            'name'              => $name,
            'location_desc'     => $this->request->getPost('location_desc'),
            'hardware_provider' => $this->request->getPost('hardware_provider') ?: 'mock',
            'external_rack_id'  => $this->request->getPost('external_rack_id') ?: null,
            'status'            => 'online',
        ], true);

        $this->audit('rack.create', 'locker_rack', $id, ['property_id' => $propertyId, 'name' => $name]);

        return redirect()->to("/admin/properties/{$propertyId}/lockers")->with('success', 'Rack added.');
    }

    public function deleteRack($rackId)
    {
        $rack = model(LockerRackModel::class)->find($rackId);
        if ($rack === null) {
            return redirect()->to('/admin/properties')->with('error', 'Rack not found.');
        }

        model(LockerRackModel::class)->delete($rackId);
        $this->audit('rack.delete', 'locker_rack', (int) $rackId);

        return redirect()->to("/admin/properties/{$rack['property_id']}/lockers")->with('success', 'Rack removed.');
    }

    public function lockers($rackId)
    {
        $rack = model(LockerRackModel::class)->find($rackId);
        if ($rack === null) {
            return redirect()->to('/admin/properties')->with('error', 'Rack not found.');
        }
        $property = model(PropertyModel::class)->find($rack['property_id']);

        return view('admin/racks/lockers', [
            'title'      => 'Lockers — ' . $rack['name'] . ' — Dropa Admin',
            'panelLabel' => 'Super Admin',
            'nav'        => view('admin/_nav'),
            'property'   => $property,
            'rack'       => $rack,
            'lockers'    => model(LockerModel::class)->where('rack_id', $rackId)->orderBy('label', 'ASC')->findAll(),
        ]);
    }

    public function createLocker($rackId)
    {
        $rack  = model(LockerRackModel::class)->find($rackId);
        $label = trim((string) $this->request->getPost('label'));
        if ($rack === null || $label === '') {
            return redirect()->back()->with('error', 'Locker label is required.');
        }

        $id = model(LockerModel::class)->insert([
            'rack_id'                 => $rackId,
            'label'                   => $label,
            'size'                    => $this->request->getPost('size') ?: 'M',
            'external_compartment_id' => $this->request->getPost('external_compartment_id') ?: null,
            'status'                  => 'available',
        ], true);

        $this->audit('locker.create', 'locker', $id, ['rack_id' => $rackId, 'label' => $label]);

        return redirect()->to("/admin/racks/{$rackId}/lockers")->with('success', 'Locker added.');
    }

    public function deleteLocker($lockerId)
    {
        $locker = model(LockerModel::class)->find($lockerId);
        if ($locker === null) {
            return redirect()->to('/admin/properties')->with('error', 'Locker not found.');
        }

        model(LockerModel::class)->delete($lockerId);
        $this->audit('locker.delete', 'locker', (int) $lockerId);

        return redirect()->to("/admin/racks/{$locker['rack_id']}/lockers")->with('success', 'Locker removed.');
    }
}
