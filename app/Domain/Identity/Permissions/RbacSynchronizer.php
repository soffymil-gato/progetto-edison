<?php

declare(strict_types=1);

namespace App\Domain\Identity\Permissions;

use Spatie\Permission\Models\Permission as PermissionModel;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Creates the registered permissions and the default roles if missing.
 * Idempotent; never removes customisations made from the admin panel.
 */
final class RbacSynchronizer
{
    public const GUARD = 'staff';

    public function __construct(private readonly PermissionRegistrar $registrar) {}

    /** @return array{permissions_created: int, roles_created: int} */
    public function sync(): array
    {
        $permissionsCreated = 0;
        foreach (Permission::cases() as $permission) {
            $model = PermissionModel::query()->firstOrCreate(['name' => $permission->value, 'guard_name' => self::GUARD]);
            $permissionsCreated += $model->wasRecentlyCreated ? 1 : 0;
        }

        $rolesCreated = 0;
        foreach (DefaultRoles::definitions() as $name => $definition) {
            $role = Role::query()->firstOrCreate(['name' => $name, 'guard_name' => self::GUARD]);
            if ($role->wasRecentlyCreated) {
                $role->syncPermissions(array_map(static fn (Permission $p): string => $p->value, $definition['permissions']));
                $rolesCreated++;
            }
        }

        // Permissions that are no longer registered in code are removed.
        PermissionModel::query()
            ->where('guard_name', self::GUARD)
            ->whereNotIn('name', Permission::values())
            ->get()
            ->each(static fn (PermissionModel $model) => $model->delete());

        $this->registrar->forgetCachedPermissions();

        return ['permissions_created' => $permissionsCreated, 'roles_created' => $rolesCreated];
    }
}
