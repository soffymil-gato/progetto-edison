<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Domain\Audit\AuditLogger;
use App\Domain\Identity\Models\Account;
use App\Domain\Identity\Models\PortalAccount;
use App\Domain\Identity\Models\StaffUser;
use App\Domain\Identity\Permissions\DefaultRoles;
use App\Domain\Identity\Services\PasswordRules;
use App\Http\Controllers\Controller;
use App\Support\Area;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Personal profile and password change (customer area and admin panel).
 */
final class ProfileController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function edit(Request $request): Response
    {
        $area = Area::current();
        /** @var Account $account */
        $account = $request->user();

        return Inertia::render('Account/Profile', [
            'account' => [
                'name' => $account->displayName(),
                'email' => $account->email,
                'username' => $account instanceof PortalAccount ? $account->username : null,
                'roles' => $account instanceof StaffUser ? $account->getRoleNames()->map(fn (string $r): string => DefaultRoles::label($r))->all() : [],
                'lastLoginAt' => $account->last_login_at?->toIso8601String(),
                'passwordChangedAt' => $account->password_changed_at?->toIso8601String(),
            ],
            'passwordMinLength' => (int) config('edison.auth.password_min_length'),
            'urls' => ['password' => route($area->routeName('profile.password'))],
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $area = Area::current();
        $data = $request->validate([
            'current_password' => ['required', 'string', 'current_password:'.$area->guard()],
            'password' => [...PasswordRules::forNewPassword(), 'different:current_password'],
        ]);

        /** @var Account $account */
        $account = $request->user();
        $account->password = $data['password'];
        $account->password_changed_at = now();
        $account->save();

        // Close the other sessions of this account; keep the current one.
        DB::table($area->sessionTable())
            ->where('user_id', $account->getKey())
            ->where('id', '!=', $request->session()->getId())
            ->delete();
        $request->session()->regenerate();

        $this->audit->record('account.password_changed', $account, $account);

        return back()->with('success', 'Password aggiornata. Le altre sessioni attive sono state chiuse.');
    }
}
