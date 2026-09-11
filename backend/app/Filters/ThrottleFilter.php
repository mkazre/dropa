<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Rate-limits an endpoint by IP. Needed because deposit/collect are
 * intentionally public (the code itself is the authorization, like a
 * physical locker) — without this, a 6-digit PIN (900k combinations) would
 * be brute-forceable from a single IP in minutes.
 *
 * Usage in Routes.php: ['filter' => 'throttle:10,60'] = 10 requests per 60s.
 */
class ThrottleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $capacity = (int) ($arguments[0] ?? 10);
        $seconds  = (int) ($arguments[1] ?? 60);

        $throttler = service('throttler');
        // Cache keys can't contain : @ ( ) { } \ / — keep it to safe characters.
        $key = 'ip_' . str_replace(['.', ':'], '_', $request->getIPAddress()) . '_' . $capacity . '_' . $seconds;

        if (! $throttler->check($key, $capacity, $seconds)) {
            return service('response')
                ->setStatusCode(429)
                ->setJSON(['status' => 429, 'error' => 429, 'messages' => ['error' => 'Too many attempts. Please wait a moment and try again.']]);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
