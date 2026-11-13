<?php

declare(strict_types=1);

namespace App\Domain\Identity\Services;

use App\Domain\Identity\Models\Account;

final readonly class LoginResult
{
    private function __construct(
        public bool $succeeded,
        public ?Account $account = null,
        public ?string $message = null,
    ) {}

    public static function success(Account $account): self
    {
        return new self(true, $account);
    }

    public static function failure(string $message): self
    {
        return new self(false, null, $message);
    }
}
