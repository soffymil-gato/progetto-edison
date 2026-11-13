<?php

declare(strict_types=1);

use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Database — Progetto Edison (Microsoft SQL Server)
|--------------------------------------------------------------------------
|
| Two connections, both SQL Server:
|
| - "portal": the platform's own database (EdisonPortal). The application
|   writes ONLY here. Migrations target this connection.
| - "legacy": Edison's existing company database. READ-ONLY by design: the
|   login configured here must hold SELECT permissions only. It is not used
|   by any feature until the schema analysis has been delivered and approved
|   (see docs/analisi/report-analisi-preliminare.md).
|
| Credentials come exclusively from environment variables / secret manager.
|
*/

$sqlsrv = static fn (string $prefix, string $defaultDatabase): array => [
    'driver' => 'sqlsrv',
    'url' => env($prefix.'_URL'),
    'host' => env($prefix.'_HOST', 'localhost'),
    'port' => env($prefix.'_PORT', '1433'),
    'database' => env($prefix.'_DATABASE', $defaultDatabase),
    'username' => env($prefix.'_USERNAME', ''),
    'password' => env($prefix.'_PASSWORD', ''),
    'charset' => 'utf8',
    'prefix' => '',
    'prefix_indexes' => true,
    // TLS is mandatory. TrustServerCertificate may be enabled only in local development.
    'encrypt' => env($prefix.'_ENCRYPT', 'yes'),
    'trust_server_certificate' => env($prefix.'_TRUST_SERVER_CERTIFICATE', 'false'),
    // Return INT/BIT columns as PHP integers (DECIMAL values stay strings: no precision loss).
    'options' => extension_loaded('pdo_sqlsrv') ? [PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE => true] : [],
];

return [

    'default' => env('DB_CONNECTION', 'portal'),

    'connections' => [

        'portal' => $sqlsrv('DB', 'EdisonPortal'),

        // Read-only is guaranteed by the database login (SELECT permissions only).
        'legacy' => $sqlsrv('LEGACY_DB', ''),

    ],

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug((string) env('APP_NAME', 'edison')).'-database-'),
            'persistent' => env('REDIS_PERSISTENT', false),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];
