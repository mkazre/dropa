<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\Notifications\NotificationService;
use App\Models\PropertyModel;
use CodeIgniter\Shield\Models\UserModel;

/** Super Admin broadcasts a message to every tenant, or one property's tenants. */
class BroadcastController extends BaseController
{
    public function index()
    {
        return view('admin/broadcast/index', [
            'title'      => 'Broadcast — Dropa Admin',
            'panelLabel' => 'Super Admin',
            'nav'        => view('admin/_nav'),
            'properties' => model(PropertyModel::class)->orderBy('name', 'ASC')->findAll(),
        ]);
    }

    public function send()
    {
        $title      = trim((string) $this->request->getPost('title'));
        $body       = trim((string) $this->request->getPost('body'));
        $propertyId = $this->request->getPost('property_id');

        if ($title === '' || $body === '') {
            return redirect()->to('/admin/broadcast')->with('error', 'Title and message are required.');
        }

        $users = (new UserModel())->whereIn('id', static function ($builder) {
            $builder->select('user_id')->from('auth_groups_users')->where('group', 'tenant');
        });
        if (! empty($propertyId)) {
            $users->where('property_id', $propertyId);
        }
        $tenants = $users->findAll();

        $service = new NotificationService();
        foreach ($tenants as $tenant) {
            $service->notify(
                ['id' => $tenant->id, 'email' => $tenant->email, 'phone' => $tenant->phone, 'push_token' => $tenant->push_token],
                $title,
                $body,
            );
        }

        $this->audit('broadcast.send', 'users', null, ['property_id' => $propertyId, 'recipients' => count($tenants)]);

        return redirect()->to('/admin/broadcast')->with('success', count($tenants) . ' tenant(s) notified.');
    }
}
