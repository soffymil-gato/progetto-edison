<?php

declare(strict_types=1);

use App\Domain\Audit\AuditLog;
use App\Domain\Identity\Models\PortalAccount;
use App\Domain\Identity\Models\StaffUser;
use App\Domain\Identity\Notifications\ResetPasswordNotification;
use App\Http\Controllers\Auth\ForgotPasswordController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

it('gives the same answer for existing and unknown addresses', function (): void {
    Notification::fake();
    $user = StaffUser::factory()->create(['email' => 'reale@example.test']);

    $this->from('/gestionale/password/recupero')->post('/gestionale/password/recupero', ['email' => 'reale@example.test'])
        ->assertSessionHas('status', ForgotPasswordController::GENERIC_ANSWER);
    $this->from('/gestionale/password/recupero')->post('/gestionale/password/recupero', ['email' => 'nessuno@example.test'])
        ->assertSessionHas('status', ForgotPasswordController::GENERIC_ANSWER);

    Notification::assertSentTo($user, ResetPasswordNotification::class);
    Notification::assertCount(1);
});

it('does not send reset links to suspended or invited accounts', function (): void {
    Notification::fake();
    $suspended = StaffUser::factory()->suspended()->create();
    $invited = StaffUser::factory()->invited()->create();

    $this->post('/gestionale/password/recupero', ['email' => $suspended->email]);
    $this->post('/gestionale/password/recupero', ['email' => $invited->email]);

    Notification::assertNothingSent();
});

it('keeps the two areas separate: a staff address gets nothing from the customer form', function (): void {
    Notification::fake();
    $staff = StaffUser::factory()->create();

    $this->post('/area-clienti/password/recupero', ['email' => $staff->email]);

    Notification::assertNothingSent();
});

it('resets the password with a valid token, closes the other sessions and audits it', function (): void {
    $account = PortalAccount::factory()->create();
    $token = Password::broker('customers')->createToken($account);
    DB::table('portal_sessions')->insert(['id' => 'sessione-altro-dispositivo', 'user_id' => $account->id, 'payload' => '', 'last_activity' => time()]);

    $this->post('/area-clienti/password/reimposta', [
        'token' => $token,
        'email' => $account->email,
        'password' => 'una-nuova-password-sicura',
        'password_confirmation' => 'una-nuova-password-sicura',
    ])->assertRedirect(route('portal.login'));

    expect(Hash::check('una-nuova-password-sicura', (string) $account->fresh()->password))->toBeTrue()
        ->and(DB::table('portal_sessions')->where('user_id', $account->id)->count())->toBe(0)
        ->and(AuditLog::query()->where('action', 'auth.password_reset.completed')->exists())->toBeTrue();
});

it('rejects invalid tokens and short passwords', function (): void {
    $account = PortalAccount::factory()->create();

    $this->from('/area-clienti/password/reimposta/x')->post('/area-clienti/password/reimposta', [
        'token' => 'token-non-valido',
        'email' => $account->email,
        'password' => 'una-nuova-password-sicura',
        'password_confirmation' => 'una-nuova-password-sicura',
    ])->assertSessionHasErrors('email');

    $this->from('/area-clienti/password/reimposta/x')->post('/area-clienti/password/reimposta', [
        'token' => Password::broker('customers')->createToken($account),
        'email' => $account->email,
        'password' => 'corta',
        'password_confirmation' => 'corta',
    ])->assertSessionHasErrors('password');
});
