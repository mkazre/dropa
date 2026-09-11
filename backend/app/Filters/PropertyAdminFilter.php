<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Lets Body Corporate admins (and staff) through to /manage/*. Controllers
 * must still scope every query to auth()->user()->property_id — this filter
 * only checks the role, it does not itself enforce per-property isolation.
 */
class PropertyAdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $user = auth()->user();

        if ($user === null) {
            return redirect()->to('/login');
        }

        if (! $user->inGroup('property_admin', 'staff', 'superadmin')) {
            return redirect()->to('/')->with('error', 'Property admin access only.');
        }

        if (! $user->inGroup('superadmin') && empty($user->property_id)) {
            return redirect()->to('/login')->with('error', 'Your account is not linked to a property yet.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
