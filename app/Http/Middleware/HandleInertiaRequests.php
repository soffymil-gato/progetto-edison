<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Identity\Models\Account;
use App\Domain\Identity\Models\PortalAccount;
use App\Domain\Identity\Models\StaffUser;
use App\Domain\Identity\Permissions\DefaultRoles;
use App\Domain\Identity\Permissions\Permission;
use App\Domain\Settings\SettingsRepository;
use App\Support\Area;
use App\Support\Navigation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;

/**
 * Props shared with every page. Only the data needed by the UI is exposed:
 * never passwords, tokens or internal identifiers beyond what is displayed.
 */
final class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function __construct(private readonly SettingsRepository $settings) {}

    /** @return array<string, mixed> */
    public function share(Request $request): array
    {
        $area = Area::current();

        return [
            ...parent::share($request),
            'area' => $area->value,
            'route' => $request->route()?->getName(),
            'appName' => config('app.name'),
            'xsrfCookie' => $area->xsrfCookie(),
            'brand' => [
                'logoUrl' => $this->logoUrl(),
            ],
            'company' => fn (): array => $this->settings->get('company'),
            'auth' => fn (): array => ['user' => $this->user($request, $area)],
            'nav' => fn (): array => Navigation::for($area, $this->account($request, $area)),
            'urls' => fn (): array => Navigation::urls($area),
            'flash' => fn (): array => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ];
    }

    private function logoUrl(): ?string
    {
        $path = config('edison.brand.logo_path');

        return is_string($path) && $path !== '' && is_file(public_path($path)) ? asset($path) : null;
    }

    private function account(Request $request, Area $area): ?Account
    {
        $guard = $area->guard();
        $user = $guard !== null ? Auth::guard($guard)->user() : null;

        return $user instanceof Account ? $user : null;
    }

    /** @return array<string, mixed>|null */
    private function user(Request $request, Area $area): ?array
    {
        $user = $this->account($request, $area);

        if ($user instanceof StaffUser) {
            $roles = $user->getRoleNames()->all();

            return [
                'name' => $user->name,
                'email' => $user->email,
                'roles' => array_map(static fn (string $role): string => DefaultRoles::label($role), $roles),
                'isSuperAdmin' => $user->isSuperAdmin(),
                // Used only to adapt the UI: every action is authorised again on the server.
                'permissions' => $user->isSuperAdmin()
                    ? Permission::values()
                    : $user->getAllPermissions()->pluck('name')->values()->all(),
            ];
        }

        if ($user instanceof PortalAccount) {
            return [
                'name' => $user->display_name,
                'email' => $user->email,
                'username' => $user->username,
            ];
        }

        return null;
    }
}
