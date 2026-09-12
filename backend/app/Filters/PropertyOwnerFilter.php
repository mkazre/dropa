<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Stricter than PropertyAdminFilter: property_admin only, not staff. Staff
 * get "limited kiosk-assist permissions" (per the plan), so anything that
 * manages tenants/staff/money/settings for the property routes through
 * this instead of the general propertyadmin filter. (Super Admins never
 * reach this filter at all — PropertyAdminFilter, which runs first on the
 * whole /manage/* group, already keeps them out; see its docblock.)
 */
class PropertyOwnerFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $user = auth()->user();

        if ($user === null) {
            return redirect()->to('/login');
        }

        if (! $user->inGroup('property_admin')) {
            return redirect()->to('/manage')->with('error', 'Body Corporate access only — staff accounts can\'t manage this.');
        }

        if (empty($user->property_id)) {
            return redirect()->to('/login')->with('error', 'Your account is not linked to a property yet.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
