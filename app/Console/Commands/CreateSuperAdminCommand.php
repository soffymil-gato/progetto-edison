<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Audit\AuditLogger;
use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Identity\Models\StaffUser;
use App\Domain\Identity\Permissions\DefaultRoles;
use App\Domain\Identity\Permissions\RbacSynchronizer;
use App\Domain\Identity\Services\InvitationService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Bootstrap of the first Super Admin. No password is typed or stored here:
 * the person receives an invitation link and chooses their own password.
 */
#[Signature('edison:create-super-admin {--name= : Nome e cognome} {--email= : Email aziendale} {--print-link : Mostra il link di attivazione nel terminale (se la posta non è ancora configurata)}')]
#[Description('Crea un utente Super Admin e invia l\'invito di attivazione.')]
final class CreateSuperAdminCommand extends Command
{
    public function handle(RbacSynchronizer $rbac, InvitationService $invitations, AuditLogger $audit): int
    {
        $rbac->sync();

        $name = (string) ($this->option('name') ?: $this->ask('Nome e cognome'));
        $email = mb_strtolower(trim((string) ($this->option('email') ?: $this->ask('Email aziendale'))));

        $validator = Validator::make(['name' => $name, 'email' => $email], [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:254', 'unique:staff_users,email'],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = DB::transaction(function () use ($name, $email, $audit): StaffUser {
            $user = new StaffUser(['name' => $name, 'email' => $email]);
            $user->status = AccountStatus::Invited;
            $user->save();
            $user->assignRole(DefaultRoles::SUPER_ADMIN);
            $audit->record('staff_user.created', null, $user, ['roles' => [DefaultRoles::SUPER_ADMIN], 'via' => 'console']);

            return $user;
        });

        $token = $invitations->invite($user, null);

        $this->info("Super Admin creato: {$user->email}. Invito inviato via email.");
        if ($this->option('print-link')) {
            $this->warn('Link di attivazione personale (non condividerlo, scade in '.config('edison.auth.invitation_ttl_hours').' ore):');
            $this->line(route('admin.invitation.show', ['token' => $token]));
        }

        return self::SUCCESS;
    }
}
