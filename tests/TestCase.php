<?php

declare(strict_types=1);

namespace Tests;

use App\Domain\Identity\Models\PortalAccount;
use App\Domain\Identity\Models\StaffUser;
use App\Domain\Identity\Permissions\DefaultRoles;
use App\Domain\Identity\Permissions\RbacSynchronizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(RbacSynchronizer::class)->sync();
        // Backend tests do not depend on compiled frontend assets (CSP nonces keep working).
        $this->withoutVite();
    }

    /** @param list<string> $permissions */
    protected function staff(array $permissions = [], ?string $role = null): StaffUser
    {
        $user = StaffUser::factory()->create()->refresh();
        if ($role !== null) {
            $user->assignRole($role);
        }
        if ($permissions !== []) {
            $user->givePermissionTo($permissions);
        }

        return $user->fresh() ?? $user;
    }

    protected function superAdmin(): StaffUser
    {
        return $this->staff(role: DefaultRoles::SUPER_ADMIN);
    }

    protected function customer(): PortalAccount
    {
        return PortalAccount::factory()->create()->refresh();
    }

    /** Authenticates like the real login does (session + start time). */
    protected function actingAsStaff(StaffUser $user): static
    {
        return $this->actingAs($user, 'staff')->withSession(['auth.started_at' => now()->getTimestamp()]);
    }

    protected function actingAsCustomer(PortalAccount $account): static
    {
        return $this->actingAs($account, 'customer')->withSession(['auth.started_at' => now()->getTimestamp()]);
    }
}
