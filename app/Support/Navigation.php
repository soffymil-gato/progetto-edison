<?php

declare(strict_types=1);

namespace App\Support;

use App\Domain\Identity\Models\Account;
use App\Domain\Identity\Models\StaffUser;
use App\Domain\Identity\Permissions\Permission;

/**
 * Navigation menus and URLs per area, computed on the server so that the
 * frontend never builds routes itself. Admin entries are filtered by
 * permission (the routes are protected independently).
 */
final class Navigation
{
    /** @return list<array{label: string, href: string, icon: string, match: string}> */
    public static function for(Area $area, ?Account $user): array
    {
        return match ($area) {
            Area::Public => [
                self::item('Home', 'public.home', 'home'),
                self::item('Azienda', 'public.azienda', 'building'),
                self::item('Prodotti', 'public.prodotti', 'package'),
                self::item('Servizi', 'public.servizi', 'wrench'),
                self::item('Contatti', 'public.contatti', 'mail'),
            ],
            Area::Portal => $user === null ? [] : [
                self::item('Dashboard', 'portal.dashboard', 'layout-dashboard'),
                self::item('Profilo e sicurezza', 'portal.profile.edit', 'user-cog', 'portal.profile.*'),
            ],
            Area::Admin => $user instanceof StaffUser ? self::adminItems($user) : [],
        };
    }

    /** @return array<string, string> */
    public static function urls(Area $area): array
    {
        $public = [
            'home' => route('public.home'),
            'azienda' => route('public.azienda'),
            'prodotti' => route('public.prodotti'),
            'servizi' => route('public.servizi'),
            'contatti' => route('public.contatti'),
            'privacy' => route('public.privacy'),
            'cookie' => route('public.cookie'),
            'portalLogin' => route('portal.login'),
        ];

        return match ($area) {
            Area::Public => $public,
            Area::Portal => [
                'publicHome' => $public['home'],
                'privacy' => $public['privacy'],
                'home' => route('portal.dashboard'),
                'logout' => route('portal.logout'),
                'profile' => route('portal.profile.edit'),
            ],
            Area::Admin => [
                'publicHome' => $public['home'],
                'home' => route('admin.dashboard'),
                'logout' => route('admin.logout'),
                'profile' => route('admin.profile.edit'),
            ],
        };
    }

    /** @return list<array{label: string, href: string, icon: string, match: string}> */
    private static function adminItems(StaffUser $user): array
    {
        $items = [self::item('Panoramica', 'admin.dashboard', 'layout-dashboard')];

        $guarded = [
            [Permission::ContactRequestsView, 'Richieste di contatto', 'admin.contact-requests.index', 'inbox', 'admin.contact-requests.*'],
            [Permission::UsersView, 'Utenti interni', 'admin.users.index', 'users', 'admin.users.*'],
            [Permission::RolesManage, 'Ruoli e permessi', 'admin.roles.index', 'shield', 'admin.roles.*'],
            [Permission::AuditView, 'Registro attività', 'admin.audit.index', 'scroll-text', 'admin.audit.*'],
            [Permission::SettingsManage, 'Impostazioni', 'admin.settings.edit', 'settings', 'admin.settings.*'],
        ];

        foreach ($guarded as [$permission, $label, $route, $icon, $match]) {
            if ($user->can($permission->value)) {
                $items[] = self::item($label, $route, $icon, $match);
            }
        }

        return $items;
    }

    /** @return array{label: string, href: string, icon: string, match: string} */
    private static function item(string $label, string $route, string $icon, ?string $match = null): array
    {
        return [
            'label' => $label,
            'href' => route($route),
            'icon' => $icon,
            'match' => $match ?? $route,
        ];
    }
}
