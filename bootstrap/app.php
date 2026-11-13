<?php

declare(strict_types=1);

use App\Http\Middleware\EnforceSessionPolicy;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\PreventRequestForgery;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetArea;
use App\Support\Area;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            // Portal and admin first: their prefixes must win over public routes.
            foreach ([Area::Portal, Area::Admin, Area::Public] as $area) {
                $registrar = Route::middleware($area->value)->name($area->value.'.');

                if ($domain = $area->domain()) {
                    $registrar->domain($domain);
                } elseif ($area->prefix() !== '') {
                    $registrar->prefix($area->prefix());
                }

                $registrar->group(base_path('routes/'.$area->value.'.php'));
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);

        $session = [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            PreventRequestForgery::class,
            SubstituteBindings::class,
        ];

        // SetArea must run before StartSession: it configures the area's isolated session.
        $middleware->group('public', [SetArea::class.':public', ...$session, HandleInertiaRequests::class]);
        $middleware->group('portal', [SetArea::class.':portal', ...$session, EnforceSessionPolicy::class, HandleInertiaRequests::class]);
        $middleware->group('admin', [SetArea::class.':admin', ...$session, EnforceSessionPolicy::class, HandleInertiaRequests::class]);

        $middleware->redirectGuestsTo(static fn (Request $request): string => route(Area::current()->routeName('login')));
        $middleware->redirectUsersTo(static fn (Request $request): string => route(Area::current() === Area::Admin ? 'admin.dashboard' : 'portal.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
