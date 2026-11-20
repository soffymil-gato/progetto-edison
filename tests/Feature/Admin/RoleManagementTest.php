<?php

declare(strict_types=1);

use App\Domain\Audit\AuditLog;
use App\Domain\Identity\Permissions\DefaultRoles;
use Spatie\Permission\Models\Role;

it('creates a custom role with permissions and audits it', function (): void {
    $this->actingAsStaff($this->superAdmin())
        ->post('/gestionale/ruoli', ['name' => 'Responsabile richieste', 'permissions' => ['contact_requests.view', 'contact_requests.manage']])
        ->assertRedirect();

    $role = Role::findByName('Responsabile richieste', 'staff');
    expect($role->permissions->pluck('name')->sort()->values()->all())->toBe(['contact_requests.manage', 'contact_requests.view'])
        ->and(AuditLog::query()->where('action', 'role.created')->exists())->toBeTrue();
});

it('reserves sensitive permissions to Super Admins', function (): void {
    $manager = $this->staff(['roles.manage']);

    $this->actingAsStaff($manager)
        ->post('/gestionale/ruoli', ['name' => 'Tentativo', 'permissions' => ['settings.manage']])
        ->assertSessionHasErrors('permissions');

    $role = Role::findByName('operatore', 'staff');
    $this->actingAsStaff($manager)
        ->put("/gestionale/ruoli/{$role->id}", ['permissions' => ['roles.manage']])
        ->assertSessionHasErrors('permissions');

    expect($role->fresh()->permissions)->toHaveCount(0);
});

it('locks the Super Admin role', function (): void {
    $role = Role::findByName(DefaultRoles::SUPER_ADMIN, 'staff');

    $this->actingAsStaff($this->superAdmin())
        ->put("/gestionale/ruoli/{$role->id}", ['permissions' => []])
        ->assertSessionHasErrors('permissions');
});

it('deletes only unused custom roles', function (): void {
    $admin = $this->superAdmin();
    $custom = Role::create(['name' => 'Temporaneo', 'guard_name' => 'staff']);
    $default = Role::findByName('analista', 'staff');
    $used = Role::create(['name' => 'In uso', 'guard_name' => 'staff']);
    $this->staff(role: 'In uso');

    $this->actingAsStaff($admin)->delete("/gestionale/ruoli/{$default->id}")->assertSessionHasErrors('role');
    $this->actingAsStaff($admin)->delete("/gestionale/ruoli/{$used->id}")->assertSessionHasErrors('role');
    $this->actingAsStaff($admin)->delete("/gestionale/ruoli/{$custom->id}")->assertRedirect(route('admin.roles.index'));

    expect(Role::query()->where('name', 'Temporaneo')->exists())->toBeFalse()
        ->and(Role::query()->where('name', 'In uso')->exists())->toBeTrue();
});

it('applies a role change to the permissions checked by the server', function (): void {
    $user = $this->staff(role: 'operatore');
    $this->actingAsStaff($user)->get('/gestionale/registro-attivita')->assertForbidden();

    $role = Role::findByName('operatore', 'staff');
    $this->actingAsStaff($this->superAdmin())->put("/gestionale/ruoli/{$role->id}", ['permissions' => ['audit.view']]);

    $this->actingAsStaff($user->fresh())->get('/gestionale/registro-attivita')->assertOk();
});
