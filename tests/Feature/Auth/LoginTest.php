<?php

declare(strict_types=1);

use App\Domain\Audit\AuditLog;
use App\Domain\Identity\Models\PortalAccount;
use App\Domain\Identity\Models\StaffUser;
use App\Domain\Identity\Services\LoginService;
use Database\Factories\PortalAccountFactory;
use Database\Factories\StaffUserFactory;
use Illuminate\Support\Facades\Hash;

it('logs a staff user in, records the access and redirects to the dashboard', function (): void {
    $user = StaffUser::factory()->create(['email' => 'operatore@example.test']);

    $this->post('/gestionale/login', ['identifier' => 'OPERATORE@example.test ', 'password' => StaffUserFactory::PASSWORD])
        ->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($user, 'staff');
    $this->assertGuest('customer');
    expect($user->fresh()->last_login_at)->not->toBeNull()
        ->and(AuditLog::query()->where('action', 'auth.login.succeeded')->where('actor_id', $user->id)->exists())->toBeTrue();
});

it('lets customers log in with email or username', function (string $field): void {
    $account = PortalAccount::factory()->create(['email' => 'cliente@example.test', 'username' => 'cliente.prova']);

    $this->post('/area-clienti/login', ['identifier' => $field === 'email' ? 'cliente@example.test' : 'Cliente.Prova', 'password' => PortalAccountFactory::PASSWORD])
        ->assertRedirect(route('portal.dashboard'));

    $this->assertAuthenticatedAs($account, 'customer');
})->with(['email', 'username']);

it('answers with the same message for unknown accounts and wrong passwords', function (): void {
    StaffUser::factory()->create(['email' => 'esiste@example.test']);

    $wrongPassword = $this->from('/gestionale/login')->post('/gestionale/login', ['identifier' => 'esiste@example.test', 'password' => 'sbagliata-123456']);
    $unknown = $this->from('/gestionale/login')->post('/gestionale/login', ['identifier' => 'non-esiste@example.test', 'password' => 'sbagliata-123456']);

    $wrongPassword->assertSessionHasErrors(['identifier' => LoginService::INVALID_CREDENTIALS]);
    $unknown->assertSessionHasErrors(['identifier' => LoginService::INVALID_CREDENTIALS]);
    $this->assertGuest('staff');
});

it('never logs the raw identifier of failed attempts', function (): void {
    $this->post('/gestionale/login', ['identifier' => 'forse-una-password-digitata-nel-campo-sbagliato', 'password' => 'x']);

    $log = AuditLog::query()->where('action', 'auth.login.failed')->sole();
    expect(json_encode($log->getAttributes()))->not->toContain('forse-una-password')
        ->and($log->context)->toHaveKey('identifier_fingerprint');
});

it('refuses suspended accounts and accounts that never accepted the invitation', function (): void {
    StaffUser::factory()->suspended()->create(['email' => 'sospeso@example.test']);
    StaffUser::factory()->invited()->create(['email' => 'invitato@example.test']);

    $this->from('/gestionale/login')->post('/gestionale/login', ['identifier' => 'sospeso@example.test', 'password' => StaffUserFactory::PASSWORD])
        ->assertSessionHasErrors(['identifier' => LoginService::ACCOUNT_DISABLED]);
    $this->from('/gestionale/login')->post('/gestionale/login', ['identifier' => 'invitato@example.test', 'password' => ''])
        ->assertSessionHasErrors('password');
    $this->from('/gestionale/login')->post('/gestionale/login', ['identifier' => 'invitato@example.test', 'password' => 'qualsiasi-password'])
        ->assertSessionHasErrors(['identifier' => LoginService::INVALID_CREDENTIALS]);

    $this->assertGuest('staff');
});

it('throttles brute force attempts per identifier, even for unknown accounts', function (): void {
    foreach (range(1, 10) as $_) {
        $this->post('/gestionale/login', ['identifier' => 'bersaglio@example.test', 'password' => 'tentativo-sbagliato']);
    }

    $this->from('/gestionale/login')->post('/gestionale/login', ['identifier' => 'bersaglio@example.test', 'password' => 'tentativo-sbagliato'])
        ->assertSessionHasErrors(['identifier' => 'Troppi tentativi di accesso. Riprova tra 15 minuti.']);
    expect(AuditLog::query()->where('action', 'auth.login.throttled')->exists())->toBeTrue();
});

it('regenerates the session id at login (session fixation)', function (): void {
    StaffUser::factory()->create(['email' => 'fix@example.test']);
    $this->get('/gestionale/login');
    $before = session()->getId();

    $this->post('/gestionale/login', ['identifier' => 'fix@example.test', 'password' => StaffUserFactory::PASSWORD]);

    expect(session()->getId())->not->toBe($before);
});

it('stores passwords with Argon2id and upgrades weaker hashes at login', function (): void {
    $user = StaffUser::factory()->create(['email' => 'rehash@example.test']);
    expect($user->password)->toStartWith('$argon2id$');

    // Simulate a hash created with weaker parameters.
    StaffUser::query()->whereKey($user->id)->update(['password' => password_hash(StaffUserFactory::PASSWORD, PASSWORD_ARGON2ID, ['memory_cost' => 512, 'time_cost' => 1, 'threads' => 1])]);

    $this->post('/gestionale/login', ['identifier' => 'rehash@example.test', 'password' => StaffUserFactory::PASSWORD])->assertRedirect();

    expect(Hash::needsRehash((string) $user->fresh()->password))->toBeFalse();
});

it('logs out, invalidates the session and records the event', function (): void {
    $user = $this->staff();

    $this->actingAsStaff($user)->post('/gestionale/logout')->assertRedirect(route('admin.login'));

    $this->assertGuest('staff');
    expect(AuditLog::query()->where('action', 'auth.logout')->where('actor_id', $user->id)->exists())->toBeTrue();
});

it('does not offer public registration', function (): void {
    $this->get('/area-clienti/registrazione')->assertNotFound();
    $this->post('/area-clienti/register', ['email' => 'x@example.test'])->assertNotFound();
});
