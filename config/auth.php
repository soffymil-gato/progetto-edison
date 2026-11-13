<?php

declare(strict_types=1);

use App\Domain\Identity\Models\PortalAccount;
use App\Domain\Identity\Models\StaffUser;

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
|
| Two fully separate guards:
| - "staff": internal users of the admin panel (gestionale);
| - "customer": customer accounts of the reserved area (portale clienti).
| A customer session is never valid in the admin panel and vice versa.
|
*/

return [

    'defaults' => [
        'guard' => 'staff',
        'passwords' => 'staff',
    ],

    'guards' => [
        'staff' => [
            'driver' => 'session',
            'provider' => 'staff',
        ],
        'customer' => [
            'driver' => 'session',
            'provider' => 'customers',
        ],
    ],

    'providers' => [
        'staff' => [
            'driver' => 'eloquent',
            'model' => StaffUser::class,
        ],
        'customers' => [
            'driver' => 'eloquent',
            'model' => PortalAccount::class,
        ],
    ],

    'passwords' => [
        'staff' => [
            'provider' => 'staff',
            'table' => 'staff_password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
        'customers' => [
            'provider' => 'customers',
            'table' => 'portal_password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 900,

];
