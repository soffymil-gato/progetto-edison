<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Builds LIKE patterns for a literal "contains" search. Values are always
 * passed as bound parameters.
 *
 * - SQL Server: the wildcard characters typed by the user (%, _ and [) are
 *   escaped with brackets, so they match literally.
 * - SQLite (preview environment only): LIKE has no escape syntax without an
 *   ESCAPE clause, so typed wildcards keep their meaning. Acceptable for the
 *   demo preview; production always runs on SQL Server.
 */
final class Like
{
    public static function contains(string $term, ?string $driver = null): string
    {
        $driver ??= DB::connection()->getDriverName();

        if ($driver === 'sqlsrv') {
            return '%'.str_replace(['[', '%', '_'], ['[[]', '[%]', '[_]'], $term).'%';
        }

        return '%'.$term.'%';
    }
}
