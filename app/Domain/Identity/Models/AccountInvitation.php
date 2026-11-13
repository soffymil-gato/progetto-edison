<?php

declare(strict_types=1);

namespace App\Domain\Identity\Models;

use App\Domain\Identity\Enums\AccountType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property AccountType $account_type
 * @property int $account_id
 * @property string $token_hash
 * @property Carbon $expires_at
 * @property Carbon|null $accepted_at
 * @property Carbon|null $revoked_at
 * @property int|null $created_by_staff_id
 */
class AccountInvitation extends Model
{
    protected $table = 'account_invitations';

    /** @var list<string> */
    protected $fillable = ['account_type', 'account_id', 'token_hash', 'expires_at', 'created_by_staff_id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'account_type' => AccountType::class,
            'account_id' => 'integer',
            'created_by_staff_id' => 'integer',
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now());
    }

    public function account(): ?Account
    {
        $class = $this->account_type->modelClass();

        return $class::query()->find($this->account_id);
    }
}
