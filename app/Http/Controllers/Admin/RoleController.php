<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\AuditLogger;
use App\Domain\Identity\Models\StaffUser;
use App\Domain\Identity\Permissions\DefaultRoles;
use App\Domain\Identity\Permissions\Permission;
use App\Domain\Identity\Permissions\RbacSynchronizer;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Configurable roles. The Super Admin role is locked (it implicitly holds
 * every permission). Sensitive permissions can be granted only by a Super Admin.
 */
final class RoleController extends Controller
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly PermissionRegistrar $registrar,
    ) {}

    public function index(): Response
    {
        $roles = Role::query()
            ->where('guard_name', RbacSynchronizer::GUARD)
            ->withCount('permissions')
            ->orderBy('id')
            ->get()
            ->map(fn (Role $role): array => [
                'id' => $role->id,
                'name' => $role->name,
                'label' => DefaultRoles::label($role->name),
                'isSuperAdmin' => $role->name === DefaultRoles::SUPER_ADMIN,
                'isDefault' => array_key_exists($role->name, DefaultRoles::definitions()),
                'permissionsCount' => $role->name === DefaultRoles::SUPER_ADMIN ? count(Permission::cases()) : $role->permissions_count,
                'usersCount' => StaffUser::role($role->name, RbacSynchronizer::GUARD)->count(),
                'editUrl' => route('admin.roles.edit', $role),
            ]);

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
            'urls' => ['create' => route('admin.roles.create')],
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Admin/Roles/Form', [
            'role' => null,
            'permissionGroups' => $this->permissionGroups($this->actor($request)),
            'urls' => ['submit' => route('admin.roles.store'), 'back' => route('admin.roles.index')],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $actor = $this->actor($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60', Rule::unique('roles', 'name')->where('guard_name', RbacSynchronizer::GUARD)],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::in(Permission::values())],
        ], [], ['name' => 'nome del ruolo']);

        $permissions = array_values($data['permissions'] ?? []);
        $this->assertCanGrant($actor, $permissions);

        $role = DB::transaction(function () use ($data, $permissions, $actor): Role {
            $role = Role::query()->create(['name' => trim($data['name']), 'guard_name' => RbacSynchronizer::GUARD]);
            $role->syncPermissions($permissions);
            $this->audit->record('role.created', $actor, ['role', $role->id], ['name' => $role->name, 'permissions' => $permissions]);

            return $role;
        });
        $this->registrar->forgetCachedPermissions();

        return redirect()->route('admin.roles.edit', $role)->with('success', 'Ruolo creato.');
    }

    public function edit(Request $request, Role $role): Response
    {
        $this->assertStaffRole($role);

        return Inertia::render('Admin/Roles/Form', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'label' => DefaultRoles::label($role->name),
                'isSuperAdmin' => $role->name === DefaultRoles::SUPER_ADMIN,
                'isDefault' => array_key_exists($role->name, DefaultRoles::definitions()),
                'permissions' => $role->permissions->pluck('name')->values()->all(),
                'usersCount' => StaffUser::role($role->name, RbacSynchronizer::GUARD)->count(),
            ],
            'permissionGroups' => $this->permissionGroups($this->actor($request)),
            'urls' => [
                'submit' => route('admin.roles.update', $role),
                'destroy' => route('admin.roles.destroy', $role),
                'back' => route('admin.roles.index'),
            ],
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->assertStaffRole($role);
        if ($role->name === DefaultRoles::SUPER_ADMIN) {
            throw ValidationException::withMessages(['permissions' => 'Il ruolo Super Admin non è modificabile.']);
        }

        $actor = $this->actor($request);
        $data = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::in(Permission::values())],
        ]);

        $before = $role->permissions->pluck('name')->sort()->values()->all();
        $after = [];
        foreach ((array) ($data['permissions'] ?? []) as $permission) {
            if (is_string($permission) && ! in_array($permission, $after, true)) {
                $after[] = $permission;
            }
        }
        sort($after);

        // Granting or revoking a sensitive permission is reserved to Super Admins.
        $this->assertCanGrant($actor, array_merge(array_diff($after, $before), array_diff($before, $after)));

        if ($before !== $after) {
            DB::transaction(function () use ($role, $after, $before, $actor): void {
                $role->syncPermissions($after);
                $this->audit->record('role.permissions_updated', $actor, ['role', $role->id], [
                    'name' => $role->name,
                    'added' => array_values(array_diff($after, $before)),
                    'removed' => array_values(array_diff($before, $after)),
                ]);
            });
            $this->registrar->forgetCachedPermissions();
        }

        return back()->with('success', 'Permessi del ruolo aggiornati.');
    }

    public function destroy(Request $request, Role $role): RedirectResponse
    {
        $this->assertStaffRole($role);
        if (array_key_exists($role->name, DefaultRoles::definitions())) {
            throw ValidationException::withMessages(['role' => 'I ruoli predefiniti non possono essere eliminati.']);
        }
        if (StaffUser::role($role->name, RbacSynchronizer::GUARD)->exists()) {
            throw ValidationException::withMessages(['role' => 'Il ruolo è assegnato ad almeno un utente: rimuovilo prima dagli utenti.']);
        }

        $actor = $this->actor($request);
        DB::transaction(function () use ($role, $actor): void {
            $this->audit->record('role.deleted', $actor, ['role', $role->id], ['name' => $role->name]);
            $role->delete();
        });
        $this->registrar->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')->with('success', 'Ruolo eliminato.');
    }

    /** @return list<array{group: string, permissions: list<array{value: string, label: string, sensitive: bool, grantable: bool}>}> */
    private function permissionGroups(StaffUser $actor): array
    {
        $groups = [];
        foreach (Permission::cases() as $permission) {
            $groups[$permission->group()][] = [
                'value' => $permission->value,
                'label' => $permission->label(),
                'sensitive' => $permission->isSensitive(),
                'grantable' => ! $permission->isSensitive() || $actor->isSuperAdmin(),
            ];
        }

        return array_map(static fn (string $group, array $permissions): array => ['group' => $group, 'permissions' => $permissions], array_keys($groups), array_values($groups));
    }

    /** @param list<string> $permissions */
    private function assertCanGrant(StaffUser $actor, array $permissions): void
    {
        if ($actor->isSuperAdmin()) {
            return;
        }
        foreach ($permissions as $permission) {
            if (Permission::from($permission)->isSensitive()) {
                throw ValidationException::withMessages(['permissions' => 'Solo un Super Admin può concedere o revocare il permesso «'.Permission::from($permission)->label().'».']);
            }
        }
    }

    private function assertStaffRole(Role $role): void
    {
        abort_unless($role->guard_name === RbacSynchronizer::GUARD, 404);
    }

    private function actor(Request $request): StaffUser
    {
        /** @var StaffUser $actor */
        $actor = $request->user();

        return $actor;
    }
}
