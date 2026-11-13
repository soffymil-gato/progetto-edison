<?php

declare(strict_types=1);

namespace App\Domain\Identity\Enums;

use App\Domain\Identity\Models\PortalAccount;
use App\Domain\Identity\Models\StaffUser;
use App\Support\Area;

enum AccountType: string
{
    case Staff = 'staff';
    case Customer = 'customer';

    public static function fromArea(Area $area): self
    {
        return match ($area) {
            Area::Admin => self::Staff,
            Area::Portal => self::Customer,
            Area::Public => throw new \LogicException('The public area has no accounts.'),
        };
    }

    public function area(): Area
    {
        return match ($this) {
            self::Staff => Area::Admin,
            self::Customer => Area::Portal,
        };
    }

    /** @return class-string<StaffUser>|class-string<PortalAccount> */
    public function modelClass(): string
    {
        return match ($this) {
            self::Staff => StaffUser::class,
            self::Customer => PortalAccount::class,
        };
    }

    public function guard(): string
    {
        return match ($this) {
            self::Staff => 'staff',
            self::Customer => 'customer',
        };
    }

    public function passwordBroker(): string
    {
        return match ($this) {
            self::Staff => 'staff',
            self::Customer => 'customers',
        };
    }
}
