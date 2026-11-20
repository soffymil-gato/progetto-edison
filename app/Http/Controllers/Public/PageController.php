<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Domain\Content\SiteContent;
use App\Domain\Settings\SettingsRepository;
use App\Http\Controllers\Controller;
use App\Support\Seo;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Pages of the public website. Content comes from resources/content/site.php
 * (editorial texts) and from the administrative settings (company data).
 */
final class PageController extends Controller
{
    public function __construct(
        private readonly SiteContent $content,
        private readonly SettingsRepository $settings,
    ) {}

    public function home(): Response
    {
        return $this->page('home', 'Public/Home', [
            'content' => $this->content->get('home'),
            'products' => $this->content->get('prodotti.categories'),
            'services' => $this->content->get('servizi.items'),
            'contactsPreview' => $this->settings->get('company'),
        ]);
    }

    public function azienda(): Response
    {
        return $this->page('azienda', 'Public/Azienda', ['content' => $this->content->get('azienda')]);
    }

    public function prodotti(): Response
    {
        return $this->page('prodotti', 'Public/Prodotti', ['content' => $this->content->get('prodotti')]);
    }

    public function servizi(): Response
    {
        return $this->page('servizi', 'Public/Servizi', [
            'content' => $this->content->get('servizi'),
            'pickup' => $this->settings->get('pickup'),
        ]);
    }

    public function contatti(): Response
    {
        return $this->page('contatti', 'Public/Contatti', [
            'content' => $this->content->get('contatti'),
            'pickup' => $this->settings->get('pickup'),
            'storeUrl' => route('public.contatti.store'),
        ]);
    }

    public function privacy(): Response
    {
        return $this->page('privacy', 'Public/Legal', ['content' => $this->content->get('legal.privacy'), 'kind' => 'privacy']);
    }

    public function cookie(): Response
    {
        return $this->page('cookie', 'Public/Legal', ['content' => $this->content->get('legal.cookie'), 'kind' => 'cookie']);
    }

    /** @param array<string, mixed> $props */
    private function page(string $key, string $component, array $props): Response
    {
        $seo = Seo::forPage($key, $this->content->meta($key), $this->settings->get('company'));

        return Inertia::render($component, [...$props, 'seo' => $seo])->withViewData(['seo' => $seo]);
    }
}
