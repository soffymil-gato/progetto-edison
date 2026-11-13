<?php

declare(strict_types=1);

namespace App\Domain\Identity\Models;

use App\Domain\Identity\Enums\AccountType;
use Database\Factories\PortalAccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Customer account of the reserved area.
 *
 * The account is linked to the customer of the company database through
 * customer_ref, resolved ONLY on the server side: the browser never chooses
 * which customer it acts for.
 *
 * @property string $customer_ref
 * @property string|null $username
 * @property string $display_name
 */
class PortalAccount extends Account
{
    /** @use HasFactory<PortalAccountFactory> */
    use HasFactory;

    protected $table = 'portal_accounts';

    /** @var list<string> */
    protected $fillable = ['customer_ref', 'email', 'username', 'display_name'];

    public function accountType(): AccountType
    {
        return AccountType::Customer;
    }

    public function displayName(): string
    {
        return $this->display_name;
    }

    public function setUsernameAttribute(?string $value): void
    {
        $value = $value === null ? null : mb_strtolower(trim($value));
        $this->attributes['username'] = $value === '' ? null : $value;
    }

    protected static function newFactory(): PortalAccountFactory
    {
        return PortalAccountFactory::new();
    }
}
