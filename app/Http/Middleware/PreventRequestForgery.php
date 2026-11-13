<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Area;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery as BaseMiddleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Laravel's CSRF protection (Sec-Fetch-Site origin check with token fallback)
 * with an XSRF cookie name specific to each area, so that tokens of
 * different areas never collide in the browser.
 */
final class PreventRequestForgery extends BaseMiddleware
{
    /**
     * @param  Request  $request
     * @param  array<string, mixed>  $config
     */
    protected function newCookie($request, $config): Cookie
    {
        return new Cookie(
            Area::current()->xsrfCookie(),
            $request->session()->token(),
            $this->availableAt(60 * (int) $config['lifetime']),
            $config['path'],
            $config['domain'],
            $config['secure'],
            false,
            false,
            $config['same_site'] ?? null,
            $config['partitioned'] ?? false,
        );
    }
}
