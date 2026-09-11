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

        $result = $this->inviteTenant($propertyId, $unitNumber, $fullName, $email, $phone);
        if ($result === null) {
            return redirect()->to('/manage/tenants')->with('error', "{$email} already has an account.");
        }

        return redirect()->to('/manage/tenants')->with('success', "Tenant invited for unit {$unitNumber}. Temporary password (also emailed): {$result['temp_password']}");
    }

    public function importForm()
    {
        return view('manage/tenants/import', [
            'title'      => 'Import Tenants — Dropa Manage',
            'panelLabel' => 'Body Corporate',
            'nav'        => view('manage/_nav'),
        ]);
    }

    /** Bulk-invite from a CSV: columns unit_number, full_name, email, phone (header row required). */
    public function import()
    {
        $propertyId = auth()->user()->property_id;
        $file       = $this->request->getFile('csv');

        if ($file === null || ! $file->isValid()) {
            return redirect()->to('/manage/tenants/import')->with('error', 'Please choose a valid CSV file.');
        }

        $rows = array_map('str_getcsv', file($file->getTempName()));
        $header = array_map(static fn ($h) => strtolower(trim($h)), array_shift($rows) ?? []);

        $required = ['unit_number', 'full_name', 'email'];
        if (array_diff($required, $header) !== []) {
            return redirect()->to('/manage/tenants/import')->with('error', 'CSV must have columns: unit_number, full_name, email, phone (optional).');
        }

        $invited = 0;
        $skipped = [];

        foreach ($rows as $row) {
            if (count($row) < count($header) || implode('', $row) === '') {
                continue;
            }
            $data = array_combine($header, $row);

            $result = $this->inviteTenant(
                $propertyId,
                trim($data['unit_number']),
                trim($data['full_name']),
                trim($data['email']),
                trim($data['phone'] ?? ''),
            );

            if ($result === null) {
                $skipped[] = $data['email'];
            } else {
                $invited++;
            }
        }

        $message = "{$invited} tenant(s) invited.";
        if ($skipped !== []) {
            $message .= ' Skipped (already have an account): ' . implode(', ', $skipped);
        }

        return redirect()->to('/manage/tenants')->with('success', $message);
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
        $this->audit('tenant.delete', 'user', (int) $id);

        return redirect()->to('/manage/tenants')->with('success', 'Tenant removed.');
    }

    /** @return array{temp_password: string}|null null if the email already has an account */
    private function inviteTenant(int $propertyId, string $unitNumber, string $fullName, string $email, string $phone): ?array
    {
        if ($email === '' || $unitNumber === '') {
            return null;
        }

        $db = db_connect();
        if ($db->table('auth_identities')->where('secret', $email)->countAllResults() > 0) {
            return null;
        }

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
        $db->table('users')->where('id', $user->id)->update([
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

        $this->audit('tenant.invite', 'user', $user->id, ['unit_number' => $unitNumber, 'email' => $email]);

        return ['temp_password' => $tempPassword];
    }
}
