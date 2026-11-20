<?php

declare(strict_types=1);

use App\Domain\Contact\ContactRequest;
use App\Domain\Identity\Models\StaffUser;
use Spatie\Permission\Models\Role;

/**
 * Every admin route is authorised on the server: hiding a button is never the protection.
 *
 * @return array<string, array{0: string, 1: string, 2: string}>
 */
function adminRoutes(): array
{
    return [
        'contact requests list' => ['GET', '/gestionale/richieste-contatto', 'contact_requests.view'],
        'contact request detail' => ['GET', '/gestionale/richieste-contatto/{contact}', 'contact_requests.view'],
        'contact request handled' => ['PATCH', '/gestionale/richieste-contatto/{contact}/gestita', 'contact_requests.manage'],
        'users list' => ['GET', '/gestionale/utenti', 'users.view'],
        'user create form' => ['GET', '/gestionale/utenti/nuovo', 'users.manage'],
        'user store' => ['POST', '/gestionale/utenti', 'users.manage'],
        'user edit' => ['GET', '/gestionale/utenti/{user}/modifica', 'users.manage'],
        'user update' => ['PUT', '/gestionale/utenti/{user}', 'users.manage'],
        'user suspend' => ['POST', '/gestionale/utenti/{user}/sospendi', 'users.manage'],
        'user reactivate' => ['POST', '/gestionale/utenti/{user}/riattiva', 'users.manage'],
        'user invitation' => ['POST', '/gestionale/utenti/{user}/invito', 'users.manage'],
        'user password reset' => ['POST', '/gestionale/utenti/{user}/reset-password', 'users.manage'],
        'roles list' => ['GET', '/gestionale/ruoli', 'roles.manage'],
        'role create form' => ['GET', '/gestionale/ruoli/nuovo', 'roles.manage'],
        'role store' => ['POST', '/gestionale/ruoli', 'roles.manage'],
        'role edit' => ['GET', '/gestionale/ruoli/{role}/modifica', 'roles.manage'],
        'role update' => ['PUT', '/gestionale/ruoli/{role}', 'roles.manage'],
        'role delete' => ['DELETE', '/gestionale/ruoli/{role}', 'roles.manage'],
        'audit list' => ['GET', '/gestionale/registro-attivita', 'audit.view'],
        'settings' => ['GET', '/gestionale/impostazioni', 'settings.manage'],
        'settings update' => ['PUT', '/gestionale/impostazioni/company', 'settings.manage'],
    ];
}

function resolveUrl(string $url): string
{
    $contact = ContactRequest::query()->create([
        'name' => 'Prova', 'email' => 'prova@example.test', 'subject' => 'Prova', 'message' => 'Messaggio di prova', 'privacy_accepted_at' => now(),
    ]);
    $user = StaffUser::factory()->create();
    $role = Role::query()->where('name', 'operatore')->firstOrFail();

    return str_replace(['{contact}', '{user}', '{role}'], [(string) $contact->id, (string) $user->id, (string) $role->id], $url);
}

it('denies every admin route to a staff user without the required permission', function (string $method, string $url, string $permission): void {
    $user = $this->staff(); // no role, no permission

    $this->actingAsStaff($user)->json($method, resolveUrl($url))->assertForbidden();
})->with(adminRoutes());

it('denies every admin route to guests and to customers', function (string $method, string $url): void {
    $resolved = resolveUrl($url);

    $this->json($method, $resolved)->assertUnauthorized();
    $this->actingAsCustomer($this->customer())->json($method, $resolved)->assertUnauthorized();
})->with(adminRoutes());

it('opens the read routes to users holding the permission', function (string $method, string $url, string $permission): void {
    $user = $this->staff([$permission]);

    $this->actingAsStaff($user)->get(resolveUrl($url))->assertOk();
})->with(array_filter(adminRoutes(), static fn (array $route): bool => $route[0] === 'GET'));

it('shows the dashboard to every active staff user, with widgets filtered by permission', function (): void {
    $this->actingAsStaff($this->staff())
        ->get('/gestionale')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Dashboard')->where('readiness', null)->missing('widgets.staff'));

    $this->actingAsStaff($this->superAdmin())
        ->get('/gestionale')
        ->assertInertia(fn ($page) => $page->has('readiness.checks')->has('widgets.staff')->has('widgets.contactRequests'));
});

it('exposes navigation entries only for granted permissions', function (): void {
    $this->actingAsStaff($this->staff(['audit.view']))
        ->get('/gestionale')
        ->assertInertia(fn ($page) => $page->has('nav', 2)->where('nav.1.label', 'Registro attività'));
});
