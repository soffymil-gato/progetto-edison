<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Identity\Models\StaffUser;
use App\Domain\Identity\Permissions\DefaultRoles;
use App\Domain\Identity\Permissions\RbacSynchronizer;
use App\Domain\Identity\Services\StaffUserAdministration;
use App\Http\Controllers\Controller;
use App\Support\Like;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

final class StaffUserController extends Controller
{
    public function __construct(private readonly StaffUserAdministration $admin) {}

    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(AccountStatus::class)],
            'role' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', Rule::in(['name', '-name', 'last_login_at', '-last_login_at', 'created_at', '-created_at'])],
        ]);

        $sort = $filters['sort'] ?? 'name';
        $users = StaffUser::query()
            ->with('roles')
            ->when($filters['q'] ?? null, fn (Builder $q, string $term) => $q->where(fn (Builder $w) => $w
                ->where('name', 'like', Like::contains($term))
                ->orWhere('email', 'like', Like::contains($term))))
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q->where('status', $status))
            ->when($filters['role'] ?? null, fn (Builder $q, string $role) => $q->whereHas('roles', fn (Builder $r) => $r->where('name', $role)))
            ->orderBy(ltrim($sort, '-'), str_starts_with($sort, '-') ? 'desc' : 'asc')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (StaffUser $user): array => $this->present($user));

        /** @var StaffUser $actor */
        $actor = $request->user();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => ['q' => $filters['q'] ?? '', 'status' => $filters['status'] ?? '', 'role' => $filters['role'] ?? '', 'sort' => $sort],
            'roles' => $this->roleOptions(),
            'statuses' => $this->statusOptions(),
            'can' => ['manage' => $actor->can('users.manage')],
            'urls' => ['index' => route('admin.users.index'), 'create' => route('admin.users.create')],
        ]);
    }

    public function create(Request $request): Response
    {
        /** @var StaffUser $actor */
        $actor = $request->user();

        return Inertia::render('Admin/Users/Form', [
            'user' => null,
            'assignableRoles' => $this->roleOptions($this->admin->assignableRoles($actor)),
            'urls' => ['submit' => route('admin.users.store'), 'back' => route('admin.users.index')],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email:rfc', 'max:254', Rule::unique('staff_users', 'email')],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', Rule::exists('roles', 'name')->where('guard_name', RbacSynchronizer::GUARD)],
        ], [], ['name' => 'nome', 'roles' => 'ruoli']);

        /** @var StaffUser $actor */
        $actor = $request->user();
        $user = $this->admin->create($actor, $data['name'], mb_strtolower($data['email']), array_values($data['roles']));

        return redirect()->route('admin.users.edit', $user)->with('success', 'Utente creato. È stato inviato l\'invito per attivare l\'account.');
    }

    public function edit(Request $request, StaffUser $staffUser): Response
    {
        /** @var StaffUser $actor */
        $actor = $request->user();

        return Inertia::render('Admin/Users/Form', [
            'user' => $this->present($staffUser) + [
                'isSelf' => $actor->is($staffUser),
                'canModify' => ! $staffUser->isSuperAdmin() || $actor->isSuperAdmin(),
                'hasPassword' => $staffUser->password !== null,
            ],
            'assignableRoles' => $this->roleOptions($this->admin->assignableRoles($actor)),
            'urls' => [
                'submit' => route('admin.users.update', $staffUser),
                'back' => route('admin.users.index'),
                'suspend' => route('admin.users.suspend', $staffUser),
                'reactivate' => route('admin.users.reactivate', $staffUser),
                'invitation' => route('admin.users.invitation', $staffUser),
                'passwordReset' => route('admin.users.password-reset', $staffUser),
            ],
        ]);
    }

    public function update(Request $request, StaffUser $staffUser): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', Rule::exists('roles', 'name')->where('guard_name', RbacSynchronizer::GUARD)],
        ], [], ['name' => 'nome', 'roles' => 'ruoli']);

        /** @var StaffUser $actor */
        $actor = $request->user();
        $this->admin->update($actor, $staffUser, $data['name'], array_values($data['roles']));

        return back()->with('success', 'Modifiche salvate.');
    }

    public function suspend(Request $request, StaffUser $staffUser): RedirectResponse
    {
        /** @var StaffUser $actor */
        $actor = $request->user();
        $this->admin->suspend($actor, $staffUser);

        return back()->with('success', 'Utente sospeso. Le sue sessioni sono state chiuse.');
    }

    public function reactivate(Request $request, StaffUser $staffUser): RedirectResponse
    {
        /** @var StaffUser $actor */
        $actor = $request->user();
        $this->admin->reactivate($actor, $staffUser);

        return back()->with('success', 'Utente riattivato.');
    }

    public function resendInvitation(Request $request, StaffUser $staffUser): RedirectResponse
    {
        /** @var StaffUser $actor */
        $actor = $request->user();
        $this->admin->resendInvitation($actor, $staffUser);

        return back()->with('success', 'Nuovo invito inviato. Il link precedente non è più valido.');
    }

    public function sendPasswordReset(Request $request, StaffUser $staffUser): RedirectResponse
    {
        /** @var StaffUser $actor */
        $actor = $request->user();
        $this->admin->sendPasswordReset($actor, $staffUser);

        return back()->with('success', 'Email di reimpostazione password inviata all\'utente.');
    }

    /** @return array<string, mixed> */
    private function present(StaffUser $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'status' => $user->status->value,
            'statusLabel' => $user->status->label(),
            'roles' => $user->getRoleNames()->values()->all(),
            'roleLabels' => $user->getRoleNames()->map(fn (string $name): string => DefaultRoles::label($name))->values()->all(),
            'lastLoginAt' => $user->last_login_at?->toIso8601String(),
            'createdAt' => $user->created_at?->toIso8601String(),
            'editUrl' => route('admin.users.edit', $user),
        ];
    }

    /**
     * @param  list<string>|null  $only
     * @return list<array{value: string, label: string}>
     */
    private function roleOptions(?array $only = null): array
    {
        $options = [];
        foreach (Role::query()->where('guard_name', RbacSynchronizer::GUARD)->orderBy('id')->pluck('name') as $name) {
            if ($only === null || in_array($name, $only, true)) {
                $options[] = ['value' => $name, 'label' => DefaultRoles::label($name)];
            }
        }

        return $options;
    }

    /** @return list<array{value: string, label: string}> */
    private function statusOptions(): array
    {
        return array_map(fn (AccountStatus $s): array => ['value' => $s->value, 'label' => $s->label()], AccountStatus::cases());
    }
}
