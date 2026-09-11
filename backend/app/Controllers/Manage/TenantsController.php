<?php

declare(strict_types=1);

namespace App\Controllers\Manage;

use App\Controllers\BaseController;
use App\Libraries\Notifications\NotificationService;
use App\Models\PropertyModel;
use App\Models\UnitModel;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;

class TenantsController extends BaseController
{
    public function index()
    {
        $propertyId = auth()->user()->property_id;
        $units      = model(UnitModel::class)->where('property_id', $propertyId)->findAll();
        $unitIds    = array_column($units, 'id');
        $unitsById  = array_column($units, null, 'id');

        $tenants = empty($unitIds)
            ? []
            : (new UserModel())->whereIn('unit_id', $unitIds)->findAll();

        return view('manage/tenants/index', [
            'title'      => 'Tenants — Dropa Manage',
            'panelLabel' => 'Body Corporate',
            'nav'        => view('manage/_nav'),
            'tenants'    => $tenants,
            'unitsById'  => $unitsById,
        ]);
    }

    public function new()
    {
        $propertyId = auth()->user()->property_id;

        return view('manage/tenants/form', [
            'title'      => 'Invite Tenant — Dropa Manage',
            'panelLabel' => 'Body Corporate',
            'nav'        => view('manage/_nav'),
            'units'      => model(UnitModel::class)->where('property_id', $propertyId)->findAll(),
        ]);
    }

    /** Invite a tenant: creates their account, links them to a unit at this property, emails them a temp password. */
    public function create()
    {
        $propertyId = auth()->user()->property_id;
        $unitNumber = trim((string) $this->request->getPost('unit_number'));
        $email      = trim((string) $this->request->getPost('email'));
        $fullName   = trim((string) $this->request->getPost('full_name'));
        $phone      = trim((string) $this->request->getPost('phone'));

        $units = model(UnitModel::class);
        $unit  = $units->where('property_id', $propertyId)->where('unit_number', $unitNumber)->first();
        if ($unit === null) {
            $unit = $units->insert(['property_id' => $propertyId, 'unit_number' => $unitNumber], true);
            $unit = $units->find($unit);
        }

        $tempPassword = bin2hex(random_bytes(6));
        $users        = new UserModel();

        $user = new User(['username' => $email]);
        $users->save($user);
        $user = $users->findById($users->getInsertID());
        $user->createEmailIdentity(['email' => $email, 'password' => $tempPassword]);
        $user->addGroup('tenant');

        // Shield's UserModel only allows its own fields; our extra columns go straight through the query builder.
        db_connect()->table('users')->where('id', $user->id)->update([
            'property_id' => $propertyId,
            'unit_id'     => $unit['id'],
            'full_name'   => $fullName,
            'phone'       => $phone,
        ]);

        $property = model(PropertyModel::class)->find($propertyId);
        (new NotificationService())->notify(
            ['id' => $user->id, 'email' => $email, 'phone' => $phone, 'push_token' => null],
            "You've been invited to Dropa",
            "{$property['name']} has set you up on Dropa for unit {$unitNumber}. Sign in with {$email} and this temporary password: {$tempPassword} — you'll be asked to change it.",
            ['email'],
        );

        return redirect()->to('/manage/tenants')->with('success', "Tenant invited for unit {$unitNumber}. Temporary password (also emailed): {$tempPassword}");
    }

    public function delete($id)
    {
        $propertyId = auth()->user()->property_id;
        $users      = new UserModel();
        $tenant     = $users->find($id);

        if ($tenant === null || (int) $tenant->property_id !== (int) $propertyId) {
            return redirect()->to('/manage/tenants')->with('error', 'Tenant not found.');
        }

        $users->delete($id);

        return redirect()->to('/manage/tenants')->with('success', 'Tenant removed.');
    }
}
