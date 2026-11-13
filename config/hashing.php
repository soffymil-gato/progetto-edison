<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Password hashing — Argon2id
|--------------------------------------------------------------------------
|
| Argon2id is mandatory for every account (staff and customers). Parameters
| exceed the OWASP minimum (m=19 MiB, t=2, p=1); tune them on production
| hardware so that a verification takes roughly 200-500 ms.
| Only the test suite lowers them (phpunit.xml) to keep tests fast.
|
*/

return [

    'driver' => 'argon2id',

    'argon' => [
        'memory' => (int) env('ARGON_MEMORY', 65536),
        'threads' => (int) env('ARGON_THREADS', 1),
        'time' => (int) env('ARGON_TIME', 4),
        'verify' => true,
    ],

    'rehash_on_login' => true,

];
