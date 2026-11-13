<?php

declare(strict_types=1);

namespace App\Domain\Identity\Services;

use Illuminate\Validation\Rules\Password;

/**
 * Password policy (NIST SP 800-63B approach): length over composition rules.
 * A breached-password check (k-anonymity service) is NOT enabled: it requires
 * an external call and is pending approval.
 */
final class PasswordRules
{
    /** @return list<mixed> */
    public static function forNewPassword(): array
    {
        return [
            'required',
            'string',
            'confirmed',
            Password::min((int) config('edison.auth.password_min_length'))
                ->max((int) config('edison.auth.password_max_length')),
        ];
    }
}
