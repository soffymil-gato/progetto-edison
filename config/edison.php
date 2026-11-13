<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Progetto Edison — application settings
|--------------------------------------------------------------------------
|
| Company data (address, contacts, pickup hours...) are NOT here: they are
| managed from the admin settings and stored in the database. This file only
| holds technical parameters.
|
*/

return [

    /*
    | The three areas of the platform. In production each area should live on
    | its own sub-domain (EDISON_*_DOMAIN): cookies are then isolated by host.
    | Without a domain the area is served under a path prefix and its cookies
    | are scoped to that path.
    */
    'areas' => [
        'public' => [
            'domain' => env('EDISON_PUBLIC_DOMAIN'),
            'prefix' => '',
            'session_cookie' => 'edison_public_session',
            'xsrf_cookie' => 'XSRF-TOKEN',
            'idle_minutes' => 120,
            'absolute_minutes' => null,
            'guard' => null,
        ],
        'portal' => [
            'domain' => env('EDISON_PORTAL_DOMAIN'),
            'prefix' => env('EDISON_PORTAL_PREFIX', 'area-clienti'),
            'session_cookie' => 'edison_portal_session',
            'xsrf_cookie' => 'XSRF-TOKEN-PORTAL',
            'idle_minutes' => (int) env('EDISON_PORTAL_IDLE_MINUTES', 60),
            'absolute_minutes' => (int) env('EDISON_PORTAL_ABSOLUTE_MINUTES', 720),
            'guard' => 'customer',
        ],
        'admin' => [
            'domain' => env('EDISON_ADMIN_DOMAIN'),
            'prefix' => env('EDISON_ADMIN_PREFIX', 'gestionale'),
            'session_cookie' => 'edison_admin_session',
            'xsrf_cookie' => 'XSRF-TOKEN-ADMIN',
            'idle_minutes' => (int) env('EDISON_ADMIN_IDLE_MINUTES', 30),
            'absolute_minutes' => (int) env('EDISON_ADMIN_ABSOLUTE_MINUTES', 720),
            'guard' => 'staff',
        ],
    ],

    'auth' => [
        // Attempts per login identifier (existing or not, so accounts cannot be enumerated).
        'max_attempts_per_identifier' => 10,
        'identifier_decay_seconds' => 900,
        // Attempts per IP address.
        'max_attempts_per_ip' => 30,
        'ip_decay_seconds' => 60,
        'invitation_ttl_hours' => 72,
        'password_min_length' => 12,
        'password_max_length' => 128,
    ],

    'contact' => [
        'max_per_hour_per_ip' => 5,
    ],

    'brand' => [
        // Path (relative to public/) of the OFFICIAL logo supplied by Edison.
        // Null = not supplied yet: a clearly marked placeholder is shown and
        // the release check fails.
        'logo_path' => env('EDISON_LOGO_PATH'),
    ],

    'content' => [
        // Marker used for every piece of content still to be supplied by Edison.
        'placeholder_marker' => '[DA FORNIRE',
    ],

    'retention' => [
        // Months after which contact requests are deleted. Null = not defined yet
        // (to be decided with the data controller / DPO): no automatic purge.
        'contact_requests_months' => env('EDISON_RETENTION_CONTACT_MONTHS'),
    ],

];
