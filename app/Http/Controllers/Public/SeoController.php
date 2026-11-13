<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Support\Area;
use Illuminate\Http\Response;

final class SeoController extends Controller
{
    private const PAGES = ['home', 'azienda', 'prodotti', 'servizi', 'contatti', 'privacy', 'cookie'];

    public function sitemap(): Response
    {
        $urls = array_map(static fn (string $page): string => sprintf(
            '<url><loc>%s</loc></url>',
            e(route('public.'.$page)),
        ), self::PAGES);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.implode('', $urls).'</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $lines = ['User-agent: *'];

        // Reserved areas are never indexed (they also send X-Robots-Tag: noindex).
        foreach ([Area::Portal, Area::Admin] as $area) {
            if ($area->domain() === null) {
                $lines[] = 'Disallow: /'.$area->prefix().'/';
            }
        }
        $lines[] = 'Disallow: /up';
        $lines[] = '';
        $lines[] = 'Sitemap: '.route('public.sitemap');

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
