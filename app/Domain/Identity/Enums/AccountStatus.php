<?php

declare(strict_types=1);

namespace App\Domain\Identity\Enums;

enum AccountStatus: string
{
    /** Created by an administrator, waiting for the invitation to be accepted. */
    case Invited = 'invited';
    case Active = 'active';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::Invited => 'Invitato',
            self::Active => 'Attivo',
            self::Suspended => 'Sospeso',
        };
    }
}
