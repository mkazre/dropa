<?php

namespace App\Controllers;

/**
 * This domain is the API + admin backend, not a marketing site — there's
 * nothing to show at "/" itself. Send a browser straight to login rather
 * than ever falling through to CodeIgniter's stock welcome page.
 */
class Home extends BaseController
{
    public function index()
    {
        return redirect()->to('/login');
    }
}
