<?php

declare(strict_types=1);

namespace App\Domain\Settings;

use App\Domain\Audit\AuditLogger;
use App\Domain\Identity\Models\StaffUser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Reads and writes administrative settings. Every change is audited with the
 * list of modified fields and their previous and new values.
 */
final class SettingsRepository
{
    private const CACHE_PREFIX = 'settings.';

    public function __construct(private readonly AuditLogger $audit) {}

    /** @return array<string, string|null> Every field of the group, null when not filled. */
    public function get(string $group): array
    {
        $this->assertGroup($group);

        /** @var array<string, string|null> $stored */
        $stored = Cache::rememberForever(self::CACHE_PREFIX.$group, static function () use ($group): array {
            $setting = Setting::query()->find($group);

            return $setting !== null ? $setting->value : [];
        });

        $values = [];
        foreach (array_keys(SettingsSchema::groups()[$group]['fields']) as $field) {
            $value = $stored[$field] ?? null;
            $values[$field] = is_string($value) && trim($value) !== '' ? $value : null;
        }

        return $values;
    }

    public function value(string $group, string $field): ?string
    {
        return $this->get($group)[$field] ?? null;
    }

    /**
     * @param  array<string, string|null>  $input
     * @return list<string> Names of the changed fields.
     */
    public function update(string $group, array $input, StaffUser $by): array
    {
        $this->assertGroup($group);

        $before = $this->get($group);
        $after = [];
        foreach (array_keys(SettingsSchema::groups()[$group]['fields']) as $field) {
            $value = $input[$field] ?? null;
            $value = is_string($value) ? trim($value) : null;
            $after[$field] = $value === '' ? null : $value;
        }

        $changed = array_keys(array_filter($after, static fn (?string $value, string $field): bool => $value !== $before[$field], ARRAY_FILTER_USE_BOTH));
        if ($changed === []) {
            return [];
        }

        DB::transaction(function () use ($group, $after, $before, $changed, $by): void {
            Setting::query()->updateOrCreate(['key' => $group], ['value' => $after, 'updated_by_staff_id' => $by->id]);

            $this->audit->record('settings.updated', $by, ['settings', $group], [
                'group' => $group,
                'changes' => array_map(static fn (string $field): array => [
                    'field' => $field,
                    'from' => $before[$field],
                    'to' => $after[$field],
                ], $changed),
            ]);
        });

        Cache::forget(self::CACHE_PREFIX.$group);

        return $changed;
    }

    private function assertGroup(string $group): void
    {
        if (! SettingsSchema::hasGroup($group)) {
            throw new InvalidArgumentException("Unknown settings group [{$group}].");
        }
    }
}
