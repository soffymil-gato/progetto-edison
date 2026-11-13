<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\AuditActions;
use App\Domain\Audit\AuditLog;
use App\Domain\Contact\ContactRequest;
use App\Domain\Contact\ContactRequestStatus;
use App\Domain\Content\ReleaseReadiness;
use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Identity\Models\StaffUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin overview. Only real figures from the platform database are shown:
 * order, customer and product KPIs arrive with the integration of the company
 * SQL Server database and are not simulated.
 */
final class DashboardController extends Controller
{
    public function __invoke(Request $request, ReleaseReadiness $readiness): Response
    {
        /** @var StaffUser $user */
        $user = $request->user();

        $widgets = [];

        if ($user->can('contact_requests.view')) {
            $widgets['contactRequests'] = [
                'new' => ContactRequest::query()->where('status', ContactRequestStatus::New->value)->count(),
                'last30Days' => ContactRequest::query()->where('created_at', '>=', now()->subDays(30))->count(),
                'url' => route('admin.contact-requests.index', ['status' => ContactRequestStatus::New->value]),
            ];
        }

        if ($user->can('users.view')) {
            $widgets['staff'] = [
                'active' => StaffUser::query()->where('status', AccountStatus::Active->value)->count(),
                'invited' => StaffUser::query()->where('status', AccountStatus::Invited->value)->count(),
                'url' => route('admin.users.index'),
            ];
        }

        if ($user->can('audit.view')) {
            $widgets['recentActivity'] = AuditLog::query()
                ->orderByDesc('occurred_at')
                ->orderByDesc('id')
                ->limit(6)
                ->get()
                ->map(fn (AuditLog $log): array => [
                    'id' => $log->id,
                    'occurredAt' => $log->occurred_at->toIso8601String(),
                    'actionLabel' => AuditActions::label($log->action),
                    'actorType' => $log->actor_type,
                    'actorLabel' => $log->actor_label,
                    'url' => route('admin.audit.show', $log),
                ]);
            $widgets['auditUrl'] = route('admin.audit.index');
        }

        return Inertia::render('Admin/Dashboard', [
            'widgets' => $widgets,
            'readiness' => $user->can('settings.manage') ? [
                'checks' => $readiness->checks(),
                'settingsUrl' => route('admin.settings.edit'),
            ] : null,
        ]);
    }
}
