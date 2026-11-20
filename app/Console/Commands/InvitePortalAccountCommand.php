<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Audit\AuditLogger;
use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Identity\Models\PortalAccount;
use App\Domain\Identity\Services\InvitationService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * DEVELOPMENT / STAGING ONLY.
 *
 * In production customer accounts will be created from the admin panel,
 * choosing the customer from the company database. Until the schema analysis
 * is approved the customer reference cannot be verified, so this command
 * refuses to run in production.
 */
#[Signature('edison:portal-invite {email} {--name= : Nome visualizzato} {--customer-ref= : Riferimento cliente (non verificabile finché il DB aziendale non è integrato)} {--username=} {--print-link}')]
#[Description('[Solo sviluppo/staging] Crea un account dell\'area clienti e invia l\'invito.')]
final class InvitePortalAccountCommand extends Command
{
    public function handle(InvitationService $invitations, AuditLogger $audit): int
    {
        if ($this->laravel->isProduction()) {
            $this->error('Comando non disponibile in produzione: gli account clienti si creano dal gestionale dopo l\'integrazione con il database aziendale.');

            return self::FAILURE;
        }

        $data = [
            'email' => mb_strtolower(trim((string) $this->argument('email'))),
            'display_name' => (string) ($this->option('name') ?: $this->ask('Nome visualizzato')),
            'customer_ref' => (string) ($this->option('customer-ref') ?: $this->ask('Riferimento cliente')),
            'username' => $this->option('username') ? (string) $this->option('username') : null,
        ];

        $validator = Validator::make($data, [
            'email' => ['required', 'email:rfc', 'max:254', 'unique:portal_accounts,email'],
            'display_name' => ['required', 'string', 'max:120'],
            'customer_ref' => ['required', 'string', 'max:64'],
            'username' => ['nullable', 'string', 'regex:/^[A-Za-z0-9._-]{3,64}$/', 'unique:portal_accounts,username'],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $account = DB::transaction(function () use ($data, $audit): PortalAccount {
            $account = new PortalAccount($data);
            $account->status = AccountStatus::Invited;
            $account->save();
            $audit->record('portal_account.created', null, $account, ['via' => 'console', 'environment' => app()->environment()]);

            return $account;
        });

        $token = $invitations->invite($account, null);
        $this->info("Account cliente creato: {$account->email}. Invito inviato.");
        if ($this->option('print-link')) {
            $this->line(route('portal.invitation.show', ['token' => $token]));
        }

        return self::SUCCESS;
    }
}
