<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Identity\Enums\AccountType;
use App\Http\Controllers\Controller;
use App\Support\Area;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Password recovery request. The answer is always the same, whether or not
 * the address belongs to an (active) account.
 */
final class ForgotPasswordController extends Controller
{
    public const GENERIC_ANSWER = 'Se l\'indirizzo corrisponde a un account attivo, riceverai a breve un\'email con le istruzioni per reimpostare la password.';

    public function show(): Response
    {
        $area = Area::current();

        return Inertia::render('Auth/ForgotPassword', [
            'urls' => [
                'store' => route($area->routeName('password.email')),
                'login' => route($area->routeName('login')),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'string', 'email:rfc', 'max:254']]);

        $type = AccountType::fromArea(Area::current());
        // Only active accounts can recover their password (invited accounts use their invitation).
        Password::broker($type->passwordBroker())->sendResetLink([
            'email' => mb_strtolower(trim($data['email'])),
            'status' => AccountStatus::Active->value,
        ]);

        return back()->with('status', self::GENERIC_ANSWER);
    }
}
