<?php

declare(strict_types=1);

use App\Domain\Settings\SettingsRepository;
use Inertia\Testing\AssertableInertia as Assert;

dataset('public pages', [
    'home' => ['/', 'Public/Home'],
    'azienda' => ['/azienda', 'Public/Azienda'],
    'prodotti' => ['/prodotti', 'Public/Prodotti'],
    'servizi' => ['/servizi', 'Public/Servizi'],
    'contatti' => ['/contatti', 'Public/Contatti'],
    'privacy' => ['/privacy', 'Public/Legal'],
    'cookie' => ['/cookie', 'Public/Legal'],
]);

it('renders every public page with server-side SEO metadata', function (string $url, string $component): void {
    $response = $this->get($url);

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component($component)->has('seo.title'))
        ->assertSee('<link rel="canonical"', false)
        ->assertSee('<meta property="og:title"', false);
})->with('public pages');

it('never publishes placeholder texts in meta tags or structured data', function (): void {
    $html = $this->get('/')->assertOk()->getContent();

    // The home meta description is still a placeholder: it must be omitted, not published.
    expect($html)->not->toContain('<meta name="description" content="[DA FORNIRE');
    // No company data configured: no Organization structured data.
    expect($html)->not->toContain('application/ld+json');
});

it('publishes Organization structured data only with real company data', function (): void {
    $admin = $this->superAdmin();
    app(SettingsRepository::class)->update('company', ['legal_name' => 'Ragione Sociale di Prova S.r.l.'], $admin);

    $this->get('/')->assertOk()->assertSee('"@type":"Organization"', false)->assertSee('Ragione Sociale di Prova S.r.l.', false);
});

it('serves a sitemap with all public pages and a robots file excluding reserved areas', function (): void {
    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
        ->assertSee(route('public.azienda'), false)
        ->assertSee(route('public.cookie'), false);

    $this->get('/robots.txt')
        ->assertOk()
        ->assertSee('Disallow: /area-clienti/')
        ->assertSee('Disallow: /gestionale/')
        ->assertSee('Sitemap: '.route('public.sitemap'));
});

it('sends the security headers with a nonce-based CSP', function (): void {
    $response = $this->get('/');

    $response->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

    $csp = (string) $response->headers->get('Content-Security-Policy');
    expect($csp)->toContain("frame-ancestors 'none'")
        ->toContain("object-src 'none'")
        ->toMatch("/script-src 'self' 'nonce-[A-Za-z0-9]+'/")
        ->not->toContain("script-src 'self' 'unsafe-inline'");
});

it('marks reserved areas as not indexable', function (): void {
    $this->get('/gestionale/login')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    $this->get('/area-clienti/login')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    expect($this->get('/')->headers->has('X-Robots-Tag'))->toBeFalse();
});
