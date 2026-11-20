<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Area;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * First middleware of every area group: it binds the current area and
 * configures an isolated session (cookie name, path, table, idle lifetime)
 * BEFORE the session is started, then selects the area's auth guard.
 */
final class SetArea
{
    public function __construct(private readonly SessionManager $sessions) {}

    public function handle(Request $request, Closure $next, string $area): Response
    {
        $current = Area::from($area);
        app()->instance(Area::class, $current);

        config([
            'session.cookie' => $current->sessionCookie(),
            'session.table' => $current->sessionTable(),
            'session.lifetime' => $current->idleMinutes(),
            'session.path' => $current->cookiePath(),
            // Host-only cookies: never shared across sub-domains.
            'session.domain' => null,
        ]);

        // The session manager caches its driver: rebuild it with the area configuration,
        // and re-bind the services that captured the previous store (long-lived
        // workers and the test suite run several requests in one application).
        $this->sessions->forgetDrivers();
        app()->forgetInstance('session.store');
        app('redirect')->setSession(app('session.store'));

        if ($guard = $current->guard()) {
            Auth::shouldUse($guard);
        }

        return $next($request);
    }
}
