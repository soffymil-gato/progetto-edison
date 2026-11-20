<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Identity\Models\PortalAccount;
use App\Domain\Identity\Models\StaffUser;
use App\Domain\Identity\Permissions\DefaultRoles;
use App\Domain\Identity\Permissions\RbacSynchronizer;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * SYNTHETIC accounts for the end-to-end test suite (tests/e2e).
 * Refuses to run in production. Addresses use the reserved ".test" domain
 * and the customer reference is fictitious.
 */
final class E2eSeeder extends Seeder
{
    /** Local default for the synthetic test accounts; CI sets a random E2E_PASSWORD per run. */
    private const LOCAL_PASSWORD = 'E2e-password-locale-1';

    public function run(RbacSynchronizer $rbac): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('E2eSeeder cannot run in production.');
        }

        $rbac->sync();
        $configured = config('edison.e2e.password');
        $password = is_string($configured) && $configured !== '' ? $configured : self::LOCAL_PASSWORD;

        $admin = new StaffUser(['name' => 'Admin E2E', 'email' => 'e2e.admin@example.test']);
        $admin->password = $password;
        $admin->status = AccountStatus::Active;
        $admin->save();
        $admin->assignRole(DefaultRoles::SUPER_ADMIN);

        $operator = new StaffUser(['name' => 'Operatore E2E', 'email' => 'e2e.operatore@example.test']);
        $operator->password = $password;
        $operator->status = AccountStatus::Active;
        $operator->save();
        $operator->assignRole('operatore');

        $customer = new PortalAccount([
            'customer_ref' => 'TEST-E2E-001',
            'email' => 'e2e.cliente@example.test',
            'username' => 'cliente.e2e',
            'display_name' => 'Cliente E2E',
        ]);
        $customer->password = $password;
        $customer->status = AccountStatus::Active;
        $customer->save();
    }
}
