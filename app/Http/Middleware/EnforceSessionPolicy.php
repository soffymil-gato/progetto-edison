<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Identity\Models\Account;
use App\Support\Area;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * For authenticated sessions of the portal and the admin panel:
 * - logs out accounts that are no longer active (e.g. suspended meanwhile);
 * - enforces the absolute session lifetime, independently of activity.
 * The idle timeout is enforced by the session lifetime (see SetArea).
 */
final class EnforceSessionPolicy
{
    public function handle(Request $request, Closure $next): Response
    {
        $area = Area::current();
        $guard = $area->guard();

        if ($guard === null || ! Auth::guard($guard)->check()) {
            return $next($request);
        }

        /** @var Account $account */
        $account = Auth::guard($guard)->user();
        $startedAt = (int) $request->session()->get('auth.started_at', 0);
        if ($startedAt === 0) {
            // Set at login by LoginService; initialise it defensively if missing.
            $startedAt = now()->getTimestamp();
            $request->session()->put('auth.started_at', $startedAt);
        }

        $absolute = $area->absoluteMinutes();
        $expired = $absolute !== null && now()->getTimestamp() - $startedAt > $absolute * 60;

        if (! $account->isActive() || $expired) {
            Auth::guard($guard)->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = $account->isActive()
                ? 'La sessione è scaduta: accedi di nuovo.'
                : 'L\'account non è più attivo.';

            return redirect()->route($area->routeName('login'))->with('error', $message);
        }

        return $next($request);
    }
}
