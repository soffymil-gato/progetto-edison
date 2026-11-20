<?php

declare(strict_types=1);

namespace App\Domain\Content;

/**
 * Finds content still marked as "to be supplied".
 */
final class PlaceholderScanner
{
    public static function marker(): string
    {
        return (string) config('edison.content.placeholder_marker');
    }

    public static function isPlaceholder(mixed $value): bool
    {
        return is_string($value) && str_contains($value, self::marker());
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return list<string> Dot paths of the placeholder values.
     */
    public static function scan(array $data, string $prefix = ''): array
    {
        $paths = [];
        foreach ($data as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;
            if (is_array($value)) {
                array_push($paths, ...self::scan($value, $path));
            } elseif (self::isPlaceholder($value)) {
                $paths[] = $path;
            }
        }

        return $paths;
    }
}
