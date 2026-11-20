<?php

declare(strict_types=1);

namespace App\Domain\Audit;

use App\Domain\Identity\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Writes audit events. Context values are sanitised: keys that may carry
 * secrets (passwords, tokens...) are always dropped.
 */
final class AuditLogger
{
    private const FORBIDDEN_KEY_FRAGMENTS = ['password', 'token', 'secret', 'remember', 'hash'];

    public function __construct(private readonly Request $request) {}

    /**
     * @param  Model|array{0: string, 1: string|int}|null  $subject
     * @param  array<string, mixed>  $context
     */
    public function record(string $action, ?Account $actor = null, Model|array|null $subject = null, array $context = []): AuditLog
    {
        [$subjectType, $subjectId] = $this->subject($subject);

        return AuditLog::query()->create([
            'occurred_at' => now(),
            'actor_type' => $actor?->accountType()->value ?? ($this->request->hasSession() ? 'anonymous' : 'system'),
            'actor_id' => $actor?->getKey(),
            'actor_label' => $actor !== null ? Str::limit($actor->email, 254, '') : null,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'ip_address' => $this->request->ip(),
            'user_agent' => Str::limit((string) $this->request->userAgent(), 250, ''),
            'context' => $context === [] ? null : $this->sanitise($context),
        ]);
    }

    /**
     * @param  Model|array{0: string, 1: string|int}|null  $subject
     * @return array{0: string|null, 1: string|null}
     */
    private function subject(Model|array|null $subject): array
    {
        if ($subject instanceof Model) {
            return [Str::snake(class_basename($subject)), (string) $subject->getKey()];
        }

        if (is_array($subject)) {
            return [(string) $subject[0], (string) $subject[1]];
        }

        return [null, null];
    }

    /**
     * @param  array<array-key, mixed>  $context
     * @return array<array-key, mixed>
     */
    private function sanitise(array $context): array
    {
        $clean = [];
        foreach ($context as $key => $value) {
            if (is_string($key) && Str::contains(Str::lower($key), self::FORBIDDEN_KEY_FRAGMENTS)) {
                continue;
            }
            $clean[$key] = is_array($value) ? $this->sanitise($value) : $value;
        }

        return $clean;
    }
}
