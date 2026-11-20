<?php

declare(strict_types=1);

namespace App\Domain\Contact;

enum ContactRequestStatus: string
{
    case New = 'new';
    case Handled = 'handled';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Da gestire',
            self::Handled => 'Gestita',
        };
    }
}
