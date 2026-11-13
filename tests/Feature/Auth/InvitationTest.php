<?php

declare(strict_types=1);

use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Identity\Models\AccountInvitation;
use App\Domain\Identity\Models\PortalAccount;
use App\Domain\Identity\Models\StaffUser;
use App\Domain\Identity\Notifications\AccountInvitationNotification;
use App\Domain\Identity\Services\InvitationService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;

it('stores only the hash of the invitation token', function (): void {
    Notification::fake();
    $user = StaffUser::factory()->invited()->create();

    $token = app(InvitationService::class)->invite($user, null);

    $invitation = AccountInvitation::query()->sole();
    expect($invitation->token_hash)->toBe(hash('sha256', $token))->not->toBe($token);
    Notification::assertSentTo($user, AccountInvitationNotification::class);
});

it('activates the account with the password chosen by the invited person', function (): void {
    Notification::fake();
    $user = StaffUser::factory()->invited()->create();
    $token = app(InvitationService::class)->invite($user, null);

    $this->get("/gestionale/invito/{$token}")
        ->assertOk()
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
        ->assertInertia(fn (Assert $page) => $page->component('Auth/AcceptInvitation')->where('valid', true)->where('email', $user->email));

    $this->post("/gestionale/invito/{$token}", ['password' => 'password-scelta-da-me', 'password_confirmation' => 'password-scelta-da-me'])
        ->assertRedirect(route('admin.login'));

    $user->refresh();
    expect($user->status)->toBe(AccountStatus::Active)
        ->and(Hash::check('password-scelta-da-me', (string) $user->password))->toBeTrue();

    // The link cannot be used twice.
    $this->get("/gestionale/invito/{$token}")->assertInertia(fn (Assert $page) => $page->where('valid', false));
});

it('rejects expired invitations and invitations replaced by a newer one', function (): void {
    Notification::fake();
    $user = StaffUser::factory()->invited()->create();
    $service = app(InvitationService::class);

    $old = $service->invite($user, null);
    $new = $service->invite($user, null);

    $this->get("/gestionale/invito/{$old}")->assertInertia(fn (Assert $page) => $page->where('valid', false));
    $this->get("/gestionale/invito/{$new}")->assertInertia(fn (Assert $page) => $page->where('valid', true));

    $this->travel(73)->hours();
    $this->get("/gestionale/invito/{$new}")->assertInertia(fn (Assert $page) => $page->where('valid', false));
    $this->post("/gestionale/invito/{$new}", ['password' => 'password-scelta-da-me', 'password_confirmation' => 'password-scelta-da-me'])
        ->assertSessionHasErrors('password');
});

it('does not accept a customer invitation in the admin panel', function (): void {
    Notification::fake();
    $account = PortalAccount::factory()->invited()->create();
    $token = app(InvitationService::class)->invite($account, null);

    $this->get("/gestionale/invito/{$token}")->assertInertia(fn (Assert $page) => $page->where('valid', false));
    $this->get("/area-clienti/invito/{$token}")->assertInertia(fn (Assert $page) => $page->where('valid', true));
});
