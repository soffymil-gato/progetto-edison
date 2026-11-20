<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\AuditLogger;
use App\Domain\Contact\ContactRequest;
use App\Domain\Contact\ContactRequestStatus;
use App\Domain\Identity\Models\StaffUser;
use App\Http\Controllers\Controller;
use App\Support\Like;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

final class ContactRequestController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(ContactRequestStatus::class)],
        ]);

        $requests = ContactRequest::query()
            ->when($filters['q'] ?? null, fn (Builder $q, string $term) => $q->where(fn (Builder $w) => $w
                ->where('name', 'like', Like::contains($term))
                ->orWhere('email', 'like', Like::contains($term))
                ->orWhere('company', 'like', Like::contains($term))
                ->orWhere('subject', 'like', Like::contains($term))))
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (ContactRequest $r): array => [
                'id' => $r->id,
                'name' => $r->name,
                'company' => $r->company,
                'email' => $r->email,
                'subject' => $r->subject,
                'status' => $r->status->value,
                'statusLabel' => $r->status->label(),
                'createdAt' => $r->created_at->toIso8601String(),
                'showUrl' => route('admin.contact-requests.show', $r),
            ]);

        return Inertia::render('Admin/ContactRequests/Index', [
            'requests' => $requests,
            'filters' => ['q' => $filters['q'] ?? '', 'status' => $filters['status'] ?? ''],
            'statuses' => array_map(fn (ContactRequestStatus $s): array => ['value' => $s->value, 'label' => $s->label()], ContactRequestStatus::cases()),
            'urls' => ['index' => route('admin.contact-requests.index')],
        ]);
    }

    public function show(Request $request, ContactRequest $contactRequest): Response
    {
        /** @var StaffUser $actor */
        $actor = $request->user();
        $handledBy = $contactRequest->handled_by_staff_id !== null ? StaffUser::query()->find($contactRequest->handled_by_staff_id) : null;

        return Inertia::render('Admin/ContactRequests/Show', [
            'request' => [
                'id' => $contactRequest->id,
                'name' => $contactRequest->name,
                'company' => $contactRequest->company,
                'email' => $contactRequest->email,
                'phone' => $contactRequest->phone,
                'subject' => $contactRequest->subject,
                'message' => $contactRequest->message,
                'status' => $contactRequest->status->value,
                'statusLabel' => $contactRequest->status->label(),
                'createdAt' => $contactRequest->created_at->toIso8601String(),
                'privacyAcceptedAt' => $contactRequest->privacy_accepted_at->toIso8601String(),
                'handledAt' => $contactRequest->handled_at?->toIso8601String(),
                'handledBy' => $handledBy?->name,
                'handlingNote' => $contactRequest->handling_note,
            ],
            'can' => ['manage' => $actor->can('contact_requests.manage')],
            'urls' => [
                'back' => route('admin.contact-requests.index'),
                'handled' => route('admin.contact-requests.handled', $contactRequest),
            ],
        ]);
    }

    public function markHandled(Request $request, ContactRequest $contactRequest): RedirectResponse
    {
        $data = $request->validate(['note' => ['nullable', 'string', 'max:1000']], [], ['note' => 'nota']);

        if ($contactRequest->status === ContactRequestStatus::Handled) {
            return back()->with('error', 'La richiesta risulta già gestita.');
        }

        /** @var StaffUser $actor */
        $actor = $request->user();
        DB::transaction(function () use ($contactRequest, $data, $actor): void {
            $contactRequest->status = ContactRequestStatus::Handled;
            $contactRequest->handled_at = now();
            $contactRequest->handled_by_staff_id = $actor->id;
            $contactRequest->handling_note = $data['note'] ?? null;
            $contactRequest->save();

            $this->audit->record('contact_request.handled', $actor, $contactRequest);
        });

        return back()->with('success', 'Richiesta segnata come gestita.');
    }
}
