<?php

declare(strict_types=1);

use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Identity\Models\StaffUser;
use Database\Factories\StaffUserFactory;

it('does not let a customer into the admin panel', function (): void {
    $this->actingAsCustomer($this->customer())
        ->get('/gestionale')
        ->assertRedirect(route('admin.login'));
});

it('does not let a staff user into the customer area', function (): void {
    $this->actingAsStaff($this->superAdmin())
        ->get('/area-clienti')
        ->assertRedirect(route('portal.login'));
});

it('uses a dedicated session cookie and path for each area', function (): void {
    $admin = $this->get('/gestionale/login');
    $portal = $this->get('/area-clienti/login');

    $adminCookie = collect($admin->headers->getCookies())->firstWhere(fn ($c) => $c->getName() === 'edison_admin_session');
    $portalCookie = collect($portal->headers->getCookies())->firstWhere(fn ($c) => $c->getName() === 'edison_portal_session');

    expect($adminCookie)->not->toBeNull()
        ->and($adminCookie->getPath())->toBe('/gestionale')
        ->and($adminCookie->isHttpOnly())->toBeTrue()
        ->and($adminCookie->getSameSite())->toBe('lax')
        ->and($portalCookie)->not->toBeNull()
        ->and($portalCookie->getPath())->toBe('/area-clienti');
    expect(collect($admin->headers->getCookies())->map(fn ($c) => $c->getName())->all())->toContain('XSRF-TOKEN-ADMIN')
        ->and(collect($portal->headers->getCookies())->map(fn ($c) => $c->getName())->all())->toContain('XSRF-TOKEN-PORTAL');
});

it('logs out a staff user suspended during an active session', function (): void {
    $user = $this->superAdmin();
    $this->superAdmin(); // another super admin keeps the system administrable

    $user->status = AccountStatus::Suspended;
    $user->save();

    $this->actingAsStaff($user)->get('/gestionale')
        ->assertRedirect(route('admin.login'))
        ->assertSessionHas('error', 'L\'account non è più attivo.');
    $this->assertGuest('staff');
});

it('enforces the absolute session lifetime of 12 hours, even with continuous activity', function (): void {
    StaffUser::factory()->create(['email' => 'lunga.sessione@example.test']);

    $this->post('/gestionale/login', ['identifier' => 'lunga.sessione@example.test', 'password' => StaffUserFactory::PASSWORD]);
    $sessionId = session()->getId();

    // Activity every 20 minutes keeps the session alive (idle timeout: 30 minutes)…
    foreach (range(1, 35) as $_) {
        $this->travel(20)->minutes();
        $this->withCookie('edison_admin_session', $sessionId)->get('/gestionale')->assertOk();
    }

    // …but 12 hours after the login the session ends anyway.
    $this->travel(21)->minutes();
    $this->withCookie('edison_admin_session', $sessionId)->get('/gestionale')
        ->assertRedirect(route('admin.login'))
        ->assertSessionHas('error', 'La sessione è scaduta: accedi di nuovo.');
});

it('shows the customer only their own account data', function (): void {
    $mine = $this->customer();
    $other = $this->customer();

    $this->actingAsCustomer($mine)->get('/area-clienti')
        ->assertOk()
        ->assertSee($mine->customer_ref)
        ->assertDontSee($other->customer_ref)
        ->assertDontSee($other->email);
});
