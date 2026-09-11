<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PropertyModel;

class PropertiesController extends BaseController
{
    private PropertyModel $properties;

    public function __construct()
    {
        $this->properties = new PropertyModel();
    }

    public function index()
    {
        return view('admin/properties/index', [
            'title'      => 'Properties — Dropa Admin',
            'panelLabel' => 'Super Admin',
            'nav'        => view('admin/_nav'),
            'properties' => $this->properties->orderBy('id', 'DESC')->findAll(),
        ]);
    }

    public function new()
    {
        return view('admin/properties/form', [
            'title'      => 'New Property — Dropa Admin',
            'panelLabel' => 'Super Admin',
            'nav'        => view('admin/_nav'),
            'property'   => null,
        ]);
    }

    public function create()
    {
        $name = $this->request->getPost('name');
        $id   = $this->properties->insert([
            'name'    => $name,
            'type'    => $this->request->getPost('type') ?: 'complex',
            'address' => $this->request->getPost('address'),
            'reservation_hold_hours' => (int) ($this->request->getPost('reservation_hold_hours') ?: 48),
            'status'  => 'active',
        ], true);

        $this->audit('property.create', 'property', $id, ['name' => $name]);

        return redirect()->to('/admin/properties')->with('success', 'Property created.');
    }

    public function edit($id)
    {
        $property = $this->properties->find($id);
        if ($property === null) {
            return redirect()->to('/admin/properties')->with('error', 'Property not found.');
        }

        return view('admin/properties/form', [
            'title'      => 'Edit Property — Dropa Admin',
            'panelLabel' => 'Super Admin',
            'nav'        => view('admin/_nav'),
            'property'   => $property,
        ]);
    }

    public function update($id)
    {
        $this->properties->update($id, [
            'name'    => $this->request->getPost('name'),
            'type'    => $this->request->getPost('type'),
            'address' => $this->request->getPost('address'),
            'reservation_hold_hours' => (int) $this->request->getPost('reservation_hold_hours'),
            'status'  => $this->request->getPost('status'),
        ]);
        $this->audit('property.update', 'property', (int) $id);

        return redirect()->to('/admin/properties')->with('success', 'Property updated.');
    }

    public function delete($id)
    {
        $this->properties->delete($id);
        $this->audit('property.delete', 'property', (int) $id);

        return redirect()->to('/admin/properties')->with('success', 'Property removed.');
    }
}
