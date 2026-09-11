<?php

declare(strict_types=1);

namespace App\Controllers\Manage;

use App\Controllers\BaseController;
use App\Libraries\Notifications\NotificationService;
use App\Models\PropertyModel;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;

/**
 * On-site staff (concierge/security) — scoped to one property like a tenant,
 * but in the `staff` group so PropertyAdminFilter's kiosk-assist permissions
 * apply instead of tenant self-service ones. No unit: staff aren't residents.
 */
class StaffController extends BaseController
{
    public function index()
    {
        $propertyId = auth()->user()->property_id;
        $staff      = (new UserModel())->where('property_id', $propertyId)->whereIn('id',
            static function ($builder) {
                $builder->select('user_id')->from('auth_groups_users')->where('group', 'staff');
            }
        )->findAll();

        return view('manage/staff/index', [
            'title'      => 'Staff — Dropa Manage',
            'panelLabel' => 'Body Corporate',
            'nav'        => view('manage/_nav'),
            'staff'      => $staff,
        ]);
    }

    public function new()
    {
        return view('manage/staff/form', [
            'title'      => 'Invite Staff — Dropa Manage',
            'panelLabel' => 'Body Corporate',
            'nav'        => view('manage/_nav'),
        ]);
    }

    public function create()
    {
        $propertyId = auth()->user()->property_id;
        $email      = trim((string) $this->request->getPost('email'));
        $fullName   = trim((string) $this->request->getPost('full_name'));
        $phone      = trim((string) $this->request->getPost('phone'));

        if ($email === '') {
            return redirect()->to('/manage/staff/new')->with('error', 'Email is required.');
        }

        $db = db_connect();
        if ($db->table('auth_identities')->where('secret', $email)->countAllResults() > 0) {
            return redirect()->to('/manage/staff')->with('error', "{$email} already has an account.");
        }

        $tempPassword = bin2hex(random_bytes(6));
        $users        = new UserModel();

        $user = new User(['username' => $email]);
        $users->save($user);
        $user = $users->findById($users->getInsertID());
        $user->createEmailIdentity(['email' => $email, 'password' => $tempPassword]);
        $user->addGroup('staff');

        $db->table('users')->where('id', $user->id)->update([
            'property_id' => $propertyId,
            'full_name'   => $fullName,
            'phone'       => $phone,
        ]);

        $property = model(PropertyModel::class)->find($propertyId);
        (new NotificationService())->notify(
            ['id' => $user->id, 'email' => $email, 'phone' => $phone, 'push_token' => null],
            "You've been added as staff on Dropa",
            "{$property['name']} has added you as on-site staff. Sign in with {$email} and this temporary password: {$tempPassword}.",
            ['email'],
        );

        $this->audit('staff.invite', 'user', $user->id, ['email' => $email]);

        return redirect()->to('/manage/staff')->with('success', "Staff invited. Temporary password (also emailed): {$tempPassword}");
    }

    public function delete($id)
    {
        $propertyId = auth()->user()->property_id;
        $users      = new UserModel();
        $staffMember = $users->find($id);

        if ($staffMember === null || (int) $staffMember->property_id !== (int) $propertyId || ! $staffMember->inGroup('staff')) {
            return redirect()->to('/manage/staff')->with('error', 'Staff member not found.');
        }

        $users->delete($id);
        $this->audit('staff.delete', 'user', (int) $id);

        return redirect()->to('/manage/staff')->with('success', 'Staff member removed.');
    }
}
