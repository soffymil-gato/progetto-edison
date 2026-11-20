<?php

declare(strict_types=1);

namespace App\Domain\Identity\Permissions;

/**
 * Default roles proposed in the preliminary report (§5.7).
 *
 * Roles remain configurable from the admin panel. The defaults below are
 * applied only when a role does not exist yet; existing customisations are
 * never overwritten. Operatore, Commerciale and Analista receive their
 * operational permissions together with the orders and reports modules.
 */
final class DefaultRoles
{
    public const SUPER_ADMIN = 'super-admin';

    /** @return array<string, array{label: string, permissions: list<Permission>}> */
    public static function definitions(): array
    {
        return [
            self::SUPER_ADMIN => [
                'label' => 'Super Admin',
                // Implicitly holds every permission (see Gate::before in AuthServiceProvider).
                'permissions' => [],
            ],
            'amministratore' => [
                'label' => 'Amministratore',
                'permissions' => [Permission::UsersView, Permission::ContactRequestsView, Permission::ContactRequestsManage],
            ],
            'operatore' => [
                'label' => 'Operatore',
                'permissions' => [],
            ],
            'commerciale' => [
                'label' => 'Commerciale',
                'permissions' => [Permission::ContactRequestsView],
            ],
            'analista' => [
                'label' => 'Analista',
                'permissions' => [],
            ],
        ];
    }

    public static function label(string $role): string
    {
        return self::definitions()[$role]['label'] ?? ucfirst(str_replace('-', ' ', $role));
    }
}
