<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Builds LIKE patterns for SQL Server, escaping the wildcard characters typed
 * by the user (%, _ and [) so that a search is always a literal "contains".
 * Values are still passed as bound parameters.
 */
final class Like
{
    public static function contains(string $term): string
    {
        return '%'.str_replace(['[', '%', '_'], ['[[]', '[%]', '[_]'], $term).'%';
    }
}
