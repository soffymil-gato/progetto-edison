<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Identity\Models\StaffUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Synthetic staff users for automated tests and local development only.
 *
 * @extends Factory<StaffUser>
 */
final class StaffUserFactory extends Factory
{
    protected $model = StaffUser::class;

    public const PASSWORD = 'Password-di-test-123';

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->userName().'@example.test',
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

    public function withRole(string $role): self
    {
        return $this->afterCreating(static fn (StaffUser $user) => $user->assignRole($role));
    }
}
