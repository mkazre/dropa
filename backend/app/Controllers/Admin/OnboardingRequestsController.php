<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OnboardingRequestModel;
use App\Models\PlatformSettingModel;

/** Super Admin's view of leads from the website's "Bring Dropa to your property" form, plus the on/off switch for that form. */
class OnboardingRequestsController extends BaseController
{
    public function index()
    {
        $settings = model(PlatformSettingModel::class);

        return view('admin/onboarding_requests/index', [
            'title'      => 'Onboarding Requests — Dropa Admin',
            'panelLabel' => 'Super Admin',
            'nav'        => view('admin/_nav'),
            'requests'   => model(OnboardingRequestModel::class)->orderBy('created_at', 'DESC')->findAll(),
            'enabled'    => $settings->get('self_registration_enabled', '1') === '1',
        ]);
    }

    public function toggle()
    {
        $enabled = $this->request->getPost('enabled') ? '1' : '0';
        model(PlatformSettingModel::class)->setValue('self_registration_enabled', $enabled);

        $this->audit('onboarding.toggle', 'platform_settings', null, ['enabled' => $enabled]);

        return redirect()->to('/admin/onboarding-requests')
            ->with('success', 'Self-registration is now ' . ($enabled === '1' ? 'enabled' : 'disabled') . '.');
    }

    public function updateStatus($id)
    {
        $status = $this->request->getPost('status');

        if (! in_array($status, ['new', 'contacted', 'converted', 'dismissed'], true)) {
            return redirect()->to('/admin/onboarding-requests')->with('error', 'Invalid status.');
        }

        model(OnboardingRequestModel::class)->update($id, ['status' => $status]);
        $this->audit('onboarding.status', 'onboarding_requests', (int) $id, ['status' => $status]);

        return redirect()->to('/admin/onboarding-requests')->with('success', 'Request updated.');
    }
}
