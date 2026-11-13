<?php

declare(strict_types=1);

use App\Domain\Content\PlaceholderScanner;
use App\Support\Like;

it('escapes SQL Server LIKE wildcards', function (): void {
    expect(Like::contains('100%'))->toBe('%100[%]%')
        ->and(Like::contains('a_b'))->toBe('%a[_]b%')
        ->and(Like::contains('[x]'))->toBe('%[[]x]%');
});

it('finds placeholder content at any depth', function (): void {
    $paths = PlaceholderScanner::scan([
        'title' => 'Testo definitivo',
        'hero' => ['subtitle' => '[DA FORNIRE: sottotitolo]'],
        'items' => [['text' => 'ok'], ['text' => '[DA FORNIRE: testo]']],
    ]);

    expect($paths)->toBe(['hero.subtitle', 'items.1.text']);
});
