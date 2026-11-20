<?php

declare(strict_types=1);

namespace App\Domain\Audit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use LogicException;

/**
 * Append-only audit event. Updates and deletes are refused by the model and,
 * on SQL Server, by a database trigger.
 *
 * @property int $id
 * @property Carbon $occurred_at
 * @property string $actor_type
 * @property int|null $actor_id
 * @property string|null $actor_label
 * @property string $action
 * @property string|null $subject_type
 * @property string|null $subject_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property array<string, mixed>|null $context
 */
class AuditLog extends Model
{
    public $timestamps = false;

    protected $table = 'audit_logs';

    /** @var list<string> */
    protected $guarded = ['id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'occurred_at' => 'immutable_datetime',
            'actor_id' => 'integer',
            'context' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::updating(static fn (): never => throw new LogicException('Audit log entries are append-only.'));
        static::deleting(static fn (): never => throw new LogicException('Audit log entries are append-only.'));
    }
}
