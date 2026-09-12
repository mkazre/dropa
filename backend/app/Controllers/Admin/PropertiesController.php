<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\Notifications\NotificationService;
use App\Models\PropertyModel;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;

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

        $adminEmail = trim((string) $this->request->getPost('admin_email'));
        $adminName  = trim((string) $this->request->getPost('admin_full_name'));

        if ($adminEmail === '') {
            return redirect()->to('/admin/properties')->with('success', 'Property created. Add a Body Corporate Admin from "Edit" whenever you have their details.');
        }

        $tempPassword = $this->inviteBodyCorporateAdmin((int) $id, $name, $adminName, $adminEmail);
        if ($tempPassword === null) {
            return redirect()->to('/admin/properties')->with('success', "Property created, but {$adminEmail} already has an account — link them to this property manually.");
        }

        return redirect()->to('/admin/properties')->with('success', "Property created. Body Corporate Admin login (also emailed): {$adminEmail} / {$tempPassword}");
    }

    /** @return string|null the temp password, or null if that email already has an account */
    private function inviteBodyCorporateAdmin(int $propertyId, string $propertyName, string $fullName, string $email): ?string
    {
        $db = db_connect();
        if ($db->table('auth_identities')->where('secret', $email)->countAllResults() > 0) {
            return null;
        }

        $tempPassword = bin2hex(random_bytes(6));
        $users        = new UserModel();

        $user = new User(['username' => $email]);
        $users->save($user);
        $user = $users->findById($users->getInsertID());
        $user->createEmailIdentity(['email' => $email, 'password' => $tempPassword]);
        $user->addGroup('property_admin');

        // Shield's UserModel only allows its own fields; our extra columns go straight through the query builder.
        $db->table('users')->where('id', $user->id)->update([
            'property_id' => $propertyId,
            'full_name'   => $fullName,
        ]);

        (new NotificationService())->notify(
            ['id' => $user->id, 'email' => $email, 'phone' => null, 'push_token' => null],
            "You've been set up on Dropa",
            "You're now the Body Corporate Admin for {$propertyName}. Sign in at /login with {$email} and this temporary password: {$tempPassword} — you'll be asked to change it.",
            ['email'],
        );

        $this->audit('property.invite_admin', 'user', $user->id, ['property_id' => $propertyId, 'email' => $email]);

        return $tempPassword;
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
