<?php

declare(strict_types=1);

use App\Domain\Identity\Models\PortalAccount;
use App\Domain\Identity\Models\StaffUser;
use Database\Seeders\PreviewSeeder;

it('keeps the whole preview out of search engines and labels it', function (): void {
    config(['edison.preview' => true]);

    $this->get('/robots.txt')->assertOk()->assertSee("Disallow: /\n", false)->assertDontSee('Sitemap:');
    $this->get('/')->assertHeader('X-Robots-Tag', 'noindex, nofollow')->assertSee('Anteprima — dati dimostrativi');
});

it('does not label or block indexing outside the preview', function (): void {
    $this->get('/')->assertDontSee('Anteprima — dati dimostrativi');
    expect($this->get('/')->headers->has('X-Robots-Tag'))->toBeFalse();
});

it('creates the demo accounts only in the preview, idempotently', function (): void {
    config(['edison.preview' => true, 'edison.preview_password' => 'password-anteprima-di-test']);

    $this->seed(PreviewSeeder::class);
    $this->seed(PreviewSeeder::class);

    expect(StaffUser::query()->where('email', 'like', 'anteprima.%')->count())->toBe(2)
        ->and(PortalAccount::query()->where('username', 'cliente.demo')->count())->toBe(1)
        ->and(StaffUser::query()->where('email', 'anteprima.admin@example.test')->sole()->isSuperAdmin())->toBeTrue();
});

it('refuses to seed demo accounts outside the preview or without a password', function (): void {
    expect(fn () => $this->seed(PreviewSeeder::class))->toThrow(RuntimeException::class, 'preview environment');

    config(['edison.preview' => true, 'edison.preview_password' => 'corta']);
    expect(fn () => $this->seed(PreviewSeeder::class))->toThrow(RuntimeException::class, 'PREVIEW_PASSWORD');
});
