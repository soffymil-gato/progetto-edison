<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Identity\Permissions\RbacSynchronizer;
use Illuminate\Database\Seeder;

/**
 * Production-safe seeder: only permissions and default roles.
 * No users, no company data. The first Super Admin is created with
 * `php artisan edison:create-super-admin`.
 */
final class DatabaseSeeder extends Seeder
{
    public function run(RbacSynchronizer $rbac): void
    {
        $rbac->sync();
    }
}
