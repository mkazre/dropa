<?php

declare(strict_types=1);

namespace App\Controllers;

/**
 * Where Shield sends a browser session right after login (see
 * Config\Auth::$redirects['login']) — routes each role to its own panel,
 * since there's no dashboard at '/' itself.
 */
class PostLoginController extends BaseController
{
    public function index()
    {
        $user = auth()->user();

        if ($user === null) {
            return redirect()->to('/login');
        }

        if ($user->inGroup('superadmin')) {
            return redirect()->to('/admin');
        }

        if ($user->inGroup('property_admin', 'staff')) {
            return redirect()->to('/manage');
        }

        // Tenants use the mobile app, not this web session — nothing to send them to here.
        return redirect()->to('/')->with('error', 'This account uses the Dropa mobile app, not this website.');
    }
}
