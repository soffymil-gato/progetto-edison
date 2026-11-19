<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Identity\Models\Account;
use App\Domain\Identity\Models\PortalAccount;
use App\Domain\Identity\Models\StaffUser;
use App\Domain\Identity\Permissions\DefaultRoles;
use App\Domain\Identity\Permissions\RbacSynchronizer;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * DEMO accounts of the preview environment (EDISON_PREVIEW=true).
 * Synthetic data only: ".test" addresses and a fictitious customer reference.
 * The password comes from PREVIEW_PASSWORD (hosting dashboard), never from
 * the repository. Idempotent: existing accounts are left untouched.
 */
final class PreviewSeeder extends Seeder
{
    public function run(RbacSynchronizer $rbac): void
    {
        if (app()->isProduction() || ! config('edison.preview')) {
            throw new RuntimeException('PreviewSeeder runs only in the preview environment (EDISON_PREVIEW=true).');
        }

        $password = config('edison.preview_password');
        if (! is_string($password) || mb_strlen($password) < (int) config('edison.auth.password_min_length')) {
            throw new RuntimeException('PREVIEW_PASSWORD is missing or shorter than the minimum password length.');
        }

        $rbac->sync();

        $admin = StaffUser::query()->where('email', 'anteprima.admin@example.test')->first()
            ?? $this->activate(new StaffUser(['name' => 'Amministratore Anteprima', 'email' => 'anteprima.admin@example.test']), $password);
        if (! $admin->hasRole(DefaultRoles::SUPER_ADMIN)) {
            $admin->assignRole(DefaultRoles::SUPER_ADMIN);
        }

        $operator = StaffUser::query()->where('email', 'anteprima.operatore@example.test')->first()
            ?? $this->activate(new StaffUser(['name' => 'Operatore Anteprima', 'email' => 'anteprima.operatore@example.test']), $password);
        if (! $operator->hasRole('operatore')) {
            $operator->assignRole('operatore');
        }

        if (! PortalAccount::query()->where('email', 'anteprima.cliente@example.test')->exists()) {
            $this->activate(new PortalAccount([
                'customer_ref' => 'DEMO-0001',
                'email' => 'anteprima.cliente@example.test',
                'username' => 'cliente.demo',
                'display_name' => 'Cliente Dimostrativo',
            ]), $password);
        }
    }

    /**
     * @template T of Account
     *
     * @param  T  $account
     * @return T
     */
    private function activate(Account $account, string $password): Account
    {
        $account->password = $password;
        $account->status = AccountStatus::Active;
        $account->password_changed_at = now();
        $account->save();

        return $account;
    }
}
