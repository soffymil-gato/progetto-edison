<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Domain\Identity\Enums\AccountType;
use App\Domain\Identity\Services\LoginService;
use App\Http\Controllers\Controller;
use App\Support\Area;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Login and logout, shared by the customer area and the admin panel:
 * the account type is derived from the area of the route, never from input.
 */
final class LoginController extends Controller
{
    public function __construct(private readonly LoginService $login) {}

    public function show(): Response
    {
        $area = Area::current();

        return Inertia::render('Auth/Login', [
            'identifierLabel' => $area === Area::Portal ? 'Email o nome utente' : 'Email aziendale',
            'urls' => [
                'store' => route($area->routeName('login.store')),
                'forgot' => route($area->routeName('password.request')),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'identifier' => ['required', 'string', 'max:254'],
            'password' => ['required', 'string', 'max:'.(int) config('edison.auth.password_max_length')],
        ], [], ['identifier' => Area::current() === Area::Portal ? 'email o nome utente' : 'email']);

        $area = Area::current();
        $result = $this->login->attempt(AccountType::fromArea($area), $data['identifier'], $data['password'], $request);

        if (! $result->succeeded) {
            return back()->withErrors(['identifier' => $result->message])->onlyInput('identifier');
        }

        return redirect()->intended(route($area->routeName('dashboard')));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $area = Area::current();
        $this->login->logout(AccountType::fromArea($area), $request);

        return redirect()->route($area->routeName('login'))->with('success', 'Sei uscito correttamente.');
    }
}
