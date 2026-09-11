<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AuditLogModel;
use CodeIgniter\Shield\Models\UserModel;

class AuditLogController extends BaseController
{
    public function index()
    {
        $entries = model(AuditLogModel::class)->orderBy('id', 'DESC')->paginate(50);

        $userIds  = array_filter(array_unique(array_column($entries, 'user_id')));
        $usersById = $userIds ? array_column((new UserModel())->whereIn('id', $userIds)->findAll(), null, 'id') : [];

        return view('admin/audit/index', [
            'title'      => 'Audit Log — Dropa Admin',
            'panelLabel' => 'Super Admin',
            'nav'        => view('admin/_nav'),
            'entries'    => $entries,
            'usersById'  => $usersById,
            'pager'      => model(AuditLogModel::class)->pager,
        ]);
    }
}
