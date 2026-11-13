<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\AuditActions;
use App\Domain\Audit\AuditLog;
use App\Http\Controllers\Controller;
use App\Support\Like;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Read-only consultation of the audit log.
 */
final class AuditLogController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'action' => ['nullable', 'string', Rule::in(array_keys(AuditActions::labels()))],
            'actor_type' => ['nullable', Rule::in(['staff', 'customer', 'anonymous', 'system'])],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $tz = 'Europe/Rome';
        $logs = AuditLog::query()
            ->when($filters['q'] ?? null, fn (Builder $q, string $term) => $q->where('actor_label', 'like', Like::contains($term)))
            ->when($filters['action'] ?? null, fn (Builder $q, string $action) => $q->where('action', $action))
            ->when($filters['actor_type'] ?? null, fn (Builder $q, string $type) => $q->where('actor_type', $type))
            ->when($filters['from'] ?? null, fn (Builder $q, string $from) => $q->where('occurred_at', '>=', Carbon::parse($from, $tz)->startOfDay()->utc()))
            ->when($filters['to'] ?? null, fn (Builder $q, string $to) => $q->where('occurred_at', '<=', Carbon::parse($to, $tz)->endOfDay()->utc()))
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString()
            ->through(fn (AuditLog $log): array => $this->present($log));

        return Inertia::render('Admin/Audit/Index', [
            'logs' => $logs,
            'filters' => [
                'q' => $filters['q'] ?? '',
                'action' => $filters['action'] ?? '',
                'actor_type' => $filters['actor_type'] ?? '',
                'from' => $filters['from'] ?? '',
                'to' => $filters['to'] ?? '',
            ],
            'actions' => collect(AuditActions::labels())->map(fn (string $label, string $value): array => ['value' => $value, 'label' => $label])->values(),
            'urls' => ['index' => route('admin.audit.index')],
        ]);
    }

    public function show(AuditLog $auditLog): Response
    {
        return Inertia::render('Admin/Audit/Show', [
            'log' => $this->present($auditLog) + [
                'context' => $auditLog->context,
                'ipAddress' => $auditLog->ip_address,
                'userAgent' => $auditLog->user_agent,
            ],
            'urls' => ['back' => route('admin.audit.index')],
        ]);
    }

    /** @return array<string, mixed> */
    private function present(AuditLog $log): array
    {
        return [
            'id' => $log->id,
            'occurredAt' => $log->occurred_at->toIso8601String(),
            'action' => $log->action,
            'actionLabel' => AuditActions::label($log->action),
            'actorType' => $log->actor_type,
            'actorLabel' => $log->actor_label,
            'subject' => $log->subject_type !== null ? $log->subject_type.' #'.$log->subject_id : null,
            'showUrl' => route('admin.audit.show', $log),
        ];
    }
}
