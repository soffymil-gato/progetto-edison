<?php

declare(strict_types=1);

use App\Domain\Audit\AuditLog;
use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Identity\Models\StaffUser;
use App\Domain\Identity\Notifications\AccountInvitationNotification;
use App\Domain\Identity\Permissions\DefaultRoles;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

it('creates a user as invited and sends the invitation: nobody types their password', function (): void {
    Notification::fake();
    $admin = $this->superAdmin();

    $this->actingAsStaff($admin)->post('/gestionale/utenti', [
        'name' => 'Nuovo Operatore',
        'email' => 'Nuovo.Operatore@example.test',
        'roles' => ['operatore'],
        'password' => 'tentativo-di-impostare-la-password',
    ])->assertRedirect();

    $user = StaffUser::query()->where('email', 'nuovo.operatore@example.test')->sole();
    expect($user->status)->toBe(AccountStatus::Invited)
        ->and($user->password)->toBeNull()
        ->and($user->hasRole('operatore'))->toBeTrue()
        ->and(AuditLog::query()->where('action', 'staff_user.created')->where('subject_id', (string) $user->id)->exists())->toBeTrue();
    Notification::assertSentTo($user, AccountInvitationNotification::class);
});

it('rejects duplicate emails', function (): void {
    $admin = $this->superAdmin();
    StaffUser::factory()->create(['email' => 'doppio@example.test']);

    $this->actingAsStaff($admin)->post('/gestionale/utenti', ['name' => 'X', 'email' => 'DOPPIO@example.test', 'roles' => ['operatore']])
        ->assertSessionHasErrors(['email' => 'Il valore indicato per email è già in uso.']);
});

it('prevents a non super admin from granting the Super Admin role or sensitive roles', function (): void {
    Notification::fake();
    $manager = $this->staff(['users.view', 'users.manage']);

    $this->actingAsStaff($manager)->post('/gestionale/utenti', ['name' => 'Scalata', 'email' => 'scalata@example.test', 'roles' => [DefaultRoles::SUPER_ADMIN]])
        ->assertSessionHasErrors('roles');

    expect(StaffUser::query()->where('email', 'scalata@example.test')->exists())->toBeFalse();
});

it('prevents a non super admin from modifying a Super Admin', function (): void {
    $manager = $this->staff(['users.view', 'users.manage']);
    $super = $this->superAdmin();

    $this->actingAsStaff($manager)->post("/gestionale/utenti/{$super->id}/sospendi")->assertSessionHasErrors('user');
    expect($super->fresh()->status)->toBe(AccountStatus::Active);
});

it('prevents users from changing their own roles or suspending themselves', function (): void {
    $admin = $this->superAdmin();
    $this->superAdmin();

    $this->actingAsStaff($admin)->put("/gestionale/utenti/{$admin->id}", ['name' => $admin->name, 'roles' => ['operatore']])
        ->assertSessionHasErrors('roles');
    $this->actingAsStaff($admin)->post("/gestionale/utenti/{$admin->id}/sospendi")->assertSessionHasErrors('user');

    expect($admin->fresh()->isSuperAdmin())->toBeTrue();
});

it('always keeps at least one active Super Admin', function (): void {
    $first = $this->superAdmin();
    $second = $this->superAdmin();

    $this->actingAsStaff($first)->post("/gestionale/utenti/{$second->id}/sospendi")->assertSessionHasNoErrors();
    expect($second->fresh()->status)->toBe(AccountStatus::Suspended);

    // $first is now the only active Super Admin: nobody else can demote it.
    $third = $this->superAdmin();
    $third->status = AccountStatus::Suspended;
    $third->save();
    $this->actingAsStaff($first)->put("/gestionale/utenti/{$third->id}", ['name' => $third->name, 'roles' => ['operatore']])
        ->assertSessionHasNoErrors();
});

it('closes the sessions of a suspended user immediately', function (): void {
    $admin = $this->superAdmin();
    $user = $this->staff(role: 'operatore');
    DB::table('admin_sessions')->insert(['id' => 'sessione-operatore', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()]);

    $this->actingAsStaff($admin)->post("/gestionale/utenti/{$user->id}/sospendi")->assertSessionHasNoErrors();

    expect(DB::table('admin_sessions')->where('user_id', $user->id)->exists())->toBeFalse()
        ->and(AuditLog::query()->where('action', 'staff_user.suspended')->exists())->toBeTrue();
});

it('reactivates a suspended user', function (): void {
    $admin = $this->superAdmin();
    $user = StaffUser::factory()->suspended()->create();

    $this->actingAsStaff($admin)->post("/gestionale/utenti/{$user->id}/riattiva")->assertSessionHasNoErrors();

    expect($user->fresh()->status)->toBe(AccountStatus::Active);
});

it('searches users with literal wildcards', function (): void {
    $admin = $this->superAdmin();
    StaffUser::factory()->create(['name' => 'Anna 100% Prova']);
    StaffUser::factory()->create(['name' => 'Bruno 1000 Prova']);

    $this->actingAsStaff($admin)->get('/gestionale/utenti?q=100%25')
        ->assertInertia(fn ($page) => $page->has('users.data', 1)->where('users.data.0.name', 'Anna 100% Prova'));
});
