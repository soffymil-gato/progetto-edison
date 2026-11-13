<?php

declare(strict_types=1);

namespace App\Domain\Identity\Permissions;

/**
 * Registry of the granular permissions of the admin panel.
 *
 * Only permissions of modules that are actually implemented are registered:
 * permissions for orders, customers, products and reports will be added
 * together with their modules (no checkbox without a real effect).
 */
enum Permission: string
{
    case UsersView = 'users.view';
    case UsersManage = 'users.manage';
    case RolesManage = 'roles.manage';
    case SettingsManage = 'settings.manage';
    case AuditView = 'audit.view';
    case ContactRequestsView = 'contact_requests.view';
    case ContactRequestsManage = 'contact_requests.manage';

    public function label(): string
    {
        return match ($this) {
            self::UsersView => 'Consultare gli utenti interni',
            self::UsersManage => 'Creare, modificare e sospendere utenti interni',
            self::RolesManage => 'Gestire ruoli e permessi',
            self::SettingsManage => 'Modificare le impostazioni aziendali',
            self::AuditView => 'Consultare il registro attività',
            self::ContactRequestsView => 'Consultare le richieste di contatto',
            self::ContactRequestsManage => 'Gestire le richieste di contatto',
        };
    }

    public function group(): string
    {
        return match ($this) {
            self::UsersView, self::UsersManage, self::RolesManage => 'Utenti e permessi',
            self::SettingsManage => 'Impostazioni',
            self::AuditView => 'Sicurezza',
            self::ContactRequestsView, self::ContactRequestsManage => 'Richieste di contatto',
        };
    }

    /**
     * Sensitive permissions can only be granted by a Super Admin.
     */
    public function isSensitive(): bool
    {
        return in_array($this, [self::RolesManage, self::SettingsManage, self::UsersManage], true);
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(static fn (self $p): string => $p->value, self::cases());
    }
}
