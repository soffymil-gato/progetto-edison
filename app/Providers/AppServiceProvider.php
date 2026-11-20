<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Content\SiteContent;
use App\Domain\Identity\Models\StaffUser;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SiteContent::class, static fn (): SiteContent => SiteContent::default());
    }

    public function boot(): void
    {
        $proxies = config('edison.trusted_proxies');
        if (is_string($proxies) && $proxies !== '') {
            TrustProxies::at($proxies === '*' ? '*' : array_map('trim', explode(',', $proxies)));
        }

        // Fail loudly on lazy loading, silently discarded attributes and missing attributes.
        Model::shouldBeStrict(! $this->app->isProduction());

        // Super Admin holds every permission, provided the account is active.
        Gate::before(static function (mixed $user, string $ability): ?bool {
            if ($user instanceof StaffUser && $user->isActive() && $user->isSuperAdmin()) {
                return true;
            }

            return null;
        });

        RateLimiter::for('contact-form', static fn (Request $request): Limit => Limit::perHour((int) config('edison.contact.max_per_hour_per_ip'))->by('contact|'.$request->ip()));

        RateLimiter::for('password-email', static fn (Request $request): array => [
            Limit::perMinute(3)->by('pwd-ip|'.$request->ip()),
            Limit::perHour(10)->by('pwd-email|'.mb_strtolower((string) $request->input('email'))),
        ]);

        RateLimiter::for('auth-form', static fn (Request $request): Limit => Limit::perMinute(30)->by('auth-form|'.$request->ip()));
    }
}
