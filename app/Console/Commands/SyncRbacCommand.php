<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Identity\Permissions\RbacSynchronizer;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('edison:rbac-sync')]
#[Description('Crea i permessi registrati e i ruoli predefiniti mancanti (idempotente).')]
final class SyncRbacCommand extends Command
{
    public function handle(RbacSynchronizer $sync): int
    {
        $result = $sync->sync();
        $this->info("Permessi creati: {$result['permissions_created']}. Ruoli creati: {$result['roles_created']}.");

        return self::SUCCESS;
    }
}
