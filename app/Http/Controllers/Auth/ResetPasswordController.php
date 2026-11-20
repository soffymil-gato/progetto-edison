<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Domain\Audit\AuditLogger;
use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Identity\Enums\AccountType;
use App\Domain\Identity\Models\Account;
use App\Domain\Identity\Services\PasswordRules;
use App\Http\Controllers\Controller;
use App\Support\Area;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

final class ResetPasswordController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function show(Request $request, string $token): Response
    {
        $area = Area::current();

        return Inertia::render('Auth/ResetPassword', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
            'passwordMinLength' => (int) config('edison.auth.password_min_length'),
            'urls' => [
                'store' => route($area->routeName('password.update')),
                'login' => route($area->routeName('login')),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc', 'max:254'],
            'password' => PasswordRules::forNewPassword(),
        ]);

        $area = Area::current();
        $type = AccountType::fromArea($area);

        $status = Password::broker($type->passwordBroker())->reset(
            [
                'email' => mb_strtolower(trim($data['email'])),
                'status' => AccountStatus::Active->value,
                'token' => $data['token'],
                'password' => $data['password'],
            ],
            function (Account $account, string $password) use ($area): void {
                $account->password = $password;
                $account->password_changed_at = now();
                $account->save();

                // Every existing session of this account in this area is closed.
                DB::table($area->sessionTable())->where('user_id', $account->getKey())->delete();

                $this->audit->record('auth.password_reset.completed', $account, $account);
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => 'Il link di reimpostazione non è valido o è scaduto. Richiedine uno nuovo.']);
        }

        return redirect()->route($area->routeName('login'))->with('success', 'Password aggiornata. Ora puoi accedere con la nuova password.');
    }
}
