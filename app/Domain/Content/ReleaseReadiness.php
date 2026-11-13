<?php

declare(strict_types=1);

namespace App\Domain\Content;

use App\Domain\Settings\SettingsRepository;
use App\Domain\Settings\SettingsSchema;

/**
 * Verifies that no placeholder or missing company data would reach production.
 * Used by `php artisan edison:release-check` and by the admin dashboard.
 */
final class ReleaseReadiness
{
    public function __construct(
        private readonly SiteContent $content,
        private readonly SettingsRepository $settings,
    ) {}

    /**
     * @return list<array{key: string, label: string, ok: bool, missing: list<string>}>
     */
    public function checks(): array
    {
        $checks = [];

        $placeholders = PlaceholderScanner::scan($this->content->all());
        $checks[] = [
            'key' => 'content',
            'label' => 'Testi del sito forniti da Edison',
            'ok' => $placeholders === [],
            'missing' => $placeholders,
        ];

        foreach (SettingsSchema::groups() as $group => $definition) {
            $values = $this->settings->get($group);
            $missing = [];
            foreach ($definition['fields'] as $field => $meta) {
                if ($meta['release'] && $values[$field] === null) {
                    $missing[] = $meta['label'];
                }
            }
            $checks[] = [
                'key' => 'settings.'.$group,
                'label' => 'Impostazioni — '.$definition['label'],
                'ok' => $missing === [],
                'missing' => $missing,
            ];
        }

        $logo = config('edison.brand.logo_path');
        $logoOk = is_string($logo) && $logo !== '' && is_file(public_path($logo));
        $checks[] = [
            'key' => 'brand.logo',
            'label' => 'Logo ufficiale Edison',
            'ok' => $logoOk,
            'missing' => $logoOk ? [] : ['File del logo ufficiale (EDISON_LOGO_PATH)'],
        ];

        return $checks;
    }

    public function isReady(): bool
    {
        foreach ($this->checks() as $check) {
            if (! $check['ok']) {
                return false;
            }
        }

        return true;
    }
}
