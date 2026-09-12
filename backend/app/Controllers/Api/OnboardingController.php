<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\OnboardingRequestModel;
use App\Models\PlatformSettingModel;

/** Public "bring Dropa to your property" lead form on the marketing website — no account needed. */
class OnboardingController extends BaseApiController
{
    /** Lets the website know whether to show the form at all, before the visitor fills it in. */
    public function status()
    {
        return $this->respond(['enabled' => $this->isEnabled()]);
    }

    public function request()
    {
        if (! $this->isEnabled()) {
            return $this->fail('Self-service signups are closed right now — please contact us directly.', 403);
        }

        $propertyName = trim((string) $this->request->getJsonVar('property_name'));
        $contactName  = trim((string) $this->request->getJsonVar('contact_name'));
        $email        = trim((string) $this->request->getJsonVar('email'));
        $unitCount    = $this->request->getJsonVar('unit_count');

        if ($propertyName === '' || $contactName === '' || $email === '') {
            return $this->failValidationErrors('Property name, your name and email are required.');
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->failValidationErrors('Please provide a valid email address.');
        }

        $id = model(OnboardingRequestModel::class)->insert([
            'property_name' => $propertyName,
            'unit_count'    => $unitCount !== null && $unitCount !== '' ? (int) $unitCount : null,
            'contact_name'  => $contactName,
            'email'         => $email,
            'status'        => 'new',
        ]);

        return $this->respondCreated(['id' => $id]);
    }

    private function isEnabled(): bool
    {
        return model(PlatformSettingModel::class)->get('self_registration_enabled', '1') === '1';
    }
}
