<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Domain\Identity\Enums\AccountType;
use App\Domain\Identity\Services\InvitationService;
use App\Domain\Identity\Services\PasswordRules;
use App\Http\Controllers\Controller;
use App\Support\Area;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Activation of an account created by the administration: the invited person
 * chooses their own password. Nobody else ever knows it.
 */
final class InvitationController extends Controller
{
    public function __construct(private readonly InvitationService $invitations) {}

    public function show(string $token): Response
    {
        $area = Area::current();
        $invitation = $this->invitations->findValid($token);
        $valid = $invitation !== null && $invitation->account_type === AccountType::fromArea($area);

        return Inertia::render('Auth/AcceptInvitation', [
            'valid' => $valid,
            'email' => $valid ? $invitation->account()?->email : null,
            'passwordMinLength' => (int) config('edison.auth.password_min_length'),
            'urls' => [
                'store' => route($area->routeName('invitation.store'), ['token' => $token]),
                'login' => route($area->routeName('login')),
            ],
        ])->withViewData(['referrerPolicy' => 'no-referrer']);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $data = $request->validate(['password' => PasswordRules::forNewPassword()]);

        $area = Area::current();
        $invitation = $this->invitations->findValid($token);

        if ($invitation === null || $invitation->account_type !== AccountType::fromArea($area)) {
            return back()->withErrors(['password' => 'L\'invito non è valido o è scaduto. Chiedi all\'amministrazione di inviarne uno nuovo.']);
        }

        $this->invitations->accept($invitation, $data['password']);

        return redirect()->route($area->routeName('login'))->with('success', 'Account attivato. Ora puoi accedere con la password scelta.');
    }
}
