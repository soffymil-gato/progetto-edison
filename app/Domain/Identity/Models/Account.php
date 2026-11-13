<?php

declare(strict_types=1);

namespace App\Domain\Identity\Models;

use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Identity\Enums\AccountType;
use App\Domain\Identity\Notifications\ResetPasswordNotification;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * Common behaviour of staff users and customer accounts.
 *
 * @property int $id
 * @property string $email
 * @property string|null $password
 * @property AccountStatus $status
 * @property Carbon|null $last_login_at
 * @property Carbon|null $password_changed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
abstract class Account extends Authenticatable implements CanResetPasswordContract
{
    use CanResetPassword;
    use Notifiable;

    /** Remember-me is not offered: sessions always expire. */
    protected $rememberTokenName = '';

    /** @var list<string> */
    protected $hidden = ['password'];

    abstract public function accountType(): AccountType;

    abstract public function displayName(): string;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'status' => AccountStatus::class,
            'last_login_at' => 'datetime',
            'password_changed_at' => 'datetime',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === AccountStatus::Active;
    }

    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new ResetPasswordNotification($token, $this->accountType()));
    }

    /** Emails are stored and compared in lower case. */
    public function setEmailAttribute(string $value): void
    {
        $this->attributes['email'] = mb_strtolower(trim($value));
    }
}
