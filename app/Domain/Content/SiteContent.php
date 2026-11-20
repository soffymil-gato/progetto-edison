<?php

declare(strict_types=1);

namespace App\Domain\Content;

use Illuminate\Support\Arr;

/**
 * Editorial content of the public website (resources/content/site.php).
 */
final class SiteContent
{
    /** @var array<string, mixed>|null */
    private ?array $content = null;

    public function __construct(private readonly string $path) {}

    public static function default(): self
    {
        return new self(resource_path('content/site.php'));
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        if ($this->content === null) {
            /** @var array<string, mixed> $content */
            $content = require $this->path;
            $this->content = $content;
        }

        return $this->content;
    }

    public function get(string $key): mixed
    {
        return Arr::get($this->all(), $key);
    }

    /** @return array{title: string, description: string} */
    public function meta(string $page): array
    {
        /** @var array{title: string, description: string} $meta */
        $meta = $this->get('meta.'.$page);

        return $meta;
    }
}
