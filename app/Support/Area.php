<?php

declare(strict_types=1);

namespace App\Support;

/**
 * The three areas of the platform. Each area has its own routes, session
 * cookie, session table, CSRF cookie, authentication guard and frontend bundle.
 */
enum Area: string
{
    case Public = 'public';
    case Portal = 'portal';
    case Admin = 'admin';

    public static function current(): self
    {
        return app()->bound(self::class) ? app(self::class) : self::Public;
    }

    /** @return array<string, mixed> */
    public function config(): array
    {
        /** @var array<string, mixed> $config */
        $config = config('edison.areas.'.$this->value);

        return $config;
    }

    public function guard(): ?string
    {
        $guard = $this->config()['guard'] ?? null;

        return is_string($guard) ? $guard : null;
    }

    public function domain(): ?string
    {
        $domain = $this->config()['domain'] ?? null;

        return is_string($domain) && $domain !== '' ? $domain : null;
    }

    public function prefix(): string
    {
        if ($this->domain() !== null) {
            return '';
        }

        $prefix = $this->config()['prefix'] ?? '';

        return trim(is_string($prefix) ? $prefix : '', '/');
    }

    public function sessionCookie(): string
    {
        return (string) $this->config()['session_cookie'];
    }

    public function xsrfCookie(): string
    {
        return (string) $this->config()['xsrf_cookie'];
    }

    public function sessionTable(): string
    {
        return match ($this) {
            self::Public => 'sessions',
            self::Portal => 'portal_sessions',
            self::Admin => 'admin_sessions',
        };
    }

    public function idleMinutes(): int
    {
        return (int) $this->config()['idle_minutes'];
    }

    public function absoluteMinutes(): ?int
    {
        $minutes = $this->config()['absolute_minutes'] ?? null;

        return is_numeric($minutes) ? (int) $minutes : null;
    }

    /** Cookie path: the area prefix when served under a path, "/" on its own domain. */
    public function cookiePath(): string
    {
        $prefix = $this->prefix();

        return $prefix === '' ? '/' : '/'.$prefix;
    }

    public function routeName(string $name): string
    {
        return $this->value.'.'.$name;
    }

    public function label(): string
    {
        return match ($this) {
            self::Public => 'Sito',
            self::Portal => 'Area clienti',
            self::Admin => 'Gestionale',
        };
    }
}
