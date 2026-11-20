<?php

declare(strict_types=1);

namespace App\Domain\Identity\Models;

use App\Domain\Identity\Enums\AccountType;
use App\Domain\Identity\Permissions\DefaultRoles;
use Database\Factories\StaffUserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;

/**
 * Internal user of the admin panel (gestionale).
 *
 * @property string $name
 */
class StaffUser extends Account
{
    /** @use HasFactory<StaffUserFactory> */
    use HasFactory;

    use HasRoles;

    protected $table = 'staff_users';

    protected string $guard_name = 'staff';

    /** @var list<string> */
    protected $fillable = ['name', 'email'];

    public function accountType(): AccountType
    {
        return AccountType::Staff;
    }

    public function displayName(): string
    {
        return $this->name;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(DefaultRoles::SUPER_ADMIN);
    }

    protected static function newFactory(): StaffUserFactory
    {
        return StaffUserFactory::new();
    }
}
