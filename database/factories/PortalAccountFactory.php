<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Identity\Models\PortalAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Synthetic customer accounts for automated tests and local development only.
 * customer_ref values are fictitious ("TEST-…") and do not refer to real customers.
 *
 * @extends Factory<PortalAccount>
 */
final class PortalAccountFactory extends Factory
{
    protected $model = PortalAccount::class;

    public const PASSWORD = 'Password-di-test-123';

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'customer_ref' => 'TEST-'.fake()->unique()->numerify('#####'),
            'email' => fake()->unique()->userName().'@cliente.example.test',
            'username' => null,
            'display_name' => fake()->name(),
            'password' => self::PASSWORD,
            'status' => AccountStatus::Active,
            'password_changed_at' => now(),
        ];
    }

    public function invited(): self
    {
        return $this->state(['status' => AccountStatus::Invited, 'password' => null, 'password_changed_at' => null]);
    }

    public function suspended(): self
    {
        return $this->state(['status' => AccountStatus::Suspended]);
    }
}
