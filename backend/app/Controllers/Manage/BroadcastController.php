<?php

declare(strict_types=1);

namespace App\Controllers\Manage;

use App\Controllers\BaseController;
use App\Libraries\Notifications\NotificationService;
use CodeIgniter\Shield\Models\UserModel;

/** Body Corporate broadcasts a message to their own property's tenants only. */
class BroadcastController extends BaseController
{
    public function index()
    {
        return view('manage/broadcast/index', [
            'title'      => 'Broadcast — Dropa Manage',
            'panelLabel' => 'Body Corporate',
            'nav'        => view('manage/_nav'),
        ]);
    }

    public function send()
    {
        $propertyId = auth()->user()->property_id;
        $title      = trim((string) $this->request->getPost('title'));
        $body       = trim((string) $this->request->getPost('body'));

        if ($title === '' || $body === '') {
            return redirect()->to('/manage/broadcast')->with('error', 'Title and message are required.');
        }

        $tenants = (new UserModel())->where('property_id', $propertyId)
            ->whereIn('id', static function ($builder) {
                $builder->select('user_id')->from('auth_groups_users')->where('group', 'tenant');
            })
            ->findAll();

        $service = new NotificationService();
        foreach ($tenants as $tenant) {
            $service->notify(
                ['id' => $tenant->id, 'email' => $tenant->email, 'phone' => $tenant->phone, 'push_token' => $tenant->push_token],
                $title,
                $body,
            );
        }

        $this->audit('broadcast.send', 'property', (int) $propertyId, ['recipients' => count($tenants)]);

        return redirect()->to('/manage/broadcast')->with('success', count($tenants) . ' tenant(s) notified.');
    }
}
