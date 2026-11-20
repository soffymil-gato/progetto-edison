<?php

declare(strict_types=1);

namespace App\Domain\Identity\Services;

use App\Domain\Audit\AuditLogger;
use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Identity\Models\StaffUser;
use App\Domain\Identity\Permissions\DefaultRoles;
use App\Domain\Identity\Permissions\Permission;
use App\Domain\Identity\Permissions\RbacSynchronizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

/**
 * Administration of internal users with anti-escalation rules:
 * - nobody changes their own roles or status;
 * - only a Super Admin assigns roles carrying sensitive permissions or the
 *   Super Admin role, and only a Super Admin modifies another Super Admin;
 * - the last active Super Admin can be neither suspended nor demoted.
 */
final class StaffUserAdministration
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly InvitationService $invitations,
    ) {}

    /** @param list<string> $roles */
    public function create(StaffUser $actor, string $name, string $email, array $roles): StaffUser
    {
        $this->assertCanAssign($actor, $roles);

        $user = DB::transaction(function () use ($actor, $name, $email, $roles): StaffUser {
            $user = new StaffUser(['name' => $name, 'email' => $email]);
            $user->status = AccountStatus::Invited;
            $user->save();
            $user->syncRoles($roles);

            $this->audit->record('staff_user.created', $actor, $user, ['roles' => $roles]);

            return $user;
        });

        $this->invitations->invite($user, $actor);

        return $user;
    }

    /** @param list<string> $roles */
    public function update(StaffUser $actor, StaffUser $user, string $name, array $roles): void
    {
        $this->assertCanModify($actor, $user);

        $currentRoles = $user->getRoleNames()->sort()->values()->all();
        $newRoles = collect($roles)->sort()->values()->all();
        $rolesChanged = $currentRoles !== $newRoles;

        if ($rolesChanged) {
            if ($actor->is($user)) {
                throw ValidationException::withMessages(['roles' => 'Non puoi modificare i tuoi ruoli.']);
            }
            $this->assertCanAssign($actor, $roles);
            if ($user->isSuperAdmin() && ! in_array(DefaultRoles::SUPER_ADMIN, $roles, true)) {
                $this->assertNotLastSuperAdmin($user);
            }
        }

        DB::transaction(function () use ($actor, $user, $name, $roles, $rolesChanged, $currentRoles): void {
            $nameChanged = $user->name !== $name;
            $user->name = $name;
            $user->save();

            if ($rolesChanged) {
                $user->syncRoles($roles);
            }

            if ($nameChanged || $rolesChanged) {
                $this->audit->record('staff_user.updated', $actor, $user, array_filter([
                    'name_changed' => $nameChanged ?: null,
                    'roles_from' => $rolesChanged ? $currentRoles : null,
                    'roles_to' => $rolesChanged ? $roles : null,
                ], static fn (mixed $v): bool => $v !== null));
            }
        });
    }

    public function suspend(StaffUser $actor, StaffUser $user): void
    {
        if ($actor->is($user)) {
            throw ValidationException::withMessages(['user' => 'Non puoi sospendere il tuo account.']);
        }
        $this->assertCanModify($actor, $user);
        if ($user->isSuperAdmin()) {
            $this->assertNotLastSuperAdmin($user);
        }

        DB::transaction(function () use ($actor, $user): void {
            $user->status = AccountStatus::Suspended;
            $user->save();
            $this->invitations->revokePending($user);
            // Active sessions are closed immediately.
            DB::table('admin_sessions')->where('user_id', $user->id)->delete();
            $this->audit->record('staff_user.suspended', $actor, $user);
        });
    }

    public function reactivate(StaffUser $actor, StaffUser $user): void
    {
        $this->assertCanModify($actor, $user);
        if ($user->status !== AccountStatus::Suspended) {
            throw ValidationException::withMessages(['user' => 'L\'utente non è sospeso.']);
        }

        DB::transaction(function () use ($actor, $user): void {
            // An account that never chose a password goes back to "invited".
            $user->status = $user->password === null ? AccountStatus::Invited : AccountStatus::Active;
            $user->save();
            $this->audit->record('staff_user.reactivated', $actor, $user);
        });

        if ($user->status === AccountStatus::Invited) {
            $this->invitations->invite($user, $actor);
        }
    }

    public function resendInvitation(StaffUser $actor, StaffUser $user): void
    {
        $this->assertCanModify($actor, $user);
        if ($user->status !== AccountStatus::Invited) {
            throw ValidationException::withMessages(['user' => 'L\'utente ha già attivato l\'account.']);
        }
        $this->invitations->invite($user, $actor);
    }

    public function sendPasswordReset(StaffUser $actor, StaffUser $user): void
    {
        $this->assertCanModify($actor, $user);
        if ($user->status !== AccountStatus::Active) {
            throw ValidationException::withMessages(['user' => 'Il recupero password è disponibile solo per account attivi.']);
        }

        Password::broker('staff')->sendResetLink(['email' => $user->email, 'status' => AccountStatus::Active->value]);
        $this->audit->record('staff_user.password_reset_sent', $actor, $user);
    }

    /**
     * Roles the actor is allowed to assign.
     *
     * @return list<string>
     */
    public function assignableRoles(StaffUser $actor): array
    {
        $roles = [];
        foreach (Role::query()->where('guard_name', RbacSynchronizer::GUARD)->with('permissions')->get() as $role) {
            if ($this->canAssignRole($actor, $role)) {
                $roles[] = $role->name;
            }
        }

        return $roles;
    }

    /** @param list<string> $roles */
    private function assertCanAssign(StaffUser $actor, array $roles): void
    {
        $allowed = $this->assignableRoles($actor);
        foreach ($roles as $role) {
            if (! in_array($role, $allowed, true)) {
                throw ValidationException::withMessages(['roles' => 'Non hai i privilegi per assegnare il ruolo «'.DefaultRoles::label($role).'».']);
            }
        }
    }

    private function canAssignRole(StaffUser $actor, Role $role): bool
    {
        if ($actor->isSuperAdmin()) {
            return true;
        }
        if ($role->name === DefaultRoles::SUPER_ADMIN) {
            return false;
        }

        $sensitive = array_map(static fn (Permission $p): string => $p->value, array_filter(Permission::cases(), static fn (Permission $p): bool => $p->isSensitive()));

        return $role->permissions->whereIn('name', $sensitive)->isEmpty();
    }

    private function assertCanModify(StaffUser $actor, StaffUser $user): void
    {
        if ($user->isSuperAdmin() && ! $actor->isSuperAdmin()) {
            throw ValidationException::withMessages(['user' => 'Solo un Super Admin può modificare un altro Super Admin.']);
        }
    }

    private function assertNotLastSuperAdmin(StaffUser $user): void
    {
        $others = StaffUser::role(DefaultRoles::SUPER_ADMIN, RbacSynchronizer::GUARD)
            ->where('status', AccountStatus::Active->value)
            ->whereKeyNot($user->id)
            ->count();

        if ($others === 0) {
            throw ValidationException::withMessages(['user' => 'Deve esistere almeno un Super Admin attivo: operazione non consentita.']);
        }
    }
}
