<?php

declare(strict_types=1);

namespace App\Support;

use App\Domain\Content\PlaceholderScanner;

/**
 * SEO metadata rendered on the server (title, description, canonical,
 * Open Graph, structured data) so that crawlers do not depend on JavaScript.
 * Placeholder texts are never published in meta tags or structured data.
 */
final class Seo
{
    /**
     * @param  array{title: string, description: string}  $meta
     * @param  array<string, string|null>  $company
     * @return array{title: string, description: string|null, canonical: string, siteName: string, jsonLd: array<string, mixed>|null}
     */
    public static function forPage(string $page, array $meta, array $company): array
    {
        $siteName = (string) config('app.name');
        $description = PlaceholderScanner::isPlaceholder($meta['description']) ? null : $meta['description'];
        $canonical = $page === 'home' ? route('public.home') : route('public.'.$page);

        return [
            'title' => $page === 'home' ? $siteName.' — '.$meta['title'] : $meta['title'].' — '.$siteName,
            'description' => $description,
            'canonical' => $canonical,
            'siteName' => $siteName,
            'jsonLd' => $page === 'home' ? self::organization($company) : null,
        ];
    }

    /**
     * Organization structured data, emitted only with REAL data from the settings.
     *
     * @param  array<string, string|null>  $company
     * @return array<string, mixed>|null
     */
    private static function organization(array $company): ?array
    {
        if ($company['legal_name'] === null) {
            return null;
        }

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $company['legal_name'],
            'url' => route('public.home'),
            'email' => $company['email'],
            'telephone' => $company['phone'],
            'vatID' => $company['vat_number'],
            'address' => $company['registered_office'],
        ], static fn (mixed $value): bool => $value !== null);
    }
}
